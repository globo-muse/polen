<?php

namespace Polen\Api\v2;

use Polen\Includes\Polen_Faq;
use WP_REST_Request;
use WP_REST_Server;

defined('ABSPATH') || die;

class Api_Polen_FAQ
{
    /**
     * Metodo construtor
     */
    public function __construct()
    {
        $this->namespace = 'polen/v2';
        $this->rest_base = 'site';
    }

    public function register_route()
    {
        register_rest_route( $this->namespace, $this->rest_base . '/faq', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_objects' ],
                'permission_callback' => "__return_true",
                'args' => []
            ]
        ] );
    }


    public function get_objects(WP_REST_Request $request)
    {
        $faq = new Polen_Faq();
        $facs = $faq->get_faq();

        return api_response($facs);
    }
}
