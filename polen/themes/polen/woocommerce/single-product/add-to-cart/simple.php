<?php
/**
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

$inputs = new Material_Inputs();

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		woocommerce_quantity_input(
			array(
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
			)
		);

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<?php $donate = get_post_meta( get_the_ID(), '_is_charity', true ); ?>
		<?php $social = social_product_is_social($product, social_get_category_base()); ?>

    <?php

    $inputs->material_button(
      Material_Inputs::TYPE_SUBMIT,
      "add-to-cart-" . esc_attr($product->get_id()),
      $product->single_add_to_cart_text(),
      "single_add_to_cart_button alt btn-get-video py-3",
      array("name" => "add-to-cart", "value" => esc_attr($product->get_id())),
      $donate ? "donate" : ""
    ); ?>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>

<?php if ( !$product->is_in_stock() ) : ?>
	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

    <?php $inputs->material_button_link("todos", "Escolher outro artista", home_url( "shop" )); ?>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
<?php endif; ?>
