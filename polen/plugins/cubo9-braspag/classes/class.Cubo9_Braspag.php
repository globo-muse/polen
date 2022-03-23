<?php

use Polen\Includes\Polen_Update_Fields;

class Cubo9_Braspag {

    /**
     * Dados de acesso PRODUÇÃO
     */
	const CIELO_COMMERCE_API           = "https://api.cieloecommerce.cielo.com.br/";
	const CIELO_COMMERCE_API_QUERY     = "https://apiquery.cieloecommerce.cielo.com.br/";
	const BRASPAG_SPLIT_API            = "https://split.braspag.com.br/";
	const BRASPAG_OAUTH2               = "https://auth.braspag.com.br/";
    const BRASPAG_CARTAO_PROTEGIDO_API = "https://cartaoprotegidoapi.braspag.com.br/";

    /**
     * Dados de acesso SANDBOX
     */
	const SANDBOX_CIELO_COMMERCE_API           = "https://apisandbox.cieloecommerce.cielo.com.br/";
	const SANDBOX_CIELO_COMMERCE_API_QUERY     = "https://apiquerysandbox.cieloecommerce.cielo.com.br/";
	const SANDBOX_BRASPAG_SPLIT_API            = "https://splitsandbox.braspag.com.br/";
    const SANDBOX_BRASPAG_OAUTH2               = "https://authsandbox.braspag.com.br/";
    const SANDBOX_BRASPAG_CARTAO_PROTEGIDO_API = "https://cartaoprotegidoapisandbox.braspag.com.br/";

    /**
     * Variáveis
     */
    private $cart_items              = array();
    private $merchant_defined_fields = array();
    
    public function __construct( $order, $session_id ) {
        global $WC_Cubo9_BraspagReduxSettings;

        $this->braspag_settings = $WC_Cubo9_BraspagReduxSettings;
        $this->softdescriptor   = substr( $WC_Cubo9_BraspagReduxSettings['braspag_softdescriptor'], 0, 13 );

        if( intval( $WC_Cubo9_BraspagReduxSettings['enable_braspag_sandbox'] ) == intval( 1 ) ) {
            $this->MERCHANT_ID                      = $WC_Cubo9_BraspagReduxSettings['sandbox_master_subordinate_merchant_id'];
            $this->CLIENT_SECRET                    = $WC_Cubo9_BraspagReduxSettings['sandbox_master_client_secret'];
            $this->MERCHANT_KEY                     = $WC_Cubo9_BraspagReduxSettings['sandbox_master_merchant_key'];
            $this->SESSION_ID_PREFIX                = $WC_Cubo9_BraspagReduxSettings['sandbox_master_session_id_prefix'];
            $this->URL_CIELO_COMMERCE_API           = self::SANDBOX_CIELO_COMMERCE_API;
            $this->URL_CIELO_COMMERCE_API_QUERY     = self::SANDBOX_CIELO_COMMERCE_API_QUERY;
            $this->URL_BRASPAG_SPLIT_API            = self::SANDBOX_BRASPAG_SPLIT_API;
            $this->URL_BRASPAG_OAUTH2               = self::SANDBOX_BRASPAG_OAUTH2;
            $this->URL_BRASPAG_CARTAO_PROTEGIDO_API = self::SANDBOX_BRASPAG_CARTAO_PROTEGIDO_API;
            $this->SANDBOX_NAME_SUFIX               = ' ACCEPT';
        } else {
            $this->MERCHANT_ID                      = $WC_Cubo9_BraspagReduxSettings['master_subordinate_merchant_id'];
            $this->CLIENT_SECRET                    = $WC_Cubo9_BraspagReduxSettings['master_client_secret'];
            $this->MERCHANT_KEY                     = $WC_Cubo9_BraspagReduxSettings['master_merchant_key'];
            $this->SESSION_ID_PREFIX                = $WC_Cubo9_BraspagReduxSettings['master_session_id_prefix'];
            $this->URL_CIELO_COMMERCE_API           = self::CIELO_COMMERCE_API;
            $this->URL_CIELO_COMMERCE_API_QUERY     = self::CIELO_COMMERCE_API_QUERY;
            $this->URL_BRASPAG_SPLIT_API            = self::BRASPAG_SPLIT_API;
            $this->URL_BRASPAG_OAUTH2               = self::BRASPAG_OAUTH2;
            $this->URL_BRASPAG_CARTAO_PROTEGIDO_API = self::BRASPAG_CARTAO_PROTEGIDO_API;
            $this->SANDBOX_NAME_SUFIX               = '';
        }
        
        if( $order instanceof WC_Order ) {
            $this->SESSION_ID           = $session_id;
            $this->BROWSER_FINGER_PRINT = str_replace( $this->SESSION_ID_PREFIX, '', $session_id );
            $this->order                = $order;
            $this->order_id             = $order->get_id();
            $this->set_items();
        }
    }

    /**
     * Autenticação Braspag
     */
    private function auth() {
        $base64_encode = base64_encode( $this->MERCHANT_ID . ':' . $this->CLIENT_SECRET );
		$headers = array(
			'Authorization' => 'Basic ' . $base64_encode,
			'Content-Type' => 'application/x-www-form-urlencoded'
        );
        
        $response = wp_remote_post( 
            $this->URL_BRASPAG_OAUTH2 . '/oauth2/token', 
            array( 
                'headers' => $headers,
                'method' => 'POST',
                'timeout' => 1000,
                'body' => array( 
                    'grant_type' => 'client_credentials'
                ),
            )
        );

        if( ! is_wp_error( $response ) ) {
            if(  wp_remote_retrieve_response_code( $response ) === 200 ) {
                $response_body = json_decode( $response["body"] );
                $this->BRASPAG_TOKEN = $response_body->access_token;
                $response = array(
                    'status'  => 'success',
                    'message' => 'Autenticado com sucesso.',
                );
            } else {
                $response = array(
                    'status'  => 'error',
                    'message' => 'Autenticação usuário falhou. (C9-BP001)',
                );
            }
        } else {
            $response = array(
                'status'  => 'error',
                'message' => $response->get_error_message() . ' (C9-BP002)',
            );
        }

        return $response;
    }

    public function get_splitpayments( $installment, $brand_slug ) {
        $splitpayments = array();
        $seller_info   = array();

        $WC_Cubo9_Braspag_Helper = new WC_Cubo9_Braspag_Helper();
        $installment_rate = $WC_Cubo9_Braspag_Helper->get_installment_rates_by_brand( $installment, $brand_slug );

        $fee_gateway                = (int) $this->braspag_settings['fee_gateway'];
        $fee_antifraude             = (int) $this->braspag_settings['fee_antifraude'];
        $pass_rates                 = (bool) $this->braspag_settings['pass_rates'];
        $pass_card_rates            = (bool) $this->braspag_settings['pass_card_rates'];
        $default_mdr                = (float) $this->braspag_settings['default_mdr'];
        $default_fee                = (int) $this->braspag_settings['default_fee'];

        $mdr = (float) $default_mdr;

        if( $pass_card_rates ) {
            $mdr = (float) $mdr + $installment_rate;
        }

        if( $pass_rates ) {
            $fee = (int) $fee_gateway + $fee_antifraude + $default_fee;
        } else {
            $fee = $default_fee;
            if( $default_mdr == (float) 0 ) {
                $fee = (int) $fee_gateway + $fee_antifraude + $default_fee;
            }
        }

        if( is_array( $this->order_sellers  ) && count( $this->order_sellers  ) > 0 ) {
            foreach( $this->order_sellers as $k => $seller_data ) {
                $Polen_Update_Fields = new Polen_Update_Fields();
                $row_seller = $Polen_Update_Fields->get_vendor_data( $seller_data[ 'id' ] );
                
                if( $row_seller->mdr && ! is_null( $row_seller->mdr ) && $row_seller->mdr != '' ) {
                    $mdr = (float) $row_seller->mdr;
                    if( $pass_card_rates ) {
                        $mdr = (float) $mdr + $installment_rate;
                    }
                }

                if( $row_seller->fee && ! is_null( $row_seller->fee ) && $row_seller->fee != '' ) {
                    if( $pass_rates ) {
                        $fee = (int) $fee_gateway + $fee_antifraude + $row_seller->fee;
                    } else {
                        $fee = (int) $row_seller->fee;
                        if( $default_mdr == (float) 0 ) {
                            $fee = (int) $fee_gateway + $fee_antifraude + $row_seller->fee;
                        }
                    }
                }

                $splitpayments[] = array(
                    'subordinatemerchantid' => $seller_data['subordinatemerchantid'],
                    'amount'                => $seller_data['amount'],
                    'fares'                 => array(
                        'mdr' => $mdr,
                        'fee' => $fee,
                    ),
                );
            }
        }

        $taxes_and_shipping_split = $this->set_taxes_and_shipping_split();
        if( $taxes_and_shipping_split && is_array( $taxes_and_shipping_split ) && isset( $taxes_and_shipping_split['subordinatemerchantid'] ) ) {
            $splitpayments[] = $taxes_and_shipping_split;
        }

        return $splitpayments;
    }

    public function pay() {
        $auth = $this->auth();
        if( $auth['status'] == 'success' && ! is_null( $this->BRASPAG_TOKEN ) ) {
            /**
             * Dados da compra
             */
            $order       = $this->order;
            $order_id    = $order->get_id();
            $amount      = number_format( $order->get_total(), 2, '', '' );
            $user        = $order->get_user();
            $billing_cpf = ( isset( $_REQUEST['billing_cpf'] ) ) ? $_REQUEST['billing_cpf'] : '';
            $billing_cpf = ( isset( $user->ID ) ) ? get_user_meta( $user->ID, 'billing_cpf', true ) : $billing_cpf;
            $billing_phone = ( isset( $_REQUEST['billing_phone'] ) ) ? $_REQUEST['billing_phone'] : '';
            $billing_phone = ( isset( $user->ID ) ) ? get_user_meta( $user->ID, 'billing_phone', true ) : $billing_phone;

            /**
             * Metadados da compra
             */
            $order_data                             = $order->get_data();
            
            $order_data['billing']['number']        = get_post_meta( $order_id, '_billing_number', true );
            $order_data['billing']['complement']    = get_post_meta( $order_id, '_billing_complement', true );
            $order_data['billing']['neighborhood']  = get_post_meta( $order_id, '_billing_neighborhood', true );
            $order_data['billing']['phone']         = $billing_phone;
            $order_data['billing']['cpf']           = $billing_cpf;

            if( isset( $order_data['shipping'] ) ) {
                $order_data['shipping']['number']       = get_post_meta( $order_id, '_shipping_number', true );
                $order_data['shipping']['complement']   = get_post_meta( $order_id, '_shipping_complement', true );
                $order_data['shipping']['neighborhood'] = get_post_meta( $order_id, '_shipping_neighborhood', true );
                $order_data['shipping']['phone']        = $billing_phone;
            }

            /**
             * Dados do usuário comprador.
             */
            if( isset( $user->display_name ) ) {
                $user_display_name = substr( $user->display_name . $this->SANDBOX_NAME_SUFIX, 0, 61 );
            } else {
                $user_display_name = '' . $this->SANDBOX_NAME_SUFIX;
            }

            $document_type     = 'CPF';
            $document_number   = substr( preg_replace( '/[^0-9]/', '', $billing_cpf ), 0, 18 );
            if( isset( $order_data['billing']['phone'] ) && ! empty( $order_data['billing']['phone'] ) ) {
                $user_phone        = '55' . substr( preg_replace( '/[^0-9]/', '', $order_data['billing']['phone'] ), 0, 15 );
                $user_mobile_phone = '55' . substr( preg_replace( '/[^0-9]/', '', $order_data['billing']['phone'] ), 0, 15 );
            } else {
                $user_phone        = '';
                $user_mobile_phone = '';
            }

            /**
             * E-mail do comprador
             */
            if( isset( $order_data['billing']['email'] ) && ! empty( $order_data['billing']['email'] ) ) {
                $billing_email = $order_data['billing']['email'];
            } elseif ( is_user_logged_in() ) {
                $current_user = wp_get_current_user();
                $billing_email = $current_user->user_email;
            } else {
                $items = WC()->cart->get_cart();
                $key = array_key_first( $items );
                $billing_email = $items[ $key ][ 'email_to_video' ];
            }

            /**
             * Dados do cartão de Crédito ou Débito.
             */
            if( isset( $_REQUEST['braspag_use_saved_card'] ) && ! empty( $_REQUEST['braspag_use_saved_card'] ) && (int) $_REQUEST['braspag_use_saved_card'] === (int) 1 ) {
                $brasapag_creditcard_saved = substr( $_REQUEST['brasapag_creditcard_saved'], 38, ( strlen( $_REQUEST['brasapag_creditcard_saved'] ) - 38 ) );
                global $wpdb;
                $sql_card = "SELECT `meta_value` FROM `" . $wpdb->usermeta . "` WHERE `umeta_id`=" . $brasapag_creditcard_saved . " AND `user_id`=" . get_current_user_id() . " AND `meta_key`='braspag_card_saved_data'";
                $res_card = $wpdb->get_results( $sql_card );
                var_dump( $res_card );
                if( $res_card && ! is_null( $res_card ) && is_array( $res_card ) && ! empty( $res_card ) ) {
                    $card_info = unserialize( $res_card[0]->meta_value );
                    $CreditCardData = array(
                        'CardToken'    => $card_info['token'],
                        'Brand'        => $card_info['brand'],
                    );
                    $creditcard_number = $card_info['card_number'];
                    $creditcard_holder = $card_info['holder'];
                    $creditcard_brand  = $card_info['brand'];
                }
            } else {
                $creditcard_cvv        = substr( preg_replace( '/[^0-9]/', '', $_REQUEST['braspag_creditcardCvv'] ), 0, 4 );
                $creditcard_brand      = substr( $_REQUEST['braspag_creditcardBrand'], 0, 10 );
                $creditcard_expiration = substr( str_replace( ' ', '', trim( $_REQUEST['braspag_creditcardValidity'] ) ), 0, 7 );
                $creditcard_number     = substr( preg_replace( '/[^0-9]/', '', $_REQUEST['braspag_creditcardNumber'] ), 0 , 19 );
                $creditcard_holder     = substr( trim( strtoupper( $_REQUEST['braspag_creditcardName'] ) ), 0, 50 );
                if( isset ( $_REQUEST['braspag_creditcardCpf'] ) ) {
                    $creditcard_holder_cpf = preg_replace( '/[^0-9]/', '', $_REQUEST['braspag_creditcardCpf'] );
                } else {
                    $creditcard_holder_cpf = '';
                }
                $creditcard_save       = ( isset( $_REQUEST['braspag_saveCreditCard'] ) && (bool) $_REQUEST['braspag_saveCreditCard'] === true ) ? true : false;

                $CreditCardData = array(
                    'CardNumber'     => $creditcard_number,     // Número do cartão de crédito (apenas dígitos)
                    'Holder'         => $creditcard_holder . $this->SANDBOX_NAME_SUFIX, // Nome impresso no cartão de crédito
                    'ExpirationDate' => $creditcard_expiration, // Data de expiração no format MM/YYYY
                    'SecurityCode'   => $creditcard_cvv,        // Código de segurança do cartão
                    'Brand'          => $creditcard_brand,      // Bandeira do Cartão ( Visa / Master / Amex / Elo / Aura / JCB / Diners / Discover )
                    'SaveCard'       => $creditcard_save,       // Se deve salvar o cartão de crédito (True/False)
                );
            }

            /**
             * Dados da forma de pagamento
             */
            $installments = ( isset( $_REQUEST['braspag_creditcardInstallments'] ) && intval( $_REQUEST['braspag_creditcardInstallments'] ) == (int) $_REQUEST['braspag_creditcardInstallments'] && (int) $_REQUEST['braspag_creditcardInstallments'] >= 1 && (int) $_REQUEST['braspag_creditcardInstallments'] <= 12 ) ? (int) $_REQUEST['braspag_creditcardInstallments'] : 1;

            /**
             * Seta o MerchantDefinedFields
             */
            $merchant_defined_fields = array();
            $card_prefix             = substr( $creditcard_number, 0, 6 );
            $card_sufix              = substr( $creditcard_number, -4 );

            $merchant_defined_fields['installments']       = $installments;
            $merchant_defined_fields['credit_card_prefix'] = $card_prefix;
            $merchant_defined_fields['credit_card_sufix']  = $card_sufix;
            $merchant_defined_fields['credit_card_name']   = $creditcard_holder;

            $this->set_merchant_defined_fields( $merchant_defined_fields );

            $request = array();
            $request['MerchantOrderId'] = $order_id;
            $request['Customer']        = array(
                'Name'            => $user_display_name,
                'IdentityType'    => $document_type,
                'Identity'        => $document_number,
                'Email'           => substr( $billing_email, 0, 60 ),
                'Phone'           => $user_phone,
                'Mobile'          => $user_mobile_phone,
                'DeliveryAddress' => array(
                    'ZipCode'    => $order_data['billing']['postcode'],
                    'Street'     => $order_data['billing']['address_1'],
                    'Number'     => $order_data['billing']['number'],
                    'Complement' => $order_data['billing']['complement'],
                    'District'   => $order_data['billing']['neighborhood'],
                    'City'       => $order_data['billing']['city'],
                    'State'      => $order_data['billing']['state'],
                    'Country'    => 'BR',
                ),
                'BillingAddress'  => array(
                    'ZipCode'    => $order_data['billing']['postcode'],
                    'Street'     => $order_data['billing']['address_1'],
                    'Number'     => $order_data['billing']['number'],
                    'Complement' => $order_data['billing']['complement'],
                    'District'   => $order_data['billing']['neighborhood'],
                    'City'       => $order_data['billing']['city'],
                    'State'      => $order_data['billing']['state'],
                    'Country'    => 'BR',
                ),
            );

            $request['Payment']         = array(
                'Type'             => 'SplittedCreditCard',  // SplittedCreditCard ou SplittedDebitCard.
                'Amount'           => $amount,               // Valor total da transação em centavos (Ex: R$ 1.901,20, informar 190120).
                'Capture'          => true,                  // SE True, a transação é efetivada, se False, ela é apenas autorizada.
                'Installments'     => $installments,         // Quantidade de parcelas (De 1 a 12);
                'SoftDescriptor'   => $this->softdescriptor, // Descrição que aparecerá na Fatura
                'CreditCard'       => $CreditCardData,
                'FraudAnalysis'    => array(
                    'Provider'         => 'Cybersource', // Possíveis valores: Cybersource (Máximo de 12 caracteres).
                    'TotalOrderAmount' => $amount,       // Valor total do pedido em centavos, podendo ser diferente do valor da transação	Ex: Valor do pedido sem a taxa de entrega.
                    'Sequence'         => 'analysefirst',
                    'SequenceCriteria' => 'onsuccess',
                    'CaptureOnLowRisk' => false, 
                    'VoidOnHighRisk'   => false,
                    'Browser'          => array(
                        'IpAddress'          => $_SERVER['REMOTE_ADDR'],     // Endereço IP do Comprador
                        'BrowserFingerPrint' => $this->BROWSER_FINGER_PRINT, // Impressão digital do dispositivo do usuário, deve ser o mesmo SESSION ID do JavaScript incluído na página.
                    ),
                    'Shipping'         => array(
                        'Addressee' => $user_display_name, // Nome e sobrenome do usuário comprador
                    ),
                    'Cart'             => array(
                        'isgift'          => ( get_post_meta( $order_id, '_c9_is_gift', true ) == strval( '1' ) ) ? true : false,
                        'returnsaccepted' => true,
                        'items'           => $this->get_cart_items(),
                    ),
                    'MerchantDefinedFields' => $this->get_merchant_defined_fields(),
                ),
                'splitpayments'        => $this->get_splitpayments( $installments, $creditcard_brand ),
            );

            $request_array = $request;
            $request_array['Payment']['CreditCard'] = array();
            $request_json = json_encode( $request_array );

            add_post_meta( $order_id, 'braspag_request_array', $request_array );
            add_post_meta( $order_id, 'braspag_request_json', $request_json );

            $headers = array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->BRASPAG_TOKEN,
            );

            $body = json_encode( $request );

            $response = wp_remote_post( $this->URL_CIELO_COMMERCE_API . '1/sales', array(
                    'method'  => 'POST',
                    'timeout' => 1000,
                    'headers' => $headers,
                    'body'    => $body
                )
            );

            add_post_meta( $order_id, 'braspag_response', $response );

            if( is_null( $response ) || is_wp_error( $response ) ) {
                // Provavel falta de comunicação entre os servidores (Timeout?)
                $message = 'Não foi possível processar a requisição, tente novamente dentro de alguns instantes. (C9-WCGW-002)';
                $return = array(
                    'result' => 'error',
                    'message' => $message,
                );
            } else if( ( ! is_null( $response ) && ! is_wp_error( $response ) ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
                $response_code = wp_remote_retrieve_response_code( $response );
                $response_body = wp_remote_retrieve_body( $response );
                $response_body_json = json_decode( $response_body );

                add_post_meta( $this->order_id, 'braspag_response_code', $response_code );
                add_post_meta( $this->order_id, 'braspag_response_body', $response_body );
                add_post_meta( $this->order_id, 'braspag_response_body_json', $response_body_json );

                if( isset( $response_body_json->Payment->Status ) ) {
                    add_post_meta( $this->order_id, 'braspag_payment_status', $response_body_json->Payment->Status );
                }

                if( isset( $response_body_json->Payment->FraudAnalysis->Status ) ) {
                    add_post_meta( $this->order_id, 'braspag_payment_fraud_analysis_status', $response_body_json->Payment->FraudAnalysis->Status );
                }

                if( isset( $response_body_json->Payment->FraudAnalysis->StatusDescription ) ) {
                    add_post_meta( $this->order_id, 'braspag_payment_fraud_analysis_status_description', $response_body_json->Payment->FraudAnalysis->StatusDescription );
                }

                if( 
                    intval( strval( $response[ 'response' ][ 'code' ] ) ) === intval( '201' ) 
                    && strtoupper( $response[ 'response' ][ 'message' ] ) == strtoupper( "Created" ) 
                    && intval( $response_body_json->Payment->Status ) == intval( '2' ) 
                    && (int) $response_body_json->Payment->FraudAnalysis->Status === (int) 1 
                ) {
                    /**
                     * Verifica se deve salvar o cartão de crédito e valida se o cartão utilizado já foi salvo anteriormente.
                     */
                    $save_card = $response_body_json->Payment->CreditCard->SaveCard;
					if( $save_card ) {
                        $card_number     = $response_body_json->Payment->CreditCard->CardNumber;
                        $holder          = $response_body_json->Payment->CreditCard->Holder;
                        $expiration_date = $response_body_json->Payment->CreditCard->ExpirationDate;
                        $card_token      = $response_body_json->Payment->CreditCard->CardToken;
                        $brand           = $response_body_json->Payment->CreditCard->Brand;
                        $card_prefix     = substr( $card_number, 0, 6 );
                        $card_sufix      = substr( $card_number, -4 );
                        $card_label      = strtoupper( $brand ) . ' - ' . $card_sufix;

                        $card_saved_data = array(
                            'card_number'     => $card_number,
                            'prefix'          => $card_prefix,
                            'sufix'           => $card_sufix,
                            'token'           => $card_token,
                            'brand'           => $brand,
                            'expiration_date' => $expiration_date,
                            'holder'          => $holder,
                            'card_label'      => $card_label,
                        );

                        add_user_meta( get_current_user_id(), 'braspag_card_saved_data', $card_saved_data );
                    }
                    
                    // Dados do pagamento
                    $WC_Cubo9_Braspag_Helper = new WC_Cubo9_Braspag_Helper;
                    $installment_value = $WC_Cubo9_Braspag_Helper->calculate_installments( $order->get_total() );
                    $installment_rate = $WC_Cubo9_Braspag_Helper->get_installment_rates_by_brand( $installments, $creditcard_brand );

                    $order->update_meta_data( '_transaction_id', $response_body_json->Payment->PaymentId );
                    
                    $order->update_meta_data( 'braspag_order_amount', $amount );
                    $order->update_meta_data( 'braspag_order_transaction_id', $response_body_json->Payment->PaymentId );
                    $order->update_meta_data( 'braspag_order_nsu', $response_body_json->Payment->ProofOfSale );
					$order->update_meta_data( 'braspag_order_tid', $response_body_json->Payment->Tid );
                    $order->update_meta_data( 'braspag_order_authorizationCode', $response_body_json->Payment->AuthorizationCode );
                    $order->update_meta_data( 'braspag_order_links', $response_body_json->Payment->Links );
                    $order->update_meta_data( 'braspag_order_brand', $creditcard_brand );
                    $order->update_meta_data( 'braspag_order_installments', $installments );
                    $order->update_meta_data( 'braspag_order_installment_value', $installment_value[ $installments ] );
                    $order->update_meta_data( 'braspag_order_brand_mdr', $installment_rate );

                    $order->update_meta_data( 'creditcard_brand', $creditcard_brand );
                    $order->update_meta_data( 'creditcard_brand_mdr', $installment_rate );
                    $order->update_meta_data( 'creditcard_installments', $installments );
                    $order->update_meta_data( 'creditcard_installment_value', str_replace( ',', '.', str_replace( '.', '', $installment_value[ $installments ] ) ) );

                    // Adiciona a nota com o dia/hora em que foi autorizado na 
                    $date_time_format = get_option('date_format') . ' \à\s ' . get_option('time_format');
                    $order->add_order_note( __('Pagamento aprovado pela <strong>Braspag</strong> em ' . date_i18n( $date_time_format , strtotime( $response_body_json->Payment->CapturedDate ) ) . "." , 'cubonove') );

                    // Marca como Aguardando ('on-hold')
                    $order->update_status( 'payment-approved' );
                    $email = WC()->mailer()->get_emails()['Polen_WC_Payment_Approved'];
                    $email->trigger( $order_id );

                    // Atualiza o estoque
                    wc_reduce_stock_levels( $order_id );

                    // Salva as alterações
                    $order->save();

                    // Remove do carrinho
                    global $woocommerce;
                    $woocommerce->cart->empty_cart();

                    $return = array(
                        'result'   => 'success',
                        'message'  => 'Sucesso!',
                        'redirect' => $order->get_checkout_order_received_url(),
                    );
                } elseif( isset( $response_body_json->Payment->FraudAnalysis->Status ) && (int) $response_body_json->Payment->FraudAnalysis->Status === (int) 3 && strtoupper( $response_body_json->Payment->FraudAnalysis->StatusDescription ) == strtoupper('REVIEW') ) {
                    $order->update_status( 'payment-in-revision' );
                    $email = WC()->mailer()->get_emails()['Polen_WC_Payment_In_Revision'];
                    $email->trigger( $order_id );
                    return array(
                        'type' => 'review',
                        'message' => 'Seu pedido está aguardando confirmação de pagamento.',
                        'body' => $response["body"],
                        'response' => $response["response"]
                    );
                } else {
                    if( is_array( $response_body_json ) && isset( $response_body_json[0]->Code ) && ! isset( $response_body_json[1]->Code ) ) {
                        $message = $response_body_json[0]->Message . " (C9-GW-" . $response_body_json[0]->Code . " #" . $this->order_id . ")";
                    } elseif( is_array( $response_body_json ) && isset( $response_body_json[0]->Code ) && isset( $response_body_json[1]->Code ) ) {
                        $message = $response_body_json[1]->Message . " (C9-GW-" . $response_body_json[1]->Code . " #" . $this->order_id . ")";
                    } elseif( is_object( $response_body_json ) && isset( $response_body_json->Payment->FraudAnalysis->Status ) ) {
                        $message = $response_body_json->Payment->FraudAnalysis->StatusDescription . " (C9-GW-" . $response_body_json->Payment->FraudAnalysis->Status . " #" . $order_id . ")";
                    } else {
                        $message = 'Não foi possível processar o seu pagamento. (C9-WCGW-007)';
                    }
                    
                    $return = array(
                        'result' => 'error',
                        'message' => $message,
                    );
                }
            } else {
                $response_code = wp_remote_retrieve_response_code( $response );
                $response_body = wp_remote_retrieve_body( $response );
                $response_body_json = json_decode( $response_body );

                add_post_meta( $this->order_id, 'braspag_response_code', $response_code );
                add_post_meta( $this->order_id, 'braspag_response_body', $response_body );
                add_post_meta( $this->order_id, 'braspag_response_body_json', $response_body_json );

                switch( $response_code ) {
                    case "400":
                        // Erro na Request processada no servidor Braspag
                        $message = 'Ocorreu um erro ao tentar processar sua requisição, ferifique os dados e tente novamente dentro de alguns instantes. (C9-WCGW-003)';
                        break;
                    case "404":
                        // Endpoint não encontrado no servidor Braspag
                        $message = 'Ocorreu erro de comunicação com o servidor. Tente novamente mais tarde. (C9-WCGW-004)';
                        break;
                    case "500":
                        // Erro interno no servidor Braspag
                        $message = 'Ocorreu erro de comunicação com o servidor. Tente novamente mais tarde. (C9-WCGW-005)';
                        break;
                    default:
                        $message = 'Ocorreu erro de comunicação com o servidor. Tente novamente mais tarde. (C9-WCGW-006)';
                        break;
                }

                $return = array(
                    'result' => 'error',
                    'message' => $message,
                );
            }
        } else {
            $message = 'Ocorreu erro interno. Tente novamente dentro de alguns instantes. (C9-WCGW-001)';
            $return = array(
                'result' => 'error',
                'message' => $message,
            );
        }

        return $return;
    }

    /**
     * Itens da compra
     */
    private function set_items() {
        $items = $this->order->get_items();
		if( count( $items ) > 0 ) {
            $sellers     = array();
            $seller_info = array();

			foreach( $items as $k => $item ) {
				$product_id    = $item['product_id'];
				$product       = wc_get_product( $product_id );
				$quantity      = $item['quantity'];
				$unit_price    = number_format( ( $item->get_subtotal() / $quantity ), 2, '.', '' );
				$product_price = str_replace( '.', '', $unit_price );
				$sku           = get_post_meta( $product_id, '_sku', true );
				$this->add_cart_item( $product->get_title(), $quantity, $product_price, $sku );

                $seller_id = get_post_field( 'post_author', $product_id );

                $_is_charity                      = get_post_meta( $product_id, '_is_charity', true );
                $_charity_subordinate_merchant_id = get_post_meta( $product_id, '_charity_subordinate_merchant_id', true );

                if( ! in_array( $seller_id, $sellers ) ) {
                    $Polen_Update_Fields = new Polen_Update_Fields();
                    $row_seller = $Polen_Update_Fields->get_vendor_data( $seller_id );
                    $sellers[] = $seller_id;
                    
                    if( $_is_charity == 'yes' && $_charity_subordinate_merchant_id && ! is_null( $_charity_subordinate_merchant_id ) && ! empty( $_charity_subordinate_merchant_id ) ) {
                        $subordinate_merchant_id = $_charity_subordinate_merchant_id;
                    } else {
                        $subordinate_merchant_id = $row_seller->subordinate_merchant_id;
                    }

                    $seller_info[ $seller_id ] = array(
                        'id'                    => $seller_id,
                        'amount'                => number_format( $item->get_subtotal(), 2, '', '' ),
                        'subordinatemerchantid' => $subordinate_merchant_id,
                    );
                } else {
                    $seller_info[ $seller_id ]['amount'] = ( $seller_info[ $seller_id ]['amount'] +  number_format( $item->get_subtotal(), 2, '', '' ) );
                }
			}

            $this->order_sellers = $seller_info;
		}
    }

    public function add_cart_item( $product_name, $quantity, $unitprice, $sku ) {
        $item = array();

		if( ! is_null( $product_name ) && ! empty( $product_name ) ) {
			$item['name'] = $product_name;
		}

		if( ! is_null( $quantity ) && ! empty( $quantity ) && intval( $quantity ) == intval( strval( $quantity ) ) && (int) $quantity > 0 ) {
			$item['quantity'] = $quantity;
		}

		if( ! is_null( $unitprice ) && ! empty( $unitprice ) && (int) $unitprice > (int) 0 ) {
			$item['unitprice'] = $unitprice;
		}

		if( ! is_null( $sku ) && ! empty( $sku ) ) {
			$item['sku'] = $sku;
		}

		if( count( $item ) > 0 ) {
			$this->cart_items[] = $item;
		}
    }

    public function get_cart_items() {
        return $this->cart_items;
    }

    /**
     * Campos para ajudar o antifraudes (MerchantDefinedFields)
     */
    public function add_merchant_defined_fields( $key, $value ) {
		if( ! empty( trim( $key ) ) && ! empty( trim( $value ) ) ) {
			$this->merchant_defined_fields[] = array( 
				'Id' => $key,
				'Value' => $value,
			);
		}
    }
    
    public function get_merchant_defined_fields() {
        return $this->merchant_defined_fields;
    }

    public function set_merchant_defined_fields( array $args ) {
        $user_id            = ( is_user_logged_in() ) ? get_current_user_id() : null;
        $order_id           = $this->order->get_id();
        $installments       = $args['installments'];
        $credit_card_prefix = $args['credit_card_prefix'];
        $credit_card_sufix  = $args['credit_card_sufix'];
        $credit_card_name   = $args['credit_card_name'];

        /**
		 * Campos para envio de análise antifraudes.
		 */
        if( ! is_null( $user_id ) ) {
            $current_user = get_user_by( 'id', $user_id );
            $wordpress_timezone = new DateTimeZone( get_option('timezone_string') );
            if( isset( $current_user->ID ) ) {
                $user_registered = new DateTime( $current_user->user_registered, $wordpress_timezone );
                $current_date = new DateTime( "now", $wordpress_timezone );
                $date_interval = $user_registered->diff( $current_date );

                /**
                 * Login do usuário
                 */ 
                if( isset( $current_user->user_login ) && ! is_null( $current_user->user_login ) && ! empty( $current_user->user_login ) ) {
                    $this->add_merchant_defined_fields( '1', $current_user->user_login );
                }

                /**
                 * Quanto tempo em dias o usuário é cadastrado na plataforma
                 */ 
                if( (int) $date_interval->days > (int) 0 ) {
                    $this->add_merchant_defined_fields( '2', $date_interval->days );
                }
            }
        }

		/**
		 * Quantidade de parcelas do pedido
		 */ 
		if( (int) $installments > (int) 0 ) {
			$this->add_merchant_defined_fields( '3', $installments );
		}

		/**
		 * Origem do pagamento: Web ou Movel
		 */ 
		$this->add_merchant_defined_fields( '4', 'Web' );

		/**
		 * Merchant ID do(s) Vendedor(es)
		 */ 
		/* $sellers = $this->get_sellers( $order_id );
		$sellers_array = array();
		foreach( $sellers as $k => $seller ) {
			$sellers_array[] = trim( $seller['subordinatemerchantid'] );
		}
		if( count( $sellers_array ) > 0 ) {
			$sellers_string = implode( '/', $sellers_array );
			$this->add_merchant_defined_fields( '7', $sellers_string );
		} */
		
		/**
		 * Identifica se cliente irá retirar o produto na loja
		 */ 
		$this->add_merchant_defined_fields( '9', 'NAO' );

		/**
		 * 4 últimos dígitos do cartão de crédito
		 */
		if( isset( $credit_card_sufix ) && ! is_null( $credit_card_sufix ) && ! empty( $credit_card_sufix ) && (int) strlen( $credit_card_sufix ) == (int) 4 ) {
			$this->add_merchant_defined_fields( '23', $credit_card_sufix );
		}

		/**
		 * Quantidade de dias desde a primeira compra realizada pelo cliente.
		 */
		$order_args = array(
			'limit'       => '1',
			'status'      => 'completed',
			'customer_id' => $user_id,
			'orderby'     => 'date',
    		'order'       => 'ASC',
		);
		$user_orders = wc_get_orders( $order_args );
		if( ! is_null( $user_orders ) && is_array( $user_orders ) && ! empty( $user_orders ) && isset( $user_orders[0]->order_date ) ) {
			$last_order = $user_orders[0]->get_date_created();
			$last_order = new DateTime( $last_order, $wordpress_timezone );
			$current_date = new DateTime( "now", $wordpress_timezone );
			$date_interval = $last_order->diff( $current_date );
			if( (int) $date_interval->days > (int) 0 ) {
				$this->add_merchant_defined_fields( '24', $date_interval->days );
			}
		}

		/**
		 * 6 primeiros dígitos do cartão de crédito
		 */
		if( isset( $credit_card_prefix ) && ! is_null( $credit_card_prefix ) && ! empty( $credit_card_prefix ) && (int) strlen( $credit_card_prefix ) == (int) 6 ) {
			$this->add_merchant_defined_fields( '26', $credit_card_prefix );
		}
		
		/**
		 * Tipo do endereço do usuário: R => Residencial, C => Comercial
		 */
		// $orderUserAddress = $this->getOrderUserAddress( $order_id );
		$addressType = 'R';
		/* if( ! is_null( $orderUserAddress ) && ! empty( $orderUserAddress ) && is_array( $orderUserAddress ) && strtoupper( substr( $orderUserAddress['address_name'], 0, 1 ) ) != strtoupper( 'C' ) ) {
			$addressType = 'C';
		} */
		$this->add_merchant_defined_fields( '27', $addressType );

		/**
		 * Identifica se foi utilizado cartão presente (GiftCard) na compra como forma de pagamento (SIM OU NAO).
		 * Atualmente não temos essa funcionalidade, então por padrão será NAO.
		 */
		$this->add_merchant_defined_fields( '36', 'NAO' );

		/**
		 * Meio de envio do pedido.
		 * Possíveis valores: Sedex, Sedex 10, 1 Dia, 2 Dias, Motoboy, Mesmo Dia
		 */
		$this->add_merchant_defined_fields( '37', 'Motoboy' );

		/**
		 * Identifica se o pedido é um presente e insere o comentário.
		 */
		$_c9_is_gift = get_post_meta( $order_id, '_c9_is_gift', true );
		if( $_c9_is_gift && ! is_null( $_c9_is_gift ) && ! empty( $_c9_is_gift ) && strval( $_c9_is_gift ) == strval( 'yes' ) ) {
			$_c9_is_gift_to = get_post_meta( $order_id, '_c9_is_gift_to', true );
			if( $_c9_is_gift_to && ! is_null( $_c9_is_gift_to ) && ! empty( $_c9_is_gift_to ) ) {
				$this->add_merchant_defined_fields( '40', $_c9_is_gift_to );
			}
		}

		/**
		 * Tipo de documento do comprador (CPF OU CNPJ)
		 */
		$this->add_merchant_defined_fields( '41', 'CPF' );

		/**
		 * Quantidade de compras que o usuário já efetuou na plataforma.
		 */
        $_order_count = wc_get_customer_order_count( $user_id );
		if( $_order_count && ! is_null( $_order_count ) && ! empty( $_order_count ) && (int) $_order_count > (int) 0 ) {
			$this->add_merchant_defined_fields( '44', $_order_count );
		}

		/**
		 * Nome impresso no cartão de crédito.
		 */
		if( isset( $credit_card_name ) && ! is_null( $credit_card_name ) && ! empty( $credit_card_name ) ) {
			$this->add_merchant_defined_fields( '46', $credit_card_name );
		}

		/**
		 * Quantidade de meios de pagamentos utilizados para efetuar a compra. 
		 * Por padrão só permitimos um meio de pagamento, ou seja nossos usuários não podem pagar a compra
		 * utilizando dois ou três cartões de crédito.
		 */
		$this->add_merchant_defined_fields( '48', '1' );

		/**
		 * Categorias do produto.
		 * A lista deverá estar atualizada com a da Braspag. Como por enquanto não temos essa lista,
		 * estou enviando o valor "Outras Categorias Não Especificadas" que é da Braspag.
		 */
		$this->add_merchant_defined_fields( '52', 'Outras Categorias Não Especificadas' );

		/**
		 * Quantidade em dias desde a data da última alteração no cadastro do usuário.
		 */
        /* $last_update = get_user_meta( $user_id, 'last_update', true );
		if( $last_update && ! is_null( $last_update ) && ! empty( $last_update ) ) {
            $last_update = date( "Y-m-d H:i:s", $last_update );
			$user_updated = new DateTime( $last_update, $wordpress_timezone );
			$current_date = new DateTime( "now", $wordpress_timezone );
            $dateInterval = $user_updated->diff( $current_date );
			if( (int) $dateInterval->days > (int) 0 ) {
				$this->add_merchant_defined_fields( '60', $dateInterval->days );
			}
		} else {
			$user_registered = new DateTime( $current_user->user_registered, $wordpress_timezone );
			$current_date = new DateTime( "now", $wordpress_timezone );
            $dateInterval = $user_registered->diff( $current_date );
			if( (int) $dateInterval->days > (int) 0 ) {
				$this->add_merchant_defined_fields( '60', $dateInterval->days );
			}
		} */

		/**
		 * Ramo de atividade do Marketplace
		 */
		$this->add_merchant_defined_fields( '83', 'Varejo' );

		/**
		 * Tipo de integração com a Braspag
		 */
		$this->add_merchant_defined_fields( '84', 'Propria' );
    }

    /**
     * Seta o subordinado que receberá as taxas e o frete.
     */
    public function set_taxes_and_shipping_split() {
        $amount               = 0;
        $total_fee            = 0;
        $order_total_shipping = 0;

        if( ! is_null( $this->order->get_items( 'fee' ) ) && ! empty( $this->order->get_items( 'fee' ) ) ) {
            $total_fees = $this->order->get_items( 'fee' );
            if( ! empty( $total_fees ) && is_array( $total_fees ) && count( $total_fees ) > 0 ) {
                $total_fee = 0;
                foreach( $total_fees as $k => $fee_data ) {
                    $total_fee = ( $total_fee + $fee_data->get_total() );
                }
                $total_fee = str_replace('.', '', number_format( $total_fee, 2, '.', '' ) );
            }
        }
        
        if( ! is_null( $this->order->get_shipping_total() ) && ! empty( $this->order->get_shipping_total() ) ) {
            $total_shipping = $this->order->get_shipping_total();
            if ( ! is_null( $total_shipping ) && ! empty( $total_shipping ) && $total_shipping > 0 ) {
                $order_total_shipping = str_replace('.', '', number_format( $total_shipping, 2, '.', '' ) );
            }
        }

        $amount = ( $total_fee + $order_total_shipping );

        if( $amount > 0 ) {
            $WC_Cubo9_Braspag_Helper = new WC_Cubo9_Braspag_Helper();
            $installment_rate        = $WC_Cubo9_Braspag_Helper->get_installment_rates_by_brand( $installment, $brand_slug );
            $fee_gateway             = (int) $this->braspag_settings['fee_gateway'];
            $fee_antifraude          = (int) $this->braspag_settings['fee_antifraude'];
            $pass_rates              = (bool) $this->braspag_settings['pass_rates'];
            $default_mdr             = (float) $this->braspag_settings['default_mdr'];
            $default_fee             = (int) $this->braspag_settings['default_fee'];

            if( intval( $this->braspag_settings['enable_braspag_sandbox'] ) == intval( 1 ) ) {
                $subordinate_merchant_id = $this->braspag_settings['sandbox_master_subordinate_merchant_id'];
            } else {
                $subordinate_merchant_id = $this->braspag_settings['master_subordinate_merchant_id'];
            }

            $mdr = (float) $default_mdr + $installment_rate;

            if( $pass_rates ) {
                $fee = (int) $fee_gateway + $fee_antifraude + $default_fee;
            } else {
                $fee = $default_fee;
                if( $default_mdr == (float) 0 ) {
                    $fee = (int) $fee_gateway + $fee_antifraude + $default_fee;
                }
            }

            return array(
                'subordinatemerchantid' => $subordinate_merchant_id,
                'amount'                => $amount,
                'fares'                 => array(
                    'mdr' => $mdr,
                    'fee' => $fee,
                ),
            );
        }
    }

    public function get_last_order_meta( $order_id, $meta_key ) {
        global $wpdb;
        $sql = "SELECT `meta_value` FROM `" . $wpdb->postmeta . "` WHERE `meta_key`='" . $meta_key . "' AND `post_id`=" . $order_id . ' ORDER BY `meta_id` DESC LIMIT 0, 1';
        $res = $wpdb->get_results( $sql );
        if( $res && ! is_wp_error( $res ) ) {
            return maybe_unserialize( $res[0]->meta_value );
        }
    }

    public function void( $amount = false, $VoidSplitPayments = array() ) {
        $auth = $this->auth();
        if( $auth['status'] == 'success' && ! is_null( $this->BRASPAG_TOKEN ) ) {
            $order = $this->order;
            if( $order && ! is_null( $order ) && is_a( $order, 'WC_Order' ) ) {
                $order_id = $order->get_id();
                if( ! $amount ) {
                    $braspag_order_transaction_id = $this->get_last_order_meta( $order_id, 'braspag_order_transaction_id' );
                    if( $braspag_order_transaction_id && ! is_null( $braspag_order_transaction_id ) && ! empty( $braspag_order_transaction_id ) ) {
                        $url = $this->URL_CIELO_COMMERCE_API . '1/sales/' . $braspag_order_transaction_id . '/void';
                        $headers = array(
                            'Content-Type'  => 'application/json',
                            'Authorization' => 'Bearer ' . $this->BRASPAG_TOKEN,
                            'Content-Length' => 0,
                        );
            
                        $body = array();
            
                        $response = wp_remote_post( $url, array(
                                'method'  => 'PUT',
                                'timeout' => 1000,
                                'headers' => $headers,
                            )
                        );

                        if( $response['response']['code'] === 200 && strtoupper( $response['response']['message'] ) === strtoupper( 'OK' ) ) {
                            $str_response_body = $response['body'];
                            update_post_meta( $order_id, 'braspag_void_response', $str_response_body );
                            $body = json_decode( $str_response_body );

                            $return = array(
                                'Status'                => $body->Status,
                                'ReasonCode'            => $body->ReasonCode,
                                'ProviderReturnCode'    => $body->ProviderReturnCode,
                                'ProviderReturnMessage' => $body->ProviderReturnMessage,
                                'ReturnCode'            => $body->ReturnCode,
                                'ReturnMessage'         => $body->ReturnMessage,
                            );
                            return $return;
                        } elseif( $response['response']['code'] === 400 && strtoupper( $response['response']['message'] ) === strtoupper( 'Bad Request' ) ) {
                            $str_response_body = $response['body'];
                            $body = json_decode( $str_response_body );
                            if( is_array( $body ) ) {
                                $return = array(
                                    'Code'    => $body[0]->Code,
                                    'Message' => $body[0]->Message,
                                );
                            } else {
                                $return = array(
                                    'Code'    => $body->Code,
                                    'Message' => $body->Message,
                                );
                            }
                            return $return;
                        }
                    }
                } elseif( $amount && (int) $amount > 0 && $VoidSplitPayments && is_array( $VoidSplitPayments ) ) {

                }
            }
        }

        return false;
    }

    public function luhn( $credit_card_number ) {
        $card_number = preg_replace( '/[^0-9]/', '', $credit_card_number );
        
        if( strlen( $card_number ) < 13 || strlen( $card_number ) > 19 ) {
            return false;
        }

        $sum = '';

        foreach( array_reverse( str_split( $card_number ) ) as $k => $v ) { 
            $sum .= ( $k % 2 ) ? $v * 2 : $v; 
        }

        return array_sum( str_split( $sum ) ) % 10 == 0;
    }

    private function get_card_flag( $credit_card_number ){
        $card_number = preg_replace( '/[^0-9]/', '', $credit_card_number );
        
        if( strlen( $card_number ) < 13 || strlen( $card_number ) > 19 ) {
            return false;
        }

        $array_elo_bin = array(
            "401178", "401179", "431274", "438935", "451416", "457393", "457631", "457632", "498405", "498410", "498411", "498412", "498418", 
            "498419", "498420", "498421", "498422", "498427", "498428", "498429", "498432", "498433", "498472", "498473", "498487", "498493", 
            "498494", "498497", "498498", "504175", "506699", "506700", "506701", "506702", "506703", "506704", "506705", "506706", "506707", 
            "506708", "506709", "506710", "506711", "506712", "506713", "506714", "506715", "506716", "506717", "506718", "506719", "506720", 
            "506721", "506722", "506723", "506724", "506725", "506726", "506727", "506728", "506729", "506730", "506731", "506732", "506733", 
            "506734", "506735", "506736", "506737", "506738", "506739", "506740", "506741", "506742", "506743", "506744", "506745", "506746", 
            "506747", "506748", "506749", "506750", "506751", "506752", "506753", "506754", "506755", "506756", "506757", "506758", "506759", 
            "506760", "506761", "506762", "506763", "506764", "506765", "506766", "506767", "506768", "506769", "506770", "506771", "506772", 
            "506773", "506774", "506775", "506776", "506777", "506778", "509000", "509001", "509002", "509003", "509004", "509005", "509006", 
            "509007", "509008", "509009", "509010", "509011", "509012", "509013", "509014", "509015", "509016", "509017", "509018", "509019", 
            "509020", "509021", "509022", "509023", "509024", "509025", "509026", "509027", "509028", "509029", "509030", "509031", "509032", 
            "509033", "509034", "509035", "509036", "509037", "509038", "509039", "509040", "509041", "509042", "509043", "509044", "509045", 
            "509046", "509047", "509048", "509049", "509050", "509051", "509052", "509053", "509054", "509055", "509056", "509057", "509058", 
            "509059", "509060", "509061", "509062", "509063", "509064", "509065", "509066", "509067", "509068", "509069", "509070", "509071", 
            "509072", "509073", "509074", "509075", "509076", "509077", "509078", "509079", "509080", "509081", "509082", "509083", "509084", 
            "509085", "509086", "509087", "509088", "509089", "509090", "509091", "509092", "509093", "509094", "509095", "509096", "509097", 
            "509098", "509099", "509100", "509101", "509102", "509103", "509104", "509105", "509106", "509107", "509108", "509109", "509110", 
            "509111", "509112", "509113", "509114", "509115", "509116", "509117", "509118", "509119", "509120", "509121", "509122", "509123", 
            "509124", "509125", "509126", "509127", "509128", "509129", "509130", "509131", "509132", "509133", "509134", "509135", "509136", 
            "509137", "509138", "509139", "509140", "509141", "509142", "509143", "509144", "509145", "509146", "509147", "509148", "509149", 
            "509150", "509151", "509152", "509153", "509154", "509155", "509156", "509157", "509158", "509159", "509160", "509161", "509162", 
            "509163", "509164", "509165", "509166", "509167", "509168", "509169", "509170", "509171", "509172", "509173", "509174", "509175", 
            "509176", "509177", "509178", "509179", "509180", "509181", "509182", "509183", "509184", "509185", "509186", "509187", "509188", 
            "509189", "509190", "509191", "509192", "509193", "509194", "509195", "509196", "509197", "509198", "509199", "509200", "509201", 
            "509202", "509203", "509204", "509205", "509206", "509207", "509208", "509209", "509210", "509211", "509212", "509213", "509214", 
            "509215", "509216", "509217", "509218", "509219", "509220", "509221", "509222", "509223", "509224", "509225", "509226", "509227", 
            "509228", "509229", "509230", "509231", "509232", "509233", "509234", "509235", "509236", "509237", "509238", "509239", "509240", 
            "509241", "509242", "509243", "509244", "509245", "509246", "509247", "509248", "509249", "509250", "509251", "509252", "509253", 
            "509254", "509255", "509256", "509257", "509258", "509259", "509260", "509261", "509262", "509263", "509264", "509265", "509266", 
            "509267", "509268", "509269", "509270", "509271", "509272", "509273", "509274", "509275", "509276", "509277", "509278", "509279", 
            "509280", "509281", "509282", "509283", "509284", "509285", "509286", "509287", "509288", "509289", "509290", "509291", "509292", 
            "509293", "509294", "509295", "509296", "509297", "509298", "509299", "509300", "509301", "509302", "509303", "509304", "509305", 
            "509306", "509307", "509308", "509309", "509310", "509311", "509312", "509313", "509314", "509315", "509316", "509317", "509318", 
            "509319", "509320", "509321", "509322", "509323", "509324", "509325", "509326", "509327", "509328", "509329", "509330", "509331", 
            "509332", "509333", "509334", "509335", "509336", "509337", "509338", "509339", "509340", "509341", "509342", "509343", "509344", 
            "509345", "509346", "509347", "509348", "509349", "509350", "509351", "509352", "509353", "509354", "509355", "509356", "509357", 
            "509358", "509359", "509360", "509361", "509362", "509363", "509364", "509365", "509366", "509367", "509368", "509369", "509370", 
            "509371", "509372", "509373", "509374", "509375", "509376", "509377", "509378", "509379", "509380", "509381", "509382", "509383", 
            "509384", "509385", "509386", "509387", "509388", "509389", "509390", "509391", "509392", "509393", "509394", "509395", "509396", 
            "509397", "509398", "509399", "509400", "509401", "509402", "509403", "509404", "509405", "509406", "509407", "509408", "509409", 
            "509410", "509411", "509412", "509413", "509414", "509415", "509416", "509417", "509418", "509419", "509420", "509421", "509422", 
            "509423", "509424", "509425", "509426", "509427", "509428", "509429", "509430", "509431", "509432", "509433", "509434", "509435", 
            "509436", "509437", "509438", "509439", "509440", "509441", "509442", "509443", "509444", "509445", "509446", "509447", "509448", 
            "509449", "509450", "509451", "509452", "509453", "509454", "509455", "509456", "509457", "509458", "509459", "509460", "509461", 
            "509462", "509463", "509464", "509465", "509466", "509467", "509468", "509469", "509470", "509471", "509472", "509473", "509474", 
            "509475", "509476", "509477", "509478", "509479", "509480", "509481", "509482", "509483", "509484", "509485", "509486", "509487", 
            "509488", "509489", "509490", "509491", "509492", "509493", "509494", "509495", "509496", "509497", "509498", "509499", "509500", 
            "509501", "509502", "509503", "509504", "509505", "509506", "509507", "509508", "509509", "509510", "509511", "509512", "509513", 
            "509514", "509515", "509516", "509517", "509518", "509519", "509520", "509521", "509522", "509523", "509524", "509525", "509526", 
            "509527", "509528", "509529", "509530", "509531", "509532", "509533", "509534", "509535", "509536", "509537", "509538", "509539", 
            "509540", "509541", "509542", "509543", "509544", "509545", "509546", "509547", "509548", "509549", "509550", "509551", "509552", 
            "509553", "509554", "509555", "509556", "509557", "509558", "509559", "509560", "509561", "509562", "509563", "509564", "509565", 
            "509566", "509567", "509568", "509569", "509570", "509571", "509572", "509573", "509574", "509575", "509576", "509577", "509578", 
            "509579", "509580", "509581", "509582", "509583", "509584", "509585", "509586", "509587", "509588", "509589", "509590", "509591", 
            "509592", "509593", "509594", "509595", "509596", "509597", "509598", "509599", "509600", "509601", "509602", "509603", "509604", 
            "509605", "509606", "509607", "509608", "509609", "509610", "509611", "509612", "509613", "509614", "509615", "509616", "509617", 
            "509618", "509619", "509620", "509621", "509622", "509623", "509624", "509625", "509626", "509627", "509628", "509629", "509630", 
            "509631", "509632", "509633", "509634", "509635", "509636", "509637", "509638", "509639", "509640", "509641", "509642", "509643", 
            "509644", "509645", "509646", "509647", "509648", "509649", "509650", "509651", "509652", "509653", "509654", "509655", "509656", 
            "509657", "509658", "509659", "509660", "509661", "509662", "509663", "509664", "509665", "509666", "509667", "509668", "509669", 
            "509670", "509671", "509672", "509673", "509674", "509675", "509676", "509677", "509678", "509679", "509680", "509681", "509682", 
            "509683", "509684", "509685", "509686", "509687", "509688", "509689", "509690", "509691", "509692", "509693", "509694", "509695", 
            "509696", "509697", "509698", "509699", "509700", "509701", "509702", "509703", "509704", "509705", "509706", "509707", "509708", 
            "509709", "509710", "509711", "509712", "509713", "509714", "509715", "509716", "509717", "509718", "509719", "509720", "509721", 
            "509722", "509723", "509724", "509725", "509726", "509727", "509728", "509729", "509730", "509731", "509732", "509733", "509734", 
            "509735", "509736", "509737", "509738", "509739", "509740", "509741", "509742", "509743", "509744", "509745", "509746", "509747", 
            "509748", "509749", "509750", "509751", "509752", "509753", "509754", "509755", "509756", "509757", "509758", "509759", "509760", 
            "509761", "509762", "509763", "509764", "509765", "509766", "509767", "509768", "509769", "509770", "509771", "509772", "509773", 
            "509774", "509775", "509776", "509777", "509778", "509779", "509780", "509781", "509782", "509783", "509784", "509785", "509786", 
            "509787", "509788", "509789", "509790", "509791", "509792", "509793", "509794", "509795", "509796", "509797", "509798", "509799", 
            "509800", "509801", "509802", "509803", "509804", "509805", "509806", "509807", "509808", "509809", "509810", "509811", "509812", 
            "509813", "509814", "509815", "509816", "509817", "509818", "509819", "509820", "509821", "509822", "509823", "509824", "509825", 
            "509826", "509827", "509828", "509829", "509830", "509831", "509832", "509833", "509834", "509835", "509836", "509837", "509838", 
            "509839", "509840", "509841", "509842", "509843", "509844", "509845", "509846", "509847", "509848", "509849", "509850", "509851", 
            "509852", "509853", "509854", "509855", "509856", "509857", "509858", "509859", "509860", "509861", "509862", "509863", "509864", 
            "509865", "509866", "509867", "509868", "509869", "509870", "509871", "509872", "509873", "509874", "509875", "509876", "509877", 
            "509878", "509879", "509880", "509881", "509882", "509883", "509884", "509885", "509886", "509887", "509888", "509889", "509890", 
            "509891", "509892", "509893", "509894", "509895", "509896", "509897", "509898", "509899", "509900", "509901", "509902", "509903", 
            "509904", "509905", "509906", "509907", "509908", "509909", "509910", "509911", "509912", "509913", "509914", "509915", "509916", 
            "509917", "509918", "509919", "509920", "509921", "509922", "509923", "509924", "509925", "509926", "509927", "509928", "509929", 
            "509930", "509931", "509932", "509933", "509934", "509935", "509936", "509937", "509938", "509939", "509940", "509941", "509942", 
            "509943", "509944", "509945", "509946", "509947", "509948", "509949", "509950", "509951", "509952", "509953", "509954", "509955", 
            "509956", "509957", "509958", "509959", "509960", "509961", "509962", "509963", "509964", "509965", "509966", "509967", "509968", 
            "509969", "509970", "509971", "509972", "509973", "509974", "509975", "509976", "509977", "509978", "509979", "509980", "509981", 
            "509982", "509983", "509984", "509985", "509986", "509987", "509988", "509989", "509990", "509991", "509992", "509993", "509994", 
            "509995", "509996", "509997", "509998", "509999", "627780", "636297", "636368", "650031", "650032", "650033", "650035", "650036", 
            "650037", "650038", "650039", "650040", "650041", "650042", "650043", "650044", "650045", "650046", "650047", "650048", "650049", 
            "650050", "650051", "650405", "650406", "650407", "650408", "650409", "650410", "650411", "650412", "650413", "650414", "650415", 
            "650416", "650417", "650418", "650419", "650420", "650421", "650422", "650423", "650424", "650425", "650426", "650427", "650428", 
            "650429", "650430", "650431", "650432", "650433", "650434", "650435", "650436", "650437", "650438", "650439", "650485", "650486", 
            "650487", "650488", "650489", "650490", "650491", "650492", "650493", "650494", "650495", "650496", "650497", "650498", "650499", 
            "650500", "650501", "650502", "650503", "650504", "650505", "650506", "650507", "650508", "650509", "650510", "650511", "650512", 
            "650513", "650514", "650515", "650516", "650517", "650518", "650519", "650520", "650521", "650522", "650523", "650524", "650525", 
            "650526", "650527", "650528", "650529", "650530", "650531", "650532", "650533", "650534", "650535", "650536", "650537", "650538", 
            "650541", "650542", "650543", "650544", "650545", "650546", "650547", "650548", "650549", "650550", "650551", "650552", "650553", 
            "650554", "650555", "650556", "650557", "650558", "650559", "650560", "650561", "650562", "650563", "650564", "650565", "650566", 
            "650567", "650568", "650569", "650570", "650571", "650572", "650573", "650574", "650575", "650576", "650577", "650578", "650579", 
            "650580", "650581", "650582", "650583", "650584", "650585", "650586", "650587", "650588", "650589", "650590", "650591", "650592", 
            "650593", "650594", "650595", "650596", "650597", "650598", "650700", "650701", "650702", "650703", "650704", "650705", "650706", 
            "650707", "650708", "650709", "650710", "650711", "650712", "650713", "650714", "650715", "650716", "650717", "650718", "650720", 
            "650721", "650722", "650723", "650724", "650725", "650726", "650727", "650901", "650902", "650903", "650904", "650905", "650906", 
            "650907", "650908", "650909", "650910", "650911", "650912", "650913", "650914", "650915", "650916", "650917", "650918", "650919", 
            "650920", "651652", "651653", "651654", "651655", "651656", "651657", "651658", "651659", "651660", "651661", "651662", "651663", 
            "651664", "651665", "651666", "651667", "651668", "651669", "651670", "651671", "651672", "651673", "651674", "651675", "651676", 
            "651677", "651678", "651679", "655000", "655001", "655002", "655003", "655004", "655005", "655006", "655007", "655008", "655009", 
            "655010", "655011", "655012", "655013", "655014", "655015", "655016", "655017", "655018", "655019", "655021", "655022", "655023", 
            "655024", "655025", "655026", "655027", "655028", "655029", "655030", "655031", "655032", "655033", "655034", "655035", "655036", 
            "655037", "655038", "655039", "655040", "655041", "655042", "655043", "655044", "655045", "655046", "655047", "655048", "655049", 
            "655050", "655051", "655052", "655053", "655054", "655055", "655056", "655057", "655058"
        );

        $elo_bin = implode( "|", $array_elo_bin );
        
        $regex = array(
            "elo"           => "/^((" . $elo_bin . "[0-9]{10})|(36297[0-9]{11})|(5067|4576|4011[0-9]{12}))/",
            "discover"      => "/^((6011[0-9]{12})|(622[0-9]{13})|(64|65[0-9]{14}))/",
            "diners"        => "/^((301|305[0-9]{11,13})|(36|38[0-9]{12,14}))/",
            "amex"          => "/^((34|37[0-9]{13}))/",
            "hipercard"     => "/^((38|60[0-9]{11,14,17}))/",
            "aura"          => "/^((50[0-9]{14}))/",
            "jcb"           => "/^((35[0-9]{14}))/",
            "master"        => "/^((5[0-9]{15}))/",
            "visa"          => "/^((4[0-9]{12,15}))/"
        );

        foreach( $regex as $card_flag => $regex_expression ) {
            if( preg_match( $regex_expression, $card_number ) ) {
                return $card_flag;
            }
        }

        return false;
    }

    public function tokenize_card( $args ) {
        $brand   = $this->get_card_flag( $args['number'] );
        $request = array();

        $request['CustomerName']   = strval( trim( strtoupper( $args['holder'] ) ) );
        $request['CardNumber']     = strval( $args['number'] );
        $request['Holder']         = strval( strtoupper( $args['holder'] ) ) . $this->SANDBOX_NAME_SUFIX;
        $request['ExpirationDate'] = strval( $args['validity'] );
        $request['Brand']          = strval( $this->get_card_flag( $args['number'] ) );
        $auth = $this->auth();
        $url = $this->URL_CIELO_COMMERCE_API . '1/card/';

        $headers = array(
            'Content-Type'  => 'application/json',
            'MerchantId'    => $this->MERCHANT_ID,
            'MerchantKey'   => $this->MERCHANT_KEY,
        );

        $response = wp_remote_post( $url, array(
                'method'  => 'POST',
                'timeout' => 1000,
                'headers' => $headers,
                'body'    => wp_json_encode( $request ),
            )
        );

        if( is_null( $response ) || is_wp_error( $response ) ) {
            $message = 'Ocorreu um erro ao tentar salvar o seu cartão. Tente novamente em alguns instantes.';
            $return = array(
                'result' => 'error',
                'message' => $message,
            );
        } elseif( ( ! is_null( $response ) && ! is_wp_error( $response ) ) || wp_remote_retrieve_response_code( $response ) === 201 ) {
            if( isset( $response['body'] ) ) {
                $body = json_decode( $response['body'] );
                if( isset( $body->CardToken ) && isset( $body->Links->Href ) ) {
                    $card_saved_data = array (
                        'card_number'     => substr( $request['CardNumber'], 0, 6 ) . '******' . substr( $request['CardNumber'], -4 ),
                        'prefix'          => substr( $request['CardNumber'], 0, 6 ),
                        'sufix'           => substr( $request['CardNumber'], -4 ),
                        'token'           => $body->CardToken,
                        'brand'           => ucfirst( $request['Brand'] ),
                        'expiration_date' => $request['ExpirationDate'],
                        'holder'          => $request['Holder'],
                        'card_label'      => strtoupper( $request['Brand'] ) . ' - ' . 2931,
                    );

                    add_user_meta( get_current_user_id(), 'braspag_card_saved_data', $card_saved_data );
                    
                    $return = array(
                        'result' => 'success',
                        'message' => 'Cartão salvo com sucesso!',
                    );
                } else {
                    $return = array(
                        'result' => 'error',
                        'message' => 'Ocorreu um erro ao tentar salvar o seu cartão. Tente novamente mais tarde.',
                    );
                }
            } else {
                $return = array(
                    'result' => 'error',
                    'message' => 'Ocorreu um erro ao tentar salvar o seu cartão. Tente novamente mais tarde.',
                );
            }
        } else {
            $message = 'Ocorreu um erro ao tentar salvar o seu cartão. Tente novamente mais tarde.';
            $return = array(
                'result' => 'error',
                'message' => $message,
            );
        }

        return $return;
    }

    public function list_user_cards( $user_id = false ) {
        if( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if( $user_id && ! is_null( $user_id ) && ! empty( $user_id ) && $user_id > 0 ) {
            global $wpdb;
            $sql = "SELECT `umeta_id`, `meta_value` FROM `" . $wpdb->usermeta . "` WHERE `meta_key`='braspag_card_saved_data' AND `user_id`=" . $user_id;
            $res = $wpdb->get_results( $sql );
            if( $res && ! is_null( $res ) && ! is_wp_error( $res ) && is_array( $res ) && count( $res ) > 0 ) {
                $cards = array();
                foreach( $res as $k => $v ) {
                    $card_info = unserialize( $v->meta_value );
                    $cards[] = array(
                        'id'              => $v->umeta_id,
                        'brand'           => $card_info['brand'],
                        'prefix'          => $card_info['prefix'],
                        'sufix'           => $card_info['sufix'],
                        'card_label'      => $card_info['card_label'],
                        'expiration_date' => $card_info['expiration_date']
                    );
                }
                return $cards;
            }
        }
    }

    public function verify_card( $args ) {
        $brand   = $this->get_card_flag( $args['number'] );
        $request = array();
        
        if( $brand && ! is_null( $brand ) && ! empty( $brand ) ) {
            $request['Alias'] = ucfirst( strtolower( $brand ) ) . ' - ' . substr( $args['number'], -4 );
        }

        $request['Card']['Number']         = $args['number'];
        $request['Card']['Holder']         = $args['holder'];
        $request['Card']['ExpirationDate'] = $args['validity'];
        $request['Card']['SecurityCode']   = $args['cvv'];

        return $request;
    }
}