<?php

namespace Polen\Includes\Cart;

use Automattic\WooCommerce\Admin\Overrides\Order;
use Polen\Includes\Cart\Polen_Cart_Item;

class Polen_Cart_Item_Factory
{
    
    /**
     * Pega o item da Order o Item direto do WC
     * @param Order $order
     * @return Automattic\WooCommerce\Admin\Overrides\Order
     * @throws Exception
     */
//    public static function cart_item_wc_factory_from_order( Order $order )
//    {
//        $data = $order->get_items();
//        foreach( $data as $item_id => $item ) {
//            return $item;
//        }
//        throw new Exception( 'has no item into the cart', 500 );
//    }
    
    
    /**
     * Pega o item da Order ja nos formato Polen_Cart_Item
     * @param \WC_Order $order
     * @return \Polen\Includes\Cart\Polen_Cart_Item
     * @throws Exception
     */
    public static function polen_cart_item_from_order( $order ) : Polen_Cart_Item
    {
        $data = $order->get_items();
        if(empty($data)) {
            return '';
        }
        foreach( $data as $item_id => $item ) {
            return new Polen_Cart_Item( $item );
        }
        throw new \Exception( 'has no item into the cart', 500 );
    }
}
