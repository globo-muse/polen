<?php

namespace ProfilePress\Core\Membership\Controllers;

use ProfilePress\Core\Classes\LoginAuth;
use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\PaymentMethods\StoreGateway;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\Membership\Services\EUVATChecker\EuVatApi;
use ProfilePress\Core\Membership\Services\OrderService;
use ProfilePress\Core\Membership\Services\TaxService;

class CheckoutController extends BaseController
{
    use CheckoutTrait;

    public function __construct()
    {
        add_action('wp_ajax_nopriv_ppress_process_checkout_login', [$this, 'process_checkout_login']);

        add_action('wp_ajax_ppress_process_checkout', [$this, 'process_checkout']);
        add_action('wp_ajax_nopriv_ppress_process_checkout', [$this, 'process_checkout']);

        add_action('wp_ajax_ppress_checkout_apply_discount', [$this, 'apply_discount']);
        add_action('wp_ajax_nopriv_ppress_checkout_apply_discount', [$this, 'apply_discount']);

        add_action('wp_ajax_ppress_checkout_remove_discount', [$this, 'remove_discount']);
        add_action('wp_ajax_nopriv_ppress_checkout_remove_discount', [$this, 'remove_discount']);

        add_action('wp_ajax_ppress_update_order_review', [$this, 'update_order_review']);
        add_action('wp_ajax_nopriv_ppress_update_order_review', [$this, 'update_order_review']);

        add_action('wp_ajax_ppress_contextual_state_field', [$this, 'contextual_state_field']);
        add_action('wp_ajax_nopriv_ppress_contextual_state_field', [$this, 'contextual_state_field']);
    }


    public function contextual_state_field()
    {
        check_ajax_referer('ppress_process_checkout', 'csrf');

        $country   = ppressPOST_var('country', '');
        $nameAttr  = ppressPOST_var('name', '');
        $idAttr    = ppressPOST_var('id', '');
        $classAttr = ppressPOST_var('class', '');

        $states = ! empty($country) ? ppress_array_of_world_states(sanitize_text_field($country)) : [];

        ob_start();

        if ( ! empty($states)) {

            printf('<select name="%s" id="%s" class="%s" autocomplete="address-level1" required="required">', $nameAttr, $idAttr, $classAttr);
            echo '<option value="">&mdash;&mdash;&mdash;</option>';
            foreach ($states as $id => $label) {
                printf('<option value="%s">%s</option>', $id, $label);
            }
            echo '</select>';

        } else {

            printf(
                '<input name="%s" type="text" id="%s" class="%s" autocomplete="address-level1" required="required">',
                $nameAttr, $idAttr, $classAttr
            );
        }

        wp_send_json_success(ob_get_clean());
    }

    public function process_checkout_login()
    {
        $nonce_check = check_ajax_referer('ppress_process_checkout', 'ppress_checkout_nonce', false);

        if (false === $nonce_check) {
            wp_send_json_error(
                $this->alert_message(
                    esc_html__('Error processing login. Nonce failed', 'wp-user-avatar')
                )
            );
        }

        $response = LoginAuth::login_auth(
            trim($_POST['ppmb_user_login']),
            $_POST['ppmb_user_pass'],
            true
        );

        if (is_wp_error($response)) {
            wp_send_json_error($this->alert_message($response->get_error_message()));
        }

        wp_send_json_success();
    }

    public function apply_discount()
    {
        try {

            $nonce_check = check_ajax_referer('ppress_process_checkout', 'ppress_checkout_nonce', false);

            if (false === $nonce_check) {

                throw new \Exception(
                    esc_html__('Error applying coupon code. Nonce failed', 'wp-user-avatar')
                );
            }

            if (empty($_POST['coupon_code'])) {

                throw new \Exception(
                    esc_html__('Please enter a coupon code.', 'wp-user-avatar')
                );
            }

            if (empty($_POST['plan_id'])) {

                throw new \Exception(
                    esc_html__('Please enter a plan ID.', 'wp-user-avatar')
                );
            }

            $plan_id     = absint($_POST['plan_id']);
            $coupon_code = sanitize_text_field($_POST['coupon_code']);

            $coupon = CouponFactory::fromCode($coupon_code);

            if ( ! $coupon->exists()) {

                throw new \Exception(
                    sprintf(esc_html__('Coupon code "%s" not found.', 'wp-user-avatar'), $coupon_code)
                );
            }

            if ( ! $coupon->is_valid($plan_id, OrderType::NEW_ORDER)) {

                throw new \Exception(
                    esc_html__('Sorry, this coupon is not valid.', 'wp-user-avatar')
                );
            }

            ppress_session()->set(CheckoutSessionData::COUPON_CODE, [
                'plan_id'     => $plan_id,
                'coupon_code' => $coupon->code,
            ]);

            wp_send_json_success();

        } catch (\Exception $e) {

            wp_send_json_error(
                $this->alert_message($e->getMessage())
            );
        }
    }

    public function remove_discount()
    {
        try {

            check_ajax_referer('ppress_process_checkout', 'ppress_checkout_nonce');

            if (empty($_POST['plan_id'])) {

                throw new \Exception(
                    esc_html__('Please enter a plan ID.', 'wp-user-avatar')
                );
            }

            $plan_id = absint($_POST['plan_id']);

            $session_coupon = ppress_session()->get(CheckoutSessionData::COUPON_CODE);

            if (isset($session_coupon['plan_id'], $session_coupon['coupon_code']) && $plan_id == $session_coupon['plan_id']) {
                ppress_session()->set(CheckoutSessionData::COUPON_CODE, null);
            }

            wp_send_json_success();

        } catch (\Exception $e) {

            wp_send_json_error(
                $this->alert_message($e->getMessage())
            );
        }
    }

    public function process_checkout()
    {
        try {

            $nonce_check = check_ajax_referer('ppress_process_checkout', 'ppress_checkout_nonce', false);

            if (false === $nonce_check) {
                throw new \Exception(esc_html__('Error processing checkout. Nonce failed', 'wp-user-avatar'));
            }

            $_POST = $this->cleanup_posted_data($_POST);

            $plan_id = (int)$_POST['plan_id'];

            if ( ! empty(ppress_settings_by_key('terms_page_id')) && empty($_POST['ppress-terms'])) {
                throw new \Exception(
                    esc_html__('Please read and accept the terms and conditions to proceed with your order.', 'wp-user-avatar')
                );
            }

            $cart_vars = OrderService::init()->checkout_order_calculation([
                'plan_id'     => $plan_id,
                'coupon_code' => CheckoutSessionData::get_coupon_code($plan_id),
                'tax_rate'    => CheckoutSessionData::get_tax_rate($plan_id)
            ]);

            $is_free_checkout = OrderService::init()->is_free_checkout($cart_vars);

            $payment_method = PaymentMethods::get_instance()->get_by_id(ppressPOST_var('ppress_payment_method', ''));

            if ((empty($_POST['ppress_payment_method']) || ! $payment_method) && $is_free_checkout === false) {

                throw new \Exception(
                    esc_html__('No payment method selected. Please try again.', 'wp-user-avatar')
                );
            }

            if ($is_free_checkout) {
                add_filter('ppress_checkout_billing_validation', '__return_false');
            } else {

                $validation_response = $payment_method->validate_fields();

                if (is_wp_error($validation_response)) {
                    throw new \Exception($validation_response->get_error_message());
                }
            }

            $customer_id = $this->register_update_user();

            if (is_wp_error($customer_id)) {
                throw new \Exception(json_encode($customer_id->get_error_messages()));
            }

            $order_id = $this->create_order($customer_id, $cart_vars);

            if (is_wp_error($order_id)) {
                throw new \Exception($order_id->get_error_message());
            }

            $subscription_id = $this->create_subscription($customer_id, $cart_vars);

            if (is_wp_error($subscription_id)) {
                throw new \Exception($subscription_id->get_error_message());
            }

            SubscriptionRepository::init()->updateColumn($subscription_id, 'parent_order_id', $order_id);
            OrderRepository::init()->updateColumn($order_id, 'subscription_id', $subscription_id);

            if ( ! $payment_method || ! $payment_method->get_id()) {
                $payment_method = StoreGateway::get_instance();
            }

            $this->save_eu_vat_details($payment_method->id, $order_id);

            if ($is_free_checkout) {
                OrderFactory::fromId($order_id)->complete_order();
                SubscriptionFactory::fromId($subscription_id)->activate_subscription();

                $process_payment = (new CheckoutResponse())->set_is_success(true);

            } else {

                /** @var CheckoutResponse $process_payment */
                $process_payment = $payment_method->process_payment(
                    $order_id,
                    $subscription_id,
                    $customer_id
                );
            }

            $order = OrderFactory::fromId($order_id);

            wp_send_json([
                'success'           => $process_payment->is_success,
                'redirect_url'      => $process_payment->redirect_url,
                'gateway_response'  => $process_payment->gateway_response,
                'error_message'     => $this->alert_message($process_payment->error_message),
                'order_success_url' => ppress_get_success_url($order->order_key, $order->payment_method),
            ]);

        } catch (\Exception $e) {

            $error_message = ppress_is_json($e->getMessage()) ? json_decode($e->getMessage(), true) : $e->getMessage();

            ppress_log_error($error_message);

            wp_send_json_error(
                $this->alert_message($error_message)
            );
        }
    }

    /**
     * @param $country_code
     * @param $country_state_code
     * @param $vat_number
     * @param PlanEntity $planObj
     *
     * @return float|int|string
     * @throws \Exception
     */
    private function get_checkout_tax_rate($country_code, $country_state_code, $vat_number, $planObj)
    {
        if ( ! TaxService::init()->is_tax_enabled()) return 0;

        if (TaxService::init()->calculate_tax_based_on_setting() == 'base') {
            $base_country = ppress_business_country();
            if ( ! empty($base_country)) {
                $country_code       = $base_country;
                $country_state_code = ppress_business_state();
            }
        }

        $tax_rate = TaxService::init()->get_country_tax_rate($country_code, $country_state_code);

        if (TaxService::init()->is_eu_vat_enabled() && TaxService::init()->is_eu_countries($country_code)) {

            $business_country          = ppress_business_country();
            $same_country_rule_setting = TaxService::init()->eu_vat_same_country_rule_setting();

            if ($business_country == $country_code && $same_country_rule_setting == 'charge_always') {
                return $tax_rate;
            }

            if ($business_country == $country_code && $same_country_rule_setting == 'no_charge') {
                return 0;
            }

            if (empty($vat_number)) return $tax_rate;

            $session_data = [
                'plan_id'      => $planObj->id,
                'vat_number'   => $vat_number,
                'country_code' => $country_code
            ];

            if (TaxService::init()->is_vat_number_validation_active()) {

                $response = EuVatApi::check_vat($vat_number, $country_code);

                if ( ! $response->is_valid()) {
                    throw new \Exception($response->get_error_message(), $response->error);
                }

                $session_data['company_name']    = $response->name;
                $session_data['company_address'] = $response->address;
                $session_data['is_valid']        = $response->is_valid();
            }

            $session_data['reverse_charged'] = true;

            ppress_session()->set(CheckoutSessionData::EU_VAT_NUMBER, $session_data);

            $tax_rate = 0;
        }

        return $tax_rate;
    }

    public function update_order_review()
    {
        check_ajax_referer('ppress_process_checkout', 'csrf');

        try {

            if (empty($_POST['plan_id'])) {

                throw new \Exception(
                    esc_html__('Please enter a plan ID.', 'wp-user-avatar')
                );
            }

            global $cart_vars;

            $planObj = ppress_get_plan(absint($_POST['plan_id']));

            $country_code       = sanitize_text_field(ppressPOST_var('country', '', true));
            $country_state_code = sanitize_text_field(ppressPOST_var('state', '', true));
            $vat_number         = sanitize_text_field(ppressPOST_var('vat_number', '', true));

            $tax_rate = $this->get_checkout_tax_rate($country_code, $country_state_code, $vat_number, $planObj);

            ppress_session()->set(CheckoutSessionData::TAX_RATE, [
                'plan_id'  => $planObj->id,
                'tax_rate' => $tax_rate,
                'country'  => $country_code,
                'state'    => $country_state_code
            ]);

            $cart_vars = OrderService::init()->checkout_order_calculation([
                'plan_id'     => $planObj->id,
                'coupon_code' => CheckoutSessionData::get_coupon_code($planObj->id),
                'tax_rate'    => CheckoutSessionData::get_tax_rate($planObj->id)
            ]);

            ob_start();
            ppress_render_view(
                'checkout/form-checkout-sidebar', [
                    'plan'      => $planObj,
                    'cart_vars' => $cart_vars
                ]
            );
            $checkout_sidebar_html = ob_get_clean();

            ob_start();
            ppress_render_view('checkout/form-payment-methods', [
                'plan'      => $planObj,
                'cart_vars' => $cart_vars
            ]);
            $checkout_payment_methods_html = ob_get_clean();

            ob_start();
            ppress_render_view('checkout/form-checkout-submit-btn', ['order_total' => $cart_vars->total, 'plan' => $planObj]);
            $checkout_submit_btn = ob_get_clean();

            wp_send_json_success(
                apply_filters('ppress_update_order_review_response', [
                    'fragments' => apply_filters('ppress_update_order_review_fragments', [
                        '.ppress-checkout_order_summary-wrap'   => $checkout_sidebar_html,
                        '.ppress-checkout_payment_methods-wrap' => $checkout_payment_methods_html,
                        '.ppress-checkout-submit'               => $checkout_submit_btn
                    ])
                ], $cart_vars, $planObj)
            );

        } catch (\Exception $e) {

            wp_send_json_error(
                $this->alert_message($e->getMessage())
            );
        }
    }
}