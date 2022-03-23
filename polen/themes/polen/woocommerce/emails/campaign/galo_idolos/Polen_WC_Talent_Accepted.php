<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_get_template( 'emails/campaign/galo_idolos/email-header.php', array( 'email_heading' => "Seu pedido foi aceito" ) );

?>

<?php
$item = Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
$talent = _polen_get_info_talent_by_product_id( $item->get_product(), "polen-square-crop-md" );
$user = $order->get_user();
$user_name = '';
if( !empty( $user ) ) {
	$user_name = $user->display_name;
}
?>

<p><?php printf( esc_html__( 'Olá %1$s, %2$s aceitou o seu pedido #%3$s de vídeo e deverá responder em até 15 dias.', 'woocommerce' ), esc_html( $user_name ), esc_html($talent['name']), esc_html($order->get_order_number()) ); ?></p>
<p>Você pode acompanhar o status do seu pedido aqui:</p>

<p class="btn_wrap">
	<a href="https://galoidolos.com.br/minha-conta/pedidos" class="btn" target="_blank" style="background:#FFCD00; color: #000;width: 100%">Acompanhe seu pedido</a>
</p>

<p>
	Caso precise de ajuda, entre em contato com nosso atendimento: <a href="mailto:atendimento@polen.me">atendimento@polen.me </a>
	<br><br>
	Atenciosamente,
	<br>
	<b>Equipe Polen!</b>
<p>

<?php

// do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if( isset( $additional_content ) && ! empty( $additional_content ) ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

wc_get_template( 'emails/campaign/galo_idolos/email-footer.php' );
