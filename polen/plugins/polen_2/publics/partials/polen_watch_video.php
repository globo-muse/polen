<?php
defined( 'ABSPATH' ) || die;

use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Debug;
use \Polen\Includes\Polen_Video_Info;

global $current_user;

$video_info = Polen_Video_Info::get_by_hash( $video_hash );
$user_id = $current_user->ID;
$order = wc_get_order($video_info->order_id);
$order_user_id = $order->get_user_id();
$cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
$product = $cart_item->get_product();
$user_talent = get_user_by('ID',$video_info->talent_id);

if( empty( $video_info ) || $user_id !== $order_user_id) {
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit();
}

polen_set_fan_viewed( $order );

?>
    <main id="primary" class="site-main">
        <?php
        polen_get_video_player( $video_info, $product, $order, $user_talent );
        ?>
    </main><!-- #main -->