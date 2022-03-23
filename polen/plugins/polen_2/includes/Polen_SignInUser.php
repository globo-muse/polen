<?php

namespace Polen\Includes;

class Polen_SignInUser
{
    public function __construct() {
        add_action( 'wp_logout', array( $this, 'polen_logout_redirect' ), 10, 1 );
        add_action( 'user_register', array( $this, 'register_check_user_logged_out_orders'), 999, 1 );
        add_shortcode( 'polen_register_form', array( $this, 'register_form' ) );
        add_filter( 'woocommerce_registration_redirect', function( $redirection_url ) { return get_bloginfo( 'url' ); }, 10, 1 );
//        add_filter( 'woocommerce_new_customer_data', array($this, 'save_name_and_birthday'), 10, 1);
//        add_filter( 'woocommerce_registration_errors', array($this, 'required_name_birthday'), 10, 3 );
    }        

    public function add_fields_sign_in()
    { ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_name"><?php esc_html_e( 'Name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="name" id="reg_name" autocomplete="name" value="<?php echo ( ! empty( $_POST['name'] ) ) ? esc_attr( wp_unslash( $_POST['name'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
        </p>
    
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_phone"><?php esc_html_e( 'Phone', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="phone" id="reg_phone" autocomplete="phone" value="<?php echo ( ! empty( $_POST['phone'] ) ) ? esc_attr( wp_unslash( $_POST['phone'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
        </p>
    <?php
    }

    public function update_user_date($customer_id, $new_customer_data, $password_generated){
        wp_update_user(['ID' => $customer_id, 'display_name' => $_POST['name']]);
        update_user_meta($customer_id, 'first_name', $_POST['name']);
        update_user_meta($customer_id, '_phone', $_POST['phone']);
    }

    public function polen_logout_redirect( $_ ) {
            wp_redirect( home_url() );
            exit();
    }

    public function register_check_user_logged_out_orders( $user_id ) {
        global $wpdb;
        $user = get_user_by( 'id', $user_id );
        if( $user && ! is_null( $user ) && ! empty( $user ) && isset( $user->user_email ) ) {
            $sql_orders = "SELECT `post_id` AS `order_id` FROM `" . $wpdb->postmeta . "` WHERE `post_id` IN ( SELECT `post_id` FROM `" . $wpdb->postmeta . "` WHERE `meta_key`='_billing_email' AND `meta_value`='" . $user->user_email . "' ) AND `meta_key`='_customer_user' AND `meta_value`='0'";
            $res_orders = $wpdb->get_results( $sql_orders );
            if( $res_orders && ! is_null( $res_orders ) && ! empty( $res_orders ) && is_array( $res_orders ) && count( $res_orders ) > 0 ) {
                foreach( $res_orders as $k => $order ) {
                    update_post_meta( $order->order_id, '_customer_user', $user->ID );
                }
            }
        }
    }

    /* 
    public function login_check_user_logged_out_orders( $user_login, $user ) {
        global $wpdb;
        if( $user && ! is_null( $user ) && ! empty( $user ) && isset( $user->user_email ) ) {
            $sql_orders = "SELECT `post_id` AS `order_id` FROM `" . $wpdb->postmeta . "` WHERE `post_id` IN ( SELECT `post_id` FROM `" . $wpdb->postmeta . "` WHERE `meta_key`='_billing_email' AND `meta_value`='" . $user->user_email . "' ) AND `meta_key`='_customer_user' AND `meta_value`='0'";
            $res_orders = $wpdb->get_results( $sql_orders );
            if( $res_orders && ! is_null( $res_orders ) && ! empty( $res_orders ) && is_array( $res_orders ) && count( $res_orders ) > 0 ) {
                foreach( $res_orders as $k => $order ) {
                    update_post_meta( $order->order_id, '_customer_user', $user->ID );
                }
            }
        }
    } 
    */

    public function register_form() {
        if ( is_admin() ) {
            wp_safe_redirect( polen_get_url_my_account() );
            exit;
        } elseif ( is_user_logged_in() ) {
            wp_safe_redirect( polen_get_url_my_account() );
            exit;
        } else {
            
            $min = get_assets_folder();
            wp_register_script( 'user-register-js', TEMPLATE_URI . '/assets/js/' . $min . 'user-register.js', array("global-js"), _S_VERSION, true );
            wp_enqueue_script( 'user-register-js' );

            do_action('polen_register_form');
            ob_start();
            wc_get_template( 'myaccount/form-register.php' );
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }
    }
    
    public function required_name_birthday( $errors, $username, $email )
    {
        $birthday = filter_input( INPUT_POST, 'birthday' );
        $name     = filter_input( INPUT_POST, 'fullname' );
        
        if( empty( $birthday ) || empty( $name ) ) {
            $errors->add( 'registration-error-missing-birthday', 'Todos os compos são obrigatórios' );
        }
        
       if( \DateTime::createFromFormat( 'd/m/Y', $birthday ) == false ) {
           $errors->add( 'registration-error-invalid-birthday', 'Data inválida' );
       }
       
       if( strlen( $name ) < 4 ) {
           $errors->add( 'registration-error-missing-name', 'Digite o nome completo' );
       }
        return $errors;
    }
    
    public function save_name_and_birthday( $data )
    {
        $birthday_str = filter_input( INPUT_POST, 'birthday' );
        $name     = filter_input( INPUT_POST, 'fullname' );
        
        $birthday = \DateTime::createFromFormat( 'd/m/Y', $birthday_str );
        
        $data['first_name'] = $name;
        $data['birthday'] = $birthday->format('Y-m-d');
        
        return $data;
    }
}
