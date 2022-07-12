<?php

namespace Polen\Api\v2;

use Polen\Includes\Polen_Faq;
use Polen\Includes\Vimeo\Polen_Vimeo_Factory;
use WP_REST_Request;
use WP_REST_Server;

defined('ABSPATH') || die;

class Api_Polen_Cases
{
    /**
     * Metodo construtor
     */
    public function __construct()
    {
        $this->namespace = 'polen/v2';
        $this->rest_base = 'site/cases';
    }

    public function register_route()
    {
        register_rest_route( $this->namespace, $this->rest_base, [
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
        $vimeo_api = Polen_Vimeo_Factory::create_vimeo_instance_with_redux();
        $response_vimeo = $vimeo_api->request('/me/projects/11480285/videos');
        $return = [];
        foreach($response_vimeo['body']['data'] as $data) {
            $aaa = [];
            $aaa['name'] = $data['name'];
            $aaa['thumbnail'] = $data['pictures']['base_link'];
            $aaa['video_link_url'] = $data['files'][0]['link'];
            $return[] = $aaa;
        }
        return api_response($return);
    }
}
