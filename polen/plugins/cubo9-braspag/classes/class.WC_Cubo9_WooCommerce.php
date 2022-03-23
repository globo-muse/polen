<?php

class WC_Cubo9_WooCommerce {

    public function __construct( $static = false ) {
        if( $static ) {
            add_action( 'init', array( $this, 'endpoints' ) );
            add_filter( 'query_vars', array( $this, 'query_vars' ), 0, 1 );
            add_filter( 'woocommerce_account_menu_items', array( $this, 'my_account_tabs' ) );
            add_action( 'woocommerce_account_payment-options_endpoint', array( $this, 'my_account_credit_cards_content' ) );
            add_action( 'woocommerce_account_add-payment-option_endpoint', array( $this, 'my_account_add_credit_card_content' ) );
            add_action( 'wp_ajax_braspag-default', array( $this, 'make_default_payment') );
            add_action( 'wp_ajax_braspag-remove', array( $this, 'remove_payment') );
            add_action( 'wp_ajax_braspag-add-card', array( $this, 'ajax_verify_card' ) );
        }
    }

    function endpoints() {
        add_rewrite_endpoint( 'payment-options', EP_PAGES );
        add_rewrite_endpoint( 'add-payment-option', EP_PAGES );
    }
    
    public function query_vars( $query_vars ) {
        $query_vars[] = 'payment-options';
        $query_vars[] = 'add-payment-option';
        return $query_vars;
    }

    public function my_account_tabs( $tabs ) {
        $tabs['payment-options'] = __( 'Opções de Pagamento', 'cubonove' );
        return $tabs;
    }

    public function my_account_credit_cards_content() {
        if( file_exists( TEMPLATEPATH . '/braspag/my-account/payment-options.php') ) {
            require_once TEMPLATEPATH . '/braspag/my-account/payment-options.php';
        } else {
            require_once PLUGIN_CUBO9_BRASPAG_DIR . 'assets/php/my-account/payment-option.php';
        }
    }

    public function my_account_add_credit_card_content() {
        if( file_exists( TEMPLATEPATH . '/braspag/my-account/add-payment-option.php') ) {
            require_once TEMPLATEPATH . '/braspag/my-account/add-payment-option.php';
        } else {
            require_once PLUGIN_CUBO9_BRASPAG_DIR . 'assets/php/my-account/add-payment-option.php';
        }
    }

    public function make_default_payment() {
        $return = array(
            'success' => 0,
        );
        if( is_user_logged_in() && isset( $_POST['id'] ) && strlen( $_POST['id'] ) == 32 ) {
            $braspag_card_saved_data = get_user_meta( get_current_user_id(), 'braspag_card_saved_data' );
            if( ! is_null( $braspag_card_saved_data ) && ! empty( $braspag_card_saved_data ) && is_array( $braspag_card_saved_data ) && count( $braspag_card_saved_data ) > 0 ) {
                $braspag_default_payment = get_user_meta( get_current_user_id(), 'braspag_default_payment', true );
                delete_user_meta( get_current_user_id(), 'braspag_default_payment' );
                foreach( $braspag_card_saved_data as $k => $cards ) {
                    foreach( $cards as $p => $data ) {
                        $prefix = md5( $p );
                        if( ( ! $braspag_default_payment || is_null( $braspag_default_payment ) || empty( $braspag_default_payment ) ) && $prefix == $_POST['id'] && $braspag_default_payment == $_POST['id'] ) {
                            delete_user_meta( get_current_user_id(), 'braspag_default_payment' );
                        } else if( ( ! $braspag_default_payment || is_null( $braspag_default_payment ) || empty( $braspag_default_payment ) ) && $prefix == $_POST['id'] ) {
                            update_user_meta( get_current_user_id(), 'braspag_default_payment', $prefix );
                            $return = array(
                                'success' => 1,
                            );
                        }
                    }
                }
            } else {
                
            }
        }
        echo wp_json_encode( $return );
        die;
    }

    public function remove_payment() {
        $return = array(
            'success' => 0,
        );
        if( is_user_logged_in() && isset( $_POST['id'] ) && strlen( $_POST['id'] ) == 32 ) {
            global $wpdb;

            $sql_delete = "SELECT `umeta_id` FROM `" . $wpdb->usermeta . "` WHERE  `meta_key`='braspag_card_saved_data' AND  `user_id`=" . get_current_user_id() . " AND MD5( `umeta_id` ) = '" . $_POST['id'] . "'";
            $res_delete = $wpdb->get_results( $sql_delete );
            
            if( $res_delete && ! is_null( $res_delete ) && ! is_wp_error( $res_delete ) && is_array( $res_delete ) && ! empty( $res_delete ) && count( $res_delete ) == 1 ) {
                $sql_default = "SELECT `umeta_id` FROM `" . $wpdb->usermeta . "` WHERE  `meta_key`='braspag_default_payment' AND  `user_id`=" . get_current_user_id();
                $res_default = $wpdb->get_results( $sql_default );
                if( $res_default && ! is_null( $res_default ) && ! is_wp_error( $res_default ) && is_array( $res_default ) && ! empty( $res_default ) && count( $res_default ) == 1 ) {

                }
                $delete = $wpdb->delete(
                    $wpdb->usermeta,
                    array(
                        'umeta_id' => $res_delete[0]->umeta_id,
                    ),
                    array(
                        '%d'
                    ),
                );

                $return = array(
                    'success' => 1,
                );
            }
        }
        echo wp_json_encode( $return );
        die;
    }

    public function ajax_verify_card() {
        if( is_user_logged_in() && $_POST ) {
            $Cubo9_Braspag = new Cubo9_Braspag( false, false );

            $number   = preg_replace( '/[^0-9]/', '', $_POST['number'] );
            $holder   = trim( $_POST['holder'] );
            $validity = preg_replace( '/[^0-9]/', '', $_POST['validity'] );
            $cvv      = preg_replace( '/[^0-9]/', '', $_POST['cvv'] );
            
            $number   = ( strlen( $number ) < 13 || strlen( $number ) > 19 ) ? false : $number;
            $holder   = ( ! empty( $holder ) && strlen( $holder ) > 3 ) ? $holder : false;
            $validity = ( strlen( $validity ) === 6 ) ? substr( $validity, 0, 2 ) . '/' . substr( $validity, -4 ) : false;
            $cvv      = ( strlen( $cvv ) === 3 || strlen( $cvv ) === 4 ) ? $cvv : false;

            $args = array(
                'number'   => $number,
                'holder'   => substr( $holder, 0, 25 ),
                'validity' => $validity,
                'cvv'      => $cvv,
            );

            $response = $Cubo9_Braspag->tokenize_card( $args );
        } else {
            $response = array(
                'error'   => true,
                'message' => 'Unknow method.',
            );
        }

        echo wp_json_encode( $response );
        die;
    }
}

new WC_Cubo9_WooCommerce( true );