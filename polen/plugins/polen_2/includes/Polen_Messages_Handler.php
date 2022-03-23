<?php

namespace Polen\Includes;

/**
 * Funcao que tenta substituir a mensageria do Woocommerce
 * pela do thema onde é mais proxima do Ajax
 */
class Polen_Messages_Handler
{

    /**
     * construct da class
     */
    public function __construct( bool $static = false )
    {
        if( $static ) {
            $this->init();
        }
    }


    /**
     * Inicialização da class e adiciona as Actions
     */
    public function init()
    {
        if( !is_admin() ) {
            add_filter( 'woocommerce_add_error',                [ $this, 'woocommerce_add_error'          ], 10, 1 );
            add_filter( 'woocommerce_add_success',              [ $this, 'woocommerce_add_success'        ], 10, 1 );
            add_filter( 'woocommerce_kses_notice_allowed_tags', [ $this, 'polen_kses_notice_allowed_tags' ], 10, 1 );
            add_action( 'polen_messages_service_success',       [ $this, 'polen_messages_service_success' ], 10, 0 );
            add_action( 'polen_messages_service_error',         [ $this, 'polen_messages_service_error'   ], 10, 0 );
        }
    }


    /**
     * Handler do wc_add_notice
     * @param string
     */
    public function woocommerce_add_error( $message )
    {
        Polen_Messages::set_message( $message, Polen_Messages::TYPE_ERROR );
        return $message;
    }


    /**
     * Handler do wc_add_notice
     * @param string
     */
    public function woocommerce_add_success( $message )
    {
        Polen_Messages::set_message( $message, Polen_Messages::TYPE_SUCCESS );
        return $message;
    }


    /**
     * Handler do polen_messages_service_success
     * que fica no themes/polen/footer.php
     */
    public function polen_messages_service_success()
    {
        if( Polen_Messages::has_message_success() ) {
            $js_complete = $this->polen_messages_service( Polen_Messages::get_type(), Polen_Messages::get_message() );
            echo $js_complete;
        }
    }


    /**
     * Handler do polen_messages_service_error
     * que fica no themes/polen/footer.php
     */
    public function polen_messages_service_error()
    {
        if( Polen_Messages::has_message_error() ) {
            $js_complete = $this->polen_messages_service( Polen_Messages::get_type(), Polen_Messages::get_message() );
            echo $js_complete;
        }
    }


    /**
     * Funcao que junta e entrega a tag script completa
     */
    protected function polen_messages_service( $type, $message )
    {
        $js_complete = $this->create_js( $type, $message );
        return $js_complete;
    }


    /**
     * Funcao que junta e entrega a tag script completa
     */
    public function create_js( $type, $message )
    {
        $js_code = $this->create_function_call_js( $type, $message );
        $js_complete = $this->create_js_tag( $js_code );
        
        return apply_filters( 'polen_message_create_js', $js_complete );
    }


    /**
     * Funcao que retorna na tag script
     */
    protected function create_js_tag( $body )
    {
        $tags = "<script>%s;</script>";
        $js_complete = sprintf( $tags, $body );
        return apply_filters( "polen_messages_create_script_tag", $js_complete );
    }


    /**
     * Funcao que retorna formatado a chamada JS para se inserida no Footer
     */
    protected function create_function_call_js( $type, $message )
    {
        $funcao_js = ( Polen_Messages::TYPE_ERROR == $type ) ? 'error' : 'message' ;
        $call_function_js = ( $funcao_js == 'error' ) ? "polMessages.%s('%s');" : "polMessages.%s('','%s');";
        $call_function_js =  apply_filters( "polen_messages_create_function_call_js`", $call_function_js );
        $return = sprintf( $call_function_js, $funcao_js, $message );

        return $return;
    }

    public function polen_kses_notice_allowed_tags( $allowed_tags )
    {
        $allowed_tags = array_merge( $allowed_tags, array( 'script' => array( 'tabindex' => true ) ) );
        return $allowed_tags;
    }
}