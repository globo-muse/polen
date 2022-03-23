<?php

use Polen\Social_Base\Social_Base_Order;
use Polen\Social_Base\Social_Base_Product;


/**
 * Verifica se um Produto é social-base
 */
function product_is_social_base( $product )
{
    return Social_Base_Product::product_is_social_base( $product );
}


/**
 * Verifica se uma Order é social-base
 */
function order_is_social_base( $order )
{
    return Social_Base_Order::is_social( $order );
}
