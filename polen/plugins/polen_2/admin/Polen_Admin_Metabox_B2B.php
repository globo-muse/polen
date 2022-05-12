<?php

namespace Polen\Admin;

defined( 'ABSPATH' ) || exit;

use Polen\Api\Api_Checkout;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Utils;
use Polen\Includes\Sendgrid\Emails\Polen_Order_B2B_Payment_Approved_Finance_Email;
use Polen\Includes\Sendgrid\Emails\Polen_Order_B2B_Payment_Approved_Finence_Email;
use WC_Order;
use WP_Error;

class Polen_Admin_Metabox_B2B
{
    protected string $path_file;

    public function __construct(bool $static = false)
    {
        $this->path_file = plugin_dir_path(__FILE__) . 'partials/metaboxes/b2b/';

        if ($static) {
            add_action('woocommerce_new_order',    [$this, 'new_order_handler'], 10000, 3);
            add_action('woocommerce_update_order', [$this, 'new_order_handler'], 10, 3);
            add_action('add_meta_boxes',           [$this, 'add_meta_box_handler']);
        }
    }

    /**
     * ADD metabox
     */
    public function add_meta_box_handler()
    {
        global $current_screen, $post;

        if (is_null($current_screen) || !isset( $current_screen->id)) {
            return null;
        }

        if ($current_screen->id == 'shop_order' && $post->b2b != '1' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit') {
            return null;
        }

        add_meta_box('add_other_fields_only_b2b', 'Informações gerais B2B', [$this, 'show_fields_b2b'], 'shop_order', 'normal', 'default');
    }

    /**
     * Gerar includes dos campos b2b
     */
    public function show_fields_b2b()
    {
        $files = array(
            'metabox-order-b2b-link.php',
            'metabox-order-b2b-fields.php',
            'metabox-order-b2b-fields-payment.php',
        );

        foreach ($files as $file) {
            $file_path = $this->path_file . $file;
            if (file_exists($file)) {
                error_log(sprintf(__('Erro ao incluir %s '), $file), E_USER_ERROR);
            }
            include_once "{$file_path}";
        }
    }

    /**
     * 
     */
    public function new_order_handler($order_id, WC_Order $order = null)
    {
        $screen = get_current_screen();
        if( is_admin() && $screen->base == 'post' && $screen->post_type == 'shop_order' && current_user_can('manage_options') ) {
            if(empty($order)) {
                $order = wc_get_order($order_id);
            }
            $cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
            if(empty($cart_item)) {
                $error = new WP_Error('no_order_item', 'Compra sem itens, não é possível');
                wp_die($error->get_error_message());
            }

            $item_order = $cart_item->get_item_order();
            if(empty($item_order)) {
                $error = new WP_Error('no_order_item', 'Compra sem itens, não é possível(2)');
                wp_die($error->get_error_message());
            }

            $installments          = !empty($_POST['installments']) ? sanitize_text_field($_POST['installments']) : 1;
            $company_name          = sanitize_text_field($_POST['company_name']);
            $corporate_name        = sanitize_text_field($_POST['corporate_name']);
            $cnpj                  = sanitize_text_field($_POST['cnpj']);
            $video_to              = sanitize_text_field($_POST['company_name']);
            $email_to_video        = sanitize_email($order->get_billing_email());
            $instructions_to_video = Polen_Utils::sanitize_xss_br_escape($_POST['instructions_to_video']);
            $video_category        = sanitize_text_field($_POST['video_category']);
            $licence_in_days       = sanitize_text_field($_POST['licence_in_days']);

            $form_of_payment       = sanitize_text_field($_POST['form_of_payment']); // forma de pagamento escolhida
            $value_payment_talent  = sanitize_text_field($_POST['value_payment_talent']);
            $payday                = sanitize_text_field($_POST['payday']);
            $video_url_b2b         = sanitize_text_field($_POST['video_url_b2b']);
            $company_location      = sanitize_text_field($_POST['company_location']);
            $qty_employees         = sanitize_text_field($_POST['qty_employees']);
            $company_size          = sanitize_text_field($_POST['company_size']);

            update_post_meta($order_id, 'b2b', '1');
            update_post_meta($order_id, Api_Checkout::ORDER_METAKEY, 'b2b');
            update_post_meta($order_id, '_billing_cnpj_cpf', $cnpj);
            update_post_meta($order_id, '_billing_corporate_name', $corporate_name);
            update_post_meta($order_id, '_billing_company', $company_name);

            wc_update_order_item_meta($item_order->get_id(), 'company_name', $company_name, true);
            wc_update_order_item_meta($item_order->get_id(), 'installments', $installments, true);
            wc_update_order_item_meta($item_order->get_id(), 'video_to', $video_to, true);
            wc_update_order_item_meta($item_order->get_id(), 'email_to_video', $email_to_video, true);
            wc_update_order_item_meta($item_order->get_id(), 'instructions_to_video', $instructions_to_video, true);
            wc_update_order_item_meta($item_order->get_id(), 'video_category', $video_category, true);
            wc_update_order_item_meta($item_order->get_id(), 'licence_in_days', $licence_in_days, true);


            wc_update_order_item_meta($item_order->get_id(), 'video_url_b2b', $video_url_b2b, true);
            wc_update_order_item_meta($item_order->get_id(), 'company_location', $company_location, true);
            wc_update_order_item_meta($item_order->get_id(), 'qty_employees', $qty_employees, true);
            wc_update_order_item_meta($item_order->get_id(), 'company_size', $company_size, true);

            # ADD
            wc_update_order_item_meta($item_order->get_id(), 'form_of_payment', $form_of_payment);
            wc_update_order_item_meta($item_order->get_id(), 'value_payment_talent', $value_payment_talent);
            wc_update_order_item_meta($item_order->get_id(), 'payday', $payday);

            remove_action('woocommerce_new_order',    [$this, 'new_order_handler'], 10);
            remove_action('woocommerce_update_order', [$this, 'new_order_handler'], 10);
            
            $post = get_post($order_id);
            if(empty($post->post_password)) {
                wp_update_post(['id'=>$order_id,'post_password'=>wc_generate_order_key()]);
            }

            if(Polen_Order::ORDER_STATUS_PAYMENT_APPROVED_INSIDE == filter_input(INPUT_POST,'order_status')) {
                $email = WC()->mailer()->get_emails()['Polen_WC_Payment_Approved'];
            }

            if(Polen_Order::ORDER_STATUS_VIDEO_SENDED_INSIDE == filter_input(INPUT_POST,'order_status')) {
                $email = WC()->mailer()->get_emails()['Polen_WC_Payment_Approved'];
                (new Polen_Order_B2B_Payment_Approved_Finance_Email(new Polen_Order_Module($order)))->send_email();
            }
        }
    }
}
