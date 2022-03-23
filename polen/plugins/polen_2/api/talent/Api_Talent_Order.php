<?php
namespace Polen\Api\Talent;

use Exception;
use Polen\Api\Api_Util_Security;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Talent;
use Polen\Includes\Talent\Polen_Talent_Controller;
use Polen\Includes\Vimeo\Polen_Vimeo;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Api_Talent_Order extends WP_REST_Controller
{
    /**
     * Controler de check permission
     */
    protected $controller_access;

    /**
     * Esquema
     */
    protected $schema = [
        '$schema'              => 'http://json-schema.org/draft-04/schema#',
        'title'                => 'Talent Dashboard',
        'type'                 => 'Object',
        'properties'           => array(
            'id' => array(
                'description'  => 'Unique identifier for the object.',
                'type'         => 'integer',
                'context'      => array( 'view', 'edit', 'embed' ),
                'readonly'     => true,
            ),
            'content' => array(
                'description'  => 'The content for the object.',
                'type'         => 'string',
            ),
        ),
    ];

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
        
        //Rota para pegar um nonde válido
        register_rest_route( $this->namespace, $this->rest_base . '/orders/nonce', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'create_nonce' ],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ]
        ] );
    
        //Rota para aceitar ou não o pedido de video
        register_rest_route( $this->namespace, $this->rest_base . '/orders/(?P<id>[\d]+)/acceptance', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [ $this, 'accept_reject_order' ],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ]    
        ] );            

        //Rota para criação de um slot no Vimeo
        register_rest_route( $this->namespace, $this->rest_base . '/orders/(?P<id>[\d]+)/slot-vimeo', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'create_vimeo_slot' ],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ]
        ] );


        //Rota para completar o envio do pedido, criacao do Video-Info
        register_rest_route( $this->namespace, $this->rest_base . '/orders/(?P<id>[\d]+)/sended', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [ $this, 'complete_order' ],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ]
        ] );
    }


    /**
     * Handler da rota para um talento aceitar ou negar um pedido
     */
    public function accept_reject_order( WP_REST_Request $request )
    {
        $order_id           = $request['id'];
        $option             = $request->get_param('option');
        $reason_reject      = $request->get_param('reason_reject') ?? '';
        $description_reject = $request->get_param('description_reject') ?? '';

        try {

            $talent_controller = new Polen_Talent_Controller();
            $result = $talent_controller->talent_accept_or_reject_api(
                $option,
                $order_id,
                $reason_reject,
                $description_reject
            );
            return api_response($result, 200);
        } catch (Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Handler para a criacao de um nonce
     */
    public function create_nonce(WP_REST_Request $request)
    {
        $ip     = $_SERVER[ 'REMOTE_ADDR' ];
        $client = $_SERVER[ 'HTTP_USER_AGENT' ];
        $user_id = get_current_user_id();
        return api_response( Api_Util_Security::create_nonce($ip . $client . $user_id), 200 );
    }


    /**
     * Handler de criacao de um slot de video no vimeo
     */
    public function create_vimeo_slot(WP_REST_Request $request)
    {
        // $security       = $request->get_param('security');
        $order_id       = $request->get_param('order_id');
        $file_size      = $request->get_param('file_size');
        $name_to_video  = $request->get_param('name_to_video');
        // $user_id        = get_current_user_id();
        // $ip             = '756937659387659823645827546';
        // $client         = $_SERVER['HTTP_USER_AGENT'];

        try {
            // if(!isset($security) || !Api_Util_Security::verify_nonce($ip . $client . $user_id, $security)) {
            //     throw new Exception('Problema na seguraça tente novamente', 403);
            // }
            if(empty($order_id) || empty($file_size) || empty($name_to_video)) {
                throw new Exception('Formato de request inválido', 403);
            }
            $vimeo = new Polen_Vimeo();
            $vimeo_response = $vimeo->make_video_slot_vimeo($order_id, $file_size, $name_to_video);
            if($vimeo_response->is_error()) {
                throw new Exception('Erro na criação do slot do video', 401);
            }
            return api_response($vimeo_response->response, 200);
        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }


    public function complete_order(WP_REST_Request $request): WP_REST_Response
    {
        $talent_id = get_current_user_id();
        $order_id  = $request['id'];
        $vimeo_id  = $request->get_param('vimeo_id');
        try{
            $polen_talent_controller = new Polen_Talent_Controller();
            $checked = $polen_talent_controller->check_product_and_order( $talent_id, $order_id );
            if(!$checked) {
                throw new Exception('Erro na relação Idolo/Pedido');
            }
            $polen_talent_controller->send_video_vimeo_complete($vimeo_id, $order_id);
            $this->set_first_order_complete($order_id, $talent_id);
            return api_response('Completo com sucesso', 200);
        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Se for uma FirstOrder seta o usuário com a flag de concluido
     * @param int
     * @param int
     */
    protected function set_first_order_complete(int $order_id, int $talent_id)
    {
        //Seta se for FirstOrder como Complete no usuário
        $order = wc_get_order($order_id);
        $polen_order = new Polen_Order_Module($order);
        if($polen_order->get_is_first_order()) {
            Polen_Talent::set_first_order_status_by_talent_id($talent_id, true);
        }
    }

    
    /**
     * Pega as informacoes das Orders do Talento Logado
     */
    public function get_orders_info(WP_REST_Request $request): WP_REST_Response
    {
        $polen_order = new Polen_Talent();
        $orders = $polen_order->get_product_id_by_talent_id(get_current_user_id());

        return api_response($orders, 200);
    }
}