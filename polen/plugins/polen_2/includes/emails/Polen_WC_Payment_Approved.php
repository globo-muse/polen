<?php

namespace Polen\Includes;

use DateTime;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Module\Polen_User_Module;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Emails;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Redux;
use WC_DateTime;

if( ! defined( 'ABSPATH') ) {
    die( 'Silence is golden.' );
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

class Polen_WC_Payment_Approved extends \WC_Email {

	/**
	 * Email do talento
	 * String
	 */
	public $recipient_talent;

	/**
	 * Assunto do Email do talento
	 * String
	 */
	public $subject_talent;

	private $social_template_html;
	private $social_template_plain;

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
		$this->heading_ep     = 'Pedido de vídeo recebido';
		$this->heading_talent_social = __( 'Doação recebida', 'polen' );

		$this->subject     = sprintf( _x( '[%s] Pagamento Aprovado', 'E-mail que será enviado ao usuário quando o pagamento do pedido é aprovado', 'polen' ), '{blogname}' );
    
		$this->talent_template_html  = 'emails/Polen_WC_Payment_Approved_Talent.php';
		$this->talent_template_plain = 'emails/plain/Polen_WC_Payment_Approved_Talent.php';
		$this->template_html  = 'emails/Polen_WC_Payment_Approved.php';
		$this->template_plain = 'emails/plain/Polen_WC_Payment_Approved.php';
		$this->template_base  = TEMPLATEPATH . '/woocommerce/';

		$this->campaign_template_html  = 'emails/campaign/%s/Polen_WC_Payment_Approved.php';
    
		add_action( 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED, [ $this, 'trigger' ] );
		add_action( 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_TALENT_ACCEPTED.'_to_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED.'_notification', [$this, 'trigger'] );
        add_action( 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_TALENT_REJECTED.'_to_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED.'_notification', [$this, 'trigger'] );
        add_action( 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_ORDER_EXPIRED.'_to_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED.'_notification', [$this, 'trigger'] );
		parent::__construct();
    }

    public function trigger( $order_id ) {
		
		$this->object = wc_get_order( $order_id );
		// if( get_post_status($order_id) == Polen_Order::ORDER_STATUS_PAYMENT_APPROVED_INSIDE ) {
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
			$order_module = new Polen_Order_Module( $this->object );
			$customer_name = $order_module->get_offered_by();
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

			$emails_polen = $Polen_Plugin_Settings['recipient_email_polen_help'];
			if(!empty($emails_polen)) {
				$emails_polen = explode(',', $emails_polen);
				foreach($emails_polen as $email_polen) {
					if(!empty($email_polen)) {
						$this->send_email_user_support(
							'd-b0693ad99b2042528fd462a5da48f503',
							'Equipe Polen',
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
			}
			
			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

            /**
			 * Não disparar email caso flag no_send_email estiver marcada
             */
			if( !Polen_Emails::is_to_send_admin_edit_order() ){
				return;
			}

			$order_is_campaing = Polen_Campaign::get_is_order_campaing( $this->object );
			if( $order_is_campaing ) {
				$this->send( $this->get_recipient(), $this->get_subject_campaign(), $this->get_content_campaign(), $this->get_headers(), $this->get_attachments() );
			} else {
				// $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
				$order_module = new Polen_Order_Module( $this->object );
				$customer_name = $order_module->get_offered_by();
				$item  = Polen_Cart_Item_Factory::polen_cart_item_from_order( $this->object );
				$total = $this->object->get_total();
				$to_name = $order_module->get_name_to_video();
				$deadline = $order_module->get_deadline();
				$deadline_date = \WC_DateTime::createFromFormat('U', $deadline);
				$deadline = !empty($deadline_date) ? $deadline_date->format('d/m/Y') : '';
				$instructions = Polen_Utils::remove_sanitize_xss_br_escape($item->get_instructions_to_video());
				$this->send_email(
					$Polen_Plugin_Settings[ Polen_Sendgrid_Redux::THEME_ID_POLEN_PAYMENT_APPROVED ],
					$customer_name,
					$this->get_recipient(),
					$order_id,
					$total,
					$deadline,
					$to_name,
					$item->get_video_category(),
					$instructions
				);
			}

			/**
			 * Envio de e-mail para o Talento
			 */
            foreach ( $this->object->get_items() as $item_id => $item ) {
                $product_id = $item->get_product_id();
            }

            $talent_user = Polen_User_Module::create_from_product_id($product_id);
            $talent_email = $talent_user->get_receiving_email();
            $this->recipient_talent = $talent_email;

            // if( ! $order_is_social ) {
            $this->send( $this->get_recipient_talent(), $this->get_subject_talent(), $this->get_content_talent(), $this->get_headers(), $this->get_attachments() );
            // } else {
            // 	$this->send( $this->get_recipient_talent(), $this->get_subject_talent_social(), $this->get_content_talent_social(), $this->get_headers(), $this->get_attachments() );
            // }
		// }
	}


	public function send_email(
		$template_id,
		$name_customer,
		$email_customer,
		$order_id,
		$order_value,
		$deadline,
		$to_name,
		$occasion,
		$instructions )
	{

        global $Polen_Plugin_Settings;
        $apikeySendgrid = $Polen_Plugin_Settings[ Polen_Sendgrid_Redux::APIKEY ];
        $send_grid = new Polen_Sendgrid_Emails( $apikeySendgrid );
        $send_grid->set_from(
            $Polen_Plugin_Settings['polen_smtp_from_email'],
            $Polen_Plugin_Settings['polen_smtp_from_name']
        );
        $send_grid->set_to( $email_customer, $name_customer );
        $send_grid->set_template_id( $template_id );
        $send_grid->set_template_data( 'customer_name', $name_customer );
        $send_grid->set_template_data( 'order_id', $order_id );
        $send_grid->set_template_data( 'order_value', $order_value );
        $send_grid->set_template_data( 'deadline', $deadline );
        $send_grid->set_template_data( 'to_name', $to_name );
        $send_grid->set_template_data( 'occasion', $occasion );
        $send_grid->set_template_data( 'instructions', $instructions );

        return $send_grid->send_email();
    }



	public function send_email_user_support(
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



	public function get_recipient_talent()
	{
		return $this->recipient_talent;
	}

	public function get_subject_talent()
	{
		return $this->subject_talent;
	}


	public function get_subject_campaign()
	{
		return 'Pagamento Aprovado';
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

	public function get_content_campaign()
	{
		$email_content = $this->get_content_campaign_html();
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

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_heading_social() {
		return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->get_option( 'heading', $this->get_default_heading_social() ) ), $this->object, $this );
	}

	/**
	 * Get email heading.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	public function get_default_heading_social() {
		return $this->heading_talent_social;
	}
    
}
