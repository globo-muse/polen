<?php

namespace ProfilePress\Core\Membership\PaymentMethods;

class StoreGateway extends AbstractPaymentMethod
{
    public function __construct()
    {
        $this->id                 = 'manual';
        $this->backend_only       = true;
        $this->method_title       = esc_html__('Store Payments', 'wp-user-avatar');
        $this->method_description = esc_html__('A payment method for manually creating orders and processing free orders. No money is actually collected.', 'wp-user-avatar');
    }

    public function admin_settings()
    {
        $settings = parent::admin_settings();

        unset($settings['title']);
        unset($settings['description']);

        return $settings;
    }

    /** fulfill contract */
    public function validate_fields()
    {

    }

    /** fulfill contract */
    public function process_payment($order_id, $subscription_id, $customer_id)
    {

    }

    /** fulfill contract */
    public function process_webhook()
    {

    }
}