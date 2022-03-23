<?php

namespace Polen\Includes;

use \Polen\Admin\Polen_Admin;

class Polen_Checkout
{

    private $table_talent;

    public function __construct( $static = false ) {
        if( $static ) {
            //add_action( 'woocommerce_edit_account_form_start', array( $this, 'add_cpf_to_form' ) );
            add_action( 'woocommerce_edit_account_form_start', array( $this, 'add_phone_to_form' ) );
            add_filter( 'woocommerce_save_account_details', array( $this, 'save_account_details' ) );
            //add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'add_cpf_and_phone_to_checkout') );
            //add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_order_meta_from_checkout' ) );
            add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'add_name_and_email_to_checkout') );
            add_filter( 'woocommerce_checkout_fields', array( $this, 'remove_woocommerce_fields' ) );
            add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
            add_filter( 'the_title',  array( $this, 'remove_thankyou_title' ), 20, 2 );
            add_action( 'woocommerce_checkout_order_processed', array( $this, 'checkout_set_order_client' ) );
            //add_filter( 'woocommerce_form_field_args', array( $this, 'teste' ), 11, 2 );
        }
    }

    public function remove_woocommerce_fields( $fields ) {
        $removed_keys = array(
            'billing_company',
            'billing_phone',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_postcode',
            'billing_country',
            'billing_state',
            'billing_email',
            'billing_first_name',
            'billing_last_name'
        );

        foreach( $removed_keys as $key ) {
            unset( $fields['billing'][$key] );
        }
        
        return $fields;
    }

 
    /**
     * Add CPF to user account form
     */
    public function add_cpf_to_form() {
        $user = wp_get_current_user();

        if( is_account_page() ) {
            ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="billing_cpf">
                    <?php _e( 'CPF', 'cubo9-marketplace' ); ?> <span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input input-text form-control form-control-lg" name="billing_cpf" id="billing_cpf" value="<?php echo esc_attr( $user->billing_cpf ); ?>" />
                <div class="error-message"></div>
            </p>
        <?php
        } else {
            if( ! empty( $user->billing_cpf ) ) {
            ?>
                <input 	type="hidden" class="woocommerce-Input input-text" name="billing_cpf" id="billing_cpf" value="<?php echo esc_attr( $user->billing_cpf ); ?>" />
                <div class="error-message"></div>
            <?php
            } else {
                ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="billing_cpf"><?php _e( 'CPF', 'woocommerce' ); ?> <span class="required">*</span></label>
                    <input type="text" class="woocommerce-Input input-text form-control form-control-lg" name="billing_cpf" id="billing_cpf" value="<?php echo esc_attr( $user->billing_cpf ); ?>" />
                    <div class="error-message"></div>
                </p>
            <?php
            }
        }   
    }

    /**
     * Adicionar o campo de CPF no Checkout para caso o usuário não possua.
     */
    public function add_cpf_and_phone_to_checkout( $checkout ) {
        /*
        $billing_cpf = get_user_meta( get_current_user_id(), 'billing_cpf', true );
        if( ! $billing_cpf || is_null( $billing_cpf ) || empty( $billing_cpf ) || strlen( $billing_cpf ) != 14 ) {
            $args = array(
                "type"        => "text",
                "required"    => true,
                "class"       => array( "form-row-wide", "input-cpf" ),
                "label"       => "CPF",
                "label_class" => array( 'title-on-checkout-notes' ),
                "placeholder" => "Informe seu CPF",
                "maxlength"   => 14,
            );
            woocommerce_form_field( 'billing_cpf', $args, $checkout->get_value( 'billing_cpf' ) );
        }
        */
        /*
        $billing_phone = get_user_meta( get_current_user_id(), 'billing_phone', true );
        if( ! $billing_phone || is_null( $billing_phone ) || empty( $billing_phone ) || strlen( $billing_phone ) != 14 ) {
            $args = array(
                "type"        => "text",
                "required"    => true,
                "class"       => array( "form-row-wide", "input-cpf" ),
                "label"       => "Telefone",
                "label_class" => array( 'title-on-checkout-notes' ),
                "placeholder" => "Informe seu Telefone",
                "maxlength"   => 14,
            );
            woocommerce_form_field( 'billing_phone', $args, $checkout->get_value( 'billing_phone' ) );
        }
        */
    }

    /**
     * Salvar o campo de CPF do usuário no Checkout para caso o usuário não possua.
     */
    public function save_order_meta_from_checkout( $order_id ) {
        $billing_cpf = get_user_meta( $_customer_user, 'billing_cpf', true );
        if( ( ! $billing_cpf || is_null( $billing_cpf ) || empty( $billing_cpf ) || strlen( $billing_cpf ) != 14 )
            && ( isset( $_POST['billing_cpf'] ) && ! empty( trim( $_POST['billing_cpf'] ) ) && strlen( trim( $_POST['billing_cpf'] ) ) == '14' ) 
        ) {
            $_customer_user = get_post_meta( $order_id, '_customer_user', true );
            update_user_meta( $_customer_user, 'billing_cpf', trim( $_POST['billing_cpf'] ) );
            update_post_meta( $order_id, 'billing_cpf', trim( $_POST['billing_cpf'] ) );
        } else if( $billing_cpf && ! is_null( $billing_cpf ) && ! empty( $billing_cpf ) && strlen( $billing_cpf ) == 14 ) {
            update_post_meta( $order_id, 'billing_cpf', $billing_cpf );
        }
    }

    public function add_phone_to_form() {
        $user = wp_get_current_user();

        if( is_account_page() ) {
            /*
            Movido para form-edit-account.php
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label><?php _e( 'Celular', 'woocommerce' ); ?></label>
                <input type="text" placeholder="<?php _e( 'Celular', 'woocommerce' ); ?>" class="woocommerce-Input input-text form-control form-control-lg" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $user->billing_phone ); ?>" />
                <div class="error-message"></div>
            </p>
        */
        } else {
            if( ! empty( $user->billing_phone ) ) {
            ?>
                <input 	type="hidden" class="woocommerce-Input input-text form-control form-control-lg" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $user->billing_phone ); ?>" />
                <div class="error-message"></div>
            <?php
            } else {
                ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label><?php _e( 'Celular', 'woocommerce' ); ?></label>
                    <input type="text" placeholder="<?php _e( 'Celular', 'woocommerce' ); ?>" class="woocommerce-Input input-text form-control form-control-lg" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $user->billing_phone ); ?>" />
                    <div class="error-message"></div>
                </p>
            <?php
            }
        }   
    }

    public function save_account_details( $user_id ) {
        //update_user_meta( $user_id, 'billing_cpf', sanitize_text_field( $_POST['billing_cpf'] ) );
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }

    public function remove_thankyou_title( $title, $id ) {
        if ( ( is_order_received_page() && get_the_ID() === $id ) || is_account_page() ) {
            $title = '';
        }
        return $title;
    }

    public function checkout_set_order_client( $order_id ) {
        if( ! is_user_logged_in() ) {
            $order = wc_get_order( $order_id );
            $email = get_post_meta( $order_id, '_billing_email', $order_id );
            if( $email && ! is_null( $email ) && ! empty( $email ) ) {
                $user = get_user_by( 'email', $email );
                if( $user && ! is_null( $user ) && isset( $user->ID ) ) {
                    update_post_meta( $order_id, '_customer_user', $user->ID );
                }
            }
        }
    }


    /**
     * Adicionar o campo de nome, sobrenome e e-mail no checkout para caso o usuário não possua.
     */
    public function add_name_and_email_to_checkout( $checkout ) {
        /*
        $billing_first_name = get_user_meta( get_current_user_id(), 'billing_first_name', true );
        if( ! $billing_first_name || is_null( $billing_first_name ) || empty( $billing_first_name ) ) {   
            $args = array(
                "type"        => "text",
                "required"    => true,
                "input_class" => array( "form-control", "input-text" ),
                "label"       => "Nome",
                "label_class" => array( 'title-on-checkout-notes' ),
                "placeholder" => "nome",
            );
            woocommerce_form_field( 'billing_first_name', $args, $checkout->get_value( 'billing_first_name' ) );
        }        
        
        $billing_last_name = get_user_meta( get_current_user_id(), 'billing_last_name', true );
        if( ! $billing_last_name || is_null( $billing_last_name ) || empty( $billing_last_name ) ) {   
            $args = array(
                "type"        => "text",
                "required"    => true,
                "input_class"       => array( "form-control", "input-text" ),
                "label"       => "Sobrenome",
                "label_class" => array( 'title-on-checkout-notes' ),
                "placeholder" => "Sobrenome",
            );
            woocommerce_form_field( 'billing_last_name', $args, $checkout->get_value( 'billing_last_name' ) );
        }
        */

        $billing_email = get_user_meta( get_current_user_id(), 'billing_email', true );
        if( ! $billing_email || is_null( $billing_email ) || empty( $billing_email ) ) {   
            if ( is_user_logged_in() ) {
                $current_user = wp_get_current_user();
                $email = $current_user->user_email;
                $type = 'hidden';
                $logged = 'd-none';
            } else {
                $items = WC()->cart->get_cart();
                $key = array_key_first( $items );
                $email = $items[ $key ][ 'email_to_video' ];
                $type = 'hidden';
                $logged = '';
            }
            $args = array(
                "type"        => $type,
                "required"    => true,
                "class"       => array( $logged ),
                "input_class" => array( "form-control", "form-control-lg", "input-text", $logged ),
                "label"       => "",
                "label_class" => array( 'title-on-checkout-notes', $logged ),
                "placeholder" => "",
                // "maxlength"   => 14,
                "default"     => $email,
            );
            woocommerce_form_field( 'billing_email', $args, $checkout->get_value( 'billing_email' ) );
        }
    }
}
