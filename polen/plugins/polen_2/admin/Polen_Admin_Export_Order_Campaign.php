<?php
namespace Polen\Admin;

ABSPATH ?? die;

use Polen\Api\Api_Checkout;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use WC_Order;
use WP_Query;

class Polen_Admin_Export_Order_Campaign
{

    public function __construct( $static = false )
    {
        if( $static ) {
            add_action( 'wp_ajax_order-by-campaign', [ $this, 'ajax_handler' ] );
        }
    }

    public function ajax_handler()
    {
        if( !current_user_can( 'administrator' ) ) {
            die('Silence is golden');
        }
        $campaign = $_GET[ 'campaign' ];
        if( empty( $campaign ) ) {
            echo 'Campaign can not be null';
            wp_die();
        }
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
    
        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
    
        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$campaign}.csv");
        header("Content-Transfer-Encoding: binary");

        $ids = $this->process_query_get_ids( $campaign );
        $orders_infos = $this->process_query_get_orders_info( $ids );
        $content = $this->create_file_body( $orders_infos );
        echo $content;
        wp_die();
    }


    public function create_file_body( $orders_info )
    {
        if ( count( $orders_info ) == 0 ) {
            return null;
          }
          ob_start();
          $df = fopen( "php://output", 'w' );
          fputcsv( $df, array_keys( reset( $orders_info ) ) );
          foreach ( $orders_info as $row ) {
             fputcsv( $df, $row );
          }
          fclose($df);
          return ob_get_clean();
    }

    public function process_query_get_ids( $campaign )
    {
        $args = [
            'fields' => 'ids',
            'order_by' => 'id',
            'order' => 'DESC',
            'post_type' => wc_get_order_types(),
            'nopaging' => true,
            'post_status' => array_keys( wc_get_order_statuses() ),
            'meta_query' => [
                [
                    'key' => Api_Checkout::ORDER_METAKEY,
                    'value' => $campaign,
                    'compare' => '=',
                ],
            ]
        ];
        
        $q = new WP_Query( $args );
        $ids = $q->get_posts();
        return $ids;
    }

    public function process_query_get_orders_info( $ids )
    {
        $return  = [];
        foreach( $ids as $id ) {
            $order = new WC_Order( $id );
        
            $ic = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
            $product = $ic->get_product();
        
            $user = $order->get_user();
        
            $item = [];
            $item[ 'date_create' ] = $order->get_date_created()->format('d/m/Y H:i:s');
            $item[ 'status' ] = wc_get_order_status_name( $order->get_status() );
            $item[ 'product' ] = $product->get_title();
            $cat_ids = $product->get_category_ids();
        
            $item[ 'categories' ] = '';
            foreach( $cat_ids as $cat_id ) {
                $term = get_term_by( 'id', $cat_id, 'product_cat' );
                $item[ 'categories' ] .= $term->name . ' ';
            }
            $item[ 'instructions' ] = $ic->get_instructions_to_video();
            $item[ 'value_total' ] = $order->get_total();
            $item[ 'cupom' ] = implode( ', ', $order->get_coupon_codes() );
            
            $item[ 'user_name' ] = $user->display_name;
            $item[ 'user_email' ] = $user->user_email;
            $item[ 'user_phone' ] = $order->get_billing_phone();
        
            $item[ 'payment_method' ] = $order->get_payment_method();
            $item[ 'payment_type' ] = $order->get_payment_method_title();
        
            $return[] = $item;
        }

        return $return;
    }

}