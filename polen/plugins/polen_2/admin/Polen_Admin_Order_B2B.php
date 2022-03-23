<?php

namespace Polen\Admin;

defined( 'ABSPATH' ) || exit;

use Polen\Api\Api_Checkout;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Polen_Talent;
use Polen\Includes\Polen_Utils;
use WC_Order;
use WP_Error;

class Polen_Admin_Order_B2B
{
    public function __construct(bool $static = false)
    {
        if($static) {
            add_action('woocommerce_new_order',    [$this, 'new_order_handler'], 10000, 3);
            add_action('woocommerce_update_order', [$this, 'new_order_handler'], 10, 3);
            add_action('add_meta_boxes',           [$this, 'add_meta_box_handler']);
        }
    }

    /**
     * 
     */
    public function add_meta_box_handler()
    {
        global $current_screen, $post;
        if( $current_screen 
            && ! is_null( $current_screen ) 
            && isset( $current_screen->id )
            && $current_screen->id == 'shop_order' 
            // && isset( $_REQUEST['action'] ) 
            // && $_REQUEST['action'] == 'edit'
        )
        {
            
            if(!empty($_GET['post']) && 'edit' == $_GET['action']) {
                if($post->b2b == '1') {
                    add_meta_box('Polen_order_b2b_link', 'Link da Order B2B', [$this, 'show_link_order_b2b'], 'shop_order', 'normal', 'default');
                    add_meta_box('Polen_order_b2b_fields', 'Campos B2B', [$this, 'add_other_fields_for'], 'shop_order', 'normal', 'default');
                }
            } else {
                add_meta_box('Polen_order_b2b_fields', 'Campos B2B', [$this, 'add_other_fields_for'], 'shop_order', 'normal', 'default');
            }
        }
    }

    /**
     * 
     */
    public function show_link_order_b2b()
    {
        global $post;
        //TODO: Tirar o LINK do hardcode e colocar num lugar apropriado ex. Redux
        echo printf('https://pagamento.polen.me?order=%s&code=%s', $post->ID, $post->post_password);
    }


    /**
     * 
     */
    public function add_other_fields_for()
    {
        $file = plugin_dir_path(__FILE__) . 'partials/metaboxes/metabox-order-b2b-fields.php';
        if(file_exists($file)) {
            require_once $file;
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

            $company_name          = sanitize_text_field($_POST['company_name']);
            $corporate_name        = sanitize_text_field($_POST['corporate_name']);
            $cnpj                  = sanitize_text_field($_POST['cnpj']);
            $video_to              = sanitize_text_field($_POST['company_name']);
            $email_to_video        = sanitize_email($order->get_billing_email());
            $instructions_to_video = Polen_Utils::sanitize_xss_br_escape($_POST['instructions_to_video']);
            $video_category        = sanitize_text_field($_POST['video_category']);
            $licence_in_days       = sanitize_text_field($_POST['licence_in_days']);
            
            update_post_meta($order_id, 'b2b', '1');
            update_post_meta($order_id, Api_Checkout::ORDER_METAKEY, 'b2b');
            update_post_meta($order_id, '_billing_cnpj', $cnpj);
            update_post_meta($order_id, '_billing_corporate_name', $corporate_name);
            update_post_meta($order_id, '_billing_company', $company_name);

            wc_update_order_item_meta($item_order->get_id(), 'company_name', $company_name, true);
            wc_update_order_item_meta($item_order->get_id(), 'video_to', $video_to, true);
            wc_update_order_item_meta($item_order->get_id(), 'email_to_video', $email_to_video, true);
            wc_update_order_item_meta($item_order->get_id(), 'instructions_to_video', $instructions_to_video, true);
            wc_update_order_item_meta($item_order->get_id(), 'video_category', $video_category, true);
            wc_update_order_item_meta($item_order->get_id(), 'licence_in_days', $licence_in_days, true);

            remove_action('woocommerce_new_order',    [$this, 'new_order_handler'], 10);
            remove_action('woocommerce_update_order', [$this, 'new_order_handler'], 10);
            
            $post = get_post($order_id);
            if(empty($post->post_password)) {
                wp_update_post(['id'=>$order_id,'post_password'=>wc_generate_order_key()]);
            }
        }
    }
}
