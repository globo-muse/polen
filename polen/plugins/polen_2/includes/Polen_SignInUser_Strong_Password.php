<?php

namespace Polen\Includes;

class Polen_SignInUser_Strong_Password
{
    public function __construct( bool $static = false )
    {
        if( $static ) {
            $this->init();
        }
    }

    
    public function init()
    {
        add_action( 'polen_register_form', array( $this, 'enqueue_scripts' ) );
        add_filter( 'woocommerce_registration_errors', array( $this, 'validate_strong_password' ), 11, 3 );
    }


    public function enqueue_scripts()
    {
        $min = get_assets_folder();
    }


    public function get_password_lenght()
    {
        return 6;
    }


    /**
     * Valida se a senha é forte
     * @param WP_Error
     * @param string
     * @param string
     */
    public function validate_strong_password( $errors, $username, $email )
    {
        // Given password
        $password = filter_input( INPUT_POST, 'password' );
        $http_referer = filter_input( INPUT_POST, '_wp_http_referer' );

        $password_lenght = $this->get_password_lenght();
        // Validate password strength
        $result_strong_password = $this->verify_strong_password( $password );
        if( '/register/' == $http_referer ) {
            if( !$result_strong_password ) {
                // $errors->add( 'registration-error', "A senha deveria ter ao menos {$password_lenght} caracteres, 1 letra maiuscula, 1 numero e 1 caracter especial", 'woocommerce' );//Antiga mensagem quando ainda precisava de uppercase, lowercase e specialChars
                $errors->add( 'registration-error', $this->get_default_message_error(), 'woocommerce' );
            }
        }

        return $errors;
    }

    public function get_default_message_error()
    {
        $password_lenght = $this->get_password_lenght();
        return "A senha deveria ter ao menos {$password_lenght} caracteres e 1 numero";
    }

    public function verify_strong_password( $password )
    {
        $password_lenght = 6;
        // Validate password strength
        $uppercase = true;//preg_match('@[A-Z]@', $password);
        $lowercase = true;//preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = true;//preg_match('@[^\w]@', $password);
        if( !$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < $password_lenght ) {
            return false;
        }
        return true;
    }
}
