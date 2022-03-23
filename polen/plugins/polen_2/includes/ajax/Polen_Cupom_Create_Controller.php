<?php
namespace Polen\Includes\Ajax;

use Polen\Includes\Polen_Cupom_Create;

class Polen_Cupom_Create_Controller
{
    public function __construct( $static = false )
    {
        if( $static ) {
            $this->add_routes();
        }
    }

    public function add_routes()
    {
        add_action( 'wp_ajax_polen_create_cupom', [ $this, 'create_cupom' ] );
    }

    public function create_cupom()
    {
        if( !current_user_can('administrator') ) {
            wp_send_json_error( 'Usuário sem permissão', 403 );
            wp_die();
        }
        $data = array();
        $data[ 'prefix_name' ]   = filter_input( INPUT_POST, 'prefix_name' );
        $data[ 'cupom_code' ]    = filter_input( INPUT_POST, 'cupom_code' );
        $data[ 'amount' ]        = filter_input( INPUT_POST, 'amount' ); // Amount
        $data[ 'discount_type' ] = filter_input( INPUT_POST, 'discount_type' ); // Type: fixed_cart, percent, fixed_product, percent_product
        $data[ 'product_ids' ]   = filter_input( INPUT_POST, 'product_ids' );
        $data[ 'description' ]   = filter_input( INPUT_POST, 'description' );
        $data[ 'expiry_date' ]   = filter_input( INPUT_POST, 'expiry_date' );
        $data[ 'usage_limit' ]   = filter_input( INPUT_POST, 'usage_limit' );

        $cupom_controller = new Polen_Cupom_Create();

        try {
            $cupom_controller->create_cupom( $data );
            wp_send_json_success( "Cupom criado com sucesso", 201 );
        } catch( \Exception $e ) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
        }
        wp_die();
    }
}