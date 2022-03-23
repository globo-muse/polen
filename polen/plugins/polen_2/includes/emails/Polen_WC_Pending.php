<?php
namespace Polen\Includes\Emails;

use Polen\Includes\Polen_Campaign;
use WC_Email;

class Polen_WC_Pending extends WC_Email
{
    public function __construct()
    {
        $this->id             = 'customer_pending_order';
        $this->customer_email = true;

        $this->title          = __( 'Pending order', 'woocommerce' );
        $this->description    = __( 'This is an order notification sent to customers containing order details wating payment.', 'woocommerce' );
        $this->template_html  = 'emails/customer-pending-order.php';
        $this->campaign_template_html  = 'emails/campaign/%s/customer-pending-order.php';
        $this->template_plain = 'emails/plain/customer-pending-order.php';
        $this->placeholders   = array(
            '{order_date}'   => '',
            '{order_number}' => '',
        );

        // Triggers for this email.
        // add_action( 'woocommerce_order_status_cancelled_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
        // add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
        // add_action( 'woocommerce_order_status_on-hold_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
        // add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ), 10, 2 );

        add_action( 'woocommerce_order_status_changed', array( $this, 'trigger' ), 12, 1 );

        // Call parent constructor.
        parent::__construct();
    }
    

    public function trigger( $order_id )
    {
        $this->object = wc_get_order( $order_id );
		if( $this->object->has_status( 'pending') ) {
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
             * Não disparar email caso flag no_send_email estiver marcada
             */
            // if (is_admin() === true && get_post_meta($order_id, 'send_email', true) != 1) {
            //     return;
            // }

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
    }

    public function get_subject()
    {
        return 'Pedido Recebido';
    }

	public function get_content() {
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

    public function is_enabled()
    {
        return true;
    }

    public function get_recipient()
    {
        return $this->recipient;
    }
}