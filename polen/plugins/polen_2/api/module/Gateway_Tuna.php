<?php

namespace Polen\Api\Module;

use Exception;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Module\Polen_User_Module;
use Polen\Includes\Polen_Campaign;
use WC_Order;

class Gateway_Tuna
{
    private string $partner_key;
    private string $partner_account;
    private string $operation_mode;

    public function __construct()
    {
        $this->credentials();
    }

    /**
     * Montar body para request com o tuna
     *
     * @param $order_id // ID da order atual
     * @param $current_user // usuario caso houver
     * @param string|array $card_info // informações do cartão de crédito
     * @return array
     */
    protected function body_for_request($order_id, $current_user = '', $card_info = null, $installments = 1, $session_id = null)
    {
        if ($session_id === null) {
            $session_id = $this->get_session_id($current_user['user_object']->data);
        }

        $order = new WC_Order($order_id);

        $costumer_order = new Polen_Order_Module($order);
        $product = $costumer_order->get_product_from_order();

        $name = $order->get_billing_first_name();
        if (empty($name)) {
            $name = $costumer_order->get_corporate_name();
        }

        $purchased_items = [
            [
                "Amount" => floatval($costumer_order->get_total()),
                "ProductDescription" => $product->get_name(),
                "ItemQuantity" => 1,
                "CategoryName" => 'b2b',
                "AntiFraud" => [
                    "Ean" => $product->get_sku()
                ],
            ]
        ];

        // $split = $this->split($costumer_order->get_item_cart_id(), $order->get_total());

//        if ($split !== null) {
//            $purchased_items[0]['Split'] = $split;
//        }

        $method = $card_info ? 'cc' : 'pix';

        $payment_method_type = $this->method_payment_type($method);

        $document_value = preg_replace('/[^0-9]/', '', $costumer_order->get_billing_cnpj_cpf());
        $document_type = $this->check_cpf_cnpj($document_value);

        $order->calculate_totals();

        $body = [
            'AppToken' => $this->partner_key,
            'Account' => $this->partner_account,
            'PartnerUniqueID' => $order_id,
            'TokenSession' => $session_id,
            'Customer' => [
                'Email' => $order->get_billing_email(),
                'Name' => $name,
                'ID' => (string) $current_user['user_object']->data->ID,
                'Document' => (string) $document_value,
                'DocumentType' => (string) $document_type,
            ],
            "FrontData" => [
                "SessionID" => session_create_id(),
                "Origin" => 'WEBSITE',
                "IpAddress" => $_SERVER['HTTP_CLIENT_IP'],
                "CookiesAccepted" => true
            ],
//            "ShippingItems" => [
//                "Items" => [
//                    [
//                        "Type" => $order->get_shipping_method(),
//                        "Amount" => floatval($order->get_shipping_total()),
//                        "Code" => '',
//                    ]
//                ]
//            ],
            "PaymentItems" => [
                "Items" => $purchased_items,
            ],
            "PaymentData" => [
                "Countrycode" => 'BR',
                "SalesChannel" => 'ECOMMERCE',
                "PaymentMethods" => [
                    [
                        "PaymentMethodType" => $payment_method_type,
                        "Amount" => floatval($order->get_total()),
                        "Installments" => $installments,
                        "CardInfo" => $card_info,
                        "BoletoInfo" => null
                    ],
                ],
                "AntiFraud" => [
                    "DeliveryAddressee" => "Antonia",
                ],
                "DeliveryAddress" => [
                    "Street" => "Ses Av. Das Nações",
                    "Number" => "Q811",
                    "Complement" => "Bloco H",
                    "Neighborhood" => "Brasilia",
                    "City" => "Brasilia",
                    "State" => "DF",
                    "Country" => "BR",
                    "PostalCode" => "70429900",
                    "Phone" => "6132442121"
                ]
            ]
        ];

        return $body;
    }

//    protected function split(int $product_id, float $price_order)
//    {
//        global $Polen_Plugin_Settings;
//        $enable_split = $Polen_Plugin_Settings['polen_split'];
//
//        if (empty($enable_split)) {
//            return null;
//        }
//
//        $talent = Polen_User_Module::create_from_product_id($product_id);
//        $split_db = $talent->get_split_setup();
//
//        $merchantDocument = $split_db[0]->cnpj;
//        $merchantDocumentType = 'CNPJ';
//
//        if ($split_db[0]->natureza_juridica == 'PF') {
//            $merchantDocument = $split_db[0]->cpf;
//            $merchantDocumentType = 'CPF';
//        }
//
//        return [
//            "merchantID" => $split_db[0]->subordinate_merchant_id,
//            "merchantDocument" => preg_replace('/[^0-9]/', '', $merchantDocument),
//            "merchantDocumentType" => $merchantDocumentType,
//            "amount" => $price_order * 0.75, // substituir valor quando for implementado valor subtotal
//        ];
//    }

    /**
     * Fazer request para API do TUNA
     *
     * @param array $body
     * @return mixed
     * @throws Exception
     */
    protected function request(array $body)
    {
        $url = $this->get_endpoint_url('Payment/Init');

        $api_response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'x-tuna-apptoken' => $this->partner_key,
                'x-tuna-account' => $this->partner_account,
//                'Accept' => '*/*',
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

        return json_decode($api_response['body']);
    }

    /**
     * Gerar ID da sessão
     *
     * @param $current_user
     * @return string
     */
    protected function get_session_id($current_user)
    {
//        try {
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
                    'Content-Type'  => 'application/json',
                    'x-tuna-apptoken' => $this->partner_key,
                    'x-tuna-account' => $this->partner_account,

                ),
                'body' => json_encode($body)
            )
        );

        if (is_wp_error($api_response)) {
            throw new Exception(__('Problemas com o processo de pagamento', 'tuna-payment'));
        }

        $response = json_decode($api_response['body']);

        return $response->sessionId;
//        } catch (\Exception $e) {
//            wp_send_json_error($e->getMessage(), 422);
//            wp_die();
//        }
    }

    /**
     * Gerar token com os dados do cartão
     *
     * @param string $session_id
     * @param array $card
     * @return string
     */
    protected function generate_token_card(string $session_id, array $card)
    {
//        try {
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
                "SingleUse" => true,
            ]
        ];

        $api_response = wp_remote_post(
            $url,
            array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'x-tuna-apptoken' => $this->partner_key,
                    'x-tuna-account' => $this->partner_account,
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
//
//            $bind = 'https://token.tunagateway.com/api/Token/Bind';
//
//            $body_bind = [
//                "Token" => $response->token,
//                "CVV" => $card['tuna_cvv'],
//                "SessionId" => $session_id
//            ];
//
//            $bind_response = wp_remote_post(
//                $bind,
//                array(
//                    'headers' => array(
//                        'Content-Type'  => 'application/json',
//                        'x-tuna-apptoken' => $this->partner_key,
//                        'x-tuna-account' => $this->partner_account,
//                    ),
//                    'body' => json_encode($body_bind)
//                )
//            );

        // print_r($bind_response); die();

        return $response->token;

//        } catch (\Exception $e) {
//            return api_response( $e->getMessage(), 422 );
//        }
    }


    /**
     * Retornar url do TUNA
     *
     * @param string $path
     * @param bool $token
     * @return string
     */
    protected function get_endpoint_url(string $path, bool $token = false): string
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
    protected function get_status_response($status_response): string
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
     * Retornar mensagem conforme o status
     *
     * @param $status_response
     * @return array
     */
    protected function get_response_message($status_response)
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
                    'Content-Type'  => 'application/json',
                    'x-tuna-apptoken' => $this->partner_key,
                    'x-tuna-account' => $this->partner_account,
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
     * Configuração das credenciais
     */
    private function credentials()
    {
        global $Polen_Plugin_Settings;

        $this->partner_key = $Polen_Plugin_Settings['polen_api_rest_partner_key'];
        $this->partner_account = $Polen_Plugin_Settings['polen_api_rest_account'];
        $this->operation_mode = $Polen_Plugin_Settings['polen_api_rest_type_keys'];
    }

    protected function check_cpf_cnpj(string $value)
    {
        $type = 'CNPJ';
        if (strlen($value) === 11) {
            $type = 'CPF';
        }

        return $type;
    }
}
