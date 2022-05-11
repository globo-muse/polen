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
            'price' => $product_module->get_price(),
            'regular_price' => $product_module->get_regular_price(),
            'sale_price' => $product_module->get_sale_price(),
            'categories' => self::prepare_categories_to_response($product_module->get_categories()),
            'price_from_to' => $product_module->get_price_from_b2b(),
            'image' => self::get_object_image($product_module->get_id()),
            'videos' => $product_module->get_vimeo_videos_page_details(),
            'createdAt' => get_the_date('Y-m-d H:i:s', $product_module->get_id()),
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

    /**
     * Retornar meta dados da imagem
     *
     * @param int $talent_id
     * @return array
     */
    static public function get_object_image(int $talent_id): array
    {
        $attachment = get_post(get_post_thumbnail_id($talent_id));
        if( empty( $attachment ) ) {
            return [];
        }
        return array(
            'id' => $attachment->ID,
            'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content,
            'src' => wp_get_attachment_image_src($attachment->ID, 'polen-thumb-lg')[0],
            'title' => $attachment->post_title,
        );
    }
}
