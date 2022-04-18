<?php

namespace Polen\Api\b2b\Talent;

use Exception;
use Polen\Api\Talent\Api_Talent_Check_Permission;
use Polen\Api\Talent\Api_Talent_Dashboard;
use Polen\Api\Talent\Api_Talent_Utils;
use Polen\Includes\Module\Polen_User_Module;
use Polen\Includes\Polen_Order_Review;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Emails;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Redux;
use Polen\Includes\v2\Polen_Order_V2;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Api_B2B_Talent_Dashboard extends Api_Talent_Dashboard
{

    /**
     * Metodo construtor
     */
    public function __construct()
    {
        $this->namespace = 'polen/v1';
        $this->rest_base = 'b2b/talents';
    }

    /**
     * Registro das Rotas
     */
    public function register_routes()
    {
        register_rest_route( $this->namespace, $this->rest_base . '/dashboard', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'dashboard' ],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ],
        ] );

        register_rest_route( $this->namespace, $this->rest_base . '/history-orders', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'handler_request_history_order' ],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ],
        ] );
    }


    /**
     * Handler do endpoint do Dashboard do talento
     */
    public function dashboard( WP_REST_Request $request ): WP_REST_Response
    {
        $products_id  = Api_Talent_Utils::get_globals_product_id();
        $product_id   = implode($products_id);
        $product      = wc_get_product($product_id);
        $product_post = get_post($product->get_id());
        $talent       = get_user_by('id', $product_post->post_author);

        $reviews = Polen_Order_Review::get_order_reviews_by_talent_id($talent->ID);

        $response = [
            'qty_pending_order'     => Polen_Order_V2::get_qty_orders_by_products_id_status($products_id, ['wc-payment-approved', 'wc-talent-accepted']),
            'qty_orders_accepted'   => Polen_Order_V2::get_total_orders_by_products_id_status($products_id, ['wc-talent-accepted']),
            'total_pending_value'   => Polen_Order_V2::get_total_orders_by_products_id_status($products_id, ['wc-pending']),
            'qty_orders_recorded'   => Polen_Order_V2::get_qty_orders_by_products_id_status($products_id, ['wc-completed']),
            'qty_orders_expired'    => Polen_Order_V2::get_qty_orders_by_products_id_status($products_id, ['wc-order-expired']),
            'deadline_orders_today' => Polen_Order_V2::get_qty_orders_by_products_id_deadline($products_id, date('Y/m/d')),
            'comment_count_order'   => count($reviews),
            'comment_rating_order'  => Polen_Order_Review::get_sum_rate_by_talent($talent->ID),
        ];

        return api_response($response);
    }


    /**
     * Handler quando o usuário talento pede um histórico das vendas completadas
     * @param WP_REST_Request
     * @return WP_REST_Response
     */
    public function handler_request_history_order(WP_REST_Request $request)
    {
        $user = wp_get_current_user();
        $user_polen = new Polen_User_Module($user->ID);
        try{
            //TODO: Remover os hardcoded
            $this->send_email_request_history_order(
                'Financeiro',
                'financeiro@polen.me',
                $user->user_firstname,
                $user_polen->get_receiving_email(),
                date('d/m/Y')
            );
            return api_response('success');
        } catch(Exception $e) {
            return api_response('error', $e->getCode());
        }
    }

        /**
     * Enviar email Via SendGrid API
     */
	public function send_email_request_history_order(
		$name,
		$email,
		$talent_name,
        $talent_email,
        $date )
	{

        global $Polen_Plugin_Settings;
        $apikeySendgrid = $Polen_Plugin_Settings[ Polen_Sendgrid_Redux::APIKEY ];
        $send_grid = new Polen_Sendgrid_Emails( $apikeySendgrid );
        $send_grid->set_from(
            $Polen_Plugin_Settings['polen_smtp_from_email'],
            $Polen_Plugin_Settings['polen_smtp_from_name']
        );
        $send_grid->set_to( $email, $name );
        $send_grid->set_reply_to($talent_email, $talent_name);
        //TODO: Remover os hardcoded
        $send_grid->set_template_id('d-f709cc4917c34dd5a0a59db3d1789d86');
        $send_grid->set_template_data( 'date', $date );
        $send_grid->set_template_data( 'talent_name', $talent_name );
        $send_grid->set_template_data( 'talent_email', $talent_email );

        return $send_grid->send_email();
    }
}
