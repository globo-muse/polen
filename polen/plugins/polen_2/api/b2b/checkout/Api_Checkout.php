<?php
namespace Polen\Api\b2b\Checkout;

use Exception;
use Polen\Api\Api_Util_Security;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Api\Module\{Tuna_Credit_Card,Tuna_Pix};
use Polen\Includes\Module\Orders\Polen_B2B_Orders;
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
            $card = false;
            $tuna = new Tuna_Pix();

            if ($method_payment == 'cc') {
                $card = true;
                $tuna = new Tuna_Credit_Card();
            }

            $required_fields = $this->required_fields($card);
            $fields_checkout = $request->get_params();

            foreach ($required_fields as $key => $field) {
                if (!isset($fields_checkout[$key]) && !empty($field)) {
                    $errors[] = "O campo {$field} é obrigatório";
                }
                $data[$key] = sanitize_text_field($fields_checkout[$key]);
            }

            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido', 403);
            }

            $create_user = new Polen_Create_Customer();
            $user = $create_user->create_new_user($data);

            $b2b_order = new Polen_B2B_Orders($request['order_id'], $request['key_order']);
            WC_Emails::instance();
            $response = $tuna->payment($request['order_id'], $user, $data);
            $b2b_order->calculate_totals();
            $b2b_order->update_order($data);

            return api_response($response);

        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
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
                'product' => $product_order->get_title(),
                'total' => $order_module->get_total()
            ];

            return api_response($response);
        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }

    public function check_status($request)
    {
        $order_id = $request['order_id'];
        $tuna = new Tuna_Pix();

        $current_status = $tuna->get_status($order_id);

        return api_response(
            [
                'paid' => $current_status == 'completed',
                'payment_status' => $current_status
            ]
        );
    }


    /**
     * Retorna todos os campos do formulário que são obrigatórios
     */
    private function required_fields($card = false): array
    {
        $fields = [
            'name' => 'Nome do representante',
            'company' => 'Nome empresa',
            'address_1' => 'Endereço',
            'address_2' => 'Complemento',
            'city' => 'Cidade',
            'postcode' => 'CEP',
            'neighborhood' => 'Bairro',
            'country' => 'País',
            'state' => 'Estado',
            'email' => 'Email',
            'phone' => 'Celular',
            'cnpj' => 'CNPJ',
            'corporate_name' => 'Razão Social',
        ];

        if ($card === true) {
            $cards = [
                'tuna_card_holder_name' => 'Nome do cartão',
                'tuna_card_brand' => 'Bandeira',
                'tuna_expiration_date' => 'Data de Vencimento',
                'tuna_card_number' => 'Numero do cartão',
                'tuna_cvv' => 'Código de segurança',
                'installments' => 'Parcelas',
            ];

            $fields = array_merge($fields, $cards);
        }

        return $fields;
    }

}
