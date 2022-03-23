<?php

use Polen\Includes\Polen_Talent;
use Polen\Social\Social;
use Polen\Social\Social_Category;
use Polen\Social\Social_Order;
use Polen\Social\Social_Product;

define( 'CATEGORY_SLUG_CRIESP', 'criesp' );

/**
 * Verifica se está dentro do App de Social ou seja /social/crianca-esperanca
 * @return bool
 */
function social_is_in_social_app()
{
    return Social::is_social_app();
}

function social_get_criesp_url()
{
	return home_url( '/social/crianca-esperanca' );
}

/**
 * Pega o object da Categoria Social
 * @param string
 * @return Social_Category
 */
function social_get_category_base( $category_slug = CATEGORY_SLUG_CRIESP )
{
    return Social_Category::get_category_by_slug( $category_slug );
}


/**
 * Pega todos os produtos de uma Categoria Social
 * @param Social_Category
 * @return array
 */
function social_get_products_by_category_slug( $category )
{
    $products = Social_Product::get_all_products_by_category( $category );
    return $products;
}


/**
 * Saber se um produto é social no caso agora criança esperanca
 * @param WC_Product
 * @param Social_Category
 * @return bool
 */
function social_product_is_social( $producty, $category )
{
    return Social_Product::product_is_social( $producty, $category );
}


/**
 * Verifica se o carrinho é social, se o produto é social
 * @return bool
 */
function social_cart_is_social()
{
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $is_social = social_product_is_social( $_product, social_get_category_base() );
    }
    return $is_social;
}


/**
 * Verifica se a ORDER é social
 * @param \WC_Order
 * @return bool
 */
function social_order_is_social( $order )
{
    return Social_Order::is_social( $order );
}


/**
 * Verifica se o Talento é social.
 * Verificando se o Produto é social
 * pelo User_ID
 * @param int
 * @return bool
 */
function social_user_is_social( $user_id )
{
    $product = Polen_Talent::get_product_by_user_id( $user_id );
    if( empty( $product ) ) {
        return false;
    }
    return social_product_is_social( $product, social_get_category_base() );
}


/**
 * Paga a imagem padrao setada na categoria
 * @param \WP_Term
 * @return string
 */
function social_get_image_by_category( $category )
{
    $category = social_get_category_base();
    $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
    $image = wp_get_attachment_url( $thumbnail_id );
    return $image;
}
