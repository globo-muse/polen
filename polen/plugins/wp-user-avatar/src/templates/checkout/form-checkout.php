<?php

/** @var ProfilePress\Core\Membership\Models\Plan\PlanEntity $planObj
 * @var ProfilePress\Core\Membership\PaymentMethods\PaymentMethodInterface[] $payment_methods
 * @var string $default_payment_method
 */

use ProfilePress\Core\Membership\Controllers\CheckoutSessionData;
use ProfilePress\Core\Membership\Services\OrderService;

$cart_vars = OrderService::init()->checkout_order_calculation([
    'plan_id'     => $planObj->id,
    'coupon_code' => CheckoutSessionData::get_coupon_code($planObj->id),
    'tax_rate'    => CheckoutSessionData::get_tax_rate($planObj->id)
]);

?>

<div id="ppress_checkout_summary" class="ppress-checkout-section ppress-checkout_side_section">
    <?php
    ppress_render_view(
        'checkout/form-checkout-sidebar', [
            'plan'      => $planObj,
            'cart_vars' => $cart_vars
        ]
    ); ?>
</div>

<div id="ppress_checkout_main_form" class="ppress-checkout-section ppress-checkout_main">

    <form method="post" id="ppress_mb_checkout_form" enctype="multipart/form-data">
        <input id="ppress-checkout-plan-id" type="hidden" name="plan_id" value="<?= $planObj->id ?>">
        <?php wp_nonce_field('ppress_process_checkout', 'ppress_checkout_nonce') ?>

        <div class="ppress-main-checkout-form__block">

            <div class="ppress-main-checkout-form__block__fieldset">
                <fieldset id="ppress_checkout_account_info">
                    <legend>
                        <?php esc_html_e('Account Information', 'wp-user-avatar') ?>
                        <?php if ( ! is_user_logged_in()): ?>
                            <a class="ppress-checkout-show-login-form" href="#">
                                <?php esc_html_e('Already have an account?', 'wp-user-avatar') ?>
                            </a>
                        <?php endif; ?>
                    </legend>

                    <?php ppress_render_view('checkout/form-login', ['plan' => $planObj]); ?>

                </fieldset>
            </div>

            <?php ppress_render_view('checkout/form-account-info-fields'); ?>

            <?php ppress_render_view('checkout/form-payment-methods', [
                'plan'      => $planObj,
                'cart_vars' => $cart_vars
            ]); ?>

            <?php ppress_render_view('checkout/form-terms'); ?>

            <?php ppress_render_view('checkout/form-checkout-submit-btn', [
                'order_total' => $cart_vars->total,
                'plan'        => $planObj
            ]); ?>

        </div>

    </form>

</div>