<?php

use ProfilePress\Core\Membership\Controllers\CheckoutSessionData;
use ProfilePress\Core\Membership\Models\Order\CartEntity;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionTrialPeriod;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\TaxService;

/** @global PlanEntity $plan */
/** @global CartEntity $cart_vars */

$coupon_code = CheckoutSessionData::get_coupon_code($plan->id);

$tax_rate = ppress_session()->get(CheckoutSessionData::TAX_RATE);
if (empty($tax_rate)) $tax_rate = '0';

$sub_total           = $cart_vars->sub_total;
$discount_amount     = $cart_vars->discount_amount;
$discount_percentage = $cart_vars->discount_percentage;
$total_amount        = $cart_vars->total;

$billing_frequency = $plan->is_recurring() ? ' ' . SubscriptionBillingFrequency::get_label($plan->billing_frequency) : '';

?>
<div class="ppress-checkout_order_summary-wrap">
    <?php do_action('ppress_checkout_summary_content_before'); ?>
    <div class="ppress-checkout_order_summary">
        <div class="ppress-checkout_order_summary__plan_name">
            <?= $plan->name ?>
            <span class="ppress-checkout_order_summary__plan_price">(<?= ppress_display_amount($plan->price) . $billing_frequency ?>)</span>
        </div>
        <?php if ( ! empty($plan->description)) : ?>
            <div class="ppress-checkout_order_summary__plan_description">
                <?= $plan->description ?>
            </div>
        <?php endif; ?>

        <dl class="ppress-checkout_order_summary__bottom_details">

            <?php if (apply_filters('ppress_checkout_show_discount', ppress_is_any_active_coupon())) : ?>

                <div class="ppress-checkout_order_summary__discount">
                    <p>
                        <?php printf(
                            esc_html__('Have a coupon? %sClick here to enter your code%s', 'wp-user-avatar'),
                            '<a class="ppress-checkout__link ppress-coupon-code-link" href="#">',
                            '</a>'
                        ) ?>
                    </p>

                    <div id="ppress-checkout-coupon-code-wrap">
                        <div class="checkout_order_summary__discount__field_wrap">
                            <input placeholder="<?php esc_html_e('Coupon code', 'wp-user-avatar') ?>" type="text" id="apply-discount" class="checkout_order_summary__discount__input">
                            <input type="submit" class="ppress-apply-discount-btn" value="<?php esc_html_e('Apply', 'wp-user-avatar') ?>">
                        </div>
                    </div>
                </div>

            <?php endif; ?>

            <?php if ( ! Calculator::init($plan->signup_fee)->isNegativeOrZero()) : ?>
                <div class="checkout_order_summary__fee_structure__item">
                    <dt><?php esc_html_e('Signup Fee', 'wp-user-avatar') ?></dt>
                    <dd><?= ppress_display_amount($plan->signup_fee) ?></dd>
                </div>
            <?php endif; ?>

            <div class="checkout_order_summary__fee_structure__item">
                <dt><?php esc_html_e('Subtotal', 'wp-user-avatar') ?></dt>
                <dd><?= ppress_display_amount($sub_total) ?></dd>
            </div>

            <?php if ( ! empty($coupon_code)) : ?>
                <div class="checkout_order_summary__fee_structure__item">
                    <dt>
                        <?php printf(
                            esc_html__('Discount %s', 'wp-user-avatar'),
                            '<span>' . $coupon_code . '</span>'
                        ) ?>
                    </dt>
                    <dd>&minus;
                        <?php if ( ! empty($discount_percentage)) : ?>
                            <?= $discount_percentage ?>% (<?= ppress_display_amount($discount_amount) ?>)
                        <?php else : ?>
                            <?= ppress_display_amount($discount_amount) ?>
                        <?php endif; ?>
                        <a id="ppress-remove-applied-coupon" href="#"><?= esc_html__('Remove', 'wp-user-avatar') ?></a>
                    </dd>
                </div>
            <?php endif; ?>

            <?php if (TaxService::init()->is_tax_enabled()) : ?>
                <div class="checkout_order_summary__fee_structure__item">
                    <dt><?= TaxService::init()->get_tax_label(sanitize_text_field(ppressPOST_var('country', '', true))) ?>
                        <?php if ( ! empty($cart_vars->tax_rate)) : ?>
                            <span><?= $cart_vars->tax_rate ?>%</span>
                        <?php endif; ?>
                    </dt>
                    <dd><?= ppress_display_amount($cart_vars->tax_amount) ?></dd>
                </div>
            <?php endif; ?>

            <div class="checkout_order_summary__fee_structure__item">
                <dt>
                    <?php esc_html_e('Total', 'wp-user-avatar'); ?>
                    <?php if ($plan->has_free_trial()) : ?>
                        <span class="checkout_order_summary__fee_structure__item__trial_term">
                            <?= SubscriptionTrialPeriod::get_label($plan->get_free_trial()) . ' ' . esc_html__('free trial', 'wp-user-avatar') ?>
                        </span>
                    <?php endif; ?>
                </dt>
                <dd><?= ppress_display_amount($total_amount) ?></dd>
            </div>
        </dl>
    </div>

    <?php if ($plan->is_recurring()) : ?>
        <div class="ppress-checkout_charge_details">
            <?php printf(
                esc_html__('You\'ll be charged %1$stoday%3$s then %2$s starting %4$s.', 'wp-user-avatar'),
                sprintf('<span>%s ', ppress_display_amount($cart_vars->initial_amount)),
                sprintf('<span>%s %s</span>', ppress_display_amount($cart_vars->recurring_amount), strtolower(SubscriptionBillingFrequency::get_label($plan->billing_frequency))),
                '</span>',
                (new DateTime($cart_vars->expiration_date, new DateTimeZone('UTC')))->setTimezone(wp_timezone())->format('j M, Y')
            );

            if ($plan->get_total_payments() > 0) {
                printf('&nbsp;' . esc_html__('%s payments total.', 'wp-user-avatar'), '<strong>' . $plan->get_total_payments() . '</strong>');
            }
            ?>
        </div>
    <?php endif; ?>
    <?php do_action('ppress_checkout_summary_content_after'); ?>
</div>