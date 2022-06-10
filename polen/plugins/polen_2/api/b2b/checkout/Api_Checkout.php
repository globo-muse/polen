<?php
namespace Polen\Api\b2b\Checkout;

use Exception;
use Polen\Api\Api_Util_Security;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Module\Polen_User_Module;
use Polen\Api\Module\Tuna;
use Polen\Includes\Hubspot\Polen_Hubspot;
use Polen\Includes\Hubspot\Polen_Hubspot_Factory;
use Polen\Includes\Module\Resource\Polen_B2B_Orders;
use Polen\Includes\Polen_Create_Customer;
use WC_Emails;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

class Api_Checkout extends WP_REST_Controller
{
    protected $controller_access;

    public function __construct()
    {
        $this->namespace = 'polen/v1';
        $this->rest_base = 'b2b';

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, $this->rest_base . '/checkout/(?P<order_id>[\d]+)/(?P<key_order>[^/]*)', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'form_checkout'],
                'permission_callback' => [],
                'args' => []
            ],
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'checkout_info_step_one'],
                'permission_callback' => [],
                'args' => []
            ]
        ] );

        register_rest_route($this->namespace, $this->rest_base . '/thankyou/(?P<order_id>[\d]+)/(?P<key_order>[^/]*)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'thankyou'],
                'permission_callback' => [],
                'args' => []
            ],
        ] );

        register_rest_route($this->namespace, $this->rest_base . '/status/(?P<order_id>[\d]+)/(?P<key_order>[^/]*)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'check_status'],
                'permission_callback' => [],
                'args' => []
            ],
        ] );

    }

    /**
     * Rota checkout
     *
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     * @throws Exception
     */
    public function form_checkout(WP_REST_Request $request): \WP_REST_Response
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $client = $_SERVER['HTTP_USER_AGENT'];
        $nonce = $request->get_param('security');

        try {
//            if(!Api_Util_Security::verify_nonce($ip . $client, $nonce)) {
//                throw new Exception('Erro na segurança', 403);
//            }

            $method_payment = $request->get_param('method_payment');
            $card = $method_payment == 'cc';

            $required_fields = $this->required_fields($card);
            $fields_checkout = $request->get_params();

            foreach ($required_fields as $key => $field) {
                if (!isset($fields_checkout[$key]) || empty($fields_checkout[$key])) {
                    $errors[] = "O campo {$field} é obrigatório";
                }
                $data[$key] = sanitize_text_field($fields_checkout[$key]);
            }

            if (!empty($errors)) {
                throw new Exception($errors[0], 403);
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido', 403);
            }

            $create_user = new Polen_Create_Customer();
            $user = $create_user->create_new_user($data);

            $b2b_order = new Polen_B2B_Orders($request['order_id'], $request['key_order']);
            $order = wc_get_order($request['order_id']);
            $order_module = new Polen_Order_Module($order);

            $tuna = new Tuna($order_module, $data);
            $tuna->set_costumer();
            if ($method_payment == 'cc') {
                $tuna->set_credit_card();
                $hubspot_method_payment = Polen_Hubspot::PAYMENT_TYPE_CC;
            }

            $payment = $tuna->pay_request();
            $tuna->meta_info_required($payment->paymentKey, $ip, $client);
            $new_status = $this->get_status_response($payment->status);

            if('failed' === $new_status || 'cancelled' === $new_status) {
                throw new Exception('Erro no pagamento, tente novamente', 422);
            }
            
            $b2b_order->update_order($data);
            $b2b_order->calculate_totals();
            WC_Emails::instance();
            $order->set_customer_id($user['user_object']->data->ID);
            $order->update_status($new_status);
            $response_message = $this->get_response_message($new_status);

            $response_payment = [
                'message' => $response_message['message'],
                'order_status' => $response_message['status_code'],
            ];

            if ($method_payment == 'pix') {
                $response_payment['pix_code'] = $payment->methods[0]->pixInfo->qrContent;
                $response_payment['pix_qrcode'] = $payment->methods[0]->pixInfo->qrImage;
                $hubspot_method_payment = Polen_Hubspot::PAYMENT_TYPE_PIX;
            }

            update_post_meta($request['order_id'], '_accepted_term', date("Y-m-d H:i:s"));

            $client_hubspot = Polen_Hubspot_Factory::create_client_with_redux();
            $hubspot = new Polen_Hubspot($client_hubspot);
            
            $hubspot->update_ticket_payment_by_order_module($order_module, true, $hubspot_method_payment);

            return api_response($response_payment);

        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Tratar response do TUNA
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

    /**
     * Exibir informações básicas
     *
     * @throws Exception
     */
    public function checkout_info_step_one(WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $b2b_order = new Polen_B2B_Orders($request['order_id'], $request['key_order']);

            return api_response($b2b_order->get_order_info_step_one());
        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }

    }

    /**
     * Exibir valor do pedido na tela de agradecimento
     *
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function thankyou(WP_REST_Request $request): \WP_REST_Response
    {
        try {
            /*TODO:Fazer validação na classe*/
            $order = wc_get_order($request['order_id']);
            if (empty($order)) {
                throw new Exception('Não existe pedido com esse ID', 403);
            }

            $order_module = new Polen_Order_Module($order);
            $product_order = $order_module->get_product_from_order();

            $response = [
                'cnpj_cpf' => $order_module->get_billing_cnpj_cpf(),
                'company_name' => $order_module->get_company_name(),
                'product' => $product_order->get_title(),
                'total' => $order_module->get_total(),
                'date' => $order_module->get_date_created()->date('d/m/Y'),
            ];

            return api_response($response);
        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Verificar status
     *
     * @param $request
     * @return \WP_REST_Response
     * @throws Exception
     */
    public function check_status($request): \WP_REST_Response
    {
        $order = wc_get_order($request['order_id']);
        $order_module = new Polen_Order_Module($order);

        $tuna = new Tuna($order_module);

        $status_tuna = $tuna->get_status();
        $current_status_tuna = $this->get_status_response($status_tuna);

        $status_payment = ['payment-approved', 'video-sended', 'completed'];
        if ($order->get_status() != $current_status_tuna) {
            $order->update_status($current_status_tuna);
        }

        return api_response(
            [
                'paid' => in_array($order->get_status(), $status_payment),
                'payment_status' => $order->get_status()
            ]
        );
    }

    /**
     * Retorna todos os campos do formulário que são obrigatórios
     */
    private function required_fields($card = false): array
    {
        $fields = [
            'terms' => 'Concordar com os termos',
            'company_name' => 'Nome empresa',
            'address_1' => 'Endereço',
            'city' => 'Cidade',
            'postcode' => 'CEP',
            'neighborhood' => 'Bairro',
            'country' => 'País',
            'state' => 'Estado',
            'email' => 'Email',
            'cnpj' => 'CNPJ',
            'corporate_name' => 'Razão Social',
            'method_payment' => 'Metodo de pagamento',
        ];

        if ($card === true) {
            $cards = [
                'card_holder_name' => 'Nome do cartão',
                'card_brand' => 'Bandeira',
                'card_expiration_date' => 'Data de Vencimento',
                'card_number' => 'Numero do cartão',
                'card_cvv' => 'CVV',
                'installments' => 'Parcelas',
            ];

            $fields = array_merge($fields, $cards);
        }

        return $fields;
    }

}
