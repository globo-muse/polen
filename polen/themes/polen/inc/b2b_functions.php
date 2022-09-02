<?php

use Polen\Admin\Polen_Admin_B2B_Product_Fields as Adm_B2B_Fields;
use Polen\Includes\Module\Polen_User_Module;
use Polen\Includes\Module\Resource\Metrics;


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

/**
 * Retornar porcentagem de influencia por região
 *
 * @param $product_id
 * @return ?array
 */
function polen_b2b_influence_by_region($product_id): ?array
{
    $user = Polen_User_Module::create_from_product_id($product_id);
    $metrics_talent = new Metrics();
    // $influence = $user->get_influence_by_region();

    if (empty($influence)) {
        return null;
    }

    foreach ($influence as $value) {
        $metrics_talent->set_percentage_by_regions($value['state_and_city']['state_id'], (int)$value['percentage']);
    }

    return $metrics_talent->get_percentage_by_regions();
}

function polen_b2b_age_group($product_id)
{
    $user = Polen_User_Module::create_from_product_id($product_id);

    return '';//$user->get_age_group();
}

function polen_b2b_audience($product_id)
{
    $user = Polen_User_Module::create_from_product_id($product_id);

    return '';//$user->get_audience();
}