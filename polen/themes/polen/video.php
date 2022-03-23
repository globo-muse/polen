<?php
get_header();

use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use \Polen\Includes\Polen_Video_Info;

$video_info = Polen_Video_Info::get_by_hash( $video_hash );

if( empty( $video_info ) ) {
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit();
}

$order = wc_get_order($video_info->order_id);
$cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
$product = $cart_item->get_product();
$user_talent = get_user_by( 'ID', $video_info->talent_id );

?>
    <main id="primary" class="site-main">
        <?php
        polen_get_video_player( $video_info, $product, $order, $user_talent );
        ?>
    </main><!-- #main -->
<?php

get_footer();
