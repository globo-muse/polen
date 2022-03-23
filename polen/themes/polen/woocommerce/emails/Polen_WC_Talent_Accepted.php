<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<?php
global $Polen_Plugin_Settings;
$order_expires = $Polen_Plugin_Settings['order_expires'];
$item = Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
$talent = _polen_get_info_talent_by_product_id($item->get_product(), "polen-square-crop-md");
?>

<p><?php printf( esc_html__( 'Olá, %1$s aceitou o seu pedido #%2$s de vídeo e deverá responder em até %3$s dias.', 'woocommerce' ), esc_html($talent['name']), esc_html($order->get_order_number()), esc_html($order_expires) ); ?></p>
<p>Você pode acompanhar o status do seu pedido aqui:</p>

<p class="btn_wrap">
	<a href="<?php echo polen_get_link_order_status( $order->get_id() ); ?>" class="btn" target="_blank" style="width: 100%">Acompanhe seu pedido</a>
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

do_action( 'woocommerce_email_footer', $email );
