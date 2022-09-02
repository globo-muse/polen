<?php

use ProfilePress\Core\Membership\Models\Order\CartEntity;
use ProfilePress\Core\Membership\PaymentMethods\AbstractPaymentMethod;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Services\OrderService;

/** @var ProfilePress\Core\Membership\Models\Plan\PlanEntity $plan
 * @var CartEntity $cart_vars
 */

$supporting_methods = 0;

$methods               = PaymentMethods::get_instance()->get_enabled_methods();
$active_payment_method = PaymentMethods::get_instance()->get_default_method();

if ( ! empty($_POST['ppress_payment_method'])) {
    $active_payment_method = sanitize_text_field($_POST['ppress_payment_method']);
}

if (empty($methods)) : ?>

    <div class="ppress-checkout-alert ppress-warning">
        <p><?php _e('Payment processing is currently disabled because no payment method is active.', 'wp-user-avatar'); ?></p>
    </div>

<?php else :

    echo '<div class="ppress-checkout_payment_methods-wrap">';

    if ( ! OrderService::init()->is_free_checkout($cart_vars)) : ?>
        <div class="ppress-main-checkout-form__block__fieldset">
            <fieldset id="ppress_checkout_payment_methods">
                <legend><?php esc_html_e('Payment Information', 'wp-user-avatar') ?></legend>

                <div class="ppress-checkout-form__payment_methods_wrap">

                    <?php foreach ($methods as $id => $method) :

                        if ($plan->is_recurring() && ! $method->supports(AbstractPaymentMethod::SUBSCRIPTIONS)) continue;

                        $supporting_methods++;

                        $nonce_key = 'ppress_checkout_' . $method->get_id() . '_nonce';

                        ?>

                        <div class="ppress-checkout-form__payment_method <?= $method->get_id() ?><?= $active_payment_method == $method->get_id() ? ' ppress-active' : '' ?>">
                            <?php wp_nonce_field($nonce_key, $nonce_key); ?>
                            <div class="ppress-checkout-form__payment_method__title_wrap">
                                <input id="ppress_payment_method_<?= $id ?>" type="radio" class="ppress-checkout-field__radio" name="ppress_payment_method" value="<?= $id ?>" <?= checked($active_payment_method, $method->get_id()) ?>>
                                <label class="ppress-checkout-form__payment_method__label" for="ppress_payment_method_<?= $id ?>">
                                    <?= $method->get_title() ?>
                                    <?php if ( ! empty($method->get_icon())) : ?>
                                        <span class="ppress-checkout-form__payment_method__icons">
										<?= $method->get_icon() ?>
                                    </span>
                                    <?php endif; ?>
                                </label>
                            </div>

                            <?php if ($method->has_fields() || ! empty($method->get_description())) : ?>
                                <div class="ppress-checkout-form__payment_method__content_wrap">
                                    <?php if ($active_payment_method == $method->get_id()): ?>
                                        <?php $method->payment_fields() ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach;

                    if ( ! $supporting_methods) : ?>

                        <div class="ppress-checkout-alert ppress-warning" style="margin:0">
                            <p><?php _e('There are no gateways enabled with support for subscriptions.', 'wp-user-avatar'); ?></p>
                        </div>

                    <?php endif; ?>

                </div>

            </fieldset>
        </div>
    <?php

    endif;

    echo '</div>';

endif;