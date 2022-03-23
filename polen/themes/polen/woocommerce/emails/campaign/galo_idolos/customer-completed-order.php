<?php

/**
 * Customer completed order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-completed-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
wc_get_template( 'emails/campaign/galo_idolos/email-header.php', array( 'email_heading' => $email_heading ) );
?>

<?php
$item = Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
$talent = _polen_get_info_talent_by_product_id($item->get_product(), "polen-square-crop-md");
?>

<div class="talent_card">
	<header>
		<div class="card_thumb">
			<img src="<?php echo polen_get_protocol() . get_wp_user_avatar_src($item->get_talent_id(), 'polen-square-crop-md'); ?>" alt="Foto do artista" class="thumb" />
		</div>
		<div style="padding-top: 3px;">
			<span class="card_title" style="display: block;"><?php echo $talent['name']; ?></span>
			<span class="card_subtitle"><?php echo $talent['category']; ?></span>
		</div>
	</header>
	<p style="clear: both;"></p>
	<hr style="margin: 0 -16px 6px;border-style: dashed; opacity: 0.3;">
	<footer>
		<p class="card_subtitle">Você pagou</p>
		<span class="card_price"><?php echo $order->get_formatted_order_total(); ?></span>
	</footer>
</div>

<div class="order_card">
	<p>Número do pedido:</p>
	<span class="order_number"><?php echo $order->get_id(); ?></span>
</div>

<p class="btn_wrap">
	<a href="https://www.galoidolos.com.br/minha-conta/assistir-video/<?php echo $order->get_id();?>" class="btn" target="_blank" style="background:#FFCD00; color: #000; width: 100%">Assistir vídeo</a>
</p>

<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
// do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
// do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
// do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
	//echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
wc_get_template( 'emails/campaign/galo_idolos/email-footer.php' );
