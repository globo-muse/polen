<?php

namespace ProfilePress\Core\Membership\Models\Customer;

use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\Membership\Models\AbstractModel;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Models\Order\OrderEntity as OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity as SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Repositories\CustomerRepository;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;

/**
 * @property int $id
 * @property string $user_id
 * @property int $purchase_count
 * @property string $total_spend
 * @property string $private_note
 * @property string $date_created
 */
class CustomerEntity extends AbstractModel implements ModelInterface
{
    protected $id = 0;

    protected $user_id = 0;

    protected $total_spend = 0;

    protected $purchase_count = 0;

    protected $private_note = '';

    protected $last_login = '';

    protected $date_created = '';

    /** @var false|\WP_User|null */
    protected $wp_user = null;

    public function __construct($data = [])
    {
        if (is_array($data) && ! empty($data)) {

            foreach ($data as $key => $value) {
                $this->$key = $value;
            }

            $this->wp_user = get_user_by('id', $this->user_id);
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return ! empty($this->id);
    }

    public function user_exists()
    {
        return ! empty($this->user_id);
    }

    public function get_id()
    {
        return absint($this->id);
    }

    public function get_user_id()
    {
        return absint($this->user_id);
    }

    public function get_first_name()
    {
        return $this->wp_user->first_name;
    }

    public function get_last_name()
    {
        return $this->wp_user->last_name;
    }

    public function get_name()
    {
        $name = [];

        if ( ! empty($this->wp_user->first_name)) {
            $name[] = $this->wp_user->first_name;
        }

        if ( ! empty($this->wp_user->last_name)) {
            $name[] = $this->wp_user->last_name;
        }

        if (empty($name) && isset($this->wp_user->user_login)) $name[] = $this->wp_user->user_login;

        if (empty($name)) $name[] = esc_html__('No Connected User', 'wp-user-avatar');

        return implode(' ', $name);
    }

    public function get_email()
    {
        return isset($this->wp_user->user_email) ? $this->wp_user->user_email : '';
    }

    public function get_wp_user()
    {
        return $this->wp_user;
    }

    /**
     * @return string
     */
    public function get_private_note()
    {
        return sanitize_textarea_field($this->private_note);
    }

    public function get_last_login()
    {
        if (empty($this->last_login)) return '';

        return ppress_format_date_time($this->last_login);
    }

    public function get_date_created()
    {
        return ! empty($this->date_created) ? ppress_format_date($this->date_created) : '';
    }

    public function is_active($include_trial = true)
    {
        $status = [SubscriptionStatus::ACTIVE, SubscriptionStatus::COMPLETED];

        if ($include_trial) {
            $status[] = SubscriptionStatus::TRIALLING;
        }

        $subs = $this->get_subscriptions($status);

        return apply_filters('ppress_customer_is_active', ! empty($subs), $this);
    }

    /**
     * @param array $args
     * @param bool $count
     *
     * @return OrderEntity[]|int
     */
    public function get_orders($args = [], $count = false)
    {
        if ( ! $this->exists()) {
            return $count === true ? 0 : [];
        }

        $args['number']      = isset($args['number']) ? $args['number'] : 0;
        $args['customer_id'] = $this->id;

        return OrderRepository::init()->retrieveBy($args, $count);
    }

    /**
     * @param array $status
     * @param array $args
     * @param bool $count
     *
     * @return SubscriptionEntity[]|int
     */
    public function get_subscriptions($status = [], $args = [], $count = false)
    {
        if ( ! $this->exists()) {
            return $count === true ? 0 : [];
        }

        $args['number']      = isset($args['number']) ? $args['number'] : 0;
        $args['customer_id'] = $this->id;
        $args['status']      = $status;

        $cache_key = md5(wp_json_encode($args) . (string)$count);

        $subscriptions = wp_cache_get($cache_key);

        if (false === $subscriptions) {
            $subscriptions = SubscriptionRepository::init()->retrieveBy($args, $count);
            wp_cache_set($cache_key, $subscriptions, '', MINUTE_IN_SECONDS);
        }

        return $subscriptions;
    }

    /**
     * @param $include_trial
     *
     * @return array
     */
    public function get_active_subscriptions($include_trial = true)
    {
        $statuses = [
            SubscriptionStatus::ACTIVE,
            SubscriptionStatus::COMPLETED,
            SubscriptionStatus::CANCELLED
        ];

        if ($include_trial) $statuses[] = SubscriptionStatus::TRIALLING;

        $subs = $this->get_subscriptions($statuses);

        $result = [];

        if ( ! empty($subs)) {

            foreach ($subs as $sub) {
                if ($sub->is_active() && ! $sub->is_expired()) $result[] = $sub;
            }
        }

        return $result;
    }

    /**
     * @param $plan_id
     *
     * @return bool
     */
    public function has_active_subscription($plan_id = null)
    {
        $active_subs = $this->get_active_subscriptions();

        if (is_null($plan_id)) return ! empty($active_subs);

        $plan_id = (int)$plan_id;

        foreach ($active_subs as $sub) {

            if ((int)$sub->plan_id === $plan_id) return true;
        }

        return false;
    }

    /**
     * @param string|null $field if specified, retrieve a single field
     *
     * @return array|string
     */
    public function get_billing_details($field = null)
    {
        $billing_fields = array_keys(CheckoutFields::billing_fields());

        $fields = [];

        foreach ($billing_fields as $billing_field) {
            $fields[$billing_field] = get_user_meta($this->user_id, $billing_field, true);
        }

        return ! is_null($field) ? $fields[$field] : $fields;
    }

    /**
     * @return false|int
     */
    public function save()
    {
        if ($this->exists()) {

            $result = CustomerRepository::init()->update($this);

            do_action('ppress_membership_update_customer', $result, $this);

            return $result;
        }

        $result = CustomerRepository::init()->add($this);

        do_action('ppress_membership_add_customer', $result, $this);

        return $result;
    }

    /**
     * Recalculate stats for this customer.
     */
    public function recalculate_stats()
    {
        if ($this->exists()) {
            $this->purchase_count = OrderRepository::init()->retrieveBy([
                'customer_id' => $this->id,
                'status'      => [OrderStatus::COMPLETED]
            ], true);

            $this->total_spend = OrderRepository::init()->get_customer_total_spend(
                $this->id
            );

            $this->save();
        }
    }

    /**
     * @param $meta_key
     * @param bool $single
     *
     * @return array|false|mixed
     */
    public function get_meta($meta_key, $single = true)
    {
        return get_user_meta(
            $this->get_user_id(),
            $meta_key,
            $single
        );
    }

    /**
     * @param $meta_key
     * @param $meta_value
     *
     * @return false|int
     */
    public function add_meta($meta_key, $meta_value)
    {
        return add_user_meta(
            $this->get_user_id(),
            $meta_key,
            $meta_value
        );
    }

    /**
     * @param $meta_key
     * @param $meta_value
     *
     * @return bool|int
     */
    public function update_meta($meta_key, $meta_value)
    {
        return update_user_meta(
            $this->get_user_id(),
            $meta_key,
            $meta_value
        );
    }

    /**
     * @param $meta_key
     * @param $meta_value
     *
     * @return bool
     */
    public function delete_meta($meta_key, $meta_value = '')
    {
        return delete_user_meta(
            $this->get_user_id(),
            $meta_key,
            $meta_value
        );
    }
}