<?php

use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_WooCommerce;

/**
 * Criar uma primeira ORDER
 */
function create_social_order( $email, $cidade, $name )
{
    $product_id = 3691;
    if( empty( $product_id ) ) {
        throw new \Exception( 'Produto inválido', 500 );
    }
    // $email = 'rodolfoneto@gmail.com';
    // $cidade = 'Recife';
    // $name = 'Rodolfo';
    $whatsapp_number = '';
    
    $user = get_user_by( 'email', $email );
    if( empty( $user ) ) {
        $new_password = wp_generate_password( 6 );
        $user_id = wc_create_new_customer( $email, $email, $new_password );
    } else {
        $user_id = $user->ID;
    }
    $args = array(
        'status'        => Polen_WooCommerce::ORDER_STATUS_TALENT_ACCEPTED,
        'customer_id'   => $user_id,
        'customer_note' => 'video-polen-social',
        'created_via'   => 'created_terminal',
    );

    $order = wc_create_order( $args );
    $order_id = $order->get_id();
    $product = wc_get_product( $product_id );
    $order_item_id = wc_add_order_item( $order_id, array(
        'order_item_name' => $product->get_title(),
        'order_item_type' => 'line_item', // product
    ));
    $order->add_meta_data( '_polen_customer_email', $email, true);
    $billing_address = array('country' => 'BR', 'email' => $email );
    $order->set_address( $billing_address, 'billing' );

    // $talent_name = $product->get_title();
    $datetime = new DateTime();
    $datetime->add( new DateInterval('P30D') );
    // $order->add_meta_data( Polen_Order::META_KEY_DEADLINE, $datetime->getTimestamp(), true );
    add_metadata( 'post', $order_id, Polen_Order::META_KEY_DEADLINE, $datetime->getTimestamp(), true );
    add_metadata( 'post', $order_id, '_polen_customer_email', $email, true );
    add_metadata( 'post', $order_id, 'social_base', '1', true );
    add_metadata( 'post', $order_id, 'social_base_campaign', 'reserva-1p-5p', true );

    $instruction = "{$name} de $cidade";
    // $final_instruction = str_replace( $product->get_title(), $instruction );
    
    wc_add_order_item_meta( $order_item_id, '_qty', 1, true );
    wc_add_order_item_meta( $order_item_id, '_product_id', $product->get_id(), true );
    wc_add_order_item_meta( $order_item_id, '_line_subtotal', 0, true );
    wc_add_order_item_meta( $order_item_id, '_line_total', 0, true );
    //Polen Custom Meta Order_Item
    wc_add_order_item_meta( $order_item_id, 'offered_by'            , '', true );
    wc_add_order_item_meta( $order_item_id, 'video_to'              , 'to_myself', true );
    wc_add_order_item_meta( $order_item_id, 'name_to_video'         , $name, true );
    wc_add_order_item_meta( $order_item_id, 'email_to_video'        , $email, true );
    wc_add_order_item_meta( $order_item_id, 'video_category'        , 'Novidade', true );
    wc_add_order_item_meta( $order_item_id, 'instructions_to_video' , $instruction, true );
    wc_add_order_item_meta( $order_item_id, 'whatsapp_number'       , $whatsapp_number, true );
    // wc_add_order_item_meta( $order_item_id, 'instructions_to_video' , $final_instruction, true );
    wc_add_order_item_meta( $order_item_id, 'allow_video_on_page'   , 'no', true );
    wc_add_order_item_meta( $order_item_id, '_fee_amount'           , 0, true );
    wc_add_order_item_meta( $order_item_id, '_line_total'           , 0, true );
    $order = new \WC_Order( $order_id );
    $order->calculate_totals();
    return $order->get_id();
}
