<?php

namespace Polen\Api\v2;

defined('ABSPATH') || die;

use Exception;
use Polen\Includes\Module\Polen_Product_Module;
use Polen\Includes\Polen_Uses_Case;
use WP_REST_Request;
use WP_REST_Server;

class Api_Polen_Home
{
    
    /**
     * Metodo construtor
     */
    public function __construct()
    {
        $this->namespace = 'polen/v2';
        $this->rest_base = 'site/home';
    }


    /**
     * Registro das Rotas
     */
    public function register_routes()
    {
        
        register_rest_route( $this->namespace, $this->rest_base . '/uses-cases', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_uses_cases' ],
                'permission_callback' => "__return_true",
                'args' => []
            ]
        ] );

        register_rest_route( $this->namespace, $this->rest_base . '/uses-cases/(?P<slug>[a-zA-Z0-9-]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_products' ],
                'permission_callback' => "__return_true",
                'args' => []
            ]
        ] );
    }


    /**
     * 
     */
    public function get_uses_cases(WP_REST_Request $request)
    {
        
        $uses_cases = Polen_Uses_Case::get_all();
        $uses_cases_response = [];
        if(! empty($uses_cases)) {
            $uses_cases_response = array_map([$this, 'prepare_uses_cases_to_response'], $uses_cases);
        }
        return api_response($uses_cases_response, 200);
    }

    
    /**
     * 
     */
    public function get_products(WP_REST_Request $request)
    {
        try {
            $use_case_slug = $request->get_param('slug');
            if(empty($use_case_slug)) {
                throw new Exception('Registro não encontrado', 404);
            }
            $products = Polen_Uses_Case::get_products_by_slug($use_case_slug);
            $products_response = [];
            if(empty($products)) {
                return api_response([]);
            }
            foreach($products as $product) {
                // $products_response = array_map([$this, 'prepare_products_to_response'], $products);
                $products_response[] = Api_Polen_Prepare_Responses::prepare_product_to_response($product);
            }
            return api_response($products_response, 200);
        } catch(Exception $e) {
            return api_response(null, $e->getCode());
        }
    }


    /**
     * 
     */
    protected function prepare_uses_cases_to_response($use_case)
    {
        return  [
            'id' => $use_case->term_id,
            'name' => $use_case->name,
            'slug' => $use_case->slug,
            'qty_products' => $use_case->count,
        ];
    }


    /**
     * 
     */
    protected function prepare_products_to_response(Polen_Product_Module $product)
    {
        return Api_Polen_Prepare_Responses::prepare_product_to_response($product);
    }
}
