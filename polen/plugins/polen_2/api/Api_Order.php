<?php
namespace Polen\Api;

use Order_Class;

class Api_Order
{
    public function get_flow_by_order( $request )
    {
        $order_id = $request[ 'id' ];
        $order = wc_get_order( $order_id );

        return $this->get_status_flow( $order );
    }



    /**
     * 
     * @param WC_Order
     */
    protected function get_status_flow( \WC_Order $order )
    {
        $order_id = $order->get_id();
        $order_status = $order->get_status();
        $email_billing = $order->get_billing_email();
        return array_values( Order_Class::polen_get_order_flow_obj( $order_id, $order_status, $email_billing ) );
    }
}