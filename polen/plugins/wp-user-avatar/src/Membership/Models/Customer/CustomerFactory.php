<?php

namespace ProfilePress\Core\Membership\Models\Customer;

use ProfilePress\Core\Membership\Models\FactoryInterface;
use ProfilePress\Core\Membership\Repositories\CustomerRepository;

class CustomerFactory implements FactoryInterface
{
    /**
     * @param $data
     *
     * @return CustomerEntity
     */
    public static function make($data)
    {
        return new CustomerEntity($data);
    }

    /**
     * @param $id
     *
     * @return CustomerEntity
     */
    public static function fromId($id)
    {
        return CustomerRepository::init()->retrieve(absint($id));
    }

    /**
     * @param $user_id
     *
     * @return CustomerEntity
     */
    public static function fromUserId($user_id)
    {
        return CustomerRepository::init()->retrieveByUserID(absint($user_id));
    }
}