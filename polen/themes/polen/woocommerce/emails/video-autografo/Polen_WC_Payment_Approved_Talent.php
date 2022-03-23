<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', 'Pedido Aprovado!', $email );

?>

<p>
	Olá, <br />
	Acabamos de receber mais um pedido de Vídeo-autógrafo.<br />
	Envie um vídeo para agradecer!<br />
</p>
<p>
	Número do pedido: <?php echo $order->get_id(); ?><br />
	Para: <?php echo $order->get_formatted_billing_full_name(); ?><br />
	Válido por 15 dias <br />
</p>

<?php
if( isset( $additional_content ) && ! empty( $additional_content ) ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
