<?php

namespace ProfilePress\Core\Membership\PaymentMethods;

use ProfilePress\Core\Membership\PaymentMethods\Stripe\Stripe;

class PaymentMethods
{
    public function __construct()
    {
        $this->registered_methods();
    }

    /**
     * @return AbstractPaymentMethod[]
     */
    public function registered_methods()
    {
        $methods = [
            StoreGateway::get_instance(),
            Stripe::get_instance()
        ];

        return apply_filters('ppress_payment_methods', $methods);
    }

    /**
     * @return PaymentMethodInterface[]
     */
    public function get_all()
    {
        $bucket = [];

        if (count($this->registered_methods()) > 0) {

            foreach ($this->registered_methods() as $method) {
                $bucket[$method->id] = $method;
            }
        }

        return $bucket;
    }

    /**
     * Returns payment method ID and title.
     *
     * @return PaymentMethodInterface[]
     */
    public function get_enabled_methods($include_backend_only = false)
    {
        $bucket = [];

        foreach ($this->get_all() as $method) {
            if ($method->is_enabled()) {

                if ( ! $include_backend_only && $method->is_backend_only()) continue;

                $bucket[$method->id] = $method;
            }
        }

        return $bucket;
    }

    /**
     * @return false|string
     */
    public function get_default_method()
    {
        $default = array_key_first($this->get_enabled_methods());
        if ( ! $default) $default = '';

        return ppress_var(get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []), 'default_payment_method', $default, true);
    }

    /**
     * @param $id
     *
     * @return AbstractPaymentMethod|false
     */
    public function get_by_id($id)
    {
        return ppress_var($this->get_all(), $id);
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}