<?php

namespace Polen\Api\v2;

use WP_Post;
use WP_REST_Request;
use WP_REST_Server;

defined('ABSPATH') || die;

class Api_Polen_FAC
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
        register_rest_route( $this->namespace, $this->rest_base . '/fac', [
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
        $facs = get_posts([
            'paginate' => false,
            'type' => 'fac',
            'status' => 'publish',
        ]);
        return api_response($this->prepare_facs_to_response($facs));
    }


    public function prepare_facs_to_response(array $posts)
    {
        $result = [];

        foreach($posts as $fac) {
            $result = [
                'id' => $fac->ID,
                'url' => $fac->permalink,
                'title' => $fac->title,
                'description' => $fac->description,
            ];
        }
        return $result;
    }
}
