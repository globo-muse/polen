<?php

namespace Polen\Includes;

class Polen_SignInUser_Terms_Use
{

    public function __construct( bool $static = false )
    {
        if( $static ) {
            $this->init();
        }
    }

    public function init()
    {
        add_filter( 'woocommerce_registration_errors', array( $this, 'validate'), 11, 3 );
    }


    /**
     * Handler que Valida o Google Recaptcha no Registro do Usuário
     * @param \WP_Error
     * @param string
     * @param string
     */
    public function validate( $errors, $username, $email )
    {
        $terms = filter_input( INPUT_POST, 'terms' );
        $terms_field = filter_input( INPUT_POST, 'terms-field' );

        if( $terms_field == '1' &&  empty( $terms ) ) {
            $errors->add('registration-error', 'Aceite os termos e condições do site', 'woocommerce');
        }

        return $errors;
    }
}
