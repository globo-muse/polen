<?php

if (!defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_email_header', $email_heading, $email);

$item = Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
$talent = _polen_get_info_talent_by_product_id($item->get_product(), "polen-square-crop-md");
$talent_name = $talent['name'];
$mail_subject = "Renovar solicitação #{$order->get_id()}";

?>

<div class="order_card">
	<p>Número do pedido:</p>
	<span class="order_number">
		<img src="<?php echo get_template_directory_uri(); ?>/assets/img/email/attention.png" alt="Icone de atenção" class="image-icon" />
		<?php echo $order->get_id(); ?>
	</span>
</div>

<p>
  Olá,<br />
	Hoje expira o prazo inicial de entrega do seu vídeo de <strong><?php echo $talent_name; ?></strong>, mas ainda não foi concluído.
Às vezes acontece por conta da agenda dos nosso talentos mas temos uma solução pra você!
Queremos saber se você tem interesse em renovar a solicitação por mais 7 dias sem nenhum custo adicional.
</p>

<p>Por favor, nos envie uma resposta para <a href="mailto:atendimento@polen.me?subject=<?php echo $mail_subject; ?>">atendimento@polen.me</a> com o número do pedido.
Do contrário, vamos proceder com o estorno em 2 dias úteis.</p>

<p class="btn_wrap">
	<a href="<?php echo polen_get_all_talents_url(); ?>" class="btn" target="_blank">Procurar outro talento</a>
</p>

<?php

// do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if (isset($additional_content) && !empty($additional_content)) {
	echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
