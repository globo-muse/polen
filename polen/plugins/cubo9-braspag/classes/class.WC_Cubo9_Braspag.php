<?php

if( ! defined( 'ABSPATH' ) ) {
    die( 'Silence is golden.');
}

class WC_Cubo9_Braspag extends WC_Payment_Gateway {

    public function __construct() {
        global $WC_Cubo9_BraspagReduxSettings;

        if( intval( $WC_Cubo9_BraspagReduxSettings['enable_braspag_sandbox'] ) == intval( 1 ) ) {
            $this->ORG_ID                       = $WC_Cubo9_BraspagReduxSettings['sandbox_master_org_id'];
            $this->SESSION_ID                   = $WC_Cubo9_BraspagReduxSettings['sandbox_master_session_id_prefix'] . md5( ( is_user_logged_in() ) ? get_current_user_id() : time() . ':' . $_SERVER['REMOTE_ADDR'] . ':' . date("Y-m-d") );
        } else {
            $this->ORG_ID                       = $WC_Cubo9_BraspagReduxSettings['master_org_id'];
            $this->SESSION_ID                   = $WC_Cubo9_BraspagReduxSettings['master_session_id_prefix'] . md5( ( is_user_logged_in() ) ? get_current_user_id() : time() . ':' . $_SERVER['REMOTE_ADDR'] . ':' . date("Y-m-d") );
        }

        $this->id                 = 'wc-cubo9-braspag';
        $this->icon               = apply_filters( 'woocommerce_offline_icon', '' );
        $this->has_fields         = true;
        $this->method_title       = __( 'Braspag', 'cubo9' );
        $this->method_description = __( 'Integração com o gateway de pagamento Braspag do grupo Cielo.', 'cubo9' );

        $this->init_form_fields();
        $this->init_settings();

        $this->action         = __( 'Configurações', 'cubo9' );
        $this->title          = $WC_Cubo9_BraspagReduxSettings['braspag_checkout_title']; 
        $this->description    = $WC_Cubo9_BraspagReduxSettings['braspag_checkout_description'];
        $this->instructions   = $WC_Cubo9_BraspagReduxSettings['braspag_checkout_instructions'];

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
        // add_action( 'woocommerce_api_wc_gateway_' . $this->id, array( $this, 'response' ) );
        // add_action( 'woocommerce_api_callback', array( $this, 'response' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'header_scripts' ), 12, 0 );
        add_action( 'wp_footer', array( $this, 'footer_scripts' ), 12, 0 );
    }

    /**
     * Formulário de configurações do add-on.
     */
    public function init_form_fields() {
        $this->form_fields = apply_filters( 'wc_cubo9_braspag_form_fields', array(
            'enabled' => array(
                'title'   => __( 'Ativar/Desativar', 'cubo9' ),
                'type'    => 'checkbox',
                'label'   => __( 'Habilita o pagamento via Braspag', 'cubo9' ),
                'default' => 'yes'
            ),
        ) );
    }

    /**
     * Formulário de pagamento do checkout.
     */
    public function payment_fields() {
        if( file_exists( TEMPLATEPATH . '/braspag/form-checkout.php' ) ) {
            require_once TEMPLATEPATH . '/braspag/form-checkout.php';
        } else {
            require_once PLUGIN_CUBO9_BRASPAG_DIR . 'assets/php/form-checkout.php';
        }
    }

    /**
     * Resposta do gateway.
     */
    public function response() {
        global $woocommerce;
		@ob_clean();

        header( 'HTTP/1.1 200 OK' );
        
        var_dump( $_REQUEST );

        die;
    }

    /**
     * Validação de campos do checkout.
     */
    public function validate_fields() {
        if( isset( $_REQUEST['braspag_use_saved_card'] ) && ! empty( $_REQUEST['braspag_use_saved_card'] ) && (int) $_REQUEST['braspag_use_saved_card'] === (int) 1 ) {
            if( ! isset( $_REQUEST['brasapag_creditcard_saved'] ) || empty( $_REQUEST['brasapag_creditcard_saved'] ) ) {
                $error_message = 'Selecione o cartão de crédito desejado ou informe um novo.';
                wc_add_notice( $error_message, 'error' );
            }
        } else {
            /* if( ! isset( $_REQUEST['braspag_creditcardInstallments'] ) || empty( $_REQUEST['braspag_creditcardInstallments'] ) ) {
                $error_message = 'Informe a quantidade de parcelas desejada.';
                wc_add_notice( $error_message, 'error' );
            } */

            if( ! isset( $_REQUEST['braspag_creditcardNumber'] ) || empty( $_REQUEST['braspag_creditcardNumber'] ) ) {
                $error_message = 'Informe o número do cartão de crédito.';
                wc_add_notice( $error_message, 'error' );
            }

            if(  ! isset( $_REQUEST['braspag_creditcardName'] ) || empty( $_REQUEST['braspag_creditcardName'] ) || ! strlen( $_REQUEST['braspag_creditcardName'] ) > 5 ) {
                $error_message = 'Informe o nome do titular do cartão de crédito.';
                wc_add_notice( $error_message, 'error' );
            }

            /* if(  ! isset( $_REQUEST['braspag_creditcardCpf'] ) || ! $this->verifyCpf( $_REQUEST['braspag_creditcardCpf'] ) ) {
                $error_message = 'Informe corretamente o CPF do titular do cartão de crédito.';
                wc_add_notice( $error_message, 'error' );
            } */

            if(  ! isset( $_REQUEST['braspag_creditcardValidity'] ) || empty( $_REQUEST['braspag_creditcardValidity'] ) ) {
                $error_message = 'Informe a data de validade do cartão de crédito.';
                wc_add_notice( $error_message, 'error' );
            }

            if(  ! isset( $_REQUEST['braspag_creditcardCvv'] ) || empty( $_REQUEST['braspag_creditcardCvv'] ) ) {
                $error_message = 'Informe o código de segurança do cartão de crédito.';
                wc_add_notice( $error_message, 'error' );
            }
        }
    }

    /**
     * Processamento do pagamento e retorno da compra.
     */
    public function process_payment( $order_id ) {
        if( ! empty( $order_id ) ) {
            $order = wc_get_order( $order_id );

            $braspag = new Cubo9_Braspag( $order, $this->SESSION_ID );
            $response = $braspag->pay();

            if( $response['result'] == 'success' ) {
                return $response;
            } else {
                wc_add_notice( $response['message'], 'error' );
                return;
            }
        } else {
            $message = "Ocorreu um erro interno. Tente novamente mais tarde. (C9-WCGW-000)";
            wc_add_notice( $message, 'error' );
            return;
        }
    }

    /**
     * Texto exibido na página de obrigada pela compra.
     */
    public function thankyou_page() {
        if ( $this->instructions ) {
            echo wpautop( wptexturize( $this->instructions ) );
        }
    }

    /**
     * Scripts do cabeçalho do checkout
     */
    public function header_scripts() {
        if( is_checkout() ) {
            $url = 'https://h.online-metrix.net/fp/tags.js?org_id=' . $this->ORG_ID . '&session_id=' . $this->SESSION_ID;
            wp_enqueue_script( 'online-metrix', $url , array('jquery'), '', false );
        }
    }

    /**
     * Scripts do cabeçalho do rodapé do checkout
     */
    public function footer_scripts() {
        if( is_account_page() ) {
            wp_enqueue_style( 'card', PLUGIN_CUBO9_BRASPAG_URL . 'assets/css/braspag.css' );
        }
        
        if( is_account_page() || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
            wp_enqueue_script( 'inputmask', PLUGIN_CUBO9_BRASPAG_URL . 'assets/vendor/jquery.inputmask.min.js', array('jquery'), '', true );
            wp_enqueue_script( 'braspag-my-account', PLUGIN_CUBO9_BRASPAG_URL . 'assets/scripts/braspag-my-account.js', array('jquery'), '', true );
            wp_localize_script( 'braspag-my-account', 'braspag', array(
                // payment-options
                'rootUrl'      => get_bloginfo('url'),
                'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
                'myAccountPaymentOptionUrl' => wc_get_account_endpoint_url( 'payment-options' ),
            ) );
        }

        if( is_checkout() ) {
            wp_enqueue_style( 'card', PLUGIN_CUBO9_BRASPAG_URL . 'assets/css/braspag.css' );
            wp_enqueue_script( 'inputmask', PLUGIN_CUBO9_BRASPAG_URL . 'assets/vendor/jquery.inputmask.min.js', array('jquery'), '', true );
            wp_enqueue_script( 'braspag', PLUGIN_CUBO9_BRASPAG_URL . 'assets/scripts/braspag.js', array('jquery'), '', true );
            
            $WC_Cubo9_Braspag_Helper = new WC_Cubo9_Braspag_Helper();
            $active_brands = $WC_Cubo9_Braspag_Helper->credit_card_brands();
            $brands = array();
            if( count( $active_brands ) > 0 ) {
                foreach( $active_brands as $k => $brand ) {
                    $brands[] = $brand->slug;
                }
            }
            wp_localize_script( 'braspag', 'braspag', array(
                'brands' => $brands
            ) );

            $scriptUrl = 'https://h.online-metrix.net/fp/tags.js?org_id=' . $this->ORG_ID . '&session_id=' . $this->SESSION_ID;
            $html = "\n\n<noscript>\n";
            $html .= "<iframe style=\"width: 100px; height: 100px; border: 0; position: absolute; top: -10000px;\" src=\"" . $scriptUrl . "\"></iframe>";
            $html .= "\n</noscript>\n\n";
            echo $html;
        }
    }

    /**
     * Validação de CPF
     */
    public function verifyCpf( $cpf ) {
        if( empty( $cpf ) ) {
            return false;
        }

        $cpf = str_pad( preg_replace( "/[^0-9]/", "", $cpf ), 11, '0', STR_PAD_LEFT );
        
        if( strlen( $cpf ) != 11 ) {
            return false;
        } else {
            if ( $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999' ) {
                return false;
            } else {
                for ($t = 9; $t < 11; $t++) {
                    for ($d = 0, $c = 0; $c < $t; $c++) { 
                        $d += $cpf[$c] * (($t + 1) - $c);
                    }

                    $d = ((10 * $d) % 11) % 10;
                    if ($cpf[$c] != $d) {
                        return false;
                    }
                }
                return true;
            }
        }
    }
}