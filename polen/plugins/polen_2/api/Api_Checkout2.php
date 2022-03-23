<?php

namespace Polen\Api;

use Automattic\WooCommerce\Client;
use DateTime;
use Exception;
use Polen\Includes\Debug;
use Polen\Includes\Emails\Polen_WC_Customer_New_Account;
use Polen\Includes\Polen_Campaign;
use Polen\Includes\Polen_Checkout_Create_User;
use Polen\Includes\Polen_Order;
use WC_Cart;
use WC_Coupon;
use WC_Customer;
use WC_Session_Handler;
use WP_REST_Request;

class Api_Checkout2
{

    private $woocommerce;
    const ORDER_METAKEY = 'hotsite';
    const USER_METAKEY  = 'hotsite';

    public function __construct()
    {
        $this->auth();
    }

    public function auth()
    {
        global $Polen_Plugin_Settings;
        $this->woocommerce = new Client(
            site_url(),
            $Polen_Plugin_Settings['polen_api_rest_cosumer_key'],
            $Polen_Plugin_Settings['polen_api_rest_cosumer_secret'],
            [
                'wp_api' => true,
                'version' => 'wc/v3'
            ]
        );
    }

    /**
     * Criação de uma order completa, seguindo os passos:
     *
     * 1- Verificar se os campos obrigatorios foram passados
     * 2- Verificar se o CPF é valido
     * 3- Criar um novo usuario caso cliete esteja deslogado
     * 4- Verificar status do cupom
     * 5- Registrar order no woocommerce
     * 6- Adicionar meta dados de acordo com o sistema
     * 7- Fazer requisição para o TUNA
     * 8- Atualizar status de acordo com o response do TUNA
     *
     * @param WP_REST_Request $request
     * @return array|void
     */
    public function create_order( WP_REST_Request $request )
    {





        $fields = $request->get_params();
        var_dump( $this->create_cart( $request ) );

        // return api_response('??');
        $order = WC()->checkout()->create_order( $fields );
        $process_checkout = WC()->checkout()->process_checkout($order);
        Debug::def($order,$process_checkout);
        return api_response($order);
        
    }

    public function create_cart( \WP_REST_Request $request )
    {
        $params = $request->get_params();
        WC()->cart->empty_cart();
        var_dump( "add_to_cart: {$params[ 'product_id' ]}", WC()->cart->add_to_cart( $params[ 'product_id' ], 1 ) );
        if( isset( $params[ 'coupon' ] ) && !empty( $params[ 'coupon' ] ) ) {
            var_dump( 'add cupom: ', WC()->cart->add_discount( $params[ 'coupon' ] ) );
        }
        var_dump( 'check_cart_items: ', WC()->cart->check_cart_items() );
        var_dump( 'needs_payment: ', WC()->cart->needs_payment() );
        // var_dump( 'get_total: ', WC()->cart->get_total() );
        // var_dump( 'get_total_discount: ', WC()->cart->get_total_discount() );
        var_dump( 'get_totals: ', WC()->cart->get_totals() );
        WC()->cart->calculate_totals();
    }

    /**
     * Criar usuario
     *
     * @param array $data
     * @return \WP_User
     */
    private function create_new_user( array $data, $campaign = '' )
    {
        $userdata = array(
            'user_login' => $data['email'],
            'user_email' => $data['email'],
            'user_pass' => wp_generate_password(6, false),
            'first_name' => $data['name'],
            'nickname' => $data['name'],
            'role' => 'customer',
        );

        $user['new_account'] = false;
        $user_wp = get_user_by( 'email', $userdata['user_email'] );
        if( false === $user_wp ) {

            $args = [];
            if( !empty( $campaign ) ) {
                $args[ 'campaign' ] = $campaign;
            }
            $args[ Polen_Checkout_Create_User::META_KEY_CREATED_BY ] = 'checkout';
            
            $api_user = new Api_User();
            $user_id = $api_user->create_user_custumer(
                $userdata['user_email'],
                $userdata['first_name'],
                $userdata['user_pass'],
                $args,
                true
            );
            $user['new_account'] = true;
            $user_wp = get_user_by( 'id', $user_id );
        }

        unset( $user_wp->user_pass );
        $user['user_object'] = $user_wp;

        $address = array(
            'billing_email' => $data['email'],
            'billing_cpf' => preg_replace('/[^0-9]/', '', $data['cpf']),
            'billing_country' => 'BR',
            'billing_phone' => preg_replace('/[^0-9]/', '', $data['phone']),
            'billing_cellphone' => preg_replace('/[^0-9]/', '', $data['phone']),
        );

        foreach ( $address as $key => $value ) {
            update_user_meta( $user['user_object']->ID, $key, $value );
        }
        return $user;
    }

    /**
     * Criar uma order no woocommerce
     *
     * @param WP_User $user
     * @param int $product_id
     * @param string $coupon
     */
    public function order_payment_woocommerce($user, $product_id, $coupon = null)
    {
        $data = [
            'payment_method' => 'tuna_payment',
            'payment_method_title' => 'API TUNA',
            'set_paid' => false,
            'customer_id'   => $user->ID,
            'customer_note' => 'created by api rest',
            'created_via'   => 'checkout_rest_api',
            'billing' => [
                'first_name' => $user->display_name,
                'country' => get_user_meta($user->ID, 'billing_country', true),
                'email' => $user->user_email,
                'phone' => get_user_meta($user->ID, 'billing_cellphone', true),
            ],
            'line_items' => [
                [
                    'product_id' => $product_id,
                    'quantity' => 1,
                ],
            ],

        ];

        if ($coupon !== null ) {
            $data['coupon_lines'][] = [
                'code' => $coupon,
            ];
        }
        return $this->woocommerce->post('orders', $data);
    }

    /**
     * Verificar se o cupom está válido para a criação da order
     *
     * @param $code_id
     */
    public function coupon_rules($code_id)
    {
        try {

            return api_response( $this->check_cupom( $code_id ), 200 );

        } catch (\Exception $e) {
            return api_response($e->getMessage(), 422);
        }

    }

    protected function check_cupom( $coupom_code )
    {
        $return = WC()->cart->apply_coupon( $coupom_code );
        if( !$return ) {
            // WC()->cart->empty_cart();
            throw new Exception( 'Cupom inválido' . __LINE__, 422 );
        }

        if( empty( WC()->cart->get_applied_coupons() ) ) {
            // WC()->cart->empty_cart();
            throw new Exception( 'Cupom inválido' . __LINE__, 422 );
        }
        return true;
    }

    /**
     * Retorna todos os campos do formulário que são obrigatórios
     */
    private function required_fields(): array
    {
        return [
            'name' => 'Nome',
            'email' => 'E-mail',
            'phone' => 'Celular/Telefone',
            'cpf' => 'CPF',
            'instruction' => 'Instrução',
            'video_to' =>  'Endereçamento do vídeo',
            'video_category' => 'Categoria do vídeo',
            'name_to_video' => 'Nome de quem receberá o vídeo',
            'allow_video_on_page' => 'Configuração de exibição',
            'product_id' => 'ID do Produto',
        ];
    }

    /**
     * Adicionar metas na order
     *
     * @param int $order_id
     * @param array $data
     * @throws Exception
     */
    private function add_meta_to_order(int $order_id, array $data)
    {
        $order = wc_get_order($order_id);
        $email = $data['email'];
        $status = $data['allow_video_on_page'] ? 'on' : 'off';
        // $product = wc_get_product($data['product_id']);

        $order->update_meta_data('_polen_customer_email', $email);
        $order->add_meta_data( self::ORDER_METAKEY, 'galo_idolos', true );

        // $order_item_id = wc_add_order_item($order_id, array(
        //     'order_item_name' => $product->get_title(),
        //     'order_item_type' => 'line_item', // product
        // ));
        $items = $order->get_items();
        $item = array_pop( $items );
        $order_item_id = $item->get_id();
        // $quantity = 1;

        // wc_add_order_item_meta($order_item_id, '_qty', $quantity, true);
        wc_add_order_item_meta($order_item_id, 'offered_by'           , $data['name'], true);
        wc_add_order_item_meta($order_item_id, 'video_to'             , $data['video_to'], true);
        wc_add_order_item_meta($order_item_id, 'name_to_video'        , $data['name_to_video'], true);
        wc_add_order_item_meta($order_item_id, 'email_to_video'       , $email, true);
        wc_add_order_item_meta($order_item_id, 'video_category'       , $data['video_category'], true);
        wc_add_order_item_meta($order_item_id, 'instructions_to_video', $data['instruction'], true);
        wc_add_order_item_meta($order_item_id, 'allow_video_on_page'  , $status, true);

        $interval  = Polen_Order::get_interval_order_basic();
        $timestamp = Polen_Order::get_deadline_timestamp($order, $interval);
        Polen_Order::save_deadline_timestamp_in_order($order, $timestamp);
        $order->add_meta_data(Polen_Order::META_KEY_DEADLINE, $timestamp, true);

        $order->save();
    }

    /**
     * Verifica se um CPF é válido
     *
     * @param string $cpf
     * @return bool
     */
    private function CPF_validate(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        if (strlen($cpf) != 11) {
            return false;
        }
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

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
