<?php

namespace Polen\Includes\Emails;

use Polen\Includes\Module\Polen_User_Module;
use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Talent;

if( ! defined( 'ABSPATH') ) {
    die( 'Silence is golden.' );
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

class Polen_WC_Order_Expire_Today extends \WC_Email {
    
    public function __construct() {
        $this->id          = 'wc_order_expire_today';
		$this->title       = __( 'Seu pedido expira hoje!', 'polen' );
		$this->description = __( 'E-mail que será enviado ao usuário e talento quando um pedido expira em 24h.', 'polen' );
		$this->customer_email = true;
		$this->heading     = __( 'Seu pedido expira hoje!', 'polen' );

		$this->subject     = sprintf( _x( '[%s] Seu pedido expira hoje!', 'E-mail enviado aos usuários quando um pedido vai expirar em 24h', 'polen' ), '{blogname}' );
    
		$this->talent_template_html  = 'emails/Polen_WC_Order_Expire_Today_Talent.php';
		$this->talent_template_plain = 'emails/plain/Polen_WC_Order_Expire_Today_Talent.php';
		$this->template_html  = 'emails/Polen_WC_Order_Expire_Today.php';
		$this->template_plain = 'emails/plain/Polen_WC_Order_Expire_Today.php';
		$this->template_base  = TEMPLATEPATH . 'woocommerce/';

		parent::__construct();
    }

    public function trigger( $order_id ) {
		$this->object = wc_get_order( $order_id );
		if( $this->object->has_status( Polen_Order::ORDER_STATUSES_NEED_TALENT_ACTION ) ) {
			if ( version_compare( '3.0.0', WC()->version, '>' ) ) {
				$order_email = $this->object->billing_email;
			} else {
				$order_email = $this->object->get_billing_email();
			}

			$this->recipient = $order_email;

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			/**
			 * Envio de e-mail para o Fã
			 */
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			/**
			 * Envio de e-mail para o Talento
			 */
			foreach ( $this->object->get_items() as $item_id => $item ) {
				$product_id = $item->get_product_id();
			}
			// $Polen_Talent = new Polen_Talent();
			$user_polen = Polen_User_Module::create_from_product_id($product_id);
			// $talent = $Polen_Talent->get_talent_from_product( $product_id );
			$this->send( $user_polen->get_receiving_email(), $this->get_subject(), $this->get_content_talent(), $this->get_headers(), $this->get_attachments() );
		}
	}

	public function get_content_talent() {
		$this->sending = true;

		if ( 'plain' === $this->get_email_type() ) {
			$email_content = wordwrap( preg_replace( $this->plain_search, $this->plain_replace, wp_strip_all_tags( $this->get_content_talent_plain() ) ), 70 );
		} else {
			$email_content = $this->get_content_talent_html();
		}

		return $email_content;
	}

    public function get_content_talent_html() {
		return wc_get_template_html( $this->talent_template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false,
			'email'			=> $this
		), '', $this->template_base );
	}

	public function get_content_talent_plain() {
		return wc_get_template_html( $this->talent_template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => true,
			'email'			=> $this
		), '', $this->template_base );
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
