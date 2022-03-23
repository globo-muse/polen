<?php

/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.4
 */

defined('ABSPATH') || exit;

if (!wc_coupons_enabled()) { // @codingStandardsIgnoreLine.
    return;
}

if( social_cart_is_social() ) {
    return;
}

//Tratamento para que se um cupom estiver aplicado
// deixa ele escrito no field e desabilita o botao
$cupons = WC()->cart->get_coupons();

$cupon_field_value = "";
$apply_cupom_label_button = "Apply"; //está em EN pq passar por translate __()
$disable_apply_cupom_button = "";
$coupon_applied = false;
if( !empty( $cupons ) ) {
    $coupon_applied = implode(WC()->cart->applied_coupons);

    foreach( $cupons as $cupom ) {
        $cupon_field_value =  $cupom->get_code();
        $apply_cupom_label_button = "Aplicado";
        $disable_apply_cupom_button = "disabled";
    }
}
?>
<form class="checkout_coupon woocommerce-form-coupon" method="post">
    <div class="box-round box-color mt-4 py-4 px-3">
        <div class="row">
            <div class="col-12">
                <label for="coupon_code" class="form-title">
                    <?php echo __('Adicionar Cupom de desconto', 'cubo9-marketplace'); ?>
                </label>
                <div class="row">
                    <div class="col-12 d-flex">
                        <input type="text"
                               name="coupon_code"
                               class="form-control form-control-lg mr-3"
                               placeholder="<?php //esc_attr_e('Coupon code', 'woocommerce'); ?>"
                               id="coupon_code" value="<?= $cupon_field_value; ?>" />

                        <?php if (!$coupon_applied) : ?>

                            <button
                                    type="submit"
                                    class="btn btn-outline-light btn-lg"
                                    name="apply_coupon"
                                    value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"
                                <?= $disable_apply_cupom_button; ?>><?php esc_html_e($apply_cupom_label_button, 'woocommerce'); ?>
                            </button>

                        <?php else : ?>

                            <a
                                    class="btn btn-outline-light btn-lg woocommerce-remove-coupon"
                                    href="<?php the_permalink(); ?>/checkout?remove_coupon=<?php echo $coupon_applied; ?>"
                                    data-coupon="<?php echo $coupon_applied; ?>">
                                Remover
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>



<script>
    jQuery(document).on('click', '.woocommerce-remove-coupon', function () {
        setTimeout(
            function () {
                window.location.href = window.location.href;
            }, 400);
    });

</script>
