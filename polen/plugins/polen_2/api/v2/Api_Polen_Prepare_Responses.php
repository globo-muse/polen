<?php

namespace Polen\Api\v2;

use Polen\Includes\Module\{Polen_Page_Module, Polen_Product_Module};
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
            'seo' => self::prepare_seo_to_response($product_module),
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
     * Preparando uma Page para a saida de em um RESPONSE
     * @param WP_POST
     * @return array
     */
    static public function prepare_page_to_response(Polen_Page_Module $page)
    {
        return [
            'id' => $page->get_id(),
            'title' => $page->get_title(),
            'slug' => $page->get_slug,
            'image' => self::prepare_image_to_response(get_post_thumbnail_id($page->get_id())),
            'excerpt' => $page->get_excerpt(),
            'content' => $page->get_content(),
            'seo' => self::prepare_seo_to_response($page)
        ];
    }


    /**
     * Preparando o objecto SEO para response esse methodo deve receber qualquer module
     * @param Module
     * @return array [title,description,meta_description,image]
     */
    static public function prepare_seo_to_response($object)
    {
        $image = $object->get_seo_image();
        $image_response = self::prepare_image_to_response($image['ID'] ?? 0);
        return [
            'title' => $object->get_seo_title(),
            'meta_title' => $object->get_seo_meta_title(),
            'description' => $object->get_seo_meta_description() ,
            'image' => $image_response,
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
        $thumbnail_id = get_post_thumbnail_id($talent_id);
        // $attachment = get_post();
        if( empty( $thumbnail_id ) ) {
            return self::prepare_image_to_response($thumbnail_id);
        }
        return [];
    }


    static public function prepare_image_to_response($attachment_id = '')
    {
        $attachment = get_post($attachment_id);
        return array(
            'id' => $attachment->ID,
            'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content,
            'src' => wp_get_attachment_image_src($attachment->ID, 'polen-thumb-xl')[0],
            'title' => $attachment->post_title,
        );
    }
}
