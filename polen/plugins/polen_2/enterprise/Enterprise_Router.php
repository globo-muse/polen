<?php
namespace Polen\Enterprise;

class Enterprise_Router extends \WP_REST_Controller
{

    public function __construct( bool $static = false )
    {
        $this->create_routes_master_class();
    }

    /**
     * Cria as rotas para landing master class no /wp-admin/admin-ajax.php
     */
    public function create_routes_master_class()
    {
        $controller = new Enterprise_Controller();
        add_action( 'wp_ajax_send_form_request',        [ $controller, 'send_form_request' ] );
        add_action( 'wp_ajax_nopriv_send_form_request',        [ $controller, 'send_form_request' ] );
    }
    
}
