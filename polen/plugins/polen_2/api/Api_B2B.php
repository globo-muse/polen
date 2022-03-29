<?php
namespace Polen\Api;

use Exception;
use Polen\Admin\Polen_Forms;
use Polen\Api\Api_Util_Security;
use Polen\Includes\Polen_Form_DB;
use Polen\Includes\Polen_Product_B2B;
use Polen\Includes\Polen_Zapier;
use WC_Product;
use WP_REST_Request;
use WP_REST_Server;

class Api_B2B
{

    protected $namespace;
    protected $rest_base;

    /**
     * Metodo construtor
     */
    public function __construct()
    {
        $this->namespace = 'polen/v1';
        $this->rest_base = 'b2b';
    }

    /**
     * Registro das Rotas
     */
    public function register_routes()
    {
        register_rest_route( $this->namespace, $this->rest_base . '/contact', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'handler_b2b_contact'),
                'permission_callback' => '__return_true',
            ),
            'schema' => array()
        ) );

        register_rest_route( $this->namespace, $this->rest_base . '/talents', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_products'),
                'permission_callback' => '__return_true',
                'args'                => [
                    'categories' => [
                        'description' => '',
                        'type'        => 'array'
                    ],
                    'limit' => [
                        'description' => 'Quantidade de retornos',
                        'type'        => 'integer'
                    ]
                ]
            ),
            'schema' => array()
        ) );
    }


    /**
     * Prepara o produto para um RESPONSE
     * @param \WC_Product
     * @return array
     */
    protected function prepare_product_to_response(WC_Product $product)
    {
        $media_obj = wp_get_attachment_image_src($product->get_image_id(), 'polen-thumb-lg');
        $url_image = '';
        if(!empty($media_obj)) {
            $url_image = $media_obj[0];
        }
        return [
            'name'      => $product->get_title(),
            'thumbnail' => $url_image,
            'sku'       => $product->get_sku(),
            'permalink' => $product->get_permalink(),
        ];
    }


    /**
     * Pega um produtos
     */
    public function get_products(WP_REST_Request $request)
    {
        $limit = $request->get_param('limit') ?? 10;
        $categories = $request->get_param('categories');
        $products_id = Polen_Product_B2B::get_all_product_ids($categories, $limit);
        $result = [];
        foreach($products_id as $product_id) {
            $result[] = $this->prepare_product_to_response(wc_get_product($product_id));
        }
        return api_response($result);
    }


    /**
     * Handler que recebe os dados de um lead do B2B
     * Envia email, envia para o Zapier, cadastrar em DB
     * 
     */
    public function handler_b2b_contact(WP_REST_Request $request)
    {
        global $Polen_Plugin_Settings;

        $ip     = '756937659387659823645827546';
        $client = $_SERVER[ 'HTTP_USER_AGENT' ];
        $nonce  = $request->get_param( 'security' );

        $form_id = filter_var($request->get_param( 'form_id' ), FILTER_SANITIZE_NUMBER_INT);
        $name    = filter_var($request->get_param( 'name' ), FILTER_SANITIZE_STRING);
        $email   = filter_var($request->get_param( 'email' ), FILTER_SANITIZE_EMAIL);
        $company = filter_var($request->get_param( 'company' ), FILTER_SANITIZE_SPECIAL_CHARS);
        $phone   = filter_var($request->get_param( 'phone' ), FILTER_SANITIZE_SPECIAL_CHARS);
        $product = filter_var($request->get_param( 'product_name' ), FILTER_SANITIZE_SPECIAL_CHARS);
        $city = filter_var($request->get_param( 'city' ), FILTER_SANITIZE_SPECIAL_CHARS);
        $state = filter_var($request->get_param( 'state' ), FILTER_SANITIZE_SPECIAL_CHARS);


        $utm_source = filter_var($request->get_param( 'utm_source' ), FILTER_SANITIZE_SPECIAL_CHARS);
        $utm_medium = filter_var($request->get_param( 'utm_medium' ), FILTER_SANITIZE_SPECIAL_CHARS);
        $utm_campaign = filter_var($request->get_param( 'utm_campaign' ), FILTER_SANITIZE_SPECIAL_CHARS);
        $utm_term = filter_var($request->get_param( 'utm_term' ), FILTER_SANITIZE_SPECIAL_CHARS);
        $utm_content = filter_var($request->get_param( 'utm_content' ), FILTER_SANITIZE_SPECIAL_CHARS);


        $terms   = '1';
        $body = compact('form_id', 'name', 'email', 'company', 'phone', 'city', 'state');
        $to_zapier = compact('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content');
        try {
            if(!Api_Util_Security::verify_nonce($ip . $client, $nonce)) {
                throw new Exception('Erro na segurança', 403);
            }

            $this->validate_inputs_b2b($body);

            $form_db = new Polen_Form_DB();
            $form_db->insert($body);

            $body['product'] = $product;
            $form_service = new Polen_Forms();
            $form_service->mailSend($body);

            $body_zapier = array_merge($body, $to_zapier);

            $url_zapier_b2b_hotspot = $Polen_Plugin_Settings['polen_url_zapier_b2b_hotspot'];
            $zapier = new Polen_Zapier();
            $zapier->send($url_zapier_b2b_hotspot, $body_zapier);

            return api_response(true, 201);
        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }




    /**
     * Validação de inputs
     * @param array
     * @throws Exception
     */
    protected function validate_inputs_b2b(array $body)
    {
        if(!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido', 401);
        }
        $required_fields = $this->required_fields_b2b();
        foreach($required_fields as $item) {
            if(empty(trim($body[$item]))) {
                throw new Exception('Todos os campos são obrigatórios');
            }
        }
    }


    /**
     * Retorna todos os campos do formulário que são obrigatórios
     */
    private function required_fields_b2b(): array
    {
        return [
            'name',
            'email',
            'company',
            'phone',
            'form_id',
        ];
    }

}