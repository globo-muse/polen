<?php
namespace Polen\Admin;

use Polen\Includes\Polen_Order;

class Polen_Admin_Order_Custom_Fields_Deadline_BulkActions
{

    const KEY_BASE_VALUE = '_deadline-';

    public function __construct( bool $static = false )
    {
        if( $static ) {
            add_action( 'bulk_actions-edit-shop_order', [ $this, 'add_deadline_option' ], 10, 1 );
            add_action( 'handle_bulk_actions-edit-shop_order', [ $this, 'handler_deadline_bulkactions' ], 10, 3 );
        }
    }

    public function add_deadline_option( $actions )
    {
        $actions[ self::KEY_BASE_VALUE . '7' ] = 'Deadline D+7';
        $actions[ self::KEY_BASE_VALUE . '15' ] = 'Deadline D+15';
        $actions[ self::KEY_BASE_VALUE . '30' ] = 'Deadline D+30';
        return $actions;
    }


    public function handler_deadline_bulkactions( $redirect_to, $action, $ids )
    {
        $value = intval( str_replace( self::KEY_BASE_VALUE, '', $action ) );
        $deadline_diff_add = $value * DAY_IN_SECONDS;
        foreach( $ids as $order_id ) {
            $order = wc_get_order( $order_id );
            if( empty( $order ) ) {
                wc_add_notice( "Order {$order_id} inválida", 'error' );
            }
            $deadline = $order->get_meta( Polen_Order::META_KEY_DEADLINE, true );
            $deadline_added = $deadline + $deadline_diff_add;
            $order->update_meta_data( Polen_Order::META_KEY_DEADLINE, $deadline_added );
            $order->save();
        }
        wp_safe_redirect( site_url( $redirect_to ) );
        exit;
    }

    
}
