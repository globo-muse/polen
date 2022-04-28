<?php
namespace Polen\Includes\Emails;

use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Campaign;
use Polen\Includes\Polen_Emails;
use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Utils;
use Polen\Includes\Polen_Video_Info;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Emails;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Redux;
use Polen\Social_Base\Social_Base_Order;

class Polen_WC_Completed_Order extends \WC_Email_Customer_Completed_Order
{
    public function __construct() {
        $this->id             = 'customer_completed_order';
        $this->customer_email = true;
        $this->title          = __( 'Completed order', 'woocommerce' );
        $this->description    = __( 'Order complete emails are sent to customers when their orders are marked completed and usually indicate that their orders have been shipped.', 'woocommerce' );
        $this->template_html  = 'emails/customer-completed-order.php';
        $this->template_plain = 'emails/plain/customer-completed-order.php';
        
        $this->template_ep_html          = 'emails/video-autografo/%s/customer-completed-order.php';
        $this->template_social_base_html = 'emails/social-base/%s/customer-completed-order.php';

        $this->placeholders   = array(
            '{order_date}'   => '',
            '{order_number}' => '',
        );
        $this->template_base  = TEMPLATEPATH . '/woocommerce/';
        // Triggers for this email.
        add_action( 'woocommerce_order_status_completed_notification', array( $this, 'trigger' ), 10, 2 );

        // Call parent constructor.
        parent::__construct();
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int            $order_id The order ID.
     * @param WC_Order|false $order Order object.
     */
    public function trigger( $order_id, $order = false ) {
        $this->setup_locale();

        if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order_id );
        }

        if ( is_a( $order, 'WC_Order' ) ) {
            $this->object                         = $order;
            $this->recipient                      = $this->object->get_billing_email();
            $this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
            $this->placeholders['{order_number}'] = $this->object->get_order_number();

            $this->campaign_template_html  = 'emails/campaign/%s/customer-completed-order.php';
        } else {
            return;
        }
        global $Polen_Plugin_Settings;
        //Pegando detalhes do orders para os emails do Sendgrid
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

        // $email_polen = $Polen_Plugin_Settings['recipient_email_polen_finance'];
        // if(!empty($email_polen)) {
        //     $this->send_email_finance(
        //         'd-5f16b3d295da40e5be8855190a58eab2',
        //         'Financeiro Polen',
        //         $email_polen,
        //         $address,
        //         $cnpj_cpf,
        //         $category,
        //         $order_date,
        //         $company_name,
        //         $talent_name,
        //         $total,
        //         $qty,
        //         $instructions
        //     );
        // }

        if( !Polen_Emails::is_to_send_admin_edit_order() ){
            return;
        }

        $cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $this->object );
        $this->product = $cart_item->get_product();

        send_zapier_by_change_status($this->object);
        $order_is_campaing = Polen_Campaign::get_is_order_campaing( $this->object );
        if( $order_is_campaing ) {
            $this->send( $this->get_recipient(), $this->get_subject_galo(), $this->get_content_campaign(), $this->get_headers(), $this->get_attachments() );
        } else {
            // $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
            $order_module = new Polen_Order_Module( $this->object );
            $customer_name = $order_module->get_offered_by();
            $order_url = $order_module->get_view_order_url();
            $this->send_email(
                $Polen_Plugin_Settings[ Polen_Sendgrid_Redux::THEME_ID_POLEN_ORDER_COMPLETED ],
                $customer_name,
                $this->get_recipient(),
                $order_id,
                $order_url
            );
        }

        $this->restore_locale();
    }


    /**
     * Enviar email Via SendGrid API
     */
	public function send_email(
        $template_id,
		$name_customer,
		$email_customer,
		$order_id,
        $order_url )
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
        $send_grid->set_template_data( 'order_url', $order_url );

        return $send_grid->send_email();
    }


    // {
    //     "instructions":"asdasdasd",
    //     "qty":"1",
    //     "total":"700.00",
    //     "talent_name":"adadasdadasd",
    //     "company_name":"asdasdasdadasd",
    //     "cnpj_cpf":"234234234234243",
    //     "address":"kjsdhfkajdfhakdjfhakdjfhakdfhakjdhf",
    //     "order_date":"10/10/1000",
    //     "category": "dasdasd"
        
    // }
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



    public function get_subject_galo()
    {
        return 'Seu pedido no Galo Ídolos foi concluído';
    }

    public function get_content_campaign()
    {
         $polen_order = Polen_Campaign::get_order_campaing_slug( $this->object );
         $file_templete = sprintf( $this->campaign_template_html, $polen_order );
         return wc_get_template_html(
             $file_templete,
             array(
                 'order'              => $this->object,
                 'email_heading'      => $this->get_heading(),
                 'additional_content' => $this->get_additional_content(),
                 'sent_to_admin'      => false,
                 'plain_text'         => false,
                 'email'              => $this,
             )
         );
    }

//    public function get_subject_ep() {
//        return 'Lacta - Seu vídeo chegou!';
//    }

//    public function get_subject_social_base() {
//        return 'Sua compra na Reserva veio com um 🎁!';
//    }

//    public function get_content_ep()
//    {
//         $polen_order = new Polen_Order_Module( $this->object );
//         $file_templete = sprintf( $this->template_ep_html, $polen_order->get_campaign_slug() );
//         return wc_get_template_html(
//             $file_templete,
//             array(
//                 'order'              => $this->object,
//                 'email_heading'      => $this->get_heading(),
//                 'additional_content' => $this->get_additional_content(),
//                 'sent_to_admin'      => false,
//                 'plain_text'         => false,
//                 'email'              => $this,
//             )
//         );
//    }

   public function get_content_social_base()
   {

        $video_info = Polen_Video_Info::get_by_order_id( $this->object->get_id() );
        $video_url = site_url( 'v/' . $video_info->hash );
        $social_campaign_name = $this->object->get_meta( Social_Base_Order::ORDER_META_KEY_campaign, true );
        return wc_get_template_html(
            sprintf( $this->template_social_base_html, $social_campaign_name ),
            array(
                'order'              => $this->object,
                'video_url'          => $video_url,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => false,
                'email'              => $this,
            )
        );
   }
}