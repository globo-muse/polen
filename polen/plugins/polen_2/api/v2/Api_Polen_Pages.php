<?php
namespace Polen\Api\v2;

use Polen\Includes\Module\Polen_Page_Module;
use WP_REST_Request;
use WP_REST_Server;

defined('ABSPATH') ?? die;

class Api_Polen_Pages
{
    /**
     * Metodo construtor
     */
    public function __construct()
    {
        $this->namespace = 'polen/v2';
        $this->rest_base = 'site/pages';
    }


    public function register_route()
    {
        register_rest_route( $this->namespace, $this->rest_base . '/(?P<slug>[a-zA-Z0-9-]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_object' ],
                'permission_callback' => "__return_true",
                'args' => []
            ]
        ] );
    }



    public function get_object(WP_REST_Request $request)
    {
        $slug_page = $request['slug'];
        if(empty($slug_page)) {
            return api_response('page not founded', 404);
        }
        $page = get_page_by_path($slug_page);
        if(empty($page)) {
            return api_response('page not founded', 404);
        }
        $page_module = new Polen_Page_Module($page);
        return api_response(Api_Polen_Prepare_Responses::prepare_page_to_response($page_module));
    }//jfhlakdfjhlaj
}
