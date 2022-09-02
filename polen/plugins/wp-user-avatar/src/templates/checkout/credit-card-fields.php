<?php /** @var string $id */?>

<div class="ppress-main-checkout-form__block__item">
    <label for="<?= esc_attr($id . '-' . 'card_name') ?>">
        <?php esc_html_e('Name on card', 'wp-user-avatar') ?>
        <span class="ppress-required">*</span>
    </label>
    <input id="<?= esc_attr($id . '-' . 'card_name') ?>" name="<?= esc_attr($id . '-' . 'card_name') ?>" class="ppress-checkout-field__input" type="text" autocomplete="cc-name">
</div>
<div class="ppress-main-checkout-form__block__item">
    <label for="<?= esc_attr($id . '-' . 'card_number') ?>">
        <?php esc_html_e('Card number', 'wp-user-avatar') ?>
        <span class="ppress-required">*</span>
    </label>
    <input id="<?= esc_attr($id . '-' . 'card_number') ?>" name="<?= esc_attr($id . '-' . 'card_number') ?>" class="ppress-checkout-field__input" type="text" autocomplete="cc-number" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;">
</div>
<div class="ppress-main-checkout-form__block__item ppress-two-third">
    <label for="<?= esc_attr($id . '-' . 'card_exp_month') ?>">
        <?php esc_html_e('Expiration date (MM/YY)', 'wp-user-avatar') ?>
        <span class="ppress-required">*</span>
    </label>
    <div class="press-main-checkout-form__block__item__cc_expiry_date">
        <select id="<?= esc_attr($id . '-' . 'card_exp_month') ?>" name="<?= esc_attr($id . '-' . 'card_exp_month') ?>" autocomplete="cc-exp-month">
            <?php for ($i = 1; $i <= 12; $i++) {
                echo '<option value="' . $i . '">' . sprintf('%02d', $i) . '</option>';
            } ?>
        </select>
        <select id="<?= esc_attr($id . '-' . 'card_exp_year') ?>" name="<?= esc_attr($id . '-' . 'card_exp_year') ?>" autocomplete="cc-exp-year">
            <?php for ($i = date('Y'); $i <= date('Y') + 30; $i++) {
                echo '<option value="' . $i . '">' . substr($i, 2) . '</option>';
            } ?>
        </select>
    </div>
</div>
<div class="ppress-main-checkout-form__block__item ppress-one-third">
    <label for="<?= esc_attr($id . '-' . 'card_cvc') ?>">
        <?php esc_html_e('CVC', 'wp-user-avatar') ?>
        <span class="ppress-required">*</span>
    </label>
    <input class="ppress-checkout-field__input" id="<?= esc_attr($id . '-' . 'card_cvc') ?>" name="<?= esc_attr($id . '-' . 'card_cvc') ?>" type="tel" pattern="[0-9]{3,4}" maxlength="4" autocomplete="cc-csc" inputmode="numeric">
</div>