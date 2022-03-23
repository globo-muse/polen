<?php

use Polen\Admin\Polen_Admin_B2B_Product_Fields as Adm_B2B_Fields;


/**
 * Funcao que retorna se o talento está disponivel para pedir um video para empresa.
 * @param \WC_Product
 * @return bool
 */
function polen_b2b_product_is_enabled( \WC_Product $product = null )
{
    if( empty( $product ) ) {
        return false;
    }

    $meta_b2b_is_enabled = $product->get_meta( Adm_B2B_Fields::FIELD_NAME_ENABLED_B2B, true );

    if( 'yes' === $meta_b2b_is_enabled ) {
        return true;
    }
    return false;
}