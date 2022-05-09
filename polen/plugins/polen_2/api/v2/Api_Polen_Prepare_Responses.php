<?php

namespace Polen\Api\v2;

use Polen\Includes\Module\Polen_Product_Module;
use WP_Term;

class Api_Polen_Prepare_Responses
{


    static public function prepare_product_to_response(Polen_Product_Module $product_module)
    {

        $product_response = [
            'id' => $product_module->get_id(),
            'description' => $product_module->get_description(),
            'slug' => $product_module->get_sku(),
            'title' => $product_module->get_title(),
            // 'category_name' => $product_module->get_category_name(),
            // 'category_slug' => $product_module->get_category_slug(),
            'categories' => self::prepare_categories_to_response($product_module->get_categories()),
            'price_from_to' => $product_module->get_price_from_b2b(),
            'image' => $product_module->get_image_url('polen-thumb-lg'),
            'videos' => $product_module->get_vimeo_videos_page_details(),
        ];
        return $product_response;
    }


    /**
     * 
     */
    static public function prepare_category_to_response(WP_Term $category)
    {
        return [
            'id' => $category->term_id,
            'slug' => $category->name,
            'name' => $category->slug,
            'count' => $category->count,
        ];
    }


    /**
     * 
     */
    static public function prepare_categories_to_response(array $categories)
    {
        $result = [];
        foreach($categories as $category) {
            $result[] = self::prepare_category_to_response($category);
        }
        return $result;
    }
}
