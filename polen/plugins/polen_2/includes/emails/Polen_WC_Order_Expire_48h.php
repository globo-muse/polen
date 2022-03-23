<?php

namespace Polen\Includes\Emails;

use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Talent;

if( ! defined( 'ABSPATH') ) {
    die( 'Silence is golden.' );
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

class Polen_WC_Order_Expire_48h extends \WC_Email {

    
    public function __construct() {
        $this->id          = 'wc_order_expire_Tomorrow';
		$this->title       = __( 'Pedidos expiram amanha!', 'polen' );
		$this->description = __( 'Esse email é enviado para nosso operacional', 'polen' );
		$this->customer_email = false;
		$this->heading     = __( 'Pedidos que expiram em 48h', 'polen' );

		$this->subject     = 'Lista de pedidos que expiram amanhã';;

		$this->template_html  = 'emails/Polen_WC_Order_Expire_48h.php';
		$this->template_plain = 'emails/plain/Polen_WC_Order_Expire_48h.php';
		$this->template_base  = TEMPLATEPATH . 'woocommerce/';

		parent::__construct();
    }

    public function trigger( $txt ) {
		$this->object = $txt;

		$emails = $this->get_emails();
		foreach( $emails as $email ){
			$this->recipient = $email;
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
		$this->restore_locale();
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

	public function get_emails_redux()
	{
		global $Polen_Plugin_Settings;
		$emails = $Polen_Plugin_Settings[ 'email_emails_order_expire_tomorrow' ];
		return $emails;
	}

	public function explode_emails( $emails )
	{
		$separetad_emails = explode( ',', $emails );
		return $separetad_emails;
	}

	public function get_emails()
	{
		$emails = $this->get_emails_redux();
		$emails_separated = $this->explode_emails( $emails );
		return $emails_separated;
	}
}
