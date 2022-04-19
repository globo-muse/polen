<?php
namespace Polen\Api\B2B\Talent;

use Exception;
use Polen\Api\b2b\Talent\Api_B2B_Talent_Dashboard;
use Polen\Includes\Module\Polen_User_Module;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Emails;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Redux;
use WP_REST_Request;
use WP_REST_Server;

class Api_B2B_Talent_Orders_Receipt extends Api_B2B_Talent_Dashboard
{
    /**
     * Metodo construtor
     */
    public function __construct()
    {
        // $this->namespace = 'polen/v1';
        // $this->rest_base = 'b2b/orders';
        parent::__construct();
    }


    public function register_routes()
    {
        register_rest_route( $this->namespace, $this->rest_base . '/(?P<order_id>[\d]+)/history-orders', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'handler_request_history_order' ],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ],
        ] );

        register_rest_route( $this->namespace, $this->rest_base . '/transference-order', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'handler_request_order_receipt' ],
                'permission_callback' => [ Api_Talent_Check_Permission::class, 'check_permission' ],
                'args' => []
            ],
        ] );
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
                'd-f709cc4917c34dd5a0a59db3d1789d86',
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
     * Handler quando o usuário talento o comprovante de uma transferencia
     * @param WP_REST_Request
     * @return WP_REST_Response
     */
    public function handler_request_order_receipt(WP_REST_Request $request)
    {
        $user = wp_get_current_user();
        $user_polen = new Polen_User_Module($user->ID);
        $order_id = $request['order_id'];

        try{
            //TODO: Remover os hardcoded
            $this->send_email_request_history_order(
                'd-6e9dd8828d924f1196fba70bb66d37e3',
                'Financeiro',
                'financeiro@polen.me',
                $user->user_firstname,
                $user_polen->get_receiving_email(),
                date('d/m/Y'),
                $order_id
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
        $template_id,
		$name,
		$email,
		$talent_name,
        $talent_email,
        $date,
        $order_id = null,
        $payment_date = null)
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
        $send_grid->set_template_id($template_id);
        $send_grid->set_template_data( 'date', $date );
        $send_grid->set_template_data( 'talent_name', $talent_name );
        $send_grid->set_template_data( 'talent_email', $talent_email );
        $send_grid->set_template_data( 'talent_email', $talent_email );
        $send_grid->set_template_data( 'order_id', $order_id );
        $send_grid->set_template_data( 'payment_date', $payment_date );

        return $send_grid->send_email();
    }
}