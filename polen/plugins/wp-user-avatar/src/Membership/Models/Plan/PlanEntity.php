<?php

namespace ProfilePress\Core\Membership\Models\Plan;

use ProfilePress\Core\Membership\Models\AbstractModel;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionTrialPeriod;
use ProfilePress\Core\Membership\Repositories\PlanRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\OrderService;

/**
 * @property int $id
 * @property string $name
 * @property string $order_note
 * @property string $description
 * @property string $price
 * @property string $billing_frequency
 * @property string $subscription_length
 * @property int $total_payments
 * @property string $signup_fee
 * @property string $free_trial
 */
class PlanEntity extends AbstractModel implements ModelInterface
{
    protected $id = 0;

    protected $name = '';

    protected $description = '';

    protected $order_note = '';

    protected $price = '0';

    protected $billing_frequency = SubscriptionBillingFrequency::MONTHLY;

    protected $subscription_length = 'renew_indefinitely';

    // 0 indicates renew indefinitely.
    protected $total_payments = 0;

    protected $signup_fee = '0';

    protected $free_trial = SubscriptionTrialPeriod::DISABLED;

    protected $status = 'false';

    protected $meta_data = [];

    public function __construct($data = [])
    {
        if (is_array($data) && ! empty($data)) {

            foreach ($data as $key => $value) {
                $this->$key = $value;

                if ($key == 'meta_data') {
                    $this->meta_data = ! empty($value) && ppress_is_json($value) ? \json_decode($value, true) : [];
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return ! empty($this->id);
    }

    public function get_id()
    {
        return absint($this->id);
    }

    public function get_name()
    {
        return $this->name;
    }

    public function is_active()
    {
        return $this->status == 'true';
    }

    public function is_recurring()
    {
        return ! empty($this->billing_frequency) && $this->billing_frequency != 'lifetime';
    }

    public function has_free_trial()
    {
        return $this->is_recurring() &&
               $this->free_trial != SubscriptionTrialPeriod::DISABLED &&
               ! OrderService::init()->customer_has_trialled($this->id);
    }

    public function has_signup_fee()
    {
        return ! Calculator::init($this->signup_fee)->isNegativeOrZero();
    }

    public function get_description()
    {
        return apply_filters('ppress_subscription_plan_description', wpautop($this->description), $this->get_id());
    }

    /**
     * @return string
     */
    public function get_price()
    {
        return ppress_sanitize_amount($this->price);
    }

    public function get_billing_frequency()
    {
        return $this->billing_frequency;
    }

    public function get_subscription_length()
    {
        return $this->subscription_length;
    }

    public function get_total_payments()
    {
        return absint($this->total_payments);
    }

    /**
     * @return string
     */
    public function get_signup_fee()
    {
        return ppress_sanitize_amount($this->signup_fee);
    }

    public function get_free_trial()
    {
        return sanitize_text_field($this->free_trial);
    }

    public function get_edit_plan_url()
    {
        return add_query_arg(['ppress_subp_action' => 'edit', 'id' => $this->id], PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);
    }

    /**
     * @return false|int
     */
    public function save()
    {
        if ($this->id > 0) {

            $result = PlanRepository::init()->update($this);

            do_action('ppress_membership_update_plan', $result, $this);

            return $result;
        }

        $result = PlanRepository::init()->add($this);

        do_action('ppress_membership_add_plan', $result, $this);

        return $result;
    }

    /**
     * @return false|string
     */
    public function get_checkout_url()
    {
        return ppress_plan_checkout_url($this->get_id());
    }

    public function update_meta($meta_key, $meta_value)
    {
        $this->meta_data[$meta_key] = $meta_value;

        return PlanRepository::init()->updateColumn(
            $this->get_id(),
            'meta_data',
            \wp_json_encode($this->meta_data)
        );
    }

    /**
     * @param $meta_key
     *
     * @return false|mixed
     */
    public function get_meta($meta_key)
    {
        return ppress_var($this->meta_data, $meta_key);
    }

    /**
     * @param $meta_key
     *
     * @return false|int
     */
    public function delete_meta($meta_key)
    {
        unset($this->meta_data[$meta_key]);

        return PlanRepository::init()->updateColumn(
            $this->get_id(),
            'meta_data',
            \wp_json_encode($this->meta_data)
        );

    }

    /**
     * @return false|int
     */
    public function activate()
    {
        return PlanRepository::init()->updateColumn($this->id, 'status', 'true');
    }

    /**
     * @return false|int
     */
    public function deactivate()
    {
        return PlanRepository::init()->updateColumn($this->id, 'status', 'false');
    }
}