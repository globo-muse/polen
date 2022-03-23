<?php

namespace Polen\Api;

use Exception;
use Polen\Includes\Polen_Campaign;
use WC_Order;

class Api_Gateway_Tuna
{
    private string $partner_key;
    private string $partner_account;
    private string $operation_mode;

    public function __construct()
    {
        $this->credentials();
    }

    /**
     * Processar pagamento no TUNA
     *
     * @param $order_id
     * @param $current_user
     * @param array $data
     */
    public function process_payment($order_id, $current_user, array $data)
    {
        $url = $this->get_endpoint_url('Payment/Init');

        $session_id = $this->get_session_id($current_user['user_object']->data);

        $customer_order = new WC_Order($order_id);

        $name = $customer_order->get_billing_first_name();
        $product = wc_get_product($data['product_id']);
        $product_campaign_slug = Polen_Campaign::get_product_campaign_slug( $product );

        $purchased_items = [
            [
                "Amount" => floatval($product->get_sale_price()),
                "ProductDescription" => $product->get_name(),
                "ItemQuantity" => 1,
                "CategoryName" => $product_campaign_slug,
                "AntiFraud" => [
                    "Ean" => $product->get_sku()
                ]
            ]
        ];

        $payment_method_type = $this->method_payment_type($data['method_payment']);

        $document_type = 'CPF';
        $document_value = sanitize_text_field($data['cpf']);

        $customer_order->calculate_totals();
        $card_info = null;
        if ($data['method_payment'] == 'cc') {
            $token = $this->generate_token_card($session_id, $data);
            $tuna_expiration_date = $this->separate_month_year($data['tuna_expiration_date']);
            $card_info = [
                "Token" => $token,
                "TokenProvider" => 'Tuna',
                "CardHolderName" => sanitize_text_field($data["tuna_card_holder_name"]),
                "BrandName" => sanitize_text_field($data["tuna_card_brand"]),
                "ExpirationMonth" => (int) $tuna_expiration_date[0],
                "ExpirationYear" => (int) $tuna_expiration_date[1],
                "TokenSingleUse" => 1,
                "SaveCard" => false,
                "BillingInfo" => [
                    "Document" => $document_value,
                    "DocumentType" => $document_type,
                    "Address" => [
                        "Street" => '',
                        "Number" => '',
                        "Complement" => '',
                        "Neighborhood" => '',
                        "City" => '',
                        "State" => '',
                        "Country" => '',
                        "PostalCode" => '',
                        "Phone" => $customer_order->get_billing_phone(),
                    ]
                ]
            ];
        }

        $body = [
            'AppToken' => $this->partner_key,
            'Account' => $this->partner_account,
            'PartnerUniqueID' => $order_id,
            'TokenSession' => $session_id,
            'Customer' => [
                'Email' => $customer_order->get_billing_email(),
                'Name' => $name,
                'ID' => $current_user['user_object']->data->ID,
                'Document' => $document_value,
                'DocumentType' => $document_type
            ],
            "AntiFraud" => [
                "DeliveryAddressee" => $name
            ],
            "DeliveryAddress" => [
                "Street" =>  $customer_order->get_shipping_address_1(),
                "Number" => '',
                "Complement" => '',
                "Neighborhood" => '',
                "City" => $customer_order->get_shipping_city(),
                "State" => 'CE',
                "Country" => 'BR',
                "PostalCode" => $customer_order->get_shipping_postcode(),
                "Phone" => '',
            ],
            "FrontData" => [
                "SessionID" => wp_get_session_token(),
                "Origin" => 'WEBSITE',
                "IpAddress" => $_SERVER['REMOTE_ADDR'],
                "CookiesAccepted" => true
            ],
            "ShippingItems" => [
                "Items" => [
                    [
                        "Type" => $customer_order->get_shipping_method(),
                        "Amount" => floatval($customer_order->get_shipping_total()),
                        "Code" => '',
                    ]
                ]
            ],
            "PaymentItems" => [
                "Items" => $purchased_items,
            ],
            "PaymentData" => [
                'Countrycode' => 'BR',
                "SalesChannel" => 'ECOMMERCE',
                "PaymentMethods" => [
                    [
                        "PaymentMethodType" => $payment_method_type,
                        "Amount" => floatval($customer_order->get_total()),
                        "Installments" => 1,
                        "CardInfo" => $card_info,
                    ]
                ]
            ]
        ];

        $api_response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Accept' => '*/*',
            ),
            'body' => json_encode($body),
            'timeout' => 120,
        ));

        if (is_wp_error($api_response)) {
            throw new Exception(__('No momento, estamos enfrentando problemas ao tentar nos conectar a este portal de pagamento. Desculpe pela inconveniência.' . $api_response->get_error_message(), 'tuna-payment'));
        }

        if (empty($api_response['body'])) {
            throw new Exception(__('Requisição incorreta', 'tuna-payment'));
        }

        $response = json_decode($api_response['body']);

        $new_status = $this->get_status_response($response->status);
        if( "failed" === $new_status || 'cancelled' === $new_status ) {
            throw new Exception( 'Erro no pagamento, tente novamente', 422 );
        }

        if ($new_status === 'payment-approved') {
            wc_reduce_stock_levels($order_id);
        }

        $response_message = $this->get_response_message($new_status);
        $customer_order->update_status( $new_status );

        $response_payment = [
            'message' => $response_message['message'],
            'order_id' => $order_id,
            'new_account' => $current_user['new_account'],
            'method_payment' => 'Cartão de crédito',
            'order_status' => $response_message['status_code'],
            'order_code' => $customer_order->get_order_key()
        ];

        if ($data['method_payment'] == 'pix') {
            $response_payment['pix_code'] = $response->methods[0]->pixInfo->qrContent;
            $response_payment['pix_qrcode'] = $response->methods[0]->pixInfo->qrImage;
            $response_payment['method_payment'] = 'pix';
        }

        return $response_payment;
    }

    /**
     * Retornar valor do metodo de pagamento de acordo com o tuna
     *
     * @param string $type
     * @return string
     */
    public function method_payment_type($type = 'cc'): string
    {
        $methods = array(
            'cc' => '1',
            'pix' => 'D',
            'billet' => '3',

        );

        return $methods[$type];
    }

    /**
     * Gerar ID da sessão
     *
     * @param $current_user
     * @return string
     */
    public function get_session_id($current_user)
    {
        try {
            $url = $this->get_endpoint_url('Token/NewSession', true);

            $body = [
                "AppToken" => $this->partner_key,
                "Customer" => [
                    "Email" => $current_user->user_email,
                    "ID" => $current_user->ID,
                ]
            ];

            $api_response = wp_remote_post(
                $url,
                array(
                    'headers' => array(
                        'Content-Type'  => 'application/json'
                    ),
                    'body' => json_encode($body)
                )
            );

            if (is_wp_error($api_response)) {
                throw new Exception(__('Problemas com o processo de pagamento', 'tuna-payment'));
            }

            $response = json_decode($api_response['body']);

            return $response->sessionId;
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage(), 422);
            wp_die();
        }
    }

    /**
     * Gerar token com os dados do cartão
     *
     * @param string $session_id
     * @param array $card
     * @return string
     */
    public function generate_token_card(string $session_id, array $card)
    {
        try {
            $url = $this->get_endpoint_url('Token/Generate', true);

            $tuna_expiration_date = $this->separate_month_year($card['tuna_expiration_date']);

            $body = [
                "SessionId" => $session_id,
                "Card" => [
                    "CardNumber" => preg_replace("/[^0-9]/", '', $card['tuna_card_number']),
                    "CardHolderName" => $card['tuna_card_holder_name'],
                    "ExpirationMonth" => (int) $tuna_expiration_date[0],
                    "ExpirationYear" => (int) $tuna_expiration_date[1],
                    "CVV" => $card['tuna_cvv'],
                    "SingleUse" => false,
                ]
            ];

            $api_response = wp_remote_post(
                $url,
                array(
                    'headers' => array(
                        'Content-Type'  => 'application/json'
                    ),
                    'body' => json_encode($body)
                )
            );

            if (is_wp_error($api_response)) {
                throw new Exception(__('Problemas com o processo de pagamento', 'tuna-payment'));
            }

            if ( empty( $api_response['body'] ) ) {
                throw new Exception(__('Problemas com o processo de pagamento, recarregue a página.', 'tuna-payment'));
            }

            $response = json_decode($api_response['body']);

            return $response->token;

        } catch (\Exception $e) {
            return api_response( $e->getMessage(), 422 );
        }
    }


    /**
     * Retornar url do TUNA
     *
     * @param string $path
     * @param bool $token
     * @return string
     */
    private function get_endpoint_url(string $path, bool $token = false): string
    {
        $url_production = 'https://engine.tunagateway.com/api';
        $url_sandbox = 'https://sandbox.tuna-demo.uy/api';

        if ($token) {
            $url_production = 'https://token.tunagateway.com/api';
            $url_sandbox = 'https://token.tuna-demo.uy/api';
        }

        $url = $this->operation_mode === 'production' ? $url_production : $url_sandbox;

        return "{$url}/{$path}";
    }

    /**
     * Quebrar data em formato de stirng para array, separando Mes e Ano
     *
     * @param string $date
     * @return array
     */
    private function separate_month_year(string $date): array
    {
        return explode('/', $date);
    }

    /**
     * Retornar Status do woocommerce de acordo com status code do TUNA
     *
     * @param $status_response
     * @return string
     */
    private function get_status_response($status_response): string
    {
        $status_code = [
            'pending' => [ '1', '0', 'C', 'P'],
            'payment-approved' => [ '2', '8', '9' ],
            'failed' => [ 'A', '6', 'N', '4', 'B', -1 ],
            'cancelled' => [ 'D', 'E' ],
        ];

        $new_status = '';
        foreach ($status_code as $status_woocommerce => $code_tuna) {
            if (in_array($status_response, $code_tuna)) {
                $new_status = $status_woocommerce;
            }
        }

        return $new_status;
    }

    /**
     * Retornar mensagem de acordo com o status
     *
     * @param $status_response
     * @return array
     */
    private function get_response_message($status_response)
    {
        $status_order = [
            'pending' => [
                'message' => 'Pagamento pendente',
                'status_code' => 200,
            ],
            'payment-approved' => [
                'message' => 'Pagamento aprovado',
                'status_code' => 200,
            ],
            'failed' => [
                'message' => 'Erro ao processar pagamento',
                'status_code' => 422,
            ],
            'cancelled' => [
                'message' => 'Pagamento cancelado',
                'status_code' => 422,
            ]
        ];

        return $status_order[$status_response];
    }

    public function get_tuna_status($order_id)
    {
        $order = wc_get_order($order_id);

        $url = $this->get_endpoint_url('Payment/Status');

        $item = [
            "AppToken" => $this->partner_key,
            "Account" => $this->partner_account,
            "PartnerUniqueID" => $order->get_id(),
            "PaymentDate" => $order->get_date_created()->format('Y-m-d')
        ];

        $api_response = wp_remote_post(
            $url,
            array(
                'headers' => array(
                    'Content-Type'  => 'application/json'
                ),
                'body' => json_encode($item)
            )
        );

        if (is_wp_error($api_response)) {
            throw new Exception(__('Problemas com o processo de pagamento, recarregue a página.', 'tuna-payment'));
        }

        if (empty($api_response['body'])) {
            throw new Exception(__('Problemas com o processo de pagamento, recarregue a página.', 'tuna-payment'));
        }

        $response = json_decode($api_response['body']);

        return $this->get_status_response($response->status);
    }

    /**
     * Configuração das credenciais
     */
    private function credentials()
    {
        global $Polen_Plugin_Settings;

        $this->partner_key = $Polen_Plugin_Settings['polen_api_rest_partner_key'];
        $this->partner_account = $Polen_Plugin_Settings['polen_api_rest_account'];
        $this->operation_mode = $Polen_Plugin_Settings['polen_api_rest_type_keys'];
    }
}
