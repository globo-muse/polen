<?php

namespace Polen\Includes;

use Polen\Includes\Emails\Polen_WC_Completed_Order;
use Polen\Includes\Emails\Polen_WC_Customer_New_Account;
use Polen\Includes\Emails\Polen_WC_Pending;
use Polen\Includes\Emails\Polen_WC_Processing;
use Polen\Includes\Emails\Polen_WC_Video_Sended;

if( ! defined( 'ABSPATH' ) ) {
    die( 'Silence is golden.' );
}

class Polen_Emails {

    public function __construct( bool $static = false ) {
        if( $static ) {
            add_filter( 'woocommerce_email_classes', array( $this, 'register_emails' ), 99, 1 );
            add_filter( 'woocommerce_email_actions', array( $this, 'email_actions' ), 20, 1 );
        }
    }

    function email_actions( $actions ) 
    {
        $polen_woocommerce = new Polen_WooCommerce();
        $order_statuses = $polen_woocommerce->order_statuses;
        foreach ( $order_statuses as $order_status => $values ) 
        {
			$actions[] = 'woocommerce_order_status_' . $order_status;
		}
        $actions[] = 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED.'_to_'.Polen_Order::ORDER_STATUS_TALENT_REJECTED;
        $actions[] = 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED.'_to_'.Polen_Order::ORDER_STATUS_TALENT_ACCEPTED;
        $actions[] = 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_TALENT_ACCEPTED.'_to_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED;
        $actions[] = 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_TALENT_REJECTED.'_to_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED;
        $actions[] = 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_ORDER_EXPIRED.'_to_'.Polen_Order::ORDER_STATUS_PAYMENT_APPROVED;
        $actions[] = 'woocommerce_order_status_'.Polen_Order::ORDER_STATUS_TALENT_ACCEPTED.'_to_'.Polen_Order::ORDER_STATUS_COMPLETED;
        return $actions;
    }

    public function register_emails( $emails )
    {

        //Nova conta no checkout
        $emails[ 'WC_Email_Customer_New_Account' ] = new Polen_WC_Customer_New_Account();

        //Limpando as Actions
        remove_action( 'woocommerce_order_status_completed_notification', array( $emails[ 'WC_Email_Customer_Completed_Order' ], 'trigger' ), 10 );
        $emails[ 'WC_Email_Customer_Completed_Order' ] = new Polen_WC_Completed_Order();

        //Limpando as Actions
        // remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $emails['WC_Email_Customer_Processing_Order'], 'trigger' ), 10 );
        // remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $emails['WC_Email_Customer_Processing_Order'], 'trigger' ), 10 );
        // remove_action( 'woocommerce_order_status_cancelled_to_on-hold_notification', array( $emails['WC_Email_Customer_Processing_Order'], 'trigger' ), 10 );
        $emails['WC_Email_Customer_Processing_Order'] = new Polen_WC_Processing();
        
        $emails['Polen_WC_Pending'] = new Polen_WC_Pending();

		require_once PLUGIN_POLEN_DIR . '/includes/emails/Polen_WC_Payment_Approved.php';
		$emails['Polen_WC_Payment_Approved'] = new Polen_WC_Payment_Approved();

        require_once PLUGIN_POLEN_DIR . '/includes/emails/Polen_WC_Payment_In_Revision.php';
		$emails['Polen_WC_Payment_In_Revision'] = new Polen_WC_Payment_In_Revision();

        require_once PLUGIN_POLEN_DIR . '/includes/emails/Polen_WC_Payment_Rejected.php';
		$emails['Polen_WC_Payment_Rejected'] = new Polen_WC_Payment_Rejected();

        require_once PLUGIN_POLEN_DIR . '/includes/emails/Polen_WC_Talent_Accepted.php';
		$emails['Polen_WC_Talent_Accepted'] = new Polen_WC_Talent_Accepted();

        require_once PLUGIN_POLEN_DIR . '/includes/emails/Polen_WC_Talent_Rejected.php';
		$emails['Polen_WC_Talent_Rejected'] = new Polen_WC_Talent_Rejected();

        require_once PLUGIN_POLEN_DIR . '/includes/emails/Polen_WC_Order_Expired.php';
		$emails['Polen_WC_Order_Expired'] = new Polen_WC_Order_Expired();

        require_once PLUGIN_POLEN_DIR . '/includes/emails/Polen_WC_Video_Sended.php';
		$emails['Polen_WC_Video_Sended'] = new Polen_WC_Video_Sended();

		return $emails;
	}


    /**
     * 
     */
    static public function is_to_send_admin_edit_order()
    {
        if( !( defined('DOING_AJAX') ) ) {
            if( is_admin() ){
                $screen = get_current_screen();
                if ( $screen->base == 'post' && $screen->post_type == 'shop_order' ){
                    if( !isset( $_POST['send_email'] ) || $_POST['send_email'] != 'on' ) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

}