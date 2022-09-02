<?php

$terms_page_id = ppress_settings_by_key('terms_page_id');

if ( ! $terms_page_id || empty($terms_page_id)) return;

$label = ppress_settings_by_key(
    'terms_agreement_label',
    sprintf(__('I have read and agree to the website %s', 'wp-user-avatar'), '[terms]'),
    true
);

$terms_link = $terms_page_id ? '<a href="' . esc_url(get_permalink($terms_page_id)) . '" class="ppress-terms-and-conditions-link" target="_blank">' . __('terms and conditions', 'wp-user-avatar') . '</a>' : __('terms and conditions', 'wp-user-avatar');

$label = wp_kses_post(trim(apply_filters('ppress_checkout_terms_and_conditions_label', str_replace('[terms]', $terms_link, $label))));

$page = get_post($terms_page_id);

?>

<div class="ppress-checkout-form__terms_condition_wrap">
    <?php if ( ! empty($terms_page_id)) : $page = get_post($terms_page_id);

        if ($page && 'publish' === $page->post_status && $page->post_content && ! has_shortcode($page->post_content, 'profilepress-checkout')) :?>
            <div class="ppress-checkout-form__terms_condition__content"><?= wp_kses_post($page->post_content) ?></div>
        <?php endif; ?>

    <?php endif; ?>

    <div class="ppress-checkout-form__terms_condition__checkbox_wrap">
        <label class="ppress-checkout-form__terms_condition__checkbox__label">
            <input id="ppress-terms" name="ppress-terms" type="checkbox" class="ppress-checkout-field__input">
            <?= $label ?> <span class="ppress-required">*</span>
        </label>
    </div>

</div>