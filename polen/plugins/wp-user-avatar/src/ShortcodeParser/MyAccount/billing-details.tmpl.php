<?php

use ProfilePress\Core\Admin\ProfileCustomFields;
use ProfilePress\Core\Classes\EditUserProfile;
use ProfilePress\Core\Membership\CheckoutFields;

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$billing_details_fields = CheckoutFields::standard_billing_fields();

$success_message = EditUserProfile::get_success_message();

echo '<div class="profilepress-myaccount-edit-profile">';

echo '<h2>' . esc_html__('Billing Address', 'wp-user-avatar') . '</h2>';

if (isset($_GET['edit']) && $_GET['edit'] == 'true') :
    echo $success_message;
endif;

if ( ! empty($this->edit_profile_form_error)) :

    if (strpos($this->edit_profile_form_error, 'profilepress-edit-profile-status') !== false) :
        echo $this->edit_profile_form_error;
    else :
        echo '<div class="profilepress-edit-profile-status">';
        echo $this->edit_profile_form_error;
        echo '</div>';
    endif;
endif;

ob_start();
echo '[pp-edit-profile-form]';

echo '<div class="profilepress-myaccount-form-wrap">';

printf(
    '<p>%s</p>',
    apply_filters(
        'ppress_myaccount_billing_details_description',
        esc_html__('The following billing address will be used on the checkout page by default.', 'wp-user-avatar'))
);

if (is_array($billing_details_fields) && ! empty($billing_details_fields)) :

    foreach ($billing_details_fields as $field_key => $field) :

        $field_type = $field['field_type'];

        $options = '';

        if ($field_key == CheckoutFields::BILLING_STATE) {

            $billing_country        = get_user_meta(get_current_user_id(), CheckoutFields::BILLING_COUNTRY, true);
            $billing_country_states = ! empty($billing_country) ? ppress_array_of_world_states($billing_country) : [];

            if ( ! empty($billing_country_states)) {
                $field_type = 'select';
                $options    = base64_encode(serialize(['' => '&mdash;&mdash;&mdash;&mdash;'] + $billing_country_states));
            }
        }

        // skip woocommerce core billing / shipping fields added to wordpress profile admin page.
        if (in_array($field_key, ppress_woocommerce_billing_shipping_fields())) continue;

        echo '<div class="profilepress-myaccount-form-field">';

        if ($field_type !== 'agreeable') {
            printf('<label for="%s">%s</label>', $field_key, $field['label']);
        }

        echo sprintf(
            '[edit-profile-cpf id="%1$s" key="%1$s" type="%2$s" key_value_options="%3$s" class="profilepress-myaccount-form-control"]',
            $field_key, $field_type, $options
        );
        echo '</div>';

    endforeach;

endif;

echo '<div class="profilepress-myaccount-form-field">';
echo '[edit-profile-submit]';
echo '</div>';
echo '</div>';

echo '<input type="hidden" name="ppmyac_form_action" value="updateProfile">';

echo '[/pp-edit-profile-form]';

echo do_shortcode(ob_get_clean(), true);
echo '</div>';

do_action('ppress_myaccount_billing_details');

add_action('wp_footer', function () {
    ?>
    <script type="text/html" id="tmpl-ppress-profile-state-input">
        <input name="ppress_billing_state" type="text" id="ppress_billing_state" class="profilepress-myaccount-form-control" value="">
    </script>

    <script type="text/html" id="tmpl-ppress-profile-state-select">
        <select name="ppress_billing_state" id="ppress_billing_state" class="profilepress-myaccount-form-control">
            <option value="">&mdash;&mdash;&mdash;</option>
            <# jQuery.each(data.options, function(index, value) { #>
            <option value="{{index}}">{{value}}</option>
            <# }); #>
        </select>
    </script>

    <script type="text/javascript">
        (function ($) {
            $(function () {

                var ppress_countries_states = <?php echo wp_json_encode(array_filter(ppress_array_of_world_states())); ?>,
                    country_state_select_tmpl = wp.template('ppress-profile-state-select'),
                    country_state_input_tmpl = wp.template('ppress-profile-state-input');

                $(document).on('change', '#ppress_billing_country', function () {

                    var val = $(this).val(), field;

                    if (val in ppress_countries_states) {

                        field = country_state_select_tmpl({
                            options: ppress_countries_states[val]
                        });

                    } else {

                        field = country_state_input_tmpl();
                    }

                    $('#ppress_billing_state').replaceWith(field);
                });
            })
        })(jQuery);
    </script>
    <?php
});