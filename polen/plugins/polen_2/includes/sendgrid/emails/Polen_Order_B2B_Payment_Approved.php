<?php

namespace Polen\Includes\Sendgrid\Emails;

use Polen\Includes\Debug;
use Polen\Includes\Module\Factory\Polen_Order_Module_Factory;
use Polen\Includes\Module\Orders\Polen_Module_B2B_Only;
use Polen\Includes\Polen_Order;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Emails;
use Polen\Includes\Sendgrid\Polen_Sendgrid_Redux;
use WC_Emails;

class Polen_Order_B2B_Payment_Approved
{
    protected $object;

    public function __construct($static = false)
    {
        if($static) {
            add_action( 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED, [ $this, 'trigger' ] );
            add_action( 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_COMPLETED, [ $this, 'trigger' ] );
        }
    }

    public function trigger($order_id, $order = null)
    {
        $this->object = $order;
        if(empty($this->object)) {
            $this->object = wc_get_order( $order_id );
        }
        $order_module = Polen_Order_Module_Factory::create_order_from_campaing($this->object);
        if(Polen_Module_B2B_Only::METAKEY_VALUE == $order_module->get_campaign_slug()) {
            $email_customer = $order_module->get_billing_email();
            $name_customer  = $order_module->get_billing_name();
            $instructions   = $order_module->get_instructions_to_video();
            $category       = $order_module->get_video_category();
            $qty            = '1';
            $total          = $order_module->get_total();
            $talent_name    = $order_module->get_talent_name();
            $address        = $order_module->get_billing_address_full();
            $company_name   = $order_module->get_company_name();
            $cnpj_cpf       = $order_module->get_billing_cnpj_cpf();
            $order_date     = $order_module->get_date_created();

            $retorno = $this->send_email(
                $email_customer,
                $name_customer,
                $company_name,
                $cnpj_cpf,
                $instructions,
                $qty,
                $total,
                $talent_name,
                $address,
                $order_date,
                $category
            );
        }
    }

    public function send_email(
        $email_customer,
        $name_customer,
        $company_name,
        $cnpj_cpf,
		$instructions,
		$qty,
        $total,
		$talent_name,
        $address,
        $order_date,
        $category)
	{

        global $Polen_Plugin_Settings;
        $apikeySendgrid = $Polen_Plugin_Settings[ Polen_Sendgrid_Redux::APIKEY ];
        $send_grid = new Polen_Sendgrid_Emails( $apikeySendgrid );
        $send_grid->set_from(
            $Polen_Plugin_Settings['polen_smtp_from_email'],
            $Polen_Plugin_Settings['polen_smtp_from_name']
        );
        $send_grid->set_to($email_customer, $name_customer);
        $send_grid->set_template_id( $Polen_Plugin_Settings[ Polen_Sendgrid_Redux::THEME_ID_POLEN_B2B_PAYMENT_APPROV ] );
        $send_grid->set_template_data( 'instructions', $instructions );
        $send_grid->set_template_data( 'qty', $qty );
        $send_grid->set_template_data( 'total', $total );
        $send_grid->set_template_data( 'talent_name', $talent_name );
        $send_grid->set_template_data( 'company_name', $company_name );
        $send_grid->set_template_data( 'cnpj_cpf', $cnpj_cpf );
        $send_grid->set_template_data( 'address', $address );
        $send_grid->set_template_data( 'order_date', $order_date );
        $send_grid->set_template_data( 'category', $category );

        return $send_grid->send_email();
    }
}