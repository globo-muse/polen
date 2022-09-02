<?php

use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\TaxService;

/** @global OrderEntity $order_data */
/** @global int $order_id */
$plan            = PlanFactory::fromId($order_data->plan_id);
$plan_price      = ppress_display_amount($plan->price, $order_data->currency);
$coupon_code     = $order_data->coupon_code;
$billing_country = $order_data->get_meta($order_data::EU_VAT_COUNTRY_CODE);
if (empty($billing_country)) {
    $billing_country = $order_data->billing_country;
}
?>
<div id="ppress-submetabox-items">
    <div class="ppress-submetabox-items-table-wrapper">
        <table cellpadding="0" cellspacing="0" class="ppress-submetabox-items-table">
            <thead>
            <tr>
                <th class="ppress-submetabox-item-column-course"><?php esc_html_e('Item', 'wp-user-avatar') ?></th>
                <th class="ppress-submetabox-item-column-price"></th>
                <th class="ppress-submetabox-item-column-total"><?php esc_html_e('Price', 'wp-user-avatar') ?></th>
                <th class="ppress-submetabox-item-column-action"></th>
            </tr>
            </thead>
            <tbody>
            <tr class="ppress-submetabox-item-row">
                <td class="ppress-submetabox-item-column-course">
                    <a target="_blank" href="<?= $plan->get_edit_plan_url() ?>"><?php echo $plan->name ?></a>
                </td>
                <td class="ppress-submetabox-item-column-price"></td>
                <td class="ppress-submetabox-item-column-total"><?= $plan_price ?></td>
                <td class="ppress-submetabox-item-column-action action-delete"></td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="ppress-submetabox-items-row-sub-total">
                <td colspan="2" class="ppress-submetabox-items-column-sub-total-label">
                    <strong><?php esc_html_e('Subtotal', 'wp-user-avatar') ?>:</strong></td>
                <td class="ppress-submetabox-items-column-sub-total"><?= ppress_display_amount($order_data->subtotal, $order_data->currency) ?></td>
                <td class="ppress-submetabox-items-column-action"></td>
            </tr>
            <tr class="ppress-submetabox-items-row-border">
                <td colspan="1"></td>
                <td colspan="3" style="border-bottom: 1px solid #f8f8f8;"></td>
            </tr>

            <?php if ( ! empty($coupon_code)) : ?>
                <tr class="ppress-submetabox-items-row-discounts">
                    <td colspan="2" class="ppress-submetabox-items-column-discounts-label">
                    <span class="coupon-wrap">
                        <span class="coupons-label"><?php esc_html_e('Applied Coupon', 'wp-user-avatar') ?></span>
                        <span class="coupon-code"><?= $coupon_code ?></span>
                    </span>
                        <span class="label-text">
                            <strong><?php esc_html_e('Discount:', 'wp-user-avatar') ?></strong>
                        </span>
                    </td>
                    <td class="ppress-submetabox-items-column-discounts-total">&minus;<?= ppress_display_amount($order_data->discount, $order_data->currency) ?></td>
                    <td class="ppress-submetabox-items-column-action"></td>
                </tr>
            <?php endif; ?>

            <?php if (TaxService::init()->is_tax_enabled() || Calculator::init($order_data->tax)->isGreaterThan('0')) : ?>
                <tr class="ppress-submetabox-items-row-tax">
                    <td colspan="2" class="ppress-submetabox-items-column-tax-label">
                        <strong><?= TaxService::init()->get_tax_label($billing_country) ?>
                            <?php if (Calculator::init($order_data->tax_rate)->isGreaterThan('0')) : ?>
                                (<?= $order_data->tax_rate ?>%)
                            <?php endif; ?>
                    </td>
                    <td class="ppress-submetabox-items-column-tax"><?= ppress_display_amount($order_data->tax, $order_data->currency) ?></td>
                    <td class="ppress-submetabox-items-column-action"></td>
                </tr>
            <?php endif; ?>

            <tr class="ppress-submetabox-items-row-border">
                <td colspan="1"></td>
                <td colspan="3" style="border-bottom: 1px solid #f8f8f8;"></td>
            </tr>
            <tr class="ppress-submetabox-items-row-total">
                <td colspan="2" class="ppress-submetabox-items-column-total-label">
                    <strong><?php esc_html_e('Total:', 'wp-user-avatar') ?></strong>
                </td>
                <td class="ppress-submetabox-items-column-total"><?= ppress_display_amount($order_data->total, $order_data->currency) ?></td>
                <td class="ppress-submetabox-items-column-action"></td>
            </tr>
            <tr class="ppress-submetabox-items-row-actions">
                <td colspan="4" class="ppress-submetabox-items-column-actions">
                    <?php if ($order_data->is_refundable()) : ?>
                        <a href="<?= $order_data->get_refund_url() ?>" id="order-refund" class="button button-secondary pp-confirm-delete order-refund"><?php esc_html_e('Refund Order', 'wp-user-avatar') ?></a>
                    <?php endif; ?>

                    <?php if ( ! $order_data->is_pending()) : ?>
                        <span class="ppress-hint-tooltip ppress-hint-wrap hint--top hint--medium hint--bounce" aria-label="<?= esc_html__('To edit this order, change the order status back to "Pending"', 'wp-user-avatar') ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                        <span class="message"><?php esc_html_e('This order is no longer editable.', 'wp-user-avatar') ?></span>
                    <?php else : ?>
                        <input type="hidden" id="ppress_order_id" value="<?= absint($_GET['id']) ?>">
                        <button type="button" class="button add-replace-order-item"><?php esc_html_e('Add/Replace Order Item', 'wp-user-avatar') ?></button>
                    <?php endif; ?>
                </td>
            </tr>
            </tfoot>
        </table>

    </div>
</div>