<?php

namespace  Polen\Api\Module;

use Exception;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Module\Polen_User_Module;

class Tuna implements Payments
{
    private string $partner_key;
    private string $partner_account;
    private string $operation_mode;
    private array $data;
    private Polen_Order_Module $order;
    private string $session_id;
    private array $card;
    private array $costumer;

    public function __construct(Polen_Order_Module $order_module, array $data = [])
    {
        $this->credentials();
        $this->order = $order_module;
        $this->data = $data;
    }

    /**
     * Criar e setar usuario que será utilizado no processo de pagamento
     */
    public function set_costumer(): void
    {
        $value_document = preg_replace( '/[^0-9]/', '', $this->order->get_billing_cnpj_cpf());
        $type_document = $this->check_cpf_cnpj($value_document);

        $this->costumer = [
            'Name' => $this->order->get_company_name(),
            'ID' => (string) $this->order->get_customer_id(),
            'Document' => (string) $value_document,
            'DocumentType' => $type_document,
            'Email' => $this->order->get_billing_email(),
        ];

        $this->set_session_id($this->costumer);
    }

    public function get_item_qty(): int
    {
        return 1;
    }

    /**
     * Mtodo para retornar o usuario atual
     *
     * @return array
     */
    public function get_customer(): array
    {
        return $this->costumer;
    }

    /**
     * Salvar Cartão que será utilizado na clase
     */
    public function set_credit_card(): void
    {
        $card_expiration_date = $this->handle_card_expiration_date($this->data['card_expiration_date']);
        $this->card = [
            "CardHolderName" => sanitize_text_field($this->data['card_holder_name']),
            "CardNumber" => preg_replace('/[^0-9]/', '', sanitize_text_field($this->data['card_number'])),
            "BrandName" => strtoupper(sanitize_text_field($this->data['card_brand'])),
            "CardCVV" => sanitize_text_field($this->data['card_cvv']),
            "ExpirationMonth" => (int) $card_expiration_date[0],
            "ExpirationYear" => (int) $card_expiration_date[1],
        ];
    }

    private function get_credit_card_to_payment(): ?array
    {
        $value_document = preg_replace('/[^0-9]/', '', $this->order->get_billing_cnpj_cpf());
        $type_document = $this->check_cpf_cnpj($value_document);

        if (empty($this->card)) {
            return null;
        }

        return [
            "Token" => $this->get_generate_token_card($this->session_id),
            "TokenProvider" => 'Tuna',
            "CardHolderName" => sanitize_text_field($this->card['CardHolderName']),
            "BrandName" => strtoupper(sanitize_text_field($this->card['BrandName'])),
            "ExpirationMonth" => $this->card['ExpirationMonth'],
            "ExpirationYear" => $this->card['ExpirationYear'],
            "TokenSingleUse" => 1,
            "SaveCard" => false,
            "BillingInfo" => [
                "Document" => $value_document,
                "DocumentType" => $type_document,
            ]
        ];
    }

    /**
     * Retonar dados do cartão que está salvo
     *
     * @return array
     */
    public function get_credit_card(): array
    {
        return $this->card;
    }

    /**
     * Tratar data de vencimento do cartão para o formato que é utilizado na API TUNA
     *
     * @param string $year
     * @return false|string[]
     */
    public function handle_card_expiration_date(string $year)
    {
        return explode('/', $year);
    }

    /**
     * Gerar ID da sessão
     *
     * @param $current_user
     */
    protected function set_session_id($current_user)
    {
        try {
            $url = $this->get_endpoint_url('Token/NewSession', true);

            $body = [
                "AppToken" => $this->partner_key,
                "Customer" => [
                    "Email" => $current_user['Email'],
                    "ID" => $current_user['ID'],
                ]
            ];

            $api_response = $this->request($url, $body);
            $this->session_id = $api_response->sessionId;

        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage(), 422);
            wp_die();
        }
    }

    /**
     * Retornar token com os dados do cartão
     *
     * @param string $session_id
     * @return \WP_REST_Response
     */
    protected function get_generate_token_card(string $session_id)
    {
        try {
            $url = $this->get_endpoint_url('Token/Generate', true);

            $body = [
                "SessionId" => $session_id,
                "Card" => [
                    "CardNumber" => $this->card['CardNumber'],
                    "CardHolderName" => $this->card['CardHolderName'],
                    "ExpirationMonth" => $this->card['ExpirationMonth'],
                    "ExpirationYear" => $this->card['ExpirationYear'],
                    "CVV" => $this->card['CardCVV'],
                    "SingleUse" => true,
                ]
            ];

            $api_response = $this->request($url, $body);

            return $api_response->token;

        } catch (\Exception $e) {
            return api_response( $e->getMessage(), 422 );
        }
    }

    /**
     * Retornar dados do item da order para pagamento
     *
     * @return array[]
     */
    public function get_itens_to_payment(): array
    {
        $product = $this->order->get_product_from_order();

        return [
            [
                "Amount" => floatval($product->get_price()),
                "ProductDescription" => $product->get_name(),
                "ItemQuantity" => $this->get_item_qty(),
                "CategoryName" => 'b2b',
                "AntiFraud" => [
                    "Ean" => $product->get_sku()
                ],
                "Split" => $this->split(),
            ]
        ];


    }

    /**
     * Fazer quest paga pagamento
     *
     * @return false|string
     */
    public function pay_request()
    {
        try {
            $url = $this->get_endpoint_url('Payment/Init');

            $body = [
                'AppToken' => $this->partner_key,
                'Account' => $this->partner_account,
                'PartnerUniqueID' => $this->order->get_id(),
                'TokenSession' => $this->session_id,
                'Customer' => $this->costumer,
                "FrontData" => [
                    "SessionID" => session_create_id(),
                    "Origin" => 'WEBSITE',
                    "IpAddress" => $_SERVER['HTTP_CLIENT_IP'],
                    "CookiesAccepted" => true
                ],
                "PaymentItems" => [
                    "Items" => $this->get_itens_to_payment(),
                ],
                "PaymentData" => [
                    "Countrycode" => 'BR',
                    "SalesChannel" => 'ECOMMERCE',
                    "PaymentMethods" => [
                        [
                            "PaymentMethodType" => $this->method_payment_type($this->data['method_payment']),
                            "Amount" => floatval($this->order->get_total()),
                            "Installments" => (int) $this->data['installments'] ?? 1,
                            "CardInfo" => $this->get_credit_card_to_payment(),
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

            // return json_encode($body);
            return $this->request($url, $body);
        } catch (\Exception $e) {
            return api_response( $e->getMessage(), 422 );
        }

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
     * Retornar tipo do documento passado
     * @param string $value
     * @return string
     */
    protected function check_cpf_cnpj(string $value): string
    {
        $type = 'CNPJ';
        if (strlen($value) === 11) {
            $type = 'CPF';
        }

        return $type;
    }

    /**
     *
     * Fazer request a API Tuna
     *
     * @param $url
     * @param $body
     * @return mixed
     * @throws Exception
     */
    public function request($url, $body)
    {
        $api_response = wp_remote_post(
            $url,
            array(
                'headers' => [
                    'Content-Type'    => 'application/json',
                    'x-tuna-apptoken' => $this->partner_key,
                    'x-tuna-account'  => $this->partner_account
                ],
                'body' => json_encode($body),
                'timeout' => 120,
            )
        );

        if (is_wp_error($api_response)) {
            throw new Exception(__('No momento, estamos enfrentando problemas ao tentar nos conectar a este portal de pagamento. Desculpe pela inconveniência.' . $api_response->get_error_message(), 'tuna-payment'));
        }

        if (empty($api_response['body'])) {
            throw new Exception(__('Erro no processamento do pagamento', 'tuna-payment'));
        }


        return json_decode($api_response['body']);
    }

    /**
     * Verificar status do pedido
     *
     * @param $order_id
     * @return string
     * @throws Exception
     */
    public function get_status()
    {
        $url = $this->get_endpoint_url('Payment/Status');

        $body = [
            "AppToken" => $this->partner_key,
            "Account" => $this->partner_account,
            "PartnerUniqueID" => $this->order->get_id(),
            "PaymentDate" => $this->order->get_date_created()->format('Y-m-d')
        ];

        $api_response = $this->request($url, $body);

        return $api_response->status;
    }

    /**
     * Adicionar Configuração Split
     * @return array|null
     */
    protected function split(): ?array
    {
        global $Polen_Plugin_Settings;
        $enable_split = $Polen_Plugin_Settings['polen_split'];

        if (empty($enable_split)) {
            return null;
        }

        $product = $this->order->get_product_from_order();
        $talent = Polen_User_Module::create_from_product_id($product->get_id());
        $documents = $talent->get_document();
        $merchant_id = $talent->get_merchant_id();

        if ($merchant_id === null) {
            return null;
        }

        if (empty($documents)) {
            return null;
        }

        return [
            "merchantID" => $merchant_id,
            "merchantDocument" => $documents['document'],
            "merchantDocumentType" => $documents['document_type'],
            "amount" => $this->order->get_total_for_talent(),
        ];
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