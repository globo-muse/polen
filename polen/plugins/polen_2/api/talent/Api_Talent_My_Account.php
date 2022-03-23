<?php
namespace Polen\Api\Talent;

use Polen\Api\Api_Util_Security;
use Polen\Includes\API\Polen_Api_User;
use Polen\Includes\Module\Polen_User_Module;
use Polen\Includes\Polen_Talent;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

class Api_Talent_My_Account extends WP_REST_Controller
{

    /**
     * Metodo construtor
     */
    public function __construct()
    {
        $this->namespace = 'polen/v1';
        $this->rest_base = 'talents';
    }

    /**
     * Registro das Rotas
     */
    public function register_routes()
    {
        #
        #
        #
        register_rest_route($this->namespace, $this->rest_base . '/myaccount', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'myaccount'],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ]
        ] );

        /**
         * 
         */
        register_rest_route($this->namespace, $this->rest_base . '/me', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'my_account_inicial'],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ]
        ] );

        /**
         * 
         */
        register_rest_route($this->namespace, $this->rest_base . '/password', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_password'],
                'permission_callback' => [Api_Talent_Check_Permission::class, 'check_permission'],
                'args' => []
            ]
        ] );

        #
        #
        #
        register_rest_route($this->namespace, $this->rest_base . '/user', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_user'],
                'permission_callback' => [Api_Talent_Check_Permission::class, 'check_permission'],
                'args' => []
            ]
        ] );

        
    }

    /**
     * Listar informações do usuario
     */
    public function myaccount(): \WP_REST_Response
    {
        $products_id  = Api_Talent_Utils::get_globals_product_id();
        $user_module = Polen_User_Module::create_from_product_id($products_id[0]);

        $talent_object = $user_module->get_info_talent();
        foreach ($talent_object as $talent) {
            $data['user_id'] = $talent->user_id;
            $data['name'] = $user_module->get_display_name();
            $data['email'] = $talent->email;
            $data['birthday'] = $talent->nascimento;
            $data['fantasy_name'] = $talent->nome_fantasia;
            $data['phone'] = $talent->celular;
            $data['telephone'] = $talent->telefone;
            $data['whatsapp'] = $talent->whatsapp;
        }

        return api_response($data);
    }

    /**
     * Atualizar Senha do usuario
     *
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function update_password(\WP_REST_Request $request): \WP_REST_Response
    {
        $products_id  = Api_Talent_Utils::get_globals_product_id();
        $user_module = Polen_User_Module::create_from_product_id($products_id[0]);

        $current = $request->get_param('current_pass');
        $new = $request->get_param('new_pass');

        try {
            $user_module->update_pass($current, $new);
            return api_response('Senha Atualizada', 200);
        } catch (\Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Atualizar Senha do usuario
     *
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function update_user(\WP_REST_Request $request): \WP_REST_Response
    {
        $products_id  = Api_Talent_Utils::get_globals_product_id();

        $user_module = Polen_User_Module::create_from_product_id($products_id[0]);
        $data = $request->get_params();

        try {
            $user_module->update_user($data);
            return api_response('Dados atualizados', 200);
        } catch (\Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Recuperar dados de um talento
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function my_account_inicial(\WP_REST_Request $request): \WP_REST_Response
    {
        $user = wp_get_current_user();

        if(empty($user)) {
            return api_response(['message' => 'Não existe nenhum usuario com esse email'], 403);
        }

        $query = new WP_Query(array('post_type' => 'product', 'author' =>  $user->data->ID));
        $products_id = Api_Talent_Utils::get_globals_product_id();

        $image = '';
        if (isset($products_id) && !empty($products_id)) {
            $att = wp_get_attachment_image_src(get_post_thumbnail_id($products_id[0]), 'polen-square-crop-xl');
            $image = $att[0];
        }
        $product = wc_get_product($products_id[0]);

        $first_order_done = Polen_Talent::get_first_order_status_by_talent_id($user->data->ID)?true:false;
        if(!$first_order_done) {
            #Se nao existir $first_order_id entao $first_order_done é true
            #caso nao exista fist_order para o talento isso precisa ser ignorado no front
            $first_order_id = Polen_Talent::get_first_order_id_by_talent_id($user->data->ID);
            if(empty($first_order_id)) {
                $first_order_done = true;
            }
        }

        $response = [
            'ID'               => $user->data->ID,
            'name'             => get_user_meta($user->data->ID,'first_name', true) . ' ' . get_user_meta($user->data->ID,'last_name', true),
            'thumbnail'        => $image,
            'first_name'       => get_user_meta($user->data->ID,'first_name', true),
            'last_name'        => get_user_meta($user->data->ID,'last_name', true),
            'phone'            => get_user_meta($user->data->ID,'billing_phone', true),
            'email'            => $user->data->user_email,
            'display_name'     => $user->data->display_name,
            'date_registered'  => $user->data->user_registered,
            'first_order_done' => $first_order_done,
            'first_order_id'   => $first_order_id,
            'sku'              => $product->get_sku(),
        ];

        return api_response($response, 200);
    }
}
