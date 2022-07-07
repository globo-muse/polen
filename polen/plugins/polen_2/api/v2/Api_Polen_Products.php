<?php

namespace Polen\Api\v2;

use Exception;
use Polen\Api\Api_Check_Permission;
use Polen\Api\Api_Product;
use Polen\Includes\Module\Factory\Polen_Product_Module_Factory;
use Polen\Includes\Polen_Campaign;
use Polen\Includes\Polen_Talents_Rules;
use Polen\Includes\Module\{Polen_Product_Module,Polen_User_Module};
use Polen\Includes\Module\Resource\Metrics;
use WC_Product_Query;
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


        register_rest_route( $this->namespace, $this->rest_base . '/gender', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_gender' ],
                'permission_callback' => [Api_Check_Permission::class, 'check_permission'],
                'args' => []
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'set_gender' ],
                'permission_callback' => [Api_Check_Permission::class, 'check_permission'],
                'args' => []
            ]
        ] );


        register_rest_route( $this->namespace, $this->rest_base . '/(?P<slug>[a-zA-Z0-9-]+)/related', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_related_products' ],
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

        register_rest_route( $this->namespace, $this->rest_base . '/(?P<s>[a-zA-Z0-9-]+)/search', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'search' ],
                'permission_callback' => "__return_true",
                'args' => []
            ]
        ] );
    }


    /**
     * Pega os produtos relacionados (na mesma cat) a retorna um array para a func polen_banner_scrollable
     * @param WP_REST_Request
     * @return array [['ID'=>xx,'talent_url'=>'...','name'=>'...','price'=>'...','category_url'=>'...','category'=>'...']]
     */
    public function get_related_products(WP_REST_Request $request)
    {
        $product_slug = $request->get_param('slug');
        $product_id = wc_get_product_id_by_sku($product_slug);
        if(empty($product_id)) {
            return api_response('Produto não encontrado', 404);
        }
        $cat_terms = wp_get_object_terms( $product_id, 'product_cat');
        $terms_ids = array();
        if (count($cat_terms) > 0) {
            foreach ($cat_terms as $k => $term) {
                $terms_ids[] = $term->term_id;
            }
        }
        if (count($terms_ids) > 0) {
            $others = get_objects_in_term($terms_ids, 'product_cat');
            $arr_obj = array();
            $arr_obj[] = get_the_ID();
            shuffle($others);
            if (count($others)) {
                $args = array();
                foreach ($others as $k => $id) {
                    if (!in_array($id, $arr_obj)) {
                        if (count($arr_obj) > 6) {
                            return api_response($args);
                        }
                        $product = wc_get_product($id);
                        $product_module = Polen_Product_Module_Factory::create_product_from_campaing($product);
                        $arr_obj[] = $id;

                        if( 'publish' === $product->get_status() ) {
                            $args[] = Api_Polen_Prepare_Responses::prepare_product_to_response($product_module);
                        }
                    }
                }
                return api_response($args);
            }
        }
        return api_response([]);
    }

    /**
     * Retornar todos os talentos
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_products(WP_REST_Request $request): \WP_REST_Response
    {
        try{
            $api_product = new Api_Product();
            $params = $request->get_params();

            $slug = $params['campaign'] ?? null;
            $slug = !empty($params['campaign_category']) ? $params['campaign_category'] : $slug;

            $products = $api_product->polen_get_products_by_campagins($params, $slug);

            $items = array();
            foreach ($products['products'] as $product) {
                $module_product = new Polen_Product_Module($product);
                $items[] = Api_Polen_Prepare_Responses::prepare_product_to_response($module_product);
            }

            $data = array(
                'items' => $items,
                'total' => $products['total'],//$api_product->get_products_count($params, $slug),
                'current_page' => $request->get_param('paged') ?? 1,
                'per_page' => count($items),
            );

            return api_response($data, 200);

        } catch (\Exception $e) {
            return api_response(
                array('message' => $e->getMessage()),
                $e->getCode()
            );
        }
    }


    /**
     * Retornar produtos de acordo com pesquisa
     *
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function search(WP_REST_Request $request): \WP_REST_Response
    {
        try{
            $params = $request->get_params();
            $api_product = new Api_Product();

            $products = $api_product->polen_get_products_by_campagins($params);

            $items = array();
            foreach ($products['products'] as $product) {
                $product = wc_get_product($product->ID);
                $module_product = new Polen_Product_Module($product);
                $module['title'] = $module_product->get_title();
                $module['slug'] = $module_product->get_sku();
                $module['image'] = $module_product->get_image_url('polen-square-crop-sm');

                $items[] = $module;
            }

            return api_response($items, 200);
        } catch (\Exception $e) {
            return api_response(
                array('message' => $e->getMessage()),
                $e->getCode()
            );
        }
    }



    /**
     * Pegar a lista de todos os produtos com gender
     */
    public function get_gender(WP_REST_Request $request)
    {
        $products = wc_get_products([
            'status' => ['publish', 'private'],
            'orderby' => 'date',
            'order' => 'DESC',
            'limit' => 1000000,
        ]);

        $result = [];
        foreach($products as $product) {
            $result[] = [
                'id' => $product->get_id(),
                'name' => $product->get_title(),
                'gender' => get_field('product_gender', $product->get_id()),
            ];
        }
        return api_response($result);
    }


    /**
     * Setar numa lista de produtos o Gender
     */
    public function set_gender(WP_REST_Request $request)
    {
        $data_input = $request->get_params();
        foreach($data_input as $data) {
            if(!$product = wc_get_product($data['id'])) {
                return api_response("Produto #{$data['id']} não encontrado (update parado a partir desse ID).", 404);
            }
            update_field('product_gender', $data['gender'], $product->get_id());
        }
        return api_response('done');
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
        $polen_rules = new Polen_Talents_Rules();

        $result = Api_Polen_Prepare_Responses::prepare_product_to_response($product_module);
        $result['region_metrics'] = $this->influence_by_region($product_module->get_id());
        $result['age_group'] = $this->age_group($product_module->get_id());
        $result['audience'] = $this->audience($product_module->get_id());
        $result['rules'] = $polen_rules->get_terms_by_product($product_module->get_id());
        $result['blog_posts'] = $product_module->get_posts_blogs_ids();

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
        $product = wc_get_product($product_id);
        $product_module = new Polen_Product_Module($product);
        $metrics_talent = new Metrics();
        $influence = $product_module->get_influence_by_region();

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
        $product = wc_get_product($product_id);
        $product_module = new Polen_Product_Module($product);

        return $product_module->get_age_group();
    }

    /**
     *
     */
    public function audience($product_id)
    {
        $product = wc_get_product($product_id);
        $product_module = new Polen_Product_Module($product);

        return $product_module->get_audience();
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
