<?php

namespace Polen\Api\v2;

use WP_REST_Request;
use WP_REST_Server;

defined('ABSPATH') || die;

class Api_Polen_Home
{
    public function __construct()
    {
        $this->namespace = 'polen/v2';
        $this->rest_base = 'home';

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, $this->rest_base . '/uses_case', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_uses_cases'],
                'permission_callback' => "__return_true",
                'args' => []
            ],
        ] );
    }


    /**
     * 
     */
    public function get_uses_cases(WP_REST_Request $request)
    {
        
    }
}
