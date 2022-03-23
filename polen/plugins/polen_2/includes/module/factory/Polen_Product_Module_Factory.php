<?php
namespace Polen\Includes\Module\Factory;

use Polen\Includes\Module\Polen_Product_Module;
use Polen\Includes\Module\Products\Polen_B2B_Only;
use Polen\Includes\Polen_Campaign;
use WC_Product;

class Polen_Product_Module_Factory
{

    public static function create_product_from_campaing(WC_Product $product)
    {
        $campaing = Polen_Campaign::get_product_campaign_slug($product);
        if('b2b-only' === $campaing) {
            return new Polen_B2B_Only($product);
        }
        return new Polen_Product_Module($product);
    }
}