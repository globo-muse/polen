<?php

if (!defined('ABSPATH')) {
	exit;
}

wc_get_template( 'emails/campaign/galo_idolos/email-header.php', array( 'email_heading' => $email_heading ) );

$item = Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
$talent = _polen_get_info_talent_by_product_id($item->get_product(), "polen-square-crop-md");

?>

<div class="order_card">
	<p>Número do pedido:</p>
	<span class="order_number">
		<img src="<?php echo get_template_directory_uri(); ?>/assets/img/email/attention.png" alt="Icone de atenção" class="image-icon" />
		<?php echo $order->get_id(); ?>
	</span>
</div>

<p>
	<?php printf(
		esc_html__( 'O artista %1$s não gravou seu vídeo #%2$s no prazo. O valor será estornado em até 24 horas', 'woocommerce' ),
		esc_html( $talent['name'] ),
		esc_html( $order->get_order_number() )
	);
	?>
</p>

<p>Mas não se preocupe, procure outros talentos no <?= get_bloginfo('name'); ?></p>

<p class="btn_wrap">
	<a href="https://galoidolos.com.br/idolos" class="btn" target="_blank">Procurar outro talento</a>
</p>

<?php

// do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if (isset($additional_content) && !empty($additional_content)) {
	echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

wc_get_template( 'emails/campaign/galo_idolos/email-footer.php' );
