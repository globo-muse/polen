<?php

namespace ProfilePress\Core\Membership\PaymentMethods;

use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $method_title
 * @property string $method_description
 * @property string $order_button_text
 * @property bool $has_fields
 * @property string $icon
 */
abstract class AbstractPaymentMethod implements PaymentMethodInterface
{
    const DEFAULT_CC_FORM = 'credit_card_form_support';

    const REFUNDS = 'refunds_support';

    const SUBSCRIPTIONS = 'subscriptions_support';

    const SUBSCRIPTION_CANCELLATION = 'subscription_cancellation_support';

    const TITLE_DB_OPTION_NAME = 'title';

    const DESCRIPTION_DB_OPTION_NAME = 'description';

    /**
     * @var string Method Unique Identifier.
     */
    protected $id;

    /**
     * @var bool Useful if method should not show up on checkout page
     */
    protected $backend_only = false;

    /**
     * Gateway title for the frontend.
     *
     * @var string
     */
    protected $title;

    /**
     * Gateway description for the frontend.
     *
     * @var string
     */
    protected $description;

    /**
     * Gateway title.
     *
     * @var string
     */
    protected $method_title = '';

    /**
     * Gateway description.
     *
     * @var string
     */
    protected $method_description = '';

    /**
     * True if the gateway shows fields on the checkout.
     *
     * @var bool
     */
    protected $has_fields = false;

    /**
     * Icon for the gateway.
     *
     * @var string
     */
    protected $icon;

    protected $supports = [];

    public function __construct()
    {
        add_action('init', [$this, 'webhook_callback'], 9);

        add_filter('ppress_subscription_can_cancel', [$this, 'can_cancel'], 10, 2);

        add_action('ppress_subscription_completed', [$this, 'cancel_sub_on_completion']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);

        add_filter('ppress_checkout_billing_validation', [$this, 'should_validate_billing_details']);
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    public function __get($key)
    {
        $value = false;

        if (method_exists($this, "get_{$key}")) {
            $value = call_user_func([$this, "get_{$key}"]);
        } elseif (isset($this->$key)) {
            $value = $this->$key;
        }

        return $value;
    }

    public function webhook_callback()
    {
        if ( ! isset($_GET['ppress-listener']) || $this->id !== $_GET['ppress-listener']) {
            return;
        }

        nocache_headers();

        $this->process_webhook();
        exit;
    }

    public function is_enabled()
    {
        return $this->get_value('enabled') == 'true';
    }

    public function is_backend_only()
    {
        return $this->backend_only === true;
    }

    /**
     * Get Gateway  Id.
     *
     * @return string The email id.
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Return the gateway's title.
     *
     * @return string
     */
    public function get_title()
    {
        if (empty($this->title)) {
            $this->title = $this->method_title;
        }

        $db_title = $this->get_value(self::TITLE_DB_OPTION_NAME);

        if ( ! empty($db_title)) {
            $this->title = $db_title;
        }

        return apply_filters('ppress_gateway_title', $this->title, $this->id);
    }

    /**
     * Return the gateway's description.
     *
     * @return string
     */
    public function get_description()
    {
        $db_description = $this->get_value(self::DESCRIPTION_DB_OPTION_NAME);

        if ( ! empty($db_description)) {
            $this->description = $db_description;
        }

        return apply_filters('ppress_gateway_description', $this->description, $this->id, $this);
    }

    /**
     * Return the title for admin screens.
     *
     * @return string
     */
    public function get_method_title()
    {
        return apply_filters('ppress_gateway_method_title', $this->method_title, $this->id, $this);
    }

    /**
     * Return the description for admin screens.
     *
     * @return string
     */
    public function get_method_description()
    {
        return apply_filters('ppress_gateway_method_description', $this->method_description, $this->id, $this);
    }

    public function admin_settings()
    {
        return [
            'enabled'     => [
                'type'           => 'checkbox',
                'label'          => esc_html__('Enable / Disable', 'wp-user-avatar'),
                /* translators: %s - Payment Gateway Title */
                'checkbox_label' => __('Check to Enable', 'wp-user-avatar'),
            ],
            'title'       => [
                'label' => __('Title', 'wp-user-avatar'),
                'type'  => 'text',
                'value' => esc_html($this->get_title()),
            ],
            'description' => [
                'label' => esc_html__('Description', 'wp-user-avatar'),
                'type'  => 'text',
                'value' => wp_kses_post($this->get_description()),
            ],
        ];
    }

    /**
     * Check if the gateway has fields on the checkout.
     *
     * @return bool
     */
    public function has_fields()
    {
        return (bool)$this->has_fields;
    }

    /**
     * Return the gateway's icon.
     *
     * @return string
     */
    public function get_icon()
    {
        $icon = $this->icon ? '<img src="' . ppress_force_https_url($this->icon) . '" alt="' . esc_attr($this->get_title()) . '" />' : '';

        return apply_filters('ppress_gateway_icon', $icon, $this->id, $this);
    }

    public function get_admin_page_url()
    {
        return add_query_arg([
            'view'    => 'payments',
            'section' => 'payment-methods',
            'method'  => $this->get_id()
        ], PPRESS_SETTINGS_SETTING_PAGE);
    }

    public function get_webhook_url()
    {
        return add_query_arg(['ppress-listener' => $this->id], home_url('/'));
    }

    /**
     * Get setting value.
     *
     * @param $setting
     */
    public function get_value($setting, $default = false)
    {
        $data = get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []);

        $setting = str_replace($this->id . '_', '', $setting);

        return ppress_var($data, $this->id . '_' . $setting, $default);
    }

    /**
     * If There are no payment fields show the description if set.
     * Override this in your gateway if you have some.
     */
    public function payment_fields()
    {
        $description = $this->get_description();

        if ( ! empty($description)) {
            echo wpautop(wptexturize($description));
        }

        $this->credit_card_form();

        $this->billing_address_form();
    }

    /**
     * Useful for enqueuing frontend assets.
     *
     * @return void
     */
    public function enqueue_frontend_assets()
    {

    }

    public function should_validate_billing_details($val)
    {
        return $val;
    }

    abstract function process_webhook();

    /**
     * Validate frontend fields.
     *
     * Validate payment fields on the frontend.
     *
     * @return bool|\WP_Error
     */
    abstract function validate_fields();

    /**
     * Process Payment.
     *
     * Process the payment. Override this in your gateway. When implemented, this should.
     * return the success and redirect in an array. e.g:
     *
     *        return array(
     *            'result'   => 'success',
     *            'redirect' => $this->get_return_url( $order )
     *        );
     *
     * @param int $order_id Order ID.
     *
     * @return mixed|void
     */
    abstract function process_payment($order_id, $subscription_id, $customer_id);

    /**
     * Process refund.
     *
     * If the p declares 'refunds' support, this will allow it to refund a passed in amount.
     *
     * @param int $order_id Order ID.
     * @param string $amount Refund amount.
     * @param string $reason Refund reason.
     *
     * @return boolean
     */
    public function process_refund($order_id, $amount = null, $reason = '')
    {

    }

    /**
     * Get a link to the transaction on the 3rd party gateway site (if applicable).
     *
     * @param string $transaction_id
     * @param OrderEntity $order
     *
     * @return string transaction URL, or empty string.
     */
    public function link_transaction_id($transaction_id, $order)
    {
        return $transaction_id;
    }

    /**
     * Get subscription profile Link.
     *
     * @param string $profile_id The profile id.
     * @param SubscriptionEntity $subscription
     *
     * @return string $profile_link The profile link link.
     */
    public function link_profile_id($profile_id, $subscription)
    {
        return $profile_id;
    }

    public function supports($feature)
    {
        return apply_filters('ppress_payment_gateway_supports', in_array($feature, $this->supports), $feature, $this);
    }

    public function credit_card_form()
    {
        if ($this->supports(self::DEFAULT_CC_FORM)) {
            ppress_render_view('checkout/credit-card-fields', [
                'id' => $this->id
            ]);
        }
    }

    protected function billing_address_form()
    {
        ppress_render_view('checkout/form-billing-fields', ['payment_method' => $this->id]);
    }

    /**
     * Determines if a subscription can be cancelled through the gateway
     */
    public function can_cancel($ret, $subscription)
    {
        return $ret;
    }

    /**
     * Returns an array of subscription statuses that can be cancelled
     *
     * @return array
     */
    public function get_cancellable_statuses()
    {
        return apply_filters('ppress_subscription_cancellable_statuses', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIALLING]);
    }

    /**
     * @param SubscriptionEntity $subscription
     *
     * @return bool|void
     */
    public function cancel_sub_on_completion($subscription)
    {
        if ($subscription->get_payment_method() !== $this->id) {
            return;
        }

        return $this->cancel_immediately($subscription);
    }

    /**
     * Cancels a subscription. If possible, cancel at the period end. If not possible, cancel immediately.
     *
     * @param SubscriptionEntity $subscription
     *
     * @return bool
     */
    public function cancel($subscription)
    {

    }

    /**
     * Cancels a subscription immediately.
     *
     * @param SubscriptionEntity $subscription
     *
     * @return bool
     */
    public function cancel_immediately($subscription)
    {
        return $this->cancel($subscription);
    }

    /**
     * Get the return url (thank you page).
     *
     * @param $order_key
     *
     * @return string
     */
    public function get_success_url($order_key = '')
    {
        return ppress_get_success_url($order_key);
    }

    /**
     * @param $order_key
     *
     * @return string
     */
    public function get_cancel_url($order_key = '')
    {
        return ppress_get_cancel_url($order_key);
    }

    public static function get_instance()
    {
        return new static();
    }
}