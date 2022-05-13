<?php
namespace Polen\Includes\Sendgrid\Emails;

use Exception;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Utils;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Emails;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Redux;

class Polen_Order_B2B_Payment_Approved_Finance_Email
{

    protected Polen_Order_Module $object;
    protected $template_id;
    protected $template_data;
    
    protected $to_email;
    protected $to_name;

    protected $from_email;
    protected $from_name;

    public function __construct(Polen_Order_Module $order_module)
    {
        $this->object = $order_module;
        $this->template_id = 'd-5f16b3d295da40e5be8855190a58eab2';

        $this->create_recipient();
        $this->create_data();
    }

    protected function create_recipient()
    {
        global $Polen_Plugin_Settings;
        $emails_polen = $Polen_Plugin_Settings['recipient_email_polen_help'];
        $this->to_email = $emails_polen;
        $this->to_name = "Equipe Polen";
    }

    protected function create_data()
    {
        $item  = Polen_Cart_Item_Factory::polen_cart_item_from_order( $this->object );
        $product = $item->get_product();

        $this->template_data['customer_name'] = $this->object->get_billing_first_name();
        $this->template_data['total'] = $this->object->get_total();
        $this->template_data['address'] = $this->object->get_billing_address_full();
        $this->template_data['cnpj_cpf'] = $this->object->get_billing_cnpj_cpf();
        $this->template_data['category'] = $item->get_video_category();
        $this->template_data['order_date'] = $this->object->get_date_created()->format('d/m/Y');
        $this->template_data['company_name'] = $this->object->get_billing_name();
        $this->template_data['talent_name'] = !empty($product) ? $product->get_title() : '';
        $this->template_data['qty'] = "1";
        $this->template_data['instructions'] = Polen_Utils::remove_sanitize_xss_br_escape($item->get_instructions_to_video());
    }

    public function send_email()
	{
        global $Polen_Plugin_Settings;
        $apikeySendgrid = $Polen_Plugin_Settings[Polen_Sendgrid_Redux::APIKEY];
        $send_grid = new Polen_Sendgrid_Emails($apikeySendgrid);
        $send_grid->set_from(
            $Polen_Plugin_Settings['polen_smtp_from_email'],
            $Polen_Plugin_Settings['polen_smtp_from_name']
        );
        $send_grid->set_to($this->to_email, $this->to_name);
        $send_grid->set_template_id($this->template_id);

        foreach($this->template_data as $key => $data) {
            $send_grid->set_template_data($key, $data);
        }

        $response_rended = $send_grid->send_email();
        if(299 < $response_rended->statusCode()) {
            throw new Exception($response_rended->body());
        }
    }
}
