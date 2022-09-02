<?php

use ProfilePress\Core\Membership\Models\Plan\PlanEntity;

/** @global $order_total */
/** @global PlanEntity $plan */

$submit_button_text = apply_filters('ppress_checkout_order_button_text', sprintf('Pay %s', ppress_display_amount($order_total)), $order_total);

if ($plan->is_recurring()) $submit_button_text = esc_html__('Subscribe', 'wp-user-avatar');

if ($plan->has_free_trial()) $submit_button_text = esc_html__('Start Trial', 'wp-user-avatar');

$submit_button_text = apply_filters('ppress_checkout_order_button_text', $submit_button_text, $order_total, $plan) ?>

<div class="ppress-checkout-form__place_order_wrap ppress-checkout-submit">
    <input id="ppress-checkout-button" name="ppress-checkout" type="submit" value="<?= $submit_button_text ?>">
</div>