<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

$order_is_social = social_order_is_social( $order );
?>
<p>
	Olá, <br />
	Acabamos de receber mais uma doação para o Criança Esperança.<br />
	Envie um vídeo para agradecer!<br />
</p>
<p>
	Número do pedido: <?php echo $order->get_id(); ?><br />
	Valor doado: <?php echo wc_price( polen_apply_polen_part_price( $order->get_subtotal(), $order_is_social ) ); ?><br />
	Válido por 15 dias <br />
</p>
<?php

// do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
// do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if( isset( $additional_content ) && ! empty( $additional_content ) ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
