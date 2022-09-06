<?php

namespace ProfilePress\Core\Membership\Models\Order;

use ProfilePress\Core\Membership\Models\AbstractModel;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\PaymentMethods\AbstractPaymentMethod;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Services\OrderService;

/**
 * @property int $id
 * @property string $order_key
 * @property int $plan_id
 * @property int $customer_id
 * @property int $subscription_id
 * @property string $order_type
 * @property string $transaction_id
 * @property string $payment_method
 * @property string $status
 * @property string $coupon_code
 * @property string $subtotal
 * @property string $tax
 * @property string $tax_rate
 * @property string $discount
 * @property string $total
 * @property array $billing_address
 * @property array $billing_city
 * @property array $billing_state
 * @property array $billing_postcode
 * @property array $billing_country
 * @property array $billing_phone
 * @property string $mode
 * @property string $currency
 * @property string $ip_address
 * @property string $date_created
 * @property string $date_completed
 */
class OrderEntity extends AbstractModel implements ModelInterface
{
    const EU_VAT_NUMBER = 'eu_vat_number';
    const EU_VAT_COUNTRY_CODE = 'eu_vat_country_code';
    const EU_VAT_COMPANY_NAME = 'eu_vat_company_name';
    const EU_VAT_COMPANY_ADDRESS = 'eu_vat_company_address';
    const EU_VAT_NUMBER_IS_VALID = 'eu_vat_number_is_valid';
    const EU_VAT_IS_REVERSE_CHARGED = 'eu_vat_is_reverse_charged';

    /**
     * Order ID
     *
     * @var int
     */
    protected $id = 0;

    protected $plan_id = 0;

    protected $subscription_id = 0;

    /**
     * The payment method mode the order was made in
     *
     * @var string
     */
    protected $mode = OrderMode::LIVE;

    protected $order_type = OrderType::NEW_ORDER;

    /**
     * The Unique order Key
     *
     * @var string
     */
    protected $order_key = '';

    protected $discount = '0';

    protected $tax = '0';

    protected $tax_rate = '0';

    protected $subtotal = '0';

    protected $total = '0';

    protected $coupon_code = '';

    /**
     * The date the order was created
     *
     * @var string
     */
    protected $date_created = '';

    /**
     * The date the payment was marked as 'complete'
     *
     * @var string
     */
    protected $date_completed = '';

    /**
     * The status of the payment
     *
     * @var string
     */
    protected $status = OrderStatus::PENDING;

    /**
     * The customer ID that made the order
     *
     * @var int
     */
    protected $customer_id = 0;

    protected $ip_address = '';

    protected $billing_address = '';

    protected $billing_city = '';

    protected $billing_state = '';

    protected $billing_country = '';

    protected $billing_postcode = '';

    protected $billing_phone = '';

    /**
     * The transaction ID returned by the payment method
     *
     * @var string
     */
    protected $transaction_id = '';

    /**
     * The payment method used to process the order
     *
     * @var string
     */
    protected $payment_method = '';

    /**
     * The currency the order was made with
     *
     * @var string
     */
    protected $currency = '';

    public function __construct($data = [])
    {
        if (is_array($data) && ! empty($data)) {

            foreach ($data as $key => $value) {
                $this->$key = $value;
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

    /**
     * @param $transaction_id
     *
     * @return false|int
     */
    public function complete_order($transaction_id = '')
    {
        $this->status = OrderStatus::COMPLETED;

        if ( ! empty($transaction_id)) {
            $this->transaction_id = $transaction_id;
        }

        $order_id = $this->save();

        do_action('ppress_order_completed', $this);

        return $order_id;
    }

    /**
     * @return false|int
     */
    public function fail_order()
    {
        $this->status = OrderStatus::FAILED;

        $order_id = $this->save();

        do_action('ppress_order_failed', $this);

        return $order_id;
    }

    /**
     * @return false|int
     */
    public function refund_order()
    {
        $this->status = OrderStatus::REFUNDED;
        $response     = $this->save();

        if ($response) {
            $this->add_note(
                sprintf(
                    __('Payment %s has been fully refunded in %s.', 'wp-user-avatar'),
                    $this->transaction_id,
                    ppress_get_payment_method($this->payment_method)->get_method_title()
                )
            );

            do_action('ppress_order_order_refunded', $this);
        }

        return $response;
    }

    public function update_status($order_status)
    {
        $old_status = $this->status;

        $this->status = $order_status;

        $response = $this->save();

        $user = is_user_logged_in() ? wp_get_current_user()->user_login : esc_html__('payment method', 'wp-user-avatar');

        $this->add_note(
            sprintf(__('Order changed from %s to %s by %s', 'wp-user-avatar'), $old_status, $this->status, $user)
        );

        do_action('ppress_order_status_updated', $order_status, $old_status, $this);

        return $response;
    }

    public function set_status($status)
    {
        $valid_statuses = (new \ReflectionClass(OrderStatus::class))->getConstants();

        if (in_array($status, $valid_statuses)) {
            $this->status = $status;
        }
    }

    public function set_mode($mode)
    {
        $valid_modes = (new \ReflectionClass(OrderMode::class))->getConstants();

        if (in_array($mode, $valid_modes)) {
            $this->mode = $mode;
        }
    }

    public function set_order_key($value)
    {
        // ensures order key doesn't exceed 64 chars (DB max length)
        $this->order_key = substr($value, 0, 64);
    }

    public function set_currency($currency)
    {
        $currencies = array_keys(ppress_get_currencies());

        if (in_array($currency, $currencies)) {
            $this->currency = $currency;
        }
    }

    /**
     * @return false|int
     */
    public function save()
    {
        if ($this->id > 0) {

            $result = OrderRepository::init()->update($this);

            do_action('ppress_order_updated', $result, $this);

            return $result;
        }

        $result = OrderRepository::init()->add($this);

        do_action('ppress_order_added', $result, $this);

        return $result;
    }

    public function get_customer_full_address()
    {
        $billing_address = $this->billing_address;

        if (empty($billing_address)) return '';

        $state = ppress_var(ppress_array_of_world_states($this->billing_country), $this->billing_state, $this->billing_state, true);

        $address   = [trim($billing_address)];
        $address[] = trim($this->billing_city . ' ' . $state);
        $address[] = $this->billing_postcode;
        $address[] = ppress_array_of_world_countries($this->billing_country);

        return implode(', ', array_filter($address));
    }

    public function get_customer_tax_id()
    {
        return $this->get_meta(self::EU_VAT_NUMBER);
    }

    public function get_subtotal()
    {
        return (string)$this->subtotal;
    }

    public function get_tax()
    {
        return (string)$this->tax;
    }

    public function get_tax_rate()
    {
        return (string)$this->tax_rate;
    }

    public function get_total()
    {
        return (string)$this->total;
    }

    public function get_discount()
    {
        return (string)$this->discount;
    }

    public function get_order_number()
    {
        return (string)apply_filters('ppress_order_number', $this->get_id(), $this);
    }

    public function get_order_key()
    {
        return $this->order_key;
    }

    public function get_reduced_order_key()
    {
        return strtoupper(substr($this->order_key, 0, 8));
    }

    public function get_order_id()
    {
        return $this->get_reduced_order_key();
    }

    public function get_plan_id()
    {
        return absint($this->plan_id);
    }

    public function get_subscription_id()
    {
        return absint($this->subscription_id);
    }

    public function get_customer_id()
    {
        return absint($this->customer_id);
    }

    public function get_customer_email()
    {
        return CustomerFactory::fromId($this->customer_id)->get_email();
    }

    /**
     * @return string
     */
    public function get_transaction_id()
    {
        return $this->transaction_id;
    }

    public function get_linked_transaction_id()
    {
        return PaymentMethods::get_instance()->get_by_id($this->payment_method)->link_transaction_id($this->transaction_id, $this);
    }

    public function get_plan_purchase_note()
    {
        return wpautop(
            do_shortcode(
                wp_kses_post(
                    ppress_get_plan($this->plan_id)->order_note
                )
            )
        );
    }

    public function key_is_valid($key)
    {
        return hash_equals($this->get_order_key(), $key);
    }

    public function is_new_order()
    {
        return $this->order_type == OrderType::NEW_ORDER;
    }

    public function is_renewal_order()
    {
        return $this->order_type == OrderType::RENEWAL_ORDER;
    }

    public function is_completed()
    {
        return $this->status == OrderStatus::COMPLETED;
    }

    public function is_failed()
    {
        return $this->status == OrderStatus::FAILED;
    }

    public function is_pending()
    {
        return $this->status == OrderStatus::PENDING;
    }

    public function is_refunded()
    {
        return $this->status == OrderStatus::REFUNDED;
    }

    public function is_refundable()
    {
        $payment_method = PaymentMethods::get_instance()->get_by_id($this->payment_method);

        if ( ! $payment_method instanceof AbstractPaymentMethod) {
            return false;
        }

        if ($this->status !== OrderStatus::COMPLETED) {
            return false;
        }

        return $payment_method->supports($payment_method::REFUNDS);
    }

    /**
     * @return string
     */
    public function get_refund_url()
    {
        $url = esc_url(wp_nonce_url(add_query_arg(array('ppress_order_action' => 'refund_order', 'id' => $this->id)), 'ppress-cancel-order'));

        return apply_filters('ppress_cancel_order_url', $url, $this);
    }

    public function update_transaction_id($transaction_id)
    {
        return OrderRepository::init()->updateColumn($this->id, 'transaction_id', $transaction_id);
    }

    /**
     * @return array
     */
    public function get_notes()
    {
        return OrderService::init()->get_order_notes($this->id);
    }

    /**
     * @param $note
     *
     * @return false|int
     */
    public function add_note($note)
    {
        return OrderService::init()->add_order_note($this->id, $note);
    }

    /**
     * @param $note_id
     *
     * @return bool
     */
    public function delete_note($note_id)
    {
        return OrderService::init()->delete_order_note_by_id($note_id);
    }

    /**
     * @param $meta_key
     *
     * @return array|false|mixed
     */
    public function get_meta($meta_key)
    {
        return OrderRepository::init()->get_meta_data(
            $this->get_id(),
            $meta_key
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
        return OrderRepository::init()->add_meta_data(
            $this->get_id(),
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
        return OrderRepository::init()->update_meta_data(
            $this->get_id(),
            $meta_key,
            $meta_value
        );
    }

    /**
     * @param $meta_key
     *
     * @return bool
     */
    public function delete_meta($meta_key)
    {
        return OrderRepository::init()->delete_meta_data(
            $this->get_id(),
            $meta_key
        );
    }
}