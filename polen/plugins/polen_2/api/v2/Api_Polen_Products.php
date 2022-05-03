<?php

namespace Polen\Api\v2;

use Polen\Includes\Module\Factory\Polen_Product_Module_Factory;
use Polen\Includes\Module\{Polen_Product_Module,Polen_User_Module};
use Polen\Includes\Module\Resource\Metrics;
use WP_REST_Request;
use WP_REST_Server;
use WP_Term;

defined('ABSPATH') || die;

class Api_Polen_Products
{
    /**
     * Metodo construtor
     */
    public function __construct()
    {
        $this->namespace = 'polen/v2';
        $this->rest_base = 'site/products';
    }

    /**
     * Registro das Rotas
     */
    public function register_routes()
    {

        register_rest_route( $this->namespace, $this->rest_base . '/', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_products' ],
                'permission_callback' => "__return_true",
                'args' => []
            ]
        ] );

        register_rest_route( $this->namespace, $this->rest_base . '/(?P<slug>[a-zA-Z0-9-]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_product' ],
                'permission_callback' => "__return_true",
                'args' => []
            ]
        ] );
    }


    /**
     * 
     */
    public function get_product(WP_REST_Request $request)
    {
        $slug = $request['slug'];
        $product_id = wc_get_product_id_by_sku($slug);
        if(!$product = wc_get_product($product_id)) {
            return api_response('Produto não encontrado', 404);
        }
        $product_module = Polen_Product_Module_Factory::create_product_from_campaing($product);

        $result = $this->prepare_product_to_response($product_module);
        $result['region_metrics'] = $this->influence_by_region($product_module->get_id());
        $result['age_group'] = $this->age_group($product_module->get_id());
        $result['audience'] = $this->audience($product_module->get_id());

        if($term_tags = $product_module->get_terms_tags()) {
            $result['tags'] = [];
            foreach($term_tags as $tag) {
                $result['tags'][] = $this->prepare_tags_to_response($tag);
            }
        }
        return api_response($result, 200);
    }



    /**
     * Retornar porcentagem de influencia por região
     *
     * @param $product_id
     * @return ?array
     */
    public function influence_by_region($product_id): ?array
    {
        $user = Polen_User_Module::create_from_product_id($product_id);
        $metrics_talent = new Metrics();
        $influence = $user->get_influence_by_region();

        if (empty($influence)) {
            return null;
        }

        foreach ($influence as $value) {
            $metrics_talent->set_percentage_by_regions($value['state_and_city']['state_id'], (int) $value['percentage']);
        }

        return $metrics_talent->get_percentage_by_regions();
    }

    /**
     * 
     */
    public function age_group($product_id)
    {
        $user = Polen_User_Module::create_from_product_id($product_id);

        return $user->get_age_group();
    }

    /**
     * 
     */
    public function audience($product_id)
    {
        $user = Polen_User_Module::create_from_product_id($product_id);

        return $user->get_audience();
    }


    /**
     * 
     */
    public function prepare_product_to_response(Polen_Product_Module $product_module)
    {

        $product_response = [
            'id' => $product_module->get_id(),
            'description' => $product_module->get_description(),
            'slug' => $product_module->get_sku(),
            'title' => $product_module->get_title(),
            'category_name' => $product_module->get_category_name(),
            'category_slug' => $product_module->get_category_slug(),
            'price_from_to' => $product_module->get_price_from_b2b(),
            'image_url' => $product_module->get_image_url('polen-thumb-lg'),
        ];
        return $product_response;
    }

    /**
     * 
     */
    public function prepare_tags_to_response(WP_Term $term)
    {
        $term_response = [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
        ];
        return $term_response;
    }
}
