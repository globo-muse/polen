<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe;

use ProfilePress\Core\Membership\Controllers\CheckoutResponse;
use ProfilePress\Core\Membership\Models\Customer\CustomerEntity;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\AbstractPaymentMethod;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\RegisterScripts;
use ProfilePressVendor\Stripe\Webhook as StripeWebhook;
use WP_Error;

class Stripe extends AbstractPaymentMethod
{
    public function __construct()
    {
        parent::__construct();

        $this->id          = 'stripe';
        $this->title       = 'Credit Card (Stripe)';
        $this->description = esc_html__('Pay with your credit card via Stripe', 'wp-user-avatar');

        $this->method_title       = 'Stripe';
        $this->method_description = esc_html__('Credit card payment method that integrates with your Stripe account.', 'wp-user-avatar');
        $this->method_description .= ( ! isset($_GET['method']) && true === PaymentHelpers::has_application_fee()) ?
            ' ' . sprintf(
                esc_html__('NOTE: The free version of ProfilePress includes an additional 2%% fee for processing payments. Remove the fee by %supgrading to premium%s.', 'wp-user-avatar'),
                '<a target="_blank" href="https://profilepress.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=stripe-gateway-method">', '</a>'
            )
            : '';

        $this->icon = PPRESS_ASSETS_URL . '/images/cards-icon.svg';

        $this->supports = [
            self::SUBSCRIPTIONS,
            self::REFUNDS,
        ];

        add_action('admin_init', [$this, 'save_stripe_connect']);
        add_action('admin_init', [$this, 'disconnect_stripe_account']);
        add_action('admin_init', [$this, 'maybe_update_webhook']);
        add_action('ppress_admin_notices', [$this, 'output_connection_error']);
    }

    public function has_fields()
    {
        return ! $this->is_offsite_checkout_style();
    }

    protected function is_offsite_checkout_style()
    {
        return $this->get_value('checkout_style', 'onsite') == 'offsite';
    }

    protected function is_billing_fields_removed()
    {
        return $this->get_value('remove_billing_fields') == 'true';
    }

    public function output_connection_error()
    {
        if (ppressGET_var('section') == 'payment-methods' && ppressGET_var('method') == 'stripe' && ! empty($_GET['oauth-error'])) {

            echo '<div class="notice notice-error is-dismissible"><p>';
            echo '<strong>' . esc_html__('Stripe Connect Error:', 'wp-user-avatar') . '</strong> ' . sanitize_text_field(ppressGET_var('oauth-error'));
            echo '</p></div>';
        }
    }

    public function save_stripe_connect()
    {
        if (ppressGET_var('pp-save-auth') == 'stripe' && ! empty($_GET['access_token'])) {

            if (current_user_can('manage_options') && wp_verify_nonce($_GET['ppnonce'], 'ppress_stripe_auth') !== false) {
                $mode                                        = ppressGET_var('livemode') === false ? 'test' : 'live';
                $old                                         = get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []);
                $old['stripe_' . $mode . '_publishable_key'] = sanitize_text_field($_GET['stripe_publishable_key']);
                $old['stripe_' . $mode . '_secret_key']      = sanitize_text_field($_GET['access_token']);
                $old['stripe_' . $mode . '_user_id']         = sanitize_text_field($_GET['stripe_user_id']);
                $old['stripe_connect_mode']                  = $mode;
                update_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, $old);

                WebhookHelpers::add_update_endpoint();
            }

            wp_redirect($this->get_admin_page_url());
            exit;
        }
    }

    public function maybe_update_webhook()
    {
        try {

            $mode = ppress_is_test_mode() ? 'test' : 'live';

            $endpoint_id = ppress_get_payment_method_setting('stripe_' . $mode . '_webhook_endpoint_id', '');

            if ( ! empty($endpoint_id) && PPRESS_STRIPE_API_VERSION !== ppress_get_payment_method_setting("stripe_{$mode}_webhook_api_version")) {
                WebhookHelpers::delete($endpoint_id);
                $endpoint_id = '';
            }

            if (empty($endpoint_id)) {
                WebhookHelpers::create();

                return;
            }

            $endpoint_url = ppress_get_payment_method_setting("stripe_{$mode}_webhook_endpoint_url", '');

            if (empty($endpoint_url) || $endpoint_url !== WebhookHelpers::webhook_url()) {
                $endpoint = WebhookHelpers::get($endpoint_id);
                WebhookHelpers::update($endpoint);

                return;
            }

            $endpoint_events = ppress_get_payment_method_setting("stripe_{$mode}_webhook_endpoint_events", []);

            if (empty($endpoint_events) || (
                    // Not all events, and saved is fewer than our current whitelist.
                    ! in_array('*', $endpoint_events, true) &&
                    count($endpoint_events) < count(WebhookHelpers::get_event_whitelist())
                )
            ) {
                $endpoint = WebhookHelpers::get($endpoint_id);
                WebhookHelpers::update($endpoint);

                return;
            }

        } catch (\Exception $e) {
            // Fail silently.
        }
    }

    public function disconnect_stripe_account()
    {
        if (ppressGET_var('section') == 'payment-methods' && ppressGET_var('method') == 'stripe' && ppressGET_var('ppress-stripe-disconnect') == 'true') {

            if (current_user_can('manage_options') && wp_verify_nonce($_GET['ppnonce'], 'ppress_stripe_disconnect') !== false) {
                $data = get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []);
                unset($data['stripe_test_publishable_key']);
                unset($data['stripe_test_secret_key']);
                unset($data['stripe_test_user_id']);
                unset($data['stripe_live_publishable_key']);
                unset($data['stripe_live_secret_key']);
                unset($data['stripe_connect_mode']);
                unset($data['stripe_connect_account_country']);
                update_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, $data);
            }

            wp_redirect($this->get_admin_page_url());
            exit;
        }
    }

    public function admin_connection_status_block()
    {
        $html = '';

        $mode = ppress_is_test_mode() ? __('test', 'wp-user-avatar') : __('live', 'wp-user-avatar');

        if ( ! Helpers::get_account_user_id() || ! Helpers::check_keys_exist()) {
            $html .= Helpers::get_connect_button($this->get_admin_page_url());
        } else {
            $html .= '<div class="pp-alert-notice pp-alert-notice-info"><p>';
            $html .= Helpers::get_account_information($this->get_admin_page_url());
            $html .= '</p></div>';

            $html .= '<p id="ppress-stripe-activated-account-actions">' . sprintf(
                    __('Your Stripe account is connected in %1$s mode. %2$sDisconnect this account%3$s.', 'wp-user-avatar'),
                    '<strong>' . $mode . '</strong>',
                    '<a class="pp-confirm-delete" href="' . esc_url(Helpers::get_disconnect_url($this->get_admin_page_url())) . '" class="ppress-disconnect-link">',
                    '</a>'
                ) . '</p>';
        }

        return $html;
    }

    public function admin_settings()
    {
        $settings = [
            'connection_status' => [
                'type'  => 'custom_field_block',
                'label' => esc_html__('Connection Status', 'wp-user-avatar'),
                'data'  => $this->admin_connection_status_block(),
            ]
        ];

        $fee_message = ( ! Helpers::check_keys_exist() && true === PaymentHelpers::has_application_fee()) ?
            sprintf(
                esc_html__('Connect now to start accepting payments instantly. This includes an additional 2%% payment processing fee. Remove the fee by %supgrading to premium%s.', 'wp-user-avatar'),
                '<a target="_blank" href="https://profilepress.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=stripe-inner-gateway-method">', '</a>'
            )
            : '';

        if (ppress_is_test_mode() && ! empty($fee_message)) {
            $settings['connection_status']['description'] = $fee_message;
        }

        $settings = array_merge($settings, parent::admin_settings());

        $connect_mode       = ppress_get_payment_method_setting('stripe_connect_mode', 'live');
        $connect_mode_label = $connect_mode == 'test' ? esc_html__('Test', 'wp-user-avatar') : esc_html__('Live', 'wp-user-avatar');
        $stripe_webhook_url = $connect_mode == 'test' ? 'https://dashboard.stripe.com/test/webhooks' : 'https://dashboard.stripe.com/webhooks';

        if (empty(Helpers::get_webhook_secret())) {

            $settings['webhook_info'] = [
                'label' => esc_html__('Webhook Setup', 'wp-user-avatar'),
                'type'  => 'custom_field_block',
                'data'  => sprintf(
                    __('In order for Stripe to function well, ensure the webhook endpoint %1$s is present in the %2$sStripe webhooks settings%3$s. Learn more from our %4$swebhook documentation%3$s', 'wp-user-avatar'),
                    '<code>' . esc_url($this->get_webhook_url()) . '</code>',
                    '<a target="_blank" href="' . esc_url($stripe_webhook_url) . '">',
                    '</a>',
                    '<a target="_blank" href="https://profilepress.com/article/setting-up-stripe/">'
                ),
            ];
        }

        $secret_connect_mode_label                   = ($connect_mode == 'test') ? esc_html__('test mode', 'wp-user-avatar') : esc_html__('live mode', 'wp-user-avatar');
        $settings[$connect_mode . '_webhook_secret'] = [
            'label'       => $connect_mode_label . ' ' . esc_html__('Webhook Secret', 'wp-user-avatar'),
            'type'        => 'password',
            'description' => sprintf(
                __(
                    'Retrieve your %3$s "Signing secret" from your %1$sStripe webhook settings%2$s. Select the endpoint then click "Reveal".',
                    'wp-user-avatar'
                ),
                '<a href="' . $stripe_webhook_url . '" target="_blank" rel="noopener noreferrer">',
                '</a>',
                '<strong>' . $secret_connect_mode_label . '</strong>'
            ),
        ];

        $settings['statement_descriptor'] = [
            'label'       => esc_html__('Statement Descriptor', 'wp-user-avatar'),
            'type'        => 'text',
            'description' => sprintf(
                esc_html__('The text that appears on your customer\'s bank or credit card statements. Choose something they will recognise to help prevent disputes, typically your business name. Must be limited to 22 characters, no special characters %1$s<%2$s, %1$s>%2$s, %1$s\'%2$s, or %1$s"%2$s.', 'wp-user-avatar'),
                '<code>', '</code>'
            )
        ];

        $settings['checkout_style'] = [
            'label'       => esc_html__('Payment Collection Method', 'wp-user-avatar'),
            'type'        => 'select',
            'options'     => [
                'onsite'  => esc_html__('Stripe Credit Card Field (On-site)', 'wp-user-avatar'),
                'offsite' => esc_html__('Stripe Payment Page (Off-site)', 'wp-user-avatar')
            ],
            'description' => esc_html__('Select how payment information will be collected. Could be right on your site with Stripe card fields or off-site through Stripe hosted payment page.', 'wp-user-avatar')
        ];

        $settings['remove_billing_fields'] = [
            'label'          => esc_html__('Remove Billing Address', 'wp-user-avatar'),
            'type'           => 'checkbox',
            'checkbox_label' => esc_html__('Check to remove billing address fields from the checkout page.', 'wp-user-avatar'),
            'description'    => esc_html__('If you do not want the billing address fields displayed on the checkout page, use this setting to remove it.', 'wp-user-avatar')
        ];

        $settings['restrict_assets'] = [
            'label'          => esc_html__('Restrict Stripe Assets', 'wp-user-avatar'),
            'type'           => 'checkbox',
            'checkbox_label' => esc_html__('Only load Stripe.com hosted assets on pages that specifically utilize Stripe functionality.', 'wp-user-avatar'),
            'description'    => sprintf(
                esc_html__('Stripe advises that their Javascript library be loaded on every page to take advantage of their advanced fraud detection rules. If you are not concerned with this, enable this setting to only load the Javascript when necessary. %sLearn more%s', 'wp-user-avatar'),
                '<a target="_blank" href="https://stripe.com/docs/js/including">', '</a>'
            )
        ];

        return $settings;
    }

    /**
     * @return bool|WP_Error
     */
    public function validate_fields()
    {
        if ( ! $this->is_offsite_checkout_style()) {

            if ( ! isset($_POST['stripe-card_name']) || strlen(trim($_POST['stripe-card_name'])) === 0) {

                return new WP_Error(
                    'no_card_name',
                    esc_html__('Please enter a name for the credit card.', 'wp-user-avatar')
                );
            }
        }

        return true;
    }

    /**
     * Disable billing validation.
     *
     * @param $val
     *
     * @return bool
     */
    public function should_validate_billing_details($val)
    {
        if ($this->is_offsite_checkout_style() || $this->is_billing_fields_removed()) {
            $val = false;
        }

        return $val;
    }

    public function get_statement_descriptor()
    {
        $statement_descriptor = $this->get_value('statement_descriptor');

        if (empty($statement_descriptor)) return '';

        $unsupported_characters = apply_filters('ppress_stripe_statement_descriptor_unsupported_characters', [
            '<',
            '>',
            '"',
            '\'',
            '\\',
            '*',
        ]);

        $statement_descriptor = trim(str_replace($unsupported_characters, '', $statement_descriptor));

        return substr($statement_descriptor, 0, 22);
    }

    public function link_transaction_id($transaction_id, $order)
    {
        if ( ! empty($transaction_id)) {
            return sprintf('<a target="_blank" href="https://dashboard.stripe.com/payments/%1$s">%1$s</a>', $transaction_id);
        }

        return $transaction_id;
    }

    public function link_profile_id($profile_id, $subscription)
    {
        if ( ! empty($profile_id)) {
            return sprintf('<a target="_blank" href="https://dashboard.stripe.com/subscriptions/%1$s">%1$s</a>', $profile_id);
        }

        return $profile_id;
    }

    /**
     * Determines if the subscription can be cancelled
     *
     * @param $ret
     *
     * @param SubscriptionEntity $subscription
     *
     * @return      bool
     */
    public function can_cancel($ret, $subscription)
    {
        if ($subscription->get_payment_method() === $this->id && ! empty($subscription->profile_id) && in_array($subscription->status, $this->get_cancellable_statuses())) {
            return true;
        }

        return $ret;
    }

    /**
     * @param $subscription
     * @param $cancel_immediately
     *
     * @return bool
     */
    private function stripe_cancel($subscription, $cancel_immediately = false)
    {
        try {

            // Before we cancel, lets make sure this subscription exists at Stripe.
            $stripeSubObj = APIClass::stripeClient()->subscriptions->retrieve($subscription->profile_id);

            if ('canceled' === $stripeSubObj->status) return false;

            if (false === $cancel_immediately) {

                if (in_array($stripeSubObj->status, ['active', 'trialing'], true)) {
                    APIClass::stripeClient()->subscriptions->update($subscription->profile_id, ['cancel_at_period_end' => true]);
                } else {
                    APIClass::stripeClient()->subscriptions->cancel($subscription->profile_id);
                }

            } else {
                APIClass::stripeClient()->subscriptions->cancel($subscription->profile_id);
            }

            // We must now loop through and cancel all unpaid invoice to ensure that additional payment attempts are not made.
            $invoices = APIClass::stripeClient()->invoices->all(['subscription' => $subscription->profile_id])->toArray();

            if (isset($invoices['data'])) {

                foreach ($invoices['data'] as $invoice) {

                    // Skip paid invoices.
                    if ($invoice['paid'] === true) continue;

                    APIClass::stripeClient()->invoices->voidInvoice($invoice['id']);
                }
            }

        } catch (\Exception $e) {

            $subscription->add_note(
                sprintf(
                    esc_html__('Attempted cancellation but was unable. Message was "%s".', 'wp-user-avatar'),
                    wp_json_encode($e)
                )
            );

            return false;
        }

        return true;
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
        return $this->stripe_cancel($subscription, true);
    }

    /**
     * Cancels a subscription at period end, unless the status of the subscription is failing. If failing, cancel immediately.
     *
     * @param SubscriptionEntity $subscription
     *
     * @return bool
     */
    public function cancel($subscription)
    {
        return $this->stripe_cancel($subscription);
    }

    public function enqueue_frontend_assets()
    {
        if ( ! PaymentMethods::get_instance()->get_by_id('stripe')->is_enabled()) return;

        wp_register_script(
            'ppress-stripe-v3',
            'https://js.stripe.com/v3/',
            [],
            null
        );

        if ('false' === $this->get_value('restrict_assets', 'false')) {
            wp_enqueue_script('ppress-stripe-v3');
        }

        if ($this->is_offsite_checkout_style()) return;

        if ( ! function_exists('ppress_is_checkout')) return;

        $publishable_key = Helpers::get_publishable_key();

        $suffix = RegisterScripts::asset_suffix();

        wp_register_script(
            'ppress-stripe-js',
            PPRESS_ASSETS_URL . "/js/stripe/stripe{$suffix}.js",
            array(
                'jquery',
                'ppress-stripe-v3',
                'ppress-frontend-script'
            ),
            PPRESS_VERSION_NUMBER,
            true
        );

        $is_checkout = ppress_is_checkout();

        if ($is_checkout || 'false' === $this->get_value('restrict_assets', 'false')) {
            wp_enqueue_script('ppress-stripe-v3');
        }

        if ($is_checkout) {

            wp_enqueue_script('ppress-stripe-js');

            $stripe_vars = [
                'publishable_key'   => trim($publishable_key),
                'createCardOptions' => ['hidePostalCode' => true],
                'locale'            => apply_filters('ppress_stripe_checkout_locale', 'auto'),
            ];

            if ($this->is_billing_fields_removed()) {
                $stripe_vars['createCardOptions']['hidePostalCode'] = false;
            }

            $stripe_vars = apply_filters('ppress_stripe_js_vars', $stripe_vars);

            wp_localize_script('ppress-stripe-js', 'ppress_stripe_vars', $stripe_vars);
        }
    }

    protected function billing_address_form()
    {
        if ($this->is_offsite_checkout_style()) return;

        if ($this->is_billing_fields_removed()) return;

        parent::billing_address_form();
    }

    public function credit_card_form()
    {
        if ($this->is_offsite_checkout_style()) return;
        ?>
        <div class="ppress-main-checkout-form__block__item">
            <label for="<?= esc_attr($this->id . '-' . 'card_name') ?>">
                <?php esc_html_e('Name on card', 'wp-user-avatar') ?>
                <span class="ppress-required">*</span>
            </label>
            <input id="<?= esc_attr($this->id . '-' . 'card_name') ?>" name="<?= esc_attr($this->id . '-' . 'card_name') ?>" class="ppress-checkout-field__input" type="text" autocomplete="cc-name">
        </div>

        <div id="ppress-stripe-card-element-wrapper" class="ppress-main-checkout-form__block__item">
            <label for="ppress-stripe-card-element">
                <?php esc_html_e('Credit card', 'wp-user-avatar') ?>
                <span class="ppress-required">*</span>
            </label>
            <div id="ppress-stripe-card-element"></div>
        </div>
        <?php
    }

    /**
     * @param OrderEntity $order
     * @param SubscriptionEntity $subscription
     *
     * @return array
     */
    public function get_order_metadata($order, $subscription)
    {
        return [
            'order_id'        => $order->id,
            'order_key'       => $order->order_key,
            'customer_id'     => $order->customer_id,
            'subscription_id' => $subscription->id,
            'caller'          => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . PPRESS_VERSION_NUMBER,
        ];
    }

    /**
     * @param OrderEntity $order
     * @param CustomerEntity $customer
     * @param SubscriptionEntity $subscription
     * @param PlanEntity $plan
     *
     * @return CheckoutResponse
     */
    public function process_offsite_payment($order, $customer, $subscription, $plan)
    {
        try {

            PaymentHelpers::empty_coupon_bucket();

            $product_price = PaymentHelpers::get_product_price($subscription, $this->get_statement_descriptor());

            if (is_wp_error($product_price)) {
                throw new \Exception($product_price->get_error_message());
            }

            $price_id = ppress_var($product_price, 'stripe_price_id');

            $stripe_customer_id = PaymentHelpers::get_stripe_customer_id($customer);

            $create_session_args_metadata = $this->get_order_metadata($order, $subscription);

            $create_session_args = [
                'success_url'         => add_query_arg(['session_id' => '{CHECKOUT_SESSION_ID}'], $this->get_success_url($order->order_key)),
                'cancel_url'          => ppress_plan_checkout_url($plan->id),
                'line_items'          => [
                    [
                        'price'    => $price_id,
                        'quantity' => 1,
                    ],
                ],
                'mode'                => $plan->billing_frequency == SubscriptionBillingFrequency::ONE_TIME ? 'payment' : 'subscription',
                'client_reference_id' => $order->order_key,
                'customer'            => $stripe_customer_id,
                'metadata'            => $create_session_args_metadata
            ];

            if ( ! $subscription->is_recurring()) {
                $create_session_args['payment_intent_data']['metadata'] = $create_session_args_metadata;
            } else {
                $create_session_args['subscription_data']['metadata'] = $create_session_args_metadata;
            }

            if ($plan->has_signup_fee()) {

                $signup_fee = $plan->has_free_trial() ? $order->total : '0';

                if (
                    ! $plan->has_free_trial() &&
                    Calculator::init($order->total)->isGreaterThan($subscription->recurring_amount)) {
                    $signup_fee = Calculator::init($order->total)->minus($subscription->recurring_amount)->val();
                }

                if (Calculator::init($signup_fee)->isGreaterThanZero()) {

                    $create_session_args['line_items'][] = [
                        'quantity'   => 1,
                        'price_data' => [
                            'unit_amount'  => PaymentHelpers::process_amount($signup_fee),
                            'currency'     => ppress_get_currency(),
                            'product_data' => [
                                'name' => PaymentHelpers::get_signup_fee_label(),
                            ],
                        ]
                    ];
                }
            }

            if ($plan->has_free_trial()) {

                $trial_period_days = PaymentHelpers::free_trial_days_count($plan->free_trial);

                if ($trial_period_days > 0) {
                    $create_session_args['subscription_data']['trial_period_days'] = $trial_period_days;
                }
            }

            if (
                ! $plan->has_free_trial() &&
                Calculator::init($order->total)->isLessThan($subscription->recurring_amount)
            ) {

                $discount_amount = Calculator::init($subscription->recurring_amount)->minus($order->total)->val();

                $stripe_coupon = APIClass::stripeClient()->coupons->create([
                    'amount_off' => PaymentHelpers::process_amount($discount_amount),
                    'currency'   => ppress_get_currency(),
                    'duration'   => 'once'
                ])->toArray();

                $create_session_args['discounts'][] = ['coupon' => $stripe_coupon['id']];

                PaymentHelpers::add_coupon_to_bucket($stripe_coupon['id']);
            }

            $create_session_args = apply_filters('ppress_stripe_create_session_args', $create_session_args, $this);

            if (PaymentHelpers::has_application_fee()) {

                if ($subscription->is_recurring()) {
                    $create_session_args['subscription_data']['application_fee_percent'] = PaymentHelpers::application_fee_percent();
                } else {
                    $create_session_args['payment_intent_data']['application_fee_amount'] = PaymentHelpers::application_fee_amount($order->total);
                }
            }

            $session_response = APIClass::stripeClient()->checkout->sessions->create($create_session_args)->toArray();

            $checkoutResponse = new CheckoutResponse();

            if (isset($session_response['url'])) {

                return $checkoutResponse
                    ->set_is_success(true)
                    ->set_redirect_url($session_response['url'])
                    ->set_gateway_response($session_response);
            }

            throw new \Exception($checkoutResponse->get_generic_error_message());

        } catch (\Exception $e) {

            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            return (new CheckoutResponse())
                ->set_is_success(false)
                ->set_error_message($e->getMessage());
        }
    }

    public function process_payment($order_id, $subscription_id, $customer_id)
    {
        $order        = OrderFactory::fromId($order_id);
        $subscription = SubscriptionFactory::fromId($subscription_id);
        $customer     = CustomerFactory::fromId($customer_id);

        $plan = PlanFactory::fromId($order->plan_id);

        if ($this->is_offsite_checkout_style()) {
            return $this->process_offsite_payment($order, $customer, $subscription, $plan);
        }

        try {

            $stripe_coupon = false;

            $customer_id = PaymentHelpers::get_stripe_customer_id($customer);

            if (empty($_POST['ppress_stripe_payment_method'])) {
                throw new \Exception(
                    esc_html__('No payment method found. Please try again.', 'wp-user-avatar')
                );
            }

            $payment_method = sanitize_text_field($_POST['ppress_stripe_payment_method']);

            APIClass::stripeClient()->paymentMethods->attach(
                $payment_method,
                ['customer' => $customer_id]
            );

            APIClass::stripeClient()->customers->update($customer_id, [
                'invoice_settings' => ['default_payment_method' => $payment_method]
            ]);

            $product_price = PaymentHelpers::get_product_price($subscription, $this->get_statement_descriptor());

            $checkout_metadata = $this->get_order_metadata($order, $subscription);

            if ($plan->is_recurring()) {

                $create_subscription_args = [
                    'customer' => $customer_id,
                    'items'    => [
                        [
                            'price'    => ppress_var($product_price, 'stripe_price_id'),
                            'quantity' => 1,
                            'metadata' => $checkout_metadata
                        ],
                    ],
                    'metadata' => $checkout_metadata
                ];

                $signup_fee = Calculator::init($order->total)->minus($subscription->recurring_amount)->val();

                // if there is a free trial, no amount is charged hence order total is 0
                // however if the plan has a signup fee, it becomes the order total hence the below code.
                if ($plan->has_free_trial()) {

                    $signup_fee = $order->total;

                    $trial_period_days = PaymentHelpers::free_trial_days_count($plan->free_trial);

                    if ($trial_period_days > 0) {
                        $create_subscription_args['trial_period_days'] = $trial_period_days;
                    }
                }

                if ($plan->has_signup_fee() && Calculator::init($signup_fee)->isGreaterThanZero()) {

                    $create_subscription_args['add_invoice_items'][] = [
                        'quantity'   => 1,
                        'price_data' => [
                            'product'     => $product_price['stripe_product_id'],
                            'unit_amount' => PaymentHelpers::process_amount($signup_fee),
                            'currency'    => ppress_get_currency(),
                        ]
                    ];
                }

                if (
                    ! $plan->has_free_trial() &&
                    Calculator::init($order->total)->isLessThan($subscription->recurring_amount)
                ) {

                    $discount_amount = Calculator::init($subscription->recurring_amount)->minus($order->total)->val();

                    $stripe_coupon = APIClass::stripeClient()->coupons->create([
                        'amount_off' => PaymentHelpers::process_amount($discount_amount),
                        'currency'   => ppress_get_currency(),
                        'duration'   => 'once'
                    ])->toArray();

                    $create_subscription_args['coupon'] = $stripe_coupon['id'];
                }

                $create_subscription_args = apply_filters('ppress_stripe_create_subscription_args', $create_subscription_args, $this);

                if (PaymentHelpers::has_application_fee()) {
                    $create_subscription_args['application_fee_percent'] = PaymentHelpers::application_fee_percent();
                }

                $create_subscription_args['expand'] = ['latest_invoice.payment_intent'];

                $response = APIClass::stripeClient()->subscriptions->create($create_subscription_args)->toArray();

                if (isset($response['latest_invoice'])) {
                    $subscription->update_profile_id($response['id']);
                }

                if (false !== $stripe_coupon) {
                    PaymentHelpers::delete_coupon($stripe_coupon['id']);
                }

                return (new CheckoutResponse())
                    ->set_is_success(true)
                    ->set_gateway_response($response);
            }

            $create_payment_intent_args = [
                'amount'         => PaymentHelpers::process_amount($order->total),
                'currency'       => ppress_get_currency(),
                'confirm'        => true,
                'customer'       => $customer_id,
                'description'    => $plan->name,
                'metadata'       => $checkout_metadata,
                'payment_method' => $payment_method
            ];

            $statement_descriptor = $this->get_statement_descriptor();

            if ( ! empty($statement_descriptor)) {
                $create_payment_intent_args['statement_descriptor'] = $statement_descriptor;
            }

            $create_payment_intent_args = apply_filters('ppress_stripe_create_payment_intent_args', $create_payment_intent_args, $this);

            if (PaymentHelpers::has_application_fee()) {
                $create_payment_intent_args['application_fee_amount'] = PaymentHelpers::application_fee_amount($order->total);
            }

            $response = APIClass::stripeClient()->paymentIntents->create($create_payment_intent_args)->toArray();

            if (isset($response['id'])) {
                $order->update_transaction_id($response['id']);
            }

            return (new CheckoutResponse())
                ->set_is_success(true)
                ->set_gateway_response($response);

        } catch (\Exception $e) {

            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            return (new CheckoutResponse())
                ->set_is_success(false)
                ->set_error_message($e->getMessage());
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        try {

            $order = OrderFactory::fromId($order_id);

            $response = APIClass::stripeClient()->refunds->create([
                'payment_intent' => $order->transaction_id,
            ]);

            switch ($response->status) {
                case 'succeeded':
                    return true;
                case 'pending':
                    $order->add_note(esc_html__('Refund request is pending', 'profilepress-pro'));
                    break;
                case 'failed':
                    $order->add_note(esc_html__('Refund request failed', 'profilepress-pro'));
                    break;
                default:
                    $order->add_note(sprintf(esc_html__('Refund request failed. Status: %s', 'profilepress-pro'), $response->status));
                    break;
            }

            return false;

        } catch (\Exception $e) {
            ppress_log_error($e->getMessage() . '; OrderID:' . $order_id);

            return false;
        }
    }

    public function process_webhook()
    {
        APIClass::_setup();

        $endpoint_secret = Helpers::get_webhook_secret();

        $payload    = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event      = null;

        try {

            $event = StripeWebhook::constructEvent($payload, $sig_header, $endpoint_secret);

        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Exception $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        $event = $event->toArray();

        $webhooks = WebhookHelpers::valid_events();

        if (in_array($event['type'], array_keys($webhooks))) {
            /** @var WebhookHandlerInterface $callable */
            $callable = $webhooks[$event['type']];

            call_user_func([$callable, 'handle'], $event['data']['object']);

            do_action('ppress_stripe_webhook_event', $event['type'], $event);
        }

        http_response_code(200);
    }
}