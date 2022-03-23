<?php

/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

use Polen\Includes\Cart\Polen_Cart_Item;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Polen_Utils;

global $Polen_Plugin_Settings;
$order_expires = $Polen_Plugin_Settings['order_expires'];

defined('ABSPATH') || exit;

// if( social_order_is_social( $order ) ) {
// 	wc_get_template(
// 		'emails/email-order-details-criesp.php',
// 		array(
// 			'order'         => $order,
// 			'sent_to_admin' => $sent_to_admin,
// 			'plain_text'    => $plain_text,
// 			'email'         => $email,
// 		)
// 	);
// 	return;
// }

$item  = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
$total = polen_get_total_order_email_detail_to_talent( $order, $email );
?>

<div style="margin-bottom: 40px;">
	<table cellspacing="0" cellpadding="0" width="100%">
		<thead>
			<tr>
				<td width="33.3333333%">&nbsp;</td>
				<td width="33.3333333%">&nbsp;</td>
				<td width="33.3333333%">&nbsp;</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<p class="details_title">Valor</p>
					<span class="details_value"><?php echo wc_price( $total ); ?></span>
				</td>
				<td>
					&nbsp;
				</td>
				<td>
					<p class="details_title">Válido por</p>
					<span class="details_value"><?php echo $order_expires; ?> dias</span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<span class="details_line"></span>
				</td>
			</tr>
			<tr>
				<?php $video_de = $item->get_offered_by(); ?>
				<?php $video_to = $item->get_video_to() ?>
				<?php if ( Polen_Cart_Item::VIDEO_FOR_OTHER_ONE == $video_to ) : ?>
					<td>
						<p class="details_title">Vídeo de</p>
						<span class="details_value"><?php echo $item->get_offered_by(); ?></span>
					</td>
					<td valign="center">
						<img src="<?php echo get_template_directory_uri(); ?>/assets/img/email/arrow.png ?>" alt="Seta para a direita">
					</td>
					<td <?php echo empty($video_de) ? " colspan='3'" : ""; ?>>
						<p class="details_title">Para</p>
						<span class="details_value"><?php echo $item->get_name_to_video(); ?></span>
					</td>
				<?php else: ?>
					<td <?php echo empty($video_de) ? " colspan='3'" : ""; ?>>
						<p class="details_title">Para</p>
						<span class="details_value"><?php echo $item->get_name_to_video(); ?></span>
					</td>
				<?php endif; ?>

			</tr>
			<tr>
				<td colspan="3">
					<span class="details_line"></span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<p class="details_title">Ocasião</p>
					<span class="details_value_small"><?php echo $item->get_video_category(); ?></span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<span class="details_line"></span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<p class="details_title">e-mail de contato</p>
					<span class="details_value_small"><?php echo $item->get_email_to_video(); ?></span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<p class="details_title">Instruções</p>
					<span id="video-instructions" class="details_value_small"><?php echo Polen_Utils::remove_sanitize_xss_br_escape($item->get_instructions_to_video()); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<script>
	let instruction = "<?php echo $item->get_instructions_to_video(); ?>";
	document.getElementById('video-instructions').innerHTML = instruction.replace(/&#38;#13;/g, "<br>").replace(/&#38;#10;/g, "<br>");
</script>
