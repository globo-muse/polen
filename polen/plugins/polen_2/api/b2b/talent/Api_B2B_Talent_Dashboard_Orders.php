<?php

namespace Polen\Api\b2b\Talent;

use Exception;
use Polen\Api\Talent\{Api_Talent_Check_Permission, Api_Talent_Utils};
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\v2\Polen_Order_V2;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Api_B2B_Talent_Dashboard_Orders extends Api_B2B_Talent_Dashboard
{
    /**
     * Metodo construtor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Registro das Rotas
     */
    public function register_routes()
    {
        register_rest_route( $this->namespace, $this->rest_base . '/orders', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'orders'],
                'permission_callback' => [Api_Talent_Check_Permission::class, 'check_permission'],
                'args' => []
            ],
        ] );

        register_rest_route( $this->namespace, $this->rest_base . '/orders/(?P<order_id>[\d]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'order'],
                'permission_callback' => [Api_Talent_Check_Permission::class, 'check_permission'],
                'args' => []
            ],
        ] );
    }

    /**
     * Rota para listagem
     */
    public function orders(WP_REST_Request $request): WP_REST_Response
    {
        try {
            $products_id  = Api_Talent_Utils::get_globals_product_id();
            $statuses = $request->get_param('statuses') ?? 'payment-approved';
            $statuses = substr_replace(explode("&", $statuses), 'wc-', 0, 0);

            $b2b_orders = Polen_Order_V2::get_b2b_orders_id_by_products_id_status($products_id, $statuses);

            $data = [];
            foreach ($b2b_orders as $b2b_order) {
                $order = wc_get_order($b2b_order->order_id);
                $polen_order = new Polen_Order_Module($order);
                $date_created = $polen_order->get_date_created();
                $total_for_talent = !empty($polen_order->get_value_payment_talent())
                    ? $polen_order->get_value_payment_talent()
                    : $polen_order->get_total_for_talent();

                $data[] = [
                    'order_id' => $polen_order->get_id(),
                    'company_name' => $polen_order->get_company_name(),
                    'video_category' => $polen_order->get_video_category(),
                    'status' => $polen_order->get_status(),
                    'date' => $date_created->date('Y-m-d h:i:s'),
                    'total_order' => (int) $polen_order->get_total(),
                    'total_talent' => $total_for_talent,
                    'payment' => $polen_order->get_form_of_payment() ?? '',
                    'payday' => $polen_order->get_payday() ?? '',
                ];
            }

            return api_response($data);
        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Exibir order b2b de acordo com o ID
     */
    public function order(WP_REST_Request $request): WP_REST_Response
    {
        try {
            $order = wc_get_order($request['order_id']);
            if (!$order) {
                throw new Exception('Não existe pedido para esse ID', 404);
            }

            $polen_order = new Polen_Order_Module($order);

            $total_for_talent = $polen_order->get_value_payment_talent();

            $product_order = $polen_order->get_product_from_order();
            $date_created = $polen_order->get_date_created();

            $order_id = $request['order_id'];
            $product_name = $product_order->get_title();
            $date = $date_created->date('Y-m-d h:i:s');
            $company_name = $polen_order->get_company_name();
            $cnpj_cpf = $polen_order->get_billing_cnpj_cpf();
            $cep = $polen_order->get_billing_postcode();
            $address = $polen_order->get_billing_address_1();
            $address_2 = $polen_order->get_billing_address_2();
            $category_video = $polen_order->get_video_category();
            $status = $polen_order->get_status();
            $total_talent = !empty($total_for_talent) ? $total_for_talent : $polen_order->get_total_for_talent();
            $total_order = $polen_order->get_total();

            $data = compact(
                'order_id',
                'product_name',
                'date',
                'company_name',
                'cnpj_cpf',
                'cep',
                'address',
                'address_2',
                'category_video',
                'status',
                'total_talent',
                'total_order'
            );

            return api_response($data);
        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }
}
