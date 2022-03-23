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

use Polen\Includes\Cart\Polen_Cart_Item_Factory;

defined('ABSPATH') || exit;

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
					<p class="details_title">Número do Pedido</p>
					<span class="details_value"><?php echo $order->get_order_number(); ?></span>
				</td>
                <td>
					<p class="details_title">Valor da Doação</p>
					<span class="details_value"><?php echo wc_price( $total ); ?></span>
				</td>
				<td>
					&nbsp;
				</td>
				<td>
					<p class="details_title">Válido por</p>
					<span class="details_value">15 dias</span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<span class="details_line"></span>
				</td>
			</tr>
		</tbody>
	</table>
</div>
