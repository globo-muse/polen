<?php

namespace Polen\Includes;

use \Polen\Admin\Polen_Admin;

class Polen_Update_Fields
{

    private $table_talent;

    public function __construct( $static = false ) {
        if( $static ) {
            /* New User */
            add_action( 'user_new_form',  array( $this, 'fields' ), 10, 1 );
            add_action( 'user_register', array( $this, 'update_vendor_profile' ) );
            
            /* Edit or Profile User */
            add_action( 'show_user_profile',  array( $this, 'fields' ), 10, 1 );
            add_action( 'edit_user_profile', array( $this, 'fields' ), 10, 1 );
            add_action( 'edit_user_profile_update', array( $this, 'update_vendor_profile' ) );
            
            if( is_admin() && isset( $_REQUEST['user_id'] ) ) {
                require_once ABSPATH . '/wp-includes/pluggable.php';
                $user = get_user_by( 'id', $_REQUEST['user_id'] );
                if( ( isset( $user->caps['user_talent'] ) && $user->caps['user_talent'] == true ) ||  
                    ( isset( $user->caps['user_charity'] ) && $user->caps['user_charity'] == true ) ) {
                    add_filter( 'woocommerce_customer_meta_fields', array( $this, 'remove_woocommerce_fields' ), 9999 );
                }
            }

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
            
            // Validacao do Cadastro do usuário
//            add_action('user_profile_update_errors', array( $this, 'check_role_talent_fields' ), 10, 3);

            //quando o usuário é apagado os dados de table_talent também são
            add_action( 'delete_user',  array( $this, 'on_delete_talent' ), 10, 1 );
        }

        global $wpdb;
        $this->table_talent = $wpdb->base_prefix . 'polen_talents';
    }

    public function check_role_talent_fields( \WP_Error $errors, $update, $user )
    {
        if( in_array( 'user_talent', $user->role ) ) {
            if( empty( $_REQUEST['talent_alias'] )) {
                $errors->add( 'slug-required', 'Slug é obrigatório para função Talento');
            }

            if( 'PJ' == $_REQUEST['natureza_juridica'] && empty( $_REQUEST['cnpj'] )) {
                $errors->add('cnpj-required', 'CNPJ é um dado obrigatório, para talentos PJ');
            }

            if( 'PF' ==  $_REQUEST['natureza_juridica'] && empty( $_REQUEST['cpf'] )) {
                $errors->add('cpf-required', 'CPF é um dado obrigatório, para talentos PF');
            }
        }
    }

    public function remove_woocommerce_fields() {
        return [];
    }

    public function admin_scripts() {
        global $wp_scripts;
        wp_enqueue_media();
        wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.min.css' );
        wp_enqueue_script('jquery-maskedinput', Polen_Admin::get_js_url( 'vendor/jquery.maskedinput.min.js'), array( 'jquery' ), null, true );
        wp_enqueue_script('StreamSaver', Polen_Admin::get_js_url( 'vendor/StreamSaver.js'), array( 'jquery' ), null, true );
        wp_enqueue_script('zip-stream', Polen_Admin::get_js_url( 'vendor/zip-stream.js'), array( 'jquery' ), null, true );
        wp_enqueue_script('polen-admin-script', Polen_Admin::get_js_url( 'admin.js' ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs' ), null, true );
        wp_enqueue_script('polen-midia-script', Polen_Admin::get_js_url( 'polen-midia.js' ), array( 'jquery' ), null, true );
    }

    public function fields( $user )
    {
        if( $user instanceof \WP_User ) {
            if( $user->has_cap('user_talent') == true ){
                require_once Polen_Admin::get_metabox_path( 'metabox-talent-data.php' );
            }
            if( $user->caps('user_charity') == true ){
                require_once Polen_Admin::get_metabox_path( 'metabox-charity-data.php' );
            }
        } else if ( $user === 'add-new-user' ) {
            require_once Polen_Admin::get_metabox_path( 'metabox-talent-data.php' );
            //require_once Polen_Admin::get_metabox_path( 'metabox-charity-data.php' );
        }
    }

    public function get_vendor_data( $user_id ) {
        global $wpdb;
        $sql = "SELECT * FROM `" . $this->table_talent . "` WHERE `user_id`=" . intval( $user_id );
        $res = $wpdb->get_row( $sql );
        if( !empty( $res ) ) {
            return $res;
        }
    }

    public function update_vendor_profile( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        $args = array();
        $args['user_id'] = $user_id;
        $email = $_POST['store_email'];

        // Categorias do Talento
        if( isset( $_POST['talent_category'] ) && !empty(  $_POST['talent_category'] ) ){
            //$talent_category = (string) strip_tags( trim( $_POST['talent_category'] ) );
            $talent_category = $_POST['talent_category'];
            if( is_countable( $talent_category ) && is_array( $talent_category ) && count( $talent_category ) > 0 ){
                wp_set_object_terms( $user_id, $talent_category, 'talent_category', false );
            }
        }
        // Aba "Geral"
        $talent_alias = (string) sanitize_title( strip_tags( trim( $_POST['talent_alias'] ) ) );
        $args['talent_alias'] = ( $talent_alias ) ? sanitize_title( $talent_alias ) : sanitize_title( $email );
        $args['talent_url'] = get_bloginfo('url') . '/talento/' . $args['talent_alias'];
        $talent_cover_image_id     = ( isset( $_POST['talent_cover_image_id'] ) && ! empty( $_POST['talent_cover_image_id'] ) ) ? $_POST['talent_cover_image_id'] : false;
        $args['cover_image_id']    = ( $talent_cover_image_id ) ? $talent_cover_image_id : '';
        $talent_cover_image_url    = ( isset( $_POST['talent_cover_image_url'] ) && ! empty( $_POST['talent_cover_image_url'] ) ) ? $_POST['talent_cover_image_url'] : false;
        $args['cover_image_url']   = ( $talent_cover_image_url ) ? $talent_cover_image_url : '';
        $talent_cover_image_thumb  = ( isset( $_POST['talent_cover_image_thumb'] ) && ! empty( $_POST['talent_cover_image_thumb'] ) ) ? $_POST['talent_cover_image_thumb'] : false;
        $args['cover_image_thumb'] = ( $talent_cover_image_thumb ) ? $talent_cover_image_thumb : '';
        $talent_profile_video      = ( isset( $_POST['talent_profile_video'] ) && ! empty( $_POST['talent_profile_video'] ) ) ? $_POST['talent_profile_video'] : '';
        $args['profile_video']     = ( $talent_profile_video ) ? $talent_profile_video : '';
        $args['tempo_resposta']    = (string) strip_tags( trim( $_POST['tempo_resposta'] ) );
        $args['profissao']         = (string) strip_tags( trim( $_POST['profissao'] ) );
        // $args['descricao']         = (string) strip_tags( trim( $_POST['descricao'] ) );

        // Aba "Dados do Talento"
        $natureza_juridica = (string) strip_tags( trim( $_POST['natureza_juridica'] ) );
        if( $natureza_juridica != 'PJ' && $natureza_juridica != 'PF' ) {
            $natureza_juridica = null;
        }
        $args['natureza_juridica'] = $natureza_juridica;

        $contact_email = sanitize_email($_POST['contact_email']);
        update_user_meta($user_id, 'contact_email', $contact_email);

        if( $natureza_juridica == 'PJ' ) {
            $razao_social = (string) strip_tags( trim( $_POST['razao_social'] ) );
            $args['razao_social'] = $razao_social;

            $nome_fantasia = (string) strip_tags( trim( $_POST['nome_fantasia'] ) );
            $args['nome_fantasia'] = $nome_fantasia;

            $cnpj = (string) strip_tags( trim( $_POST['cnpj'] ) );
            $args['cnpj'] = $cnpj;
        }

        if( $natureza_juridica == 'PF' ) {
            $nome = (string) strip_tags( trim( $_POST['nome'] ) );
            $args['nome'] = $nome;

            $cpf = (string) strip_tags( trim( $_POST['cpf'] ) );
            $args['cpf'] = $cpf;
        }

        if( strip_tags( trim( $_POST['reter_iss'] ) ) == 'S' ) {
            $reter_iss = 'S';
        } else {
            $reter_iss = 'N';
        }

        $nascimento = (string) strip_tags( trim( $_POST['nascimento'] ) );
        $args['nascimento'] = $nascimento;

        $args['reter_iss'] = $reter_iss;

        // Aba "Informações de Contato"
        $email = (string) strip_tags( trim( $_POST['store_email'] ) );
        $args['email'] = $email;

        $telefone = (string) strip_tags( trim( $_POST['telefone'] ) );
        $args['telefone'] = $telefone;

        $celular = (string) strip_tags( trim( $_POST['celular'] ) );
        $args['celular'] = $celular;

        $whatsapp = (string) strip_tags( trim( $_POST['whatsapp'] ) );
        $args['whatsapp'] = $whatsapp;
        
        // Aba "Redes Sociais"
        $facebook = (string) strip_tags( trim( $_POST['facebook'] ) );
        $args['facebook'] = $facebook;

        $instagram = (string) strip_tags( trim( $_POST['instagram'] ) );
        $args['instagram'] = $instagram;

        $twitter = (string) strip_tags( trim( $_POST['twitter'] ) );
        $args['twitter'] = $twitter;

        $pinterest = (string) strip_tags( trim( $_POST['pinterest'] ) );
        $args['pinterest'] = $twitter;

        $linkedin = (string) strip_tags( trim( $_POST['linkedin'] ) );
        $args['linkedin'] = $linkedin;

        $youtube = (string) strip_tags( trim( $_POST['youtube'] ) );
        $args['youtube'] = $youtube;

        // Aba "Dados Bancários"
        
        $banco = strip_tags( trim( $_POST['banco'] ) );
        if( !empty( $banco ) ) {
            list( $codigo_banco, $nome_banco ) = explode( ":", $banco );
            $args['codigo_banco'] = $codigo_banco;
            $args['banco'] = $nome_banco;
        } else {
            $args['codigo_banco'] = null;
            $args['banco'] = null;
        }

        
        $agencia = (string) strip_tags( trim( $_POST['agencia'] ) );
        if( (int) $agencia == (int) 0 ) {
            $args['agencia'] = null;
        } else {
            $args['agencia'] = str_pad( $agencia, 4, "0", STR_PAD_LEFT );
        }
        
        $conta = (string) strip_tags( trim( $_POST['conta'] ) );
        if( empty( $conta ) ) {
            $args['conta'] = null;
        } else {
            $args['conta'] = $conta;
        }

        $tipo_conta = (string) strip_tags( trim( $_POST['tipo_conta'] ) );
        if( empty( $tipo_conta ) ) {
            $args['tipo_conta'] = null;
        } else {
            $args['tipo_conta'] = $tipo_conta;
        }

        // Aba "Configurações Financeiras"
        $subordinate_merchant_id = (string) strip_tags( trim( $_POST['subordinate_merchant_id'] ) );
        $mdr = (float) strip_tags( trim( $_POST['mdr'] ) );
        $fee = (int) strip_tags( trim( $_POST['fee'] ) );

        if( empty( $subordinate_merchant_id ) ) {
            $subordinate_merchant_id = null;
        }

        if( ! $mdr > 0 ) {
            $mdr = null;
        }

        if( ! $fee > 0 ) {
            $fee = null;
        }
    
        $args['subordinate_merchant_id'] = $subordinate_merchant_id;
        $args['mdr'] = $mdr;
        $args['fee'] = $fee;

        global $wpdb;
        $wpdb->show_errors = true;
        $vendorData = $this->get_vendor_data( $user_id );
//        echo '<pre>';var_dump($vendorData);die;
        if( $vendorData && ! is_null( $vendorData ) && ! empty( $vendorData ) && isset( $vendorData->ID ) ) {
            $statusDate = new \DateTime();
            $timeZone = new \DateTimeZone( get_option('timezone_string') );
            $statusDate->setTimezone( $timeZone );
            $updated = $statusDate->format( 'Y-m-d H:i:s' );
            $args['updated'] = $updated;

            unset( $args['user_id'] );
            $where = array(
                'ID' => $vendorData->ID,
                'user_id' => $vendorData->user_id
            );
            $update = $wpdb->update(
                $this->table_talent,
                $args,
                $where
            );
        } else {
            $args['updated'] = null;
            $insert = $wpdb->insert(
                $this->table_talent,
                $args
            );
            if( is_wp_error( $insert ) ) {
                var_dump( $wpdb->print_error() );die;
            }
        }
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
                <input 	type="hidden" class="woocommerce-Input input-text form-control form-control-lg" name="billing_cpf" id="billing_cpf" value="<?php echo esc_attr( $user->billing_cpf ); ?>" />
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

        $billing_phone = get_user_meta( get_current_user_id(), 'billing_phone', true );
        if( ! $billing_phone || is_null( $billing_phone ) || empty( $billing_phone ) || strlen( $billing_phone ) != 14 ) {
            $args = array(
                "type"        => "text",
                "required"    => true,
                "class"       => array( "form-row-wide", "input-cpf" ),
                "label"       => "Telefone",
                "label_class" => array( 'title-on-checkout-notes' ),
                "placeholder" => "Informe seu Telefone",
                "maxlength"   => 20,
            );
            woocommerce_form_field( 'billing_phone', $args, $checkout->get_value( 'billing_phone' ) );
        }

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
            ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <input type="text" placeholder="<?php _e( 'Celular', 'woocommerce' ); ?>" class="woocommerce-Input input-text form-control form-control-lg" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $user->billing_phone ); ?>" />
                <div class="error-message"></div>
            </p>
        <?php
        } else {
            if( ! empty( $user->billing_phone ) ) {
            ?>
                <input 	type="hidden" class="woocommerce-Input input-text form-control form-control-lg" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $user->billing_phone ); ?>" />
                <div class="error-message"></div>
            <?php
            } else {
                ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <input type="text" placeholder="<?php _e( 'Celular', 'woocommerce' ); ?>" class="woocommerce-Input input-text form-control form-control-lg" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $user->billing_phone ); ?>" />
                    <div class="error-message"></div>
                </p>
            <?php
            }
        }   
    }

    public function save_account_details( $user_id ) {
        update_user_meta( $user_id, 'billing_cpf', sanitize_text_field( $_POST['billing_cpf'] ) );
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }

    //apaga o registro do talento na tabela auxiliar ao apagar o talento
    //manda para a lixeira os produtos do talento apagado
    public function on_delete_talent( $user_id ){
        $user = get_user_by( 'id', $user_id );
        if( ( isset( $user->caps['user_talent'] ) && $user->caps['user_talent'] == true ) ) {
            global $wpdb;    
            $wpdb->delete( $this->table_talent, array( 'user_id' => $user_id ) );

            $sqlProducts = "SELECT * FROM {$wpdb->base_prefix}posts WHERE `post_author`=" . intval( $user_id );
            $resultProducts = $wpdb->get_results($sqlProducts);
            foreach( $resultProducts as $product ):
                wp_update_post( array( 'ID' => $product->ID, 'post_status' => 'trash' ));
            endforeach;    
        }         
    }
}
