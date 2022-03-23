<?php

namespace Polen\Includes\Order_Review;

use Polen\Includes\Talent\Polen_Talent_Controller_Base;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Polen_Order_Review;

class Polen_Order_Review_Controller extends Polen_Talent_Controller_Base
{
    public function create_order_review( $a )
    {

        $user_id  = get_current_user_id();
        $rate     = filter_input( INPUT_POST, 'rate', FILTER_SANITIZE_NUMBER_INT );
        $comment  = filter_input( INPUT_POST, 'comment', FILTER_SANITIZE_STRING );
        $order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
        $approved = '0';

        try {
            $order = wc_get_order( $order_id );
            $cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
            $talent_id = $cart_item->get_talent_id();
        
            $order_review = new Polen_Order_Review();
            $order_review->set_user_id( $user_id );
            $order_review->set_comment_karma( $rate );
            $order_review->set_rate( $rate );
            $order_review->set_comment_content( $comment );
            $order_review->set_order_id( $order_id );
            $order_review->set_comment_approved( $approved );
            $order_review->set_talent_id( $talent_id );

            $order_review->save();
            
            wp_send_json_success( 'Comentário criado com sucesso. Em análise', 201 );
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
        }
        wp_die();
    }
}
