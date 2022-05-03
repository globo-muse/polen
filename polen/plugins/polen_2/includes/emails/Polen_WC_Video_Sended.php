<?php

namespace Polen\Includes\Emails;

use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Module\Polen_User_Module;
use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Utils;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Emails;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Redux;

if( ! defined( 'ABSPATH') ) {
    die( 'Silence is golden.' );
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

class Polen_WC_Video_Sended extends \WC_Email 
{

	/**
	 * Assunto do Email do talento
	 * String
	 */
	public $subject_social;

    public function __construct() {
        $this->id          = 'wc_payment_approved';
		$this->title       = __( 'Pagamento Aprovado', 'polen' );
		$this->description = __( 'E-mail que será enviado ao usuário quando o pagamento do pedido é aprovado.', 'polen' );
		$this->customer_email = true;
		$this->heading     = __( 'Pagamento Aprovado', 'polen' );

		add_action( 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_VIDEO_SENDED, [ $this, 'trigger' ] );
		
		parent::__construct();
    }

    public function trigger( $order_id ) {
		
		$this->object = wc_get_order( $order_id );
		if ( version_compare( '3.0.0', WC()->version, '>' ) ) {
			$order_email = $this->object->billing_email;
		} else {
			$order_email = $this->object->get_billing_email();
		}

		$this->recipient = $order_email;

		$cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $this->object );
		$this->product = $cart_item->get_product();

		//Pegando detalhes do orders para os emails do Sendgrid


        global $Polen_Plugin_Settings;
        //Pegando detalhes do orders para os emails do Sendgrid
        $order_module = new Polen_Order_Module( $this->object );
        $item  = Polen_Cart_Item_Factory::polen_cart_item_from_order( $this->object );
        $product = $item->get_product();
        $total = $this->object->get_total();
        $address = $order_module->get_billing_address_full();
        $cnpj_cpf = $order_module->get_billing_cnpj_cpf();
        $category = $item->get_video_category();
        $order_date = $order_module->get_date_created()->format('d/m/Y');
        $company_name = $order_module->get_billing_name();
        $talent_name = !empty($product) ? $product->get_title() : '';
        $qty = "1";
        $instructions = Polen_Utils::remove_sanitize_xss_br_escape($item->get_instructions_to_video());

        global $Polen_Plugin_Settings;
        $email_polen = $Polen_Plugin_Settings['recipient_email_polen_finance'];
        if(!empty($email_polen)) {
            $this->send_email_finance(
                'd-5f16b3d295da40e5be8855190a58eab2',
                'Financeiro Polen',
                $email_polen,
                $address,
                $cnpj_cpf,
                $category,
                $order_date,
                $company_name,
                $talent_name,
                $total,
                $qty,
                $instructions
            );
        }	
	}

	public function send_email_finance(
		$template_id,
		$name,
		$email,
        $address,
		$cnpj_cpf,
        $category,
        $order_date,
		$company_name,
		$talent_name,
		$total,
		$qty,
		$instructions )
	{

        global $Polen_Plugin_Settings;
        $apikeySendgrid = $Polen_Plugin_Settings[ Polen_Sendgrid_Redux::APIKEY ];
        $send_grid = new Polen_Sendgrid_Emails( $apikeySendgrid );
        $send_grid->set_from(
            $Polen_Plugin_Settings['polen_smtp_from_email'],
            $Polen_Plugin_Settings['polen_smtp_from_name']
        );
        $send_grid->set_to( $email, $name );
        $send_grid->set_template_id( $template_id );
        $send_grid->set_template_data( 'address', $address );
        $send_grid->set_template_data( 'cnpj_cpf', $cnpj_cpf );
        $send_grid->set_template_data( 'category', $category );
        $send_grid->set_template_data( 'order_date', $order_date );
        $send_grid->set_template_data( 'company_name', $company_name );
        $send_grid->set_template_data( 'talent_name', $talent_name );
        $send_grid->set_template_data( 'total', $total );
        $send_grid->set_template_data( 'qty', $qty );
        $send_grid->set_template_data( 'instructions', $instructions );

        return $send_grid->send_email();
    }
}
