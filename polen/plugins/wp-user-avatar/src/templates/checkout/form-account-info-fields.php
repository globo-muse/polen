<?php

use ProfilePress\Core\Membership\CheckoutFields;

foreach (CheckoutFields::account_info_fields() as $field_key => $field) {

    $skip_fields = CheckoutFields::logged_in_hidden_fields();

    if (is_user_logged_in() && (in_array($field_key, $skip_fields) || ppress_var($field, 'logged_in_hide') == 'true')) continue;

    $field_type  = ppress_var($field, 'field_type', '');
    $is_required = ppress_var($field, 'required') == 'true';
    $width_class = $field['width'] == 'full' ? '' : $field['width'];
    if ($width_class == 'half') $width_class = 'ppress-co-half';
    if ($width_class == 'one-third') $width_class = 'ppress-one-third';
    $label = wp_kses_post($field['label']);

    printf('<div class="ppress-main-checkout-form__block__item ppmb-%s %s">', $field_type, $width_class);

    if ($field_type != 'agreeable') {
        printf(
            '<label for="%s">%s%s</label>',
            esc_attr($field_key),
            $label,
            ppress_var($field, 'required') == 'true' ? '<span class="ppress-required">*</span>' : ''
        );
    }
    echo CheckoutFields::render_field($field_key, $is_required);
    echo '</div>';
}


if (is_user_logged_in()) {
    printf('<input name="ppmb_email" type="hidden" id="ppmb_email" value="%s">', wp_get_current_user()->user_email);
}