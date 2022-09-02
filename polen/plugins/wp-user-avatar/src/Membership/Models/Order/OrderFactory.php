<?php

namespace ProfilePress\Core\Membership\Models\Order;

use ProfilePress\Core\Membership\Models\FactoryInterface;
use ProfilePress\Core\Membership\Repositories\OrderRepository;

class OrderFactory implements FactoryInterface
{
    /**
     * @param $data
     *
     * @return OrderEntity
     */
    public static function make($data)
    {
        return new OrderEntity($data);
    }

    /**
     * @param $id
     *
     * @return OrderEntity
     */
    public static function fromId($id)
    {
        return OrderRepository::init()->retrieve(absint($id));
    }

    /**
     * @param $id
     *
     * @return OrderEntity|false
     */
    public static function fromTransactionId($id)
    {
        $orders = OrderRepository::init()->retrieveBy([
            'transaction_id' => $id
        ]);

        if ( ! empty($orders)) return $orders[0];

        return false;
    }

    /**
     * @param $order_key
     *
     * @return OrderEntity
     */
    public static function fromOrderKey($order_key)
    {
        return OrderRepository::init()->retrieveByOrderKey(sanitize_text_field($order_key));
    }
}