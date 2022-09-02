<?php

namespace ProfilePress\Core\Admin;

use ProfilePress\Core\Classes\FileUploader;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Classes\WPProfileFieldParserTrait;
use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\ShortcodeParser\Builder\FieldsShortcodeCallback;

class ProfileCustomFields
{
    use WPProfileFieldParserTrait;

    static private $instance;

    public $upload_errors;

    /**
     * add the extra field and update to DB
     */
    public function __construct()
    {
        add_action('show_user_profile', array($this, 'display_billing_details_fields'), 1);
        add_action('edit_user_profile', array($this, 'display_billing_details_fields'), 1);

        add_action('personal_options_update', array($this, 'save_profile_update'));
        add_action('edit_user_profile_update', array($this, 'save_profile_update'));

        add_action('user_profile_update_errors', array($this, 'file_upload_errors'), 10, 3);

        add_action('user_edit_form_tag', array($this, 'add_form_enctype'));

        add_action('admin_footer', [$this, 'js_scripts']);
    }

    public function date_field_picker($field_key)
    {
        echo sprintf('<script>jQuery(function ($) {$("#%1$s").flatpickr(%2$s);});</script>', $field_key, json_encode(
            FieldsShortcodeCallback::date_picker_config($field_key)
        ));
    }

    /**
     * Add multipart/form-data to wordpress profile admin page
     */
    public function edit_form_type()
    {
        echo ' enctype="multipart/form-data"';
    }

    public function display_billing_details_fields($user)
    {
        $billing_fields = CheckoutFields::standard_billing_fields();

        echo '<h3>' . apply_filters('ppress_billing_details_wp_profile_header', esc_html__('Billing Address (ProfilePress)', 'profilepress-pro')) . '</h3>';

        echo '<table class="form-table">';

        foreach ($billing_fields as $field_key => $field) {

            $field_data = PROFILEPRESS_sql::get_profile_custom_field_by_key($field_key);

            $label_name  = $field['label'];
            $field_type  = $field['field_type'];
            $options     = ppress_var($field_data, 'options', []);
            $description = ppress_var($field_data, 'description', '');

            // skip woocommerce core billing / shipping fields added to wordpress profile admin page.
            if (in_array($field_key, ppress_woocommerce_billing_shipping_fields())) continue;

            if (in_array($field_key, $this->core_user_fields())) continue;

            if ($field_key == CheckoutFields::BILLING_STATE) {

                $billing_country        = get_user_meta($user->ID, CheckoutFields::BILLING_COUNTRY, true);
                $billing_country_states = ppress_array_of_world_states($billing_country);

                if ( ! empty($billing_country_states)) {
                    $field_type = 'select';
                    $options    = ['' => '&mdash;&mdash;&mdash;&mdash;'] + $billing_country_states;
                }
            }


            $this->parse_custom_field($user, $label_name, $field_key, $field_type, $options, $description);
        }
        echo '</table>';
    }


    /**
     * Update user profile info.
     *
     * @param int $user_id
     */
    public function save_profile_update($user_id)
    {
        $custom_fields = ppress_custom_fields_key_value_pair(true);

        if ( ! $custom_fields || empty($custom_fields)) return;

        foreach ($custom_fields as $field_key => $field) {

            $field_data = isset($_POST[$field_key]) ? $_POST[$field_key] : '';

            $field_value = is_array($field_data) ? array_map('sanitize_textarea_field', $field_data) : sanitize_textarea_field($field_data);

            update_user_meta($user_id, $field_key, $field_value);

            do_action('ppress_after_custom_field_update', $field_key, $field_value, $user_id);
        }

        // update file uploads
        $uploads       = FileUploader::init();
        $upload_errors = '';

        foreach ($uploads as $field_key => $uploaded_filename_or_wp_error) {
            if (is_wp_error($uploads[$field_key])) {
                $upload_errors .= $uploads[$field_key]->get_error_message() . '<br/>';
                // save the error in a global state
                $this->upload_errors = $upload_errors;
            }
        }

        if (empty($upload_errors)) {
            // we get the old array of stored file for the user
            $old = get_user_meta($user_id, 'pp_uploaded_files', true);
            $old = ! empty($old) ? $old : array();

            // we loop through the array of newly uploaded files and remove any file (unsetting the file array key)
            // that isn't be updated i.e if the field is left empty, unsetting it prevent update_user_meta
            // fom overriding it.
            // we then merge the old and new uploads before saving the data to user meta table.
            foreach ($uploads as $key => $value) {
                if (is_null($value) || empty($value)) {
                    unset($uploads[$key]);
                }
            }

            update_user_meta($user_id, 'pp_uploaded_files', array_merge($old, $uploads));
        }
    }

    /**
     * Output generated files upload errors.
     *
     * @param \WP_Error $errors
     * @param string $update
     * @param \WP_User $user
     */
    public function file_upload_errors($errors, $update, $user)
    {
        if (empty($this->upload_errors)) return;

        $errors->add('file_upload_err', $this->upload_errors);
    }

    public function js_scripts()
    {
        $screen = get_current_screen();

        if ( ! isset($screen->id) || $screen->id != 'profile') {
            return;
        }
        ?>

        <script type="text/html" id="tmpl-ppress-profile-state-input">
            <input type="text" name="ppress_billing_state" id="ppress_billing_state" value="" class="regular-text">
        </script>

        <script type="text/html" id="tmpl-ppress-profile-state-select">
            <select id="ppress_billing_state" name="ppress_billing_state">
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

                    $(document).on('change', 'select[name=ppress_billing_country]', function () {

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
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}