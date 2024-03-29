<?php

namespace Polen\Includes;

use Polen\Includes\Module\Polen_Product_Module;
use WC_Product;
use WP_Term;

class Polen_Uses_Case
{

    const TAXONOMY_TERM  = 'product_use_cases';
    const KEY_CAMPAIGN   = 'product_use_cases';
    const LABEL_CAMPAIGN = 'Casos de Uso';
    const SLUG_CAMPAIGN  = 'product_use_cases';

    public function __construct( bool $static = false )
    {
        if( $static ) {
            add_action('init', [ $this, 'create_taxonomy_campaigns' ]);
        }
    }


    /**
     * Registrar taxonomia de campanha em produtos
     */
    public function create_taxonomy_campaigns()
    {
        register_taxonomy(
            self::KEY_CAMPAIGN,
            'product',
            array(
                'label' => self::LABEL_CAMPAIGN,
                'rewrite' => array( 'slug' => self::SLUG_CAMPAIGN ),
                'hierarchical' => true,
            )
        );
    }


    /**
     * Pegar todas os Casos de Uso cadastrados no Admin
     * @return Array
     */
    public static function get_all()
    {
        $taxonomies_raw = get_terms([
            'taxonomy'   => self::TAXONOMY_TERM,
            'hide_empty' => true,
        ]);
        // $reponse_taxonomies = [];
        // foreach($taxonomies_raw as $taxonomy_raw) {
        //     // $taxonomy_raw = self::prepare_taxonomi_to_response($taxonomy_raw);
        //     $reponse_taxonomies[] = $taxonomy_raw;
        // }
        return $taxonomies_raw;
    }


    /**
     * Pegar todos os produtos de um caso de uso pelo slug
     * do caso de uso
     * 
     * @param string
     * @return Array
     */
    public static function get_products_by_slug($slug)
    {
        $all_ids = get_posts( array(
            'post_type' => 'product',
            'numberposts' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => self::TAXONOMY_TERM,
                    'field' => 'slug',
                    'terms' => $slug,
                    'operator' => 'IN',
                    )
                ),
        ));
        $response_products = [];
        foreach ( $all_ids as $id ) {
            $product = new WC_Product($id);
            $product_polen = new Polen_Product_Module($product);
            $response_products[] = $product_polen;
        }
        return $response_products;
    }
}
