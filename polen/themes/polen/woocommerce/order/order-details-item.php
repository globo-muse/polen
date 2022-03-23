<?php
/**
 * @version 5.2.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
	return;
}
?>
<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">

	<td class="woocommerce-table__product-name product-name">
		<?php
		$is_visible        = $product && $product->is_visible();
		$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

		echo apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

		//wc_display_item_meta( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		//var_dump( $item );
		foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
			//var_dump( $meta_id, $meta);
			//$value     = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );
			//$strings[] = $args['label_before'] . wp_kses_post( $meta->display_key ) . $args['label_after'] . $value;
			if( $meta->display_key == 'video_to' ){
				echo 'O vídeo é para ';

				if( $meta->display_value == 'other_one' ){
					echo 'outra pessoa';
				}else{
					echo 'mim';
				}
			}			

			if( $meta->display_key == 'offered_by' ){
				echo '<div>Oferecido por: '.strip_tags( $meta->display_value ).'</div>';
			}

			if( $meta->display_key == 'name_to_video' ){
				echo '<div>O vídeo é para: '.strip_tags( $meta->display_value ).'</div>';
			}

			if( $meta->display_key == 'email_to_video' ){
				echo '<div>E-mail para receber updates: '.strip_tags( $meta->display_value ).'</div>';
			}

			if( $meta->display_key == 'video_category' ){
				echo '<div>Ocasião do vídeo: '.strip_tags( $meta->display_value) .'</div>';				
			}

			if( $meta->display_key == 'instructions_to_video' ){
				echo '<div>Instruções: '.strip_tags( strip_tags( $meta->display_value ) ).'</div>';				
			}


		}


		do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
		?>
	</td>

	<td class="woocommerce-table__product-total product-total">
	</td>

</tr>

<?php if ( $show_purchase_note && $purchase_note ) : ?>

<tr class="woocommerce-table__product-purchase-note product-purchase-note">

	<td colspan="2"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>

</tr>

<?php endif; ?>
