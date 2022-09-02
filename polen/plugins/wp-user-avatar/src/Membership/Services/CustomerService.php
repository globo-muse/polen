<?php

namespace ProfilePress\Core\Membership\Services;

use ProfilePress\Core\Membership\Repositories\CustomerRepository;

class CustomerService
{
    /**
     * @param $customer_id
     *
     * @return false|int
     */
    public function delete_customer($customer_id)
    {
        return CustomerRepository::init()->delete($customer_id);
    }

    /**
     * @return self
     */
    public static function init()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}