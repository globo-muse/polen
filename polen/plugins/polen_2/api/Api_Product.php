<?php

namespace Polen\Api;

use Polen\Includes\Polen_Campaign;
use stdClass;
use WC_Product_Query;
use WP_Query;

class Api_Product
{
    /**
     * Retornar talentos de acordo com a campanha
     *
     * @param array $params
     * @param string $campaingn
     * @return stdClass
     */
    public function polen_get_products_by_campagins(array $params, string $campaingn = 'galo_idolos'): stdClass
    {
        $per_page = $params['per_page'] ?? get_option('posts_per_page');
        $paged = $params['paged'] ?? 1;
        $orderby = $params['orderby'] ?? 'popularity';
        $orderby = explode('-', $orderby);
        $category = $params['category'] ?? '';

        $order = $orderby[1] ?? 'DESC';

        $args = array(
            'limit'    => $per_page,
            'page'     => $paged,
            'paginate' => true,
            'status' => 'publish',
            'orderby' => $orderby[0],
            'order' => $order,
            'tax_query' => [
                [
                    'taxonomy' => Polen_Campaign::KEY_CAMPAIGN,
                    'field' => 'slug',
                    'terms' => $campaingn,
                    'operator' => 'NOT IN',
                ]
            ]
        );

        if (!empty($campaingn)) {
            $args['tax_query'][0]['operator'] = 'IN';
            $args['tax_query'][0]['terms'] = $campaingn;
        }

        if (!empty($category)) {
            $category = explode('&', $category);
            $args['category'] = $category;
        }

        if (isset($params['s'])) {
            $args['s'] = $params['s'];
        }

        $query = new WC_Product_Query($args);

        return $query->get_products(); // wc_products_array_orderby($query->get_products(), $orderby[0], $order);
    }

    /**
     * Retornar quantidade de posts encontrados de acordo com os filtros passados
     *
     * @param array $args
     * @param string $campaingn
     * @return int
     */
    public function get_products_count(array $args, string $campaingn = ''): int
    {
        $query_args = array(
            'return' => 'ids',
            'post_type' => 'product',
            'post_status' => 'publish',
            'meta_query' => array(),
        );

        if (!empty($campaingn)) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'campaigns',
                'field' => 'slug',
                'terms' => $campaingn,
            );
        }

        if (!empty($args['product_cat'])) {
            $query_args['product_cat'] = $args['category'];
        }

        $query = new WP_Query( $query_args );

        return $query->found_posts;
    }

    /**
     * Retornar meta dados da imagem
     *
     * @param int $talent_id
     * @return array
     */
    public function get_object_image(int $talent_id): array
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

    /**
     * Verificar se o produto contem estoque e retornar a quantidade
     */
    public function check_stock($product)
    {
        $stock = $product->get_stock_quantity();
        if ($stock === null && $product->is_in_stock()) {
            $stock = true;
        }

        return $stock;
    }
}
