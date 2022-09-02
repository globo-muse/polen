<?php

namespace ProfilePress\Core\Membership\Controllers;

use ProfilePress\Core\Classes\FileUploader;
use ProfilePress\Core\Classes\PasswordReset;
use ProfilePress\Core\Classes\RegistrationAuth;
use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\Membership\CheckoutFields as CF;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\CartEntity;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionTrialPeriod;
use ProfilePress\Core\Membership\PaymentMethods\StoreGateway;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\OrderService;
use ProfilePress\Core\Membership\Services\SubscriptionService;

trait CheckoutTrait
{
    public function cleanup_posted_data($POST)
    {
        // cleanup $_POST
        unset($POST['_wp_http_referer']);
        unset($POST['action']);
        unset($POST['ppress_checkout_nonce']);
        // remove checkout login form fields
        unset($POST['ppmb_user_login']);
        unset($POST['ppmb_user_pass']);
        //remove hidden required fields
        unset($POST['required-fields']);

        return ppress_clean($POST, 'trim');
    }

    public function alert_message($messages, $type = 'error')
    {
        if (empty($messages)) return '';

        $alert = sprintf('<div class="ppress-checkout-alert ppress-%s">', $type);

        if (is_array($messages) && count($messages) > 1) {
            $alert .= '<ul>';
            foreach ($messages as $message) {
                $alert .= sprintf('<li>%s</li>', $message);
            }
            $alert .= '</ul>';
        }

        if (is_array($messages) && 1 == count($messages)) {
            $alert .= sprintf('<p>%s</p>', $messages[0]);
        }

        if (is_string($messages)) {
            $alert .= sprintf('<p>%s</p>', $messages);
        }

        $alert .= '</div>';

        return $alert;
    }

    public function should_skip_validation($field_key, $field_settings)
    {
        if (is_user_logged_in() && ((ppress_var($field_settings, 'logged_in_hide') == 'true') || in_array($field_key, CF::logged_in_hidden_fields()))) {
            return true;
        }

        return false;
    }

    public function validate_required_field($field_key, $field_type)
    {
        if ('file' == $field_type && ! empty($_FILES[$field_key]['name'])) {
            return true;
        }

        if (ppress_is_boolean($_POST[$field_key]) || ! empty($_POST[$field_key])) {
            return true;
        }

        return false;
    }

    /**
     * @param int $customer_id
     * @param CartEntity $cart_vars
     *
     * @return int|\WP_Error
     */
    public function create_subscription($customer_id, $cart_vars)
    {
        $plan_obj = ppress_get_plan((int)$_POST['plan_id']);
        $plan_id  = $plan_obj->id;

        // delete all pending subs of plan_id by customer
        SubscriptionRepository::init()->delete_pending_subs($customer_id, $plan_id);

        $subscription = new SubscriptionEntity();

        $subscription->plan_id = $plan_id;

        $subscription->customer_id       = $customer_id;
        $subscription->billing_frequency = $plan_obj->billing_frequency;
        $subscription->initial_amount    = $cart_vars->initial_amount;
        $subscription->initial_tax_rate  = $cart_vars->initial_tax_rate;
        $subscription->initial_tax       = $cart_vars->initial_tax;

        $subscription->recurring_amount   = $cart_vars->recurring_amount;
        $subscription->recurring_tax      = $cart_vars->recurring_tax;
        $subscription->recurring_tax_rate = $cart_vars->recurring_tax_rate;

        $subscription->total_payments = $plan_obj->total_payments;
        $subscription->trial_period   = $plan_obj->has_free_trial() ? $plan_obj->free_trial : SubscriptionTrialPeriod::DISABLED;

        $subscription->status = SubscriptionStatus::PENDING;

        $subscription->expiration_date = SubscriptionService::init()->get_plan_expiration_datetime($plan_id);

        if (Calculator::init($subscription->recurring_amount)->isNegativeOrZero()) {
            $subscription->expiration_date = '';
        }

        $subscription_id = $subscription->save();

        if ( ! $subscription_id || ! is_int($subscription_id)) {
            return new \WP_Error('subscription_creation_failed', esc_html__('Unable to create subscription. Please try again', 'wp-user-avatar'));
        }

        return $subscription_id;
    }

    /**
     * @param $customer_id
     * @param CartEntity $cart_vars
     *
     * @return int|\WP_Error
     */
    public function create_order($customer_id, $cart_vars)
    {
        $plan_id = (int)$_POST['plan_id'];

        $payment_method = sanitize_text_field(ppressPOST_var('ppress_payment_method', StoreGateway::get_instance()->get_id(), true));

        // delete all pending orders of plan_id by customer
        OrderRepository::init()->delete_pending_orders($customer_id, $plan_id);

        $billing_fields = CheckoutFields::standard_billing_fields();

        $order = new OrderEntity();

        if (is_array($billing_fields) && ! empty($billing_fields)) {
            foreach ($billing_fields as $field_key => $field) {
                $posted_field = $payment_method . '_' . $field_key;
                if ((isset($_POST[$posted_field]) && ppress_is_boolean($_POST[$posted_field])) || ! empty($_POST[$posted_field])) {

                    $key = str_replace('ppress_', '', $field_key);

                    $order->$key = ppress_clean($_POST[$posted_field]);
                }
            }
        }

        $order->order_key      = OrderService::init()->generate_order_key();
        $order->plan_id        = $plan_id;
        $order->customer_id    = $customer_id;
        $order->order_type     = OrderType::NEW_ORDER;
        $order->mode           = ppress_get_payment_mode();
        $order->payment_method = sanitize_text_field($payment_method);
        $order->status         = OrderStatus::PENDING;
        $order->coupon_code    = $cart_vars->coupon_code;
        $order->discount       = $cart_vars->discount_amount;
        $order->subtotal       = $cart_vars->sub_total;
        $order->tax            = $cart_vars->tax_amount;
        $order->tax_rate       = $cart_vars->tax_rate;
        $order->total          = $cart_vars->total;
        $order->currency       = ppress_get_currency();
        $order->ip_address     = ppress_get_ip_address();
        $order_id              = $order->save();

        if ( ! $order_id || ! is_int($order_id)) {
            return new \WP_Error('order_creation_failed', esc_html__('Unable to create order. Please try again', 'wp-user-avatar'));
        }

        return $order_id;
    }

    /**
     * @param $user_id
     *
     * @return int|\WP_Error
     */
    public function create_customer($user_id)
    {
        $customer = CustomerFactory::fromUserId($user_id);

        $customer_id = $customer->get_id();

        if ( ! $customer->exists()) {
            $customer->user_id = $user_id;
            $customer_id       = $customer->save();
            if ( ! $customer_id) {
                return new \WP_Error('customer_creation_failure', esc_html__('Unable to create customer. Please try again', 'wp-user-avatar'));
            }
        }

        return $customer_id;
    }

    public function register_update_user()
    {
        $error_bucket = new \WP_Error();

        $is_user_update = false;

        $billing_fields      = CF::billing_fields();
        $account_info_fields = CF::account_info_fields();

        $should_validate_fields = [];

        // --------START ---------   validation for required fields ----------------------//
        foreach ($account_info_fields as $field_key => $field_settings) {

            if ($this->should_skip_validation($field_key, $field_settings)) continue;

            $should_validate_fields[] = $field_key;

            if (ppress_var($field_settings, 'required') == 'true') {

                if ( ! $this->validate_required_field($field_key, $field_settings['field_type'])) {
                    $error_bucket->add('required_field_empty', sprintf(__('%s field is required', 'wp-user-avatar'), $field_settings['label']));
                }
            }
        }

        $payment_method = ppressPOST_var('ppress_payment_method', '');

        if (apply_filters('ppress_checkout_billing_validation', true, $billing_fields)) {
            foreach ($billing_fields as $field_key => $field_settings) {

                if ($this->should_skip_validation($field_key, $field_settings)) continue;

                $should_validate_fields[] = $field_key;

                if (ppress_var($field_settings, 'required') == 'true') {
                    // add payment method id from billing field IDs so validation will work.
                    if ( ! empty($payment_method)) $field_key = $payment_method . '_' . $field_key;

                    if ( ! $this->validate_required_field($field_key, $field_settings['field_type'])) {
                        $error_bucket->add('required_field_empty', sprintf(__('%s field is required', 'wp-user-avatar'), $field_settings['label']));
                    }
                }
            }
        }

        if ($error_bucket->has_errors()) {
            return $error_bucket;
        }

        // --------END ---------   validation for required fields ----------------------//


        // --------START ---------   validation ----------------------//
        $email  = ppressPOST_var(CF::ACCOUNT_EMAIL_ADDRESS, '');
        $email2 = ppressPOST_var(CF::ACCOUNT_CONFIRM_EMAIL_ADDRESS, '');

        if (in_array(CF::ACCOUNT_EMAIL_ADDRESS, $should_validate_fields)) {

            if (isset($_POST[CF::ACCOUNT_CONFIRM_EMAIL_ADDRESS]) && ($email != $email2)) {
                $error_bucket->add('email_mismatch', esc_html__('Email addresses do not match', 'wp-user-avatar'));
            } elseif ( ! is_email($email)) {
                $error_bucket->add('invalid_email', esc_html__('Email address is not valid', 'wp-user-avatar'));
            } elseif (email_exists($email)) {
                $error_bucket->add('email_used', esc_html__('Email already used. Login or use a different email to complete your order', 'wp-user-avatar'));
            }
        }

        if (in_array(CF::ACCOUNT_USERNAME, $should_validate_fields)) {

            $username = ppressPOST_var(CF::ACCOUNT_USERNAME, '');

            if (empty($username)) {
                $username = sanitize_user(current(explode('@', $email)), true);
                // Ensure username is unique.
                $append     = 1;
                $o_username = $username;
                while (username_exists($username)) {
                    $username = $o_username . $append;
                    $append++;
                }
            }

            if ( ! validate_username($username)) {
                $error_bucket->add('invalid_username', esc_html__('Username is invalid because it uses illegal characters', 'wp-user-avatar'));
            }
        }

        if (in_array(CF::ACCOUNT_PASSWORD, $should_validate_fields)) {

            $password  = ppressPOST_var(CF::ACCOUNT_PASSWORD, '');
            $password2 = ppressPOST_var(CF::ACCOUNT_CONFIRM_PASSWORD, '');

            if (isset($_POST[CF::ACCOUNT_CONFIRM_PASSWORD]) && ($password != $password2)) {
                $error_bucket->add('password_mismatch', esc_html__('Passwords do not match', 'wp-user-avatar'));
            }

            $flag_to_send_password_reset = false;

            if (empty($password) && (ppressPOST_var('ppmb_password_present') != 'true')) {
                $password                    = wp_generate_password(24);
                $flag_to_send_password_reset = apply_filters('ppress_enable_auto_send_password_reset_flag', true);
            }
        }

        // --------END ---------   validation ----------------------//

        // --------START ---------   validation for file upload ----------------------//
        $uploads = FileUploader::init();
        if ( ! empty($uploads)) {
            foreach ($uploads as $field_key => $uploaded_filename_or_wp_error) {
                if (is_wp_error($uploads[$field_key])) {
                    $error_bucket->add('file_upload_error', $uploads[$field_key]->get_error_message());
                }
            }
        }
        // --------END ---------   validation for file upload ----------------------//

        if ($error_bucket->has_errors()) {
            return $error_bucket;
        }

        $valid_userdata_fields = array_keys(CF::standard_account_info_fields()) + ['ppmb_password_present'];

        $real_userdata = array_filter(apply_filters('ppress_checkout_registration_user_data', [
            'user_login'   => isset($username) ? $username : '',
            'user_pass'    => isset($password) ? $password : '',
            'user_email'   => $email,
            'user_url'     => ppressPOST_var(CF::ACCOUNT_WEBSITE, ''),
            'nickname'     => ppressPOST_var(CF::ACCOUNT_NICKNAME, ''),
            'display_name' => ppressPOST_var(CF::ACCOUNT_DISPLAY_NAME, ''),
            'first_name'   => ppressPOST_var(CF::ACCOUNT_FIRST_NAME, ''),
            'last_name'    => ppressPOST_var(CF::ACCOUNT_LAST_NAME, ''),
            'description'  => ppressPOST_var(CF::ACCOUNT_BIO, ''),
        ]));

        // get the data for use by update_meta
        $custom_usermeta = [];

        // loop over the $_POST data and create an array of the invalid userdata/ custom usermeta
        foreach ($_POST as $key => $value) {

            // remove payment method prefix from key
            $key = str_replace($payment_method . '_', '', $key);

            if (in_array($key, $valid_userdata_fields) || in_array($key, ppress_reserved_field_keys())) continue;

            if ( ! in_array($key, array_keys(ppress_custom_fields_key_value_pair(true)))) continue;

            $custom_usermeta[$key] = is_array($value) ? array_map('sanitize_textarea_field', $value) : sanitize_textarea_field($value);
        }

        // merge real data(for use by wp_insert_user()) and custom fields data
        // $real_userdata comes second so custom user meta won't override it.
        $user_data = array_merge($custom_usermeta, $real_userdata);

        $reg_form_errors = apply_filters('ppress_checkout_registration_validation', $error_bucket, $user_data);

        if (is_wp_error($reg_form_errors) && $reg_form_errors->get_error_code() != '') {
            return $reg_form_errors->get_error_message();
        }

        do_action('ppress_before_checkout_registration', $user_data);

        if (is_user_logged_in()) {
            $user_id             = get_current_user_id();
            $is_user_update      = true;
            $real_userdata['ID'] = $user_id;
            $user_id             = wp_update_user($real_userdata);
        } else {
            $user_id = wp_insert_user($real_userdata);
        }

        if (is_wp_error($user_id)) {
            return $user_id->get_error_message();
        }

        $customer_id = $this->create_customer($user_id);

        // --------START ---------   register custom field ----------------------//

        // if we get to this point, it means the files pass validation defined above.
        // array of files uploaded. Array key is the "custom field key" and the filename as the array value.
        $custom_usermeta['pp_uploaded_files'] = $uploads;

        if (is_array($custom_usermeta)) {

            foreach ($custom_usermeta as $key => $value) {
                if ( ! empty($value)) {
                    update_user_meta($user_id, $key, $value);
                    do_action('ppress_after_custom_field_update', $key, $value, $user_id, 'checkout');
                }
            }
        }

        // --------END ---------   register custom field ----------------------//

        if (isset($flag_to_send_password_reset, $username) && $flag_to_send_password_reset === true) {
            PasswordReset::retrieve_password_func($username);
        }

        if ( ! $is_user_update && isset($user_id, $password)) {
            // record signup via
            add_user_meta($user_id, '_pp_signup_via', 'checkout');

            RegistrationAuth::send_welcome_email($user_id, $password);

            ppress_wp_new_user_notification($user_id, null, 'admin');
        }

        return $customer_id;
    }

    public function save_eu_vat_details($payment_method_id, $order_id)
    {
        $key = sprintf('%s_ppress_vat_number', $payment_method_id);

        if ( ! empty($_POST[$key])) {

            $order      = OrderFactory::fromId($order_id);
            $vat_number = sanitize_text_field($_POST[$key]);

            $vat_data = CheckoutSessionData::get_eu_vat_number_details($order->plan_id, $vat_number);

            $order->update_meta(OrderEntity::EU_VAT_NUMBER, sanitize_text_field($_POST[$key]));

            $order->update_meta(
                OrderEntity::EU_VAT_COUNTRY_CODE,
                ppress_var($vat_data, 'country_code', '')
            );

            $order->update_meta(
                OrderEntity::EU_VAT_NUMBER_IS_VALID,
                ppress_var($vat_data, 'is_valid') === true ? 'true' : 'false'
            );

            $order->update_meta(
                OrderEntity::EU_VAT_IS_REVERSE_CHARGED,
                ppress_var($vat_data, 'reverse_charged') === true ? 'true' : 'false'
            );

            if ( ! empty($vat_data['company_name'])) {
                $order->update_meta(OrderEntity::EU_VAT_COMPANY_NAME, sanitize_text_field($vat_data['company_name']));
            }

            if ( ! empty($vat_data['company_address'])) {
                $order->update_meta(OrderEntity::EU_VAT_COMPANY_ADDRESS, sanitize_text_field($vat_data['company_address']));
            }
        }
    }
}