<?php

if (!defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_email_header', $email_heading, $email);

$item = Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
$talent = _polen_get_info_talent_by_product_id($item->get_product(), "polen-square-crop-md");
$talent_name = $talent['name'];
$mail_subject = "Renovar solicitação #{$order->get_id()}";
$url_send_video = site_url( 'my-account/send-video/?order_id=' ) . $order->get_id();
?>

<div class="order_card">
	<p>Número do pedido:</p>
	<span class="order_number">
		<img src="<?php echo get_template_directory_uri(); ?>/assets/img/email/attention.png" alt="Icone de atenção" class="image-icon" />
		<?php echo $order->get_id(); ?>
	</span>
</div>

<p>
  Olá <?= $talent_name; ?>,<br />
	Hoje expira o prazo inicial de entrega de um dos pedidos feitos por um fã seu.<br>
	Esse vídeo-polen pode ser gravado nesse link <a href="<?= $url_send_video; ?>"><?= $url_send_video; ?></a>.<br>
	Obrigada pela atenção.<br>
</p>

<?php

if (isset($additional_content) && !empty($additional_content)) {
	echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
