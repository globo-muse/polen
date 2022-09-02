<?php

use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\Membership\Services\TaxService;

/** @global $payment_method */

$billing_fields = CheckoutFields::billing_fields();

$billing_fields = array_filter($billing_fields, function ($field_key) use ($billing_fields) {

    $skip_fields = CheckoutFields::logged_in_hidden_fields();

    if (is_user_logged_in() && (in_array($field_key, $skip_fields) || ppress_var($billing_fields[$field_key], 'logged_in_hide') == 'true')) {
        return false;
    }

    return true;

}, ARRAY_FILTER_USE_KEY);

if (TaxService::init()->is_tax_enabled() && TaxService::init()->calculate_tax_based_on_setting() == 'billing') {
    // this ensures billing fields are always present
    $billing_fields = array_merge(CheckoutFields::standard_billing_fields(), $billing_fields);
    // and this ensures billing fields are required
    $billing_fields[CheckoutFields::BILLING_ADDRESS]['required']   = 'true';
    $billing_fields[CheckoutFields::BILLING_CITY]['required']      = 'true';
    $billing_fields[CheckoutFields::BILLING_COUNTRY]['required']   = 'true';
    $billing_fields[CheckoutFields::BILLING_STATE]['required']     = 'true';
    $billing_fields[CheckoutFields::BILLING_POST_CODE]['required'] = 'true';
}

if ( ! empty($billing_fields)) {

    echo '<div class="ppress-checkout-form__payment_method__heading">';
    esc_html_e('Billing Address', 'wp-user-avatar');
    echo '</div>';

    if (
        TaxService::init()->is_tax_enabled() &&
        TaxService::init()->is_eu_vat_enabled() &&
        in_array(ppressPOST_var('country'), TaxService::init()->get_eu_countries(), true)
    ) {

        $billing_fields[CheckoutFields::VAT_NUMBER] = [
            'label'          => TaxService::init()->get_vat_number_field_label(
                esc_html__('VAT Number', 'wp-user-avatar')
            ),
            'width'          => 'full',
            'required'       => 'false',
            'logged_in_hide' => 'false',
            'field_type'     => 'text'
        ];
    }

    foreach ($billing_fields as $field_key => $field) {

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
                esc_attr($payment_method . '_' . $field_key),
                $label,
                ppress_var($field, 'required') == 'true' ? '<span class="ppress-required">*</span>' : ''
            );
        }

        $extra_attr = [];

        switch ($field_key) {
            case CheckoutFields::BILLING_ADDRESS:
                $extra_attr['autocomplete'] = "billing address-line1";
                break;
            case CheckoutFields::BILLING_CITY:
                $extra_attr['autocomplete'] = "address-level2";
                break;
            case CheckoutFields::BILLING_COUNTRY:
                $extra_attr['autocomplete'] = "country";
                break;
            case CheckoutFields::BILLING_STATE:
                $extra_attr['autocomplete'] = "address-level1";
                break;
            case CheckoutFields::BILLING_POST_CODE:
                $extra_attr['autocomplete'] = "billing postal-code";
                break;
            case CheckoutFields::VAT_NUMBER:
                $extra_attr['placeholder'] = "e.g. DE123456789";
                break;
        }

        echo CheckoutFields::render_field($field_key, $is_required, $extra_attr, $payment_method);
        echo '</div>';
    }
}