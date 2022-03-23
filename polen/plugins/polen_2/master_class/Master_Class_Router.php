<?php
namespace Polen\Master_class;

class Master_Class_Router extends \WP_REST_Controller
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
        $controller = new Master_Class_Controller();
        add_action( 'wp_ajax_send_form_request',        [ $controller, 'send_form_request' ] );
        add_action( 'wp_ajax_nopriv_send_form_request',        [ $controller, 'send_form_request' ] );
    }
    
}
