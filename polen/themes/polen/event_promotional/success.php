<?php

use Polen\Includes\Cart\Polen_Cart_Item_Factory;

$product = $GLOBALS[ Promotional_Event_Rewrite::GLOBAL_KEY_PRODUCT_OBJECT ];

$order_id  = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING );
$order_key = filter_input( INPUT_GET, 'order_key', FILTER_SANITIZE_STRING );
$order = wc_get_order( $order_id );

if( empty( $order ) ) {
    wp_safe_redirect( event_promotional_url_code_validation( $product ) );
    exit;
}

if( $order->get_order_key() != $order_key ) {
	wp_safe_redirect( event_promotional_url_code_validation( $product ) );
    exit;
}

$order_item_cart = Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
$email_billing = $order_item_cart->get_email_to_video();

$order_array = event_promotional_get_order_flow_obj($order->get_id(), $order->get_status(), $email_billing);
$order_number = $order->get_id();

get_header();
?>

<main id="primary" class="site-main">
	<div class="row">
		<div class="col-12 col-md-8 m-md-auto event-lacta">
      <div class="row">
        <div class="col-12 mb-5">
          <?php polen_get_lacta_thank_you($product->get_title()); ?>
        </div>
      </div>
			<?php
				event_promotional_get_order_flow_layout($order_array, $order_number );
        get_lacta_partners();
			?>
		</div>
	</div>
</main><!-- #main -->

<?php
get_footer();
