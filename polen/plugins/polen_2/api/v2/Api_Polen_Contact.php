<?php
namespace Polen\Api\V2;

use Exception;
use Polen\Includes\Polen_Zapier;
use WP_REST_Request;
use WP_REST_Server;

defined('ABSPATH') ?? die;

class Api_Polen_Contact
{

    public function __construct()
    {
        $this->namespace = 'polen/v2';
        $this->rest_base = 'site';
    }


    public function register_route()
    {
        register_rest_route($this->namespace, $this->rest_base . '/contact', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ $this, 'send_contact' ],
            'permission_callback' => "__return_true",
            'args' => []
        ]);
    }


    public function send_contact(WP_REST_Request $request)
    {
        global $Polen_Plugin_Settings;
        $url_zapier = $Polen_Plugin_Settings['polen_url_zapier_b2b_hotspot'];
        $params = $request->get_params();
        $zapie_service = new Polen_Zapier;
        try{
            $zapie_service->send($url_zapier, $params);
            return api_response('enviado');
        } catch (Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }
}