<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

$item = Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
$talent = _polen_get_info_talent_by_product_id($item->get_product(), "polen-square-crop-md");

?>

<p>
	Olá, <?php echo $item->get_offered_by(); ?><br />
	Infelizmente, <strong><?php echo $talent['name']; ?></strong> não poderá gravar o vídeo solicitado por você.
</p>

<p>De acordo com nossos termos de uso (<a href="https://polen.me/termos-de-uso/" target="_blank">https://polen.me/termos-de-uso/</a>), é direito de cada talento decidir não gravar um vídeo solicitado e, nestes casos, nós devolvemos integralmente o valor pago por você (consulte o estorno integral em seu cartão de crédito).</p>

<p>Uma outra razão pela qual o seu vídeo pode vir a não ser gravado é se nas suas instruções você pediu:</p>

<ol>
	<li>Que o talento leia, cante ou declame trechos de obras musicais ou literárias, mesmo que sejam dele próprio.</li>
	<li>Que o talento se manifeste em assuntos ligados à política partidária e afins. </li>
	<li>Que o talento incite, promova, facilite e/ou incentive ações que sejam ofensivas, perigosas, gratuitamente violentas, difamatórias e ilegais.</li>
	<li>Que o talento envie mensagem de cunho ameaçador, odioso, racista, homofóbico, transfóbico, sexista e/ou depreciativo.</li>
	<li>Que o talento envie nudez ou pornografia.</li>
	<li>Que o talento faça publicidade de marca, comércio, produto ou serviço.</li>
</ol>

<p>Caso seu pedido eventualmente tenha se enquadrado num desses casos, o valor pago por você também será devolvido integralmente.</p>
<p>Esperamos que entenda que essas pequenas regras servem para que a Polen cumpra a sua missão de levar boas emoções a todos.</p>
<p>Se desejar, entre em contato com nosso atendimento: atendimento@polen.me</p>

<p>Atenciosamente,<br />Equipe Polen</p>

<p class="btn_wrap">
	<a href="<?php echo polen_get_all_talents_url(); ?>" class="btn" target="_blank">Ver todos os Ídolos</a>
</p>

<?php

//do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
//do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
//do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if( isset( $additional_content ) && ! empty( $additional_content ) ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
