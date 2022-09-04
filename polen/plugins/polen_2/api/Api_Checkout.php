<?php

namespace Polen\Api;

use Automattic\WooCommerce\Client;
use Exception;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Campaign;
use Polen\Includes\Polen_Checkout_Create_User;
use Polen\Includes\Polen_Create_Customer;
use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Utils;
use WP_REST_Request;

class Api_Checkout
{

    private $woocommerce;
    const ORDER_METAKEY = 'hotsite';
    const USER_METAKEY  = 'hotsite';


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
        try {
            $nonce = $request->get_param( 'security' );
            // if( !wp_verify_nonce( $nonce, $_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'] ) ) {
            if( $nonce != '1d13b5e353' ) {
                throw new Exception( 'Falha na verificação de segurança', 401 );
            }
            // $tuna = new Api_Gateway_Tuna();
            $fields = $request->get_params();
            $required_fields = $this->required_fields();
            $errors = array();

            foreach ($required_fields as $key => $field) {
                if (!isset($fields[$key]) && !empty($field)) {
                    $errors[] = "O campo {$field} é obrigatório";
                }
                $data[$key] = sanitize_text_field($fields[$key]);
            }

            if (!empty($errors)) {
                return api_response( $errors, 422 );
            }

            // if (!$this->CPF_validate($fields['cpf'])) {
            //     throw new Exception( 'CPF Inválido', 422 );
            // }

            $product = wc_get_product( $fields['product_id'] );
            if( empty( $product ) ) {
                throw new Exception( 'Produto inválido', 422 );
            }
            // if (!$product->is_in_stock()) {
            //     throw new Exception( 'Produto sem estoque', 422 );
            // }

            $create_user = new Polen_Create_Customer();
            $product = wc_get_product( $fields[ 'product_id' ] );
            $campaign_slug = Polen_Campaign::get_product_campaign_slug( $product );
            $user = $create_user->create_new_user( $data, $campaign_slug );
            
            WC()->cart->empty_cart();
            
            $add_product_cart = WC()->cart->add_to_cart( $product->get_id(), 1 );
            if( !$add_product_cart ) {
                throw new Exception( 'Esse produto não pode ser comprado', 422 );
            }

            $coupon = null;
            // if (isset($fields['coupon'])) {
            //     $this->check_cupom($fields['coupon']);
            //     $coupon = sanitize_text_field($fields['coupon']);

            //     $check_cupom_data = [ 'billing_email' => $fields[ 'email' ] ];
            //     WC()->cart->check_customer_coupons( $check_cupom_data );
            //     if( empty( WC()->cart->get_applied_coupons() ) ) {
            //         throw new Exception( 'Cupom inválido', 422 );
            //     }
            // }

            $order_woo = $this->order_payment_woocommerce($user['user_object']->data, $fields['product_id'], $coupon);
            $this->add_meta_to_order($order_woo, $data);

            // if (WC()->cart->get_cart_contents_total() == 0) {
                $order_without_payment = [
                    'message' => 'Order criada sem pagamento',
                    'order_id' => $order_woo->get_id(),
                    'new_account' => $user['new_account'],
                    'order_status' => 200,
                    'order_code' => $order_woo->get_order_key()
                ];
                WC()->cart->empty_cart();
                $order_woo->payment_complete();
                $order_woo->update_status('payment-approved');
                $order_woo->save();

                return api_response( $order_without_payment, 201 );
            // } else {
            //     // $order_woo->set_payment_method_title($this->method_payment_name($data['method_payment']) ?? 'NONE');
            // }
            // // $payment = $tuna->process_payment($order_woo->get_id(), $user, $fields);
            // $response_payment = [
            //     'message' => 'ok',
            //     'order_id' => $order_woo->get_id(),
            //     // 'new_account' => $current_user['new_account'],
            //     'method_payment' => 'none',
            //     'order_status' => '200',//$response_message['status_code'],
            //     'order_code' => $order_woo->get_order_key()
            // ];

            // return api_response( $response_payment, 201 );

        } catch (\Exception $e) {
            return api_response( $e->getMessage(), $e->getCode() );
        }
    }



    /**
     * Criar uma order no woocommerce
     *
     * @param WP_User $user
     * @param int $product_id
     * @param $coupon
     */
    public function order_payment_woocommerce($user, $product_id, $coupon = '')
    {
        $args = [
            'status'        => 'pending',
            'customer_id'   => $user->ID,
            'customer_note' => 'created by api rest',
            'created_via'   => 'checkout_rest_api',
            'parent'        => null,
            'cart_hash'     => null,
        ];

        $order = wc_create_order($args);
        $product = wc_get_product($product_id);

        $address = array(
            'first_name' => $user->display_name,
            'last_name'  => '',
            'email'      => $user->user_email,
            'phone'      => get_user_meta($user->ID, 'billing_cellphone', true),
            'address_1'  => '',
            'address_2'  => '',
            'city'       => '',
            'state'      => '',
            'postcode'   => '',
            'country'    => 'BR'
        );

        $order->add_product($product, 1);
        $order->set_address($address, 'billing');
        if( !empty( $coupon ) ) {
            $result_cumpo_apply = $order->apply_coupon($coupon);
            if( is_wp_error( $result_cumpo_apply ) ) {
                $order->update_status( 'cancelled', "erro-no-cupom-{$coupon}" );
                throw new Exception( 'Cupom inválido', 422 );
            }
        }
        $order->calculate_totals();
        $order->save();

        return $order;
    }

    /**
     * Verificar se o cupom está válido para a criação da order
     *
     * @param $code_id
     */
    public function coupon_rules($code_id, $product_id = 0 )
    {
        if( !empty( $product_id ) ) {
            WC()->cart->empty_cart();
            $add_product_cart = WC()->cart->add_to_cart( $product_id, 1 );
        }
        $this->check_cupom( $code_id );
    }


    /**
     * 
     */
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
            // 'phone' => 'Celular/Telefone',
            // 'cpf' => 'CPF',
            'instruction' => 'Instrução',
            // 'video_to' =>  'Endereçamento do vídeo',
            'video_category' => 'Categoria do vídeo',
            // 'name_to_video' => 'Nome de quem receberá o vídeo',
            'allow_video_on_page' => 'Configuração de exibição',
            'product_id' => 'ID do Produto',
            // 'method_payment' => 'Método de pagamento',
        ];
    }

    /**
     * Adicionar metas na order
     *
     * @param \WC_Order $order
     * @param array $data
     * @throws Exception
     */
    private function add_meta_to_order($order, array $data)
    {
        $email = $data['email'];
        $status = $data['allow_video_on_page'] ? 'on' : 'off';
        // $product = wc_get_product($data['product_id']);

        $order->update_meta_data('_polen_customer_email', $email);

        $items = $order->get_items();
        $item = array_pop($items);
        $order_item_id = $item->get_id();
        $quantity = 1;

        $product = wc_get_product( $data[ 'product_id' ] );
        $campaign_slug = Polen_Campaign::get_product_campaign_slug( $product );

        $order->add_meta_data( self::ORDER_METAKEY, $campaign_slug, true);

        $instructions = Polen_Utils::sanitize_xss_br_escape($data['instruction']);

        wc_add_order_item_meta($order_item_id, '_line_subtotal', $order->get_subtotal(), true );
        wc_add_order_item_meta($order_item_id, '_line_total'   , $order->get_total(), true );

        wc_add_order_item_meta($order_item_id, '_qty'                 , $quantity, true);
        wc_add_order_item_meta($order_item_id, 'offered_by'           , '', true);
        wc_add_order_item_meta($order_item_id, 'video_to'             , Polen_Order_Module::VIDEO_TO_OTHER_ONE, true);
        wc_add_order_item_meta($order_item_id, 'name_to_video'        , $data['name'], true);
        wc_add_order_item_meta($order_item_id, 'email_to_video'       , $email, true);
        wc_add_order_item_meta($order_item_id, 'video_category'       , $data['video_category'], true);
        wc_add_order_item_meta($order_item_id, 'instructions_to_video', $instructions, true);
        wc_add_order_item_meta($order_item_id, 'allow_video_on_page'  , $status, true);

        $interval  = Polen_Order::get_interval_order_basic();
        $timestamp = Polen_Order::get_deadline_timestamp($order, $interval);
        Polen_Order::save_deadline_timestamp_in_order($order, $timestamp);

        return $order->save();
    }

    public function get_status($order_id)
    {
        $tuna = new Api_Gateway_Tuna();

        $order = wc_get_order($order_id);
        $status_woocommerce = $order->get_status();
        $status_tuna = $tuna->get_tuna_status($order_id);

        if ($status_woocommerce != $status_tuna) {
            $order->update_status($status_tuna);
        }

        return $status_tuna;
    }

    /**
     * Retornar Titulo do metodo de pagaamento
     */
    public function method_payment_name($type = 'cc'): string
    {
        $methods = array(
            'cc' => 'Cartão de Crédito',
            'pix' => 'Pix',
            'billet' => 'Boleto',
        );

        return $methods[$type];
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
