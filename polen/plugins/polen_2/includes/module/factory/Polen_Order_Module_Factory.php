<?php
namespace Polen\Includes\Module\Factory;

use Polen\Includes\Module\Orders\Polen_Module_B2B_Only;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Campaign;
use WC_Order;

class Polen_Order_Module_Factory
{

    public static function create_order_from_campaing(WC_Order $object)
    {
        $campaing = Polen_Campaign::get_order_campaing_slug($object);
        if('b2b-only' === $campaing) {
            return new Polen_Module_B2B_Only($object->get_id(), $object->get_order_key());
        }
        return new Polen_Order_Module($object);
    }
}
