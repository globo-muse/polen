<?php
namespace Polen\Includes;

use Exception;
use WP_Error;

class Polen_Create_Customer
{
    /**
     * Criar usuario
     *
     * @param array $data
     * @return \WP_User
     */
    public function create_new_user( array $data, $campaign = '', $is_checkout = false )
    {
        $userdata = array(
            'user_login' => $data['email'],
            'user_email' => $data['email'],
            'user_pass' => wp_generate_password(6, false),
            'first_name' => $data['name'],
            'nickname' => $data['name'],
            'role' => 'customer',
        );

        $user['new_account'] = false;
        $user_wp = get_user_by( 'email', $userdata['user_email'] );
        if( false === $user_wp ) {

            $args = [];
            if( !empty( $campaign ) ) {
                $args[ 'campaign' ] = $campaign;
            }
            if( $is_checkout ) {
                $args[ Polen_Checkout_Create_User::META_KEY_CREATED_BY ] = 'checkout';
            }
            
            $user_id = $this->create_user_custumer(
                $userdata['user_email'],
                $userdata['first_name'],
                $userdata['user_pass'],
                $args,
                $is_checkout
            );
            $user['new_account'] = true;
            $user_wp = get_user_by( 'id', $user_id );
        }

        unset( $user_wp->user_pass );
        $user['user_object'] = $user_wp;

        $address = array(
            'billing_email' => $data['email'],
            'billing_cpf' => preg_replace('/[^0-9]/', '', $data['cpf'] ?? '' ),
            'billing_country' => 'BR',
            'billing_phone' => preg_replace('/[^0-9]/', '', $data['phone'] ?? '' ),
            'billing_cellphone' => preg_replace('/[^0-9]/', '', $data['phone'] ?? '' ),
        );

        foreach ( $address as $key => $value ) {
            update_user_meta( $user['user_object']->ID, $key, $value );
        }
        return $user;
    }


    /**
     * Cria um user Customer com os mesmos hooks e actions do WC
     * 
     * @param string
     * @param string
     * @param string
     * @param array
     * @return int
     */
    public function create_user_custumer( $email, $user_name, $password, $metas = [], $is_checkout = false )
    {
        $args = [ 'display_name' => $user_name ];

        //Seguindo Padrao do Woocommerce com as ACTIONs
        $errors = new WP_Error();

        do_action( 'woocommerce_register_post', $email, $email, $errors );
        
        $errors = apply_filters( 'woocommerce_registration_errors', $errors, $email, $email );
		if ( $errors->get_error_code() ) {
			throw new Exception( $errors->get_error_messages(), $errors->get_error_code() );
		}
		
        $new_customer_data = apply_filters(
			'woocommerce_new_customer_data',
			array_merge(
				$args,
				array(
					'user_login' => $email,
					'user_pass'  => $password,
					'user_email' => $email,
					'role'       => 'customer',
				)
			)
		);

        $customer_id = wp_insert_user( $new_customer_data );

        if( is_wp_error( $customer_id ) ) {
            throw new Exception( implode( $customer_id->get_error_messages() ), 403 );
        }

        if( isset( $metas[ 'campaign' ] ) ) {
            Polen_Campaign::set_user_campaign( $customer_id, $metas['campaign'] );
            unset( $metas['campaign'] );
        }

        if( !empty( $metas ) ) {
            foreach( $metas as $key => $value ) {
                update_user_meta( $customer_id, $key, $value );
            }
        }

        if( !$is_checkout ) {
            $password = '';
        }

        do_action( 'woocommerce_created_customer', $customer_id, [ 'user_pass' => $password ], $is_checkout );

        return $customer_id;
    }
}