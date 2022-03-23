<?php

/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
	exit;
}
do_action('polen_before_cart');
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}

use Polen\Includes\Polen_Update_Fields;

$Talent_Fields = new Polen_Update_Fields();
?>

<!-- <div class="row mt-2">
	<div class="col-12">
		<div class="progress" style="height: 7px;">
			<div class="progress-bar bg-primary" role="progressbar" style="width: 95%;" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
		</div>
	</div>
</div> -->
<div class="row">
	<div class="col-12 col-md-6 order-md-2 mt-md-2 mb-2">
		<?php
		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
			$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
			$talent_id = get_post_field('post_author', $product_id);
			$thumbnail = polen_get_thumbnail($product_id);
			$talent = get_user_by('id', $talent_id);
			$is_social = social_product_is_social( $_product, social_get_category_base() );
			$talent_data = $Talent_Fields->get_vendor_data($talent_id);

			$talent_cart_detail = array(
				"has_details" => true,
				"avatar" => $thumbnail["image"],
				"alt" => $thumbnail["alt"],
				"name" => $_product->get_title(),
				"career" => $talent_data->profissao,
				"price" => $_product->get_price_html(),
				"discount" => wc_price( WC()->cart->get_discount_total() ),
				"from" => $cart_item['offered_by'] ? $cart_item['offered_by'] : null,
				"to" => $cart_item['name_to_video'] ? $cart_item['name_to_video'] : null,
				"category" => $cart_item['video_category'] ? $cart_item['video_category'] : null,
				"mail" => $cart_item['email_to_video'] ? $cart_item['email_to_video'] : null,
				"description" => $cart_item['instructions_to_video'] ? $cart_item['instructions_to_video'] : null
			);

			if( 'to_myself' == $cart_item['video_to'] ) {
				// unset( $talent_cart_detail[ "to" ] );
				$talent_cart_detail[ "to" ] = $talent_cart_detail[ "from" ];
				$talent_cart_detail[ "from" ] = "";
			}
		}
		polen_get_talent_card($talent_cart_detail, $is_social); ?>
	</div>
	<form name="checkout" method="post" class="checkout woocommerce-checkout col-12 col-md-6 order-md-1" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-12">
				<?php
				if (is_user_logged_in() && get_current_user_id() > 0) { ?>
					<!-- <h3>Você está logado como:</h3> -->
				<?php $user_id = get_current_user_id();
					$user_data = get_userdata($user_id);
					// echo $user_data->display_name;
				}
				?>

				<?php if ($checkout->get_checkout_fields()) : ?>

					<?php do_action('woocommerce_checkout_before_customer_details'); ?>

					<div class="row" id="customer_details">
						<div class="col-12 col-md-12">
							<?php do_action('woocommerce_checkout_billing'); ?>
						</div>

						<div class="col-12 col-md-12">
							<?php do_action('woocommerce_checkout_shipping'); ?>
						</div>
					</div>

					<?php do_action('woocommerce_checkout_after_customer_details'); ?>

				<?php endif; ?>

				<?php woocommerce_checkout_payment(); ?>

			</div>
			<div class="col-md-12" style="display: none;">

				<?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

				<h3 id="order_review_heading" class="title-alt"><?php esc_html_e('Resumo da compra', 'polen'); ?></h3>

				<?php do_action('woocommerce_checkout_before_order_review'); ?>

				<?php
				/* pagamento está na mesma action do review
			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>
				*/
				woocommerce_order_review();
				?>
				<?php do_action('woocommerce_checkout_after_order_review'); ?>
			</div>
		</div>
	</form>
</div>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
