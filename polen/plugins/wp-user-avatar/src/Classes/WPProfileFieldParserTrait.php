<?php

namespace ProfilePress\Core\Classes;

use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\ShortcodeParser\Builder\FieldsShortcodeCallback;

trait WPProfileFieldParserTrait
{
    /**
     * Add multipart/form-data to wordpress profile admin page
     */
    public function add_form_enctype()
    {
        echo ' enctype="multipart/form-data"';
    }

    protected function date_field_picker($field_key)
    {
        echo sprintf('<script>jQuery(function ($) {$("#%1$s").flatpickr(%2$s);});</script>', $field_key, json_encode(
            FieldsShortcodeCallback::date_picker_config($field_key)
        ));
    }

    private function description_markup($description)
    {
        if ( ! empty($description)) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }

    public function parse_custom_field($user, $label_name, $field_key, $field_type, $options = [], $description = '')
    {
        $input_fields_array = ['text', 'password', 'email', 'tel', 'number', 'hidden'];
        ?>
        <tr>
            <th>
                <label for="<?= $field_key; ?>"><?= htmlspecialchars_decode($label_name); ?></label>
            </th>
            <td>
                <?php if (in_array(($type = $field_type), $input_fields_array)) : ?>
                    <input type="<?= $type; ?>" name="<?= esc_attr($field_key); ?>" id="<?= $field_key; ?>" value="<?= esc_attr(get_the_author_meta($field_key, $user->ID)); ?>" class="regular-text"/>
                    <?php $this->description_markup($description); ?>
                <?php endif; ?>

                <?php if ($field_type == 'date') : ?>
                    <input type="text" name="<?= esc_attr($field_key); ?>" id="<?= $field_key; ?>" value="<?= esc_attr(get_the_author_meta($field_key, $user->ID)); ?>" class="pp_datepicker regular-text">
                    <?php $this->description_markup($description); ?>
                    <?php $this->date_field_picker($field_key); ?>
                <?php endif; ?>

                <?php if ($field_type == 'country') : ?>
                    <?php $countries = ppress_array_of_world_countries(); ?>
                    <?php $value = esc_attr(get_the_author_meta($field_key, $user->ID)); ?>
                    <select name="<?= $field_key; ?>">
                        <option value=""><?php esc_html__('Select a country&hellip;', 'profilepress-pro'); ?></option>
                        <?php foreach ($countries as $ckey => $cvalue) : ?>
                            <option value="<?= $ckey; ?>" <?php selected($value, $ckey); ?>><?= $cvalue; ?> </option>';
                        <?php endforeach; ?>
                    </select>
                    <?php $this->description_markup($description); ?>
                <?php endif; ?>

                <?php if ($field_type == 'textarea') : ?>
                    <textarea rows="5" name="<?= $field_key; ?>"><?= esc_attr(get_the_author_meta($field_key, $user->ID)); ?></textarea>
                    <?php $this->description_markup($description); ?>
                <?php endif; ?>

                <?php if ($field_type == 'radio') : ?>
                    <?php $radio_buttons = array_map('trim', explode(',', $options)); ?>
                    <?php foreach ($radio_buttons as $radio_button) : $radio_button = esc_attr($radio_button); ?>
                        <input id="<?= $radio_button ?>" type="radio" name="<?= esc_attr($field_key); ?>" value="<?= $radio_button ?>" <?php checked((get_the_author_meta($field_key, $user->ID)), $radio_button); ?> />
                        <label for="<?= $radio_button ?>"><?= $radio_button ?></label>
                        <br/>
                    <?php endforeach; ?>
                    <?php $this->description_markup($description); ?>
                <?php endif; ?>

                <?php if ($field_type == 'agreeable') {
                    $cpf_saved_data = get_the_author_meta($field_key, $user->ID);
                    $cpf_saved_data = ('1' == $cpf_saved_data) ? 'true' : $cpf_saved_data;

                    echo sprintf('<input type="hidden" name="%s" value="false" style="display: none">', $field_key);
                    echo sprintf('<input id="%1$s" type="checkbox" name="%1$s" value="true" %2$s/>', esc_attr($field_key), checked($cpf_saved_data, 'true', false));
                    echo sprintf('<label for="%1$s">%2$s</label><br/>', esc_attr($field_key), $description);
                }

                if ($field_type == 'checkbox') {
                    $checkbox_values  = array_map('trim', explode(',', $options));
                    $key              = $field_key;
                    $checkbox_tag_key = "{$key}[]";
                    $cpf_saved_data   = get_the_author_meta($field_key, $user->ID);
                    ?>
                    <?php foreach ($checkbox_values as $checkbox_value) {
                        $checked = null;
                        // if data is for multi select dropdown
                        if (is_array($cpf_saved_data) && in_array($checkbox_value, $cpf_saved_data)) {
                            $checked = 'checked="checked"';
                        } // if data is a single checkbox
                        elseif ( ! is_array($cpf_saved_data) && $checkbox_value == $cpf_saved_data) {
                            $checked = 'checked="checked"';
                        }
                        echo sprintf('<input id="%1$s" type="checkbox" name="%2$s" value="%1$s" %3$s/>', esc_attr($checkbox_value), esc_attr($checkbox_tag_key), $checked);
                        echo sprintf('<label for="%1$s">%1$s</label><br/>', esc_attr($checkbox_value));
                    }
                    $this->description_markup($description);
                }

                if ($field_type == 'select') {

                    $select_options_values = $options;
                    if ( ! is_array($options)) {
                        $select_options_values = array_map('trim', explode(',', $options));
                        // make array values the array keys too
                        $select_options_values = array_combine($select_options_values, $select_options_values);
                    }
                    $is_multi_selectable = ppress_is_select_field_multi_selectable($field_key);
                    $select_tag_key      = $is_multi_selectable ? "{$field_key}[]" : $field_key;
                    $multiple            = $is_multi_selectable ? 'multiple' : null;

                    $cpf_saved_data = get_the_author_meta($field_key, $user->ID);

                    echo "<select id='$field_key' name='$select_tag_key' $multiple>";
                    foreach ($select_options_values as $options_key => $options_value) {
                        $selected = null;
                        // if data is for multi select dropdown
                        if (is_array($cpf_saved_data) && in_array($options_key, $cpf_saved_data)) {
                            $selected = 'selected="selected"';
                        } // if data is not multi select dropdown but a single selection dropdown
                        elseif ( ! $is_multi_selectable && ! is_array($cpf_saved_data) && $options_key == $cpf_saved_data) {
                            $selected = 'selected="selected"';
                        }

                        echo sprintf('<option value="%1$s" %3$s>%2$s</option>', esc_attr($options_key), esc_attr($options_value), $selected);
                    }
                    echo '</select>';

                    $this->description_markup($description);

                    if ($is_multi_selectable === true) {
                        echo sprintf('<script>jQuery(function () {jQuery("#%s").select2({width: "350px"});});</script>', $field_key);
                    }
                }

                if ($field_type == 'file') {
                    $user_upload_data = get_user_meta($user->ID, 'pp_uploaded_files', true);
                    // if the user uploads isn't empty and there exist a file with the custom field key.
                    if ( ! empty($user_upload_data) && $filename = @$user_upload_data[$field_key]) {
                        $link = PPRESS_FILE_UPLOAD_URL . $filename;
                        echo "<p><a href='$link'>$filename</a></p>";
                    }
                    ?>
                    <input name="<?= $field_key; ?>" type="file">
                    <?php $this->description_markup($description); ?>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Array of core user profile.
     *
     * This is useful in cases where say first and last name field is added so it can be added to buddypress
     * extended profile synced to WordPress user profile.
     *
     * @return array
     */
    public function core_user_fields()
    {
        return [
            'first_name',
            'last_name',
            'user_nicename',
            'user_url',
            'display_name'
        ];
    }
}