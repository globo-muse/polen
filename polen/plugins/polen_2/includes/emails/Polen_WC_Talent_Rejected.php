<?php

namespace Polen\Includes;

use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Emails;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Redux;

if( ! defined( 'ABSPATH') ) {
    die( 'Silence is golden.' );
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

class Polen_WC_Talent_Rejected extends \WC_Email {

    public function __construct() {
        $this->id          = 'wc_talent_rejected';
		$this->title       = __( 'O talento rejeitou', 'polen' );
		$this->description = __( 'E-mail que será enviado ao usuário quando o talento rejeitar o pedido.', 'polen' );
		$this->customer_email = true;
		$this->heading     = __( 'O talento rejeitou', 'polen' );

		$this->subject     = sprintf( _x( '[%s] O talento rejeitou', 'E-mail que será enviado ao usuário quando o talento rejeitar o pedido.', 'polen' ), '{blogname}' );
    
		$this->template_html  = 'emails/Polen_WC_Talent_Rejected.php';
		$this->template_plain = 'emails/plain/Polen_WC_Talent_Rejected.php';
		$this->template_base  = TEMPLATEPATH . 'woocommerce/';

		$this->campaign_template_html = 'emails/campaign/%s/Polen_WC_Talent_Rejected.php';
    
		// add_action( 'woocommerce_order_status_changed', array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED.'_to_'.Polen_Order::ORDER_STATUS_TALENT_REJECTED.'_notification', [$this, 'trigger']);
		
		parent::__construct();
    }

	public function trigger( $order_id ) {
		$this->object = wc_get_order( $order_id );
		if( $this->object->get_status() === Polen_WooCommerce::ORDER_STATUS_TALENT_REJECTED ) {
            send_zapier_by_change_status($this->object);
			if ( version_compare( '3.0.0', WC()->version, '>' ) ) {
				$order_email = $this->object->billing_email;
			} else {
				$order_email = $this->object->get_billing_email();
			}

			$this->recipient = $order_email;

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			if( !Polen_Emails::is_to_send_admin_edit_order() ){
				return;
			}
			
			$order_is_campaing = Polen_Campaign::get_is_order_campaing( $this->object );
			if( $order_is_campaing ) {
				$this->send( $this->get_recipient(), $this->get_subject_campaing(), $this->get_content_campaign_html(), $this->get_headers(), $this->get_attachments() );
			} else {
				// $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

				$cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $this->object );
				$this->product = $cart_item->get_product();

				$name_customer  = $cart_item->get_offered_by();
				$email_customer = $this->get_recipient();
				$talent_name    = $this->product->get_title();
				$shop_link      = get_permalink( wc_get_page_id( 'shop' ) );
			
				$this->send_email(
					$name_customer,
					$email_customer,
					$talent_name,
					$order_id,
					$shop_link,
				);
			}
		}
	}



	public function send_email( $name_customer, $email_customer, $talent_name, $order_id, $shop_link )
	{
		global $Polen_Plugin_Settings;
		$apikeySendgrid = $Polen_Plugin_Settings[ Polen_Sendgrid_Redux::APIKEY ];
		$send_grid = new Polen_Sendgrid_Emails( $apikeySendgrid );
		$send_grid->set_from(
			$Polen_Plugin_Settings['polen_smtp_from_email'],
			$Polen_Plugin_Settings['polen_smtp_from_name']
		);
		$send_grid->set_to( $email_customer, $name_customer );
		$send_grid->set_template_id( $Polen_Plugin_Settings[ Polen_Sendgrid_Redux::THEME_ID_POLEN_TALENT_REJECT ] );
		$send_grid->set_template_data( 'customer_name', $name_customer );
		$send_grid->set_template_data( 'order_id', $order_id );
		$send_grid->set_template_data( 'talent_name', $talent_name );
		$send_grid->set_template_data( 'shop_link', $shop_link );

		return $send_grid->send_email();
	}




	public function get_subject_campaing()
	{
		return 'O talento não aceitou o seu pedido';
	}


    public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false,
			'email'			=> $this
		), '', $this->template_base );
	}

    public function get_content_campaign_html() {
		$slug_campaign = Polen_Campaign::get_order_campaing_slug( $this->object );
		$file_templete = sprintf( $this->campaign_template_html, $slug_campaign );
		return wc_get_template_html( $file_templete, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false,
			'email'			=> $this
		), '', $this->template_base );
	}

	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => true,
			'email'			=> $this
		), '', $this->template_base );
	}
    
}