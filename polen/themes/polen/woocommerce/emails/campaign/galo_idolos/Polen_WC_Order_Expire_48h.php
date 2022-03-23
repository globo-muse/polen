<?php

use Polen\Includes\Debug;

if (!defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_email_header', $email_heading, $email);

// $item = Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
// $talent = _polen_get_info_talent_by_product_id($item->get_product(), "polen-square-crop-md");
// $talent_name = $talent['name'];
// $mail_subject = "Renovar solicitação #{$order->get_id()}";

?>

<p>
	Lista dos Pedidos a vencer:
	<table>
		<?= $order; ?>
	</table>
</p>

<?php

// do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if (isset($additional_content) && !empty($additional_content)) {
	echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
