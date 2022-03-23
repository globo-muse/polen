<?php

/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.5
 */

defined('ABSPATH') || exit;

global $product;

$attribute_keys  = array_keys($attributes);
$variations_json = wp_json_encode($available_variations);
$variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);
$donate = get_post_meta(get_the_ID(), '_is_charity', true);

function clean_p($text)
{
	$remove_list = array("<p>", "</p>");
	return str_replace($remove_list, "", $text);
}

foreach ($available_variations as $variation) :
	$name_input_1 = array_keys($variation['attributes'])[0];
	$id = str_replace('attribute_', '', $name_input_1);
	$value = $variation['attributes'][$name_input_1];
	$is_corp = strtolower($value) == "empresa";
	$btn_class = $is_corp ? "btn-outline-light" : "btn-primary";
?>

	<form class="mt-3" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>">
		<input type="hidden" name="<?= $name_input_1; ?>" id="<?= $id; ?>" data-attribute_name="<?= $name_input_1; ?>" data-show_option_none="yes" value="<?= $value; ?>">
		<input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
		<input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
		<input type="hidden" name="variation_id" class="variation_id" value="<?= $variation['variation_id']; ?>" />
		<button type="submit" class="btn <?php echo $btn_class; ?> btn-lg btn-block">
			<?php if ($donate) : ?>
				<span class="mr-2"><?php Icon_Class::polen_icon_donate(); ?></span>
			<?php elseif ($is_corp) :  ?>
				<span class="mr-2"><?php Icon_Class::polen_icon_company(); ?></span>
			<?php endif; ?>
			<?php echo clean_p($variation['variation_description']) . ' ' . wc_price($variation['display_price']); ?>
		</button>
	</form>
<?php endforeach; ?>
<?php
