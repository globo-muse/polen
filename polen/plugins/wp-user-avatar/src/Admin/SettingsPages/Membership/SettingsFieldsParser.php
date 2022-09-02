<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership;

use ProfilePress\Core\Membership\Models\Coupon\CouponUnit;

class SettingsFieldsParser
{
    protected $config;
    protected $dbData;
    protected $field_class;

    public function __construct($config, $dbData = [], $field_class = 'ppress-plan-control')
    {
        $this->config      = $config;
        $this->dbData      = $dbData;
        $this->field_class = $field_class;
    }

    protected function field_output($config)
    {
        $field_id    = sanitize_text_field($config['id']);
        $placeholder = esc_attr(ppress_var($config, 'placeholder', ''));

        $field_data = ppressPOST_var($field_id, $this->dbData->$field_id);

        switch ($config['type']) {
            case 'text':
                printf('<input placeholder="%4$s" class="%3$s" name="%1$s" id="%1$s" type="text" value="%2$s">', esc_attr($field_id), esc_attr($field_data), esc_attr($this->field_class), $placeholder);
                break;
            case 'number':
                printf('<input placeholder="%4$s" class="%3$s" name="%1$s" id="%1$s" type="number" value="%2$s">', esc_attr($field_id), esc_attr($field_data), esc_attr($this->field_class), $placeholder);
                break;
            case 'price':
                printf('<input class="%3$s" step="any" placeholder="0.00" name="%1$s" id="%1$s" type="number" value="%2$s">', esc_attr($field_id), esc_attr($field_data), esc_attr($this->field_class));
                break;
            case 'textarea':
                printf('<textarea class="%3$s" name="%1$s" id="%1$s">%2$s</textarea>', esc_attr($field_id), esc_attr($field_data), esc_attr($this->field_class));
                break;
            case 'discount':
                printf('<span class="ppress-amount-type-wrapper">
                            <input type="text" required="required" class="%3$s" id="amount" name="amount" value="%6$s" placeholder="0">
							<label for="ppress-discount-type" class="screen-reader-text">%s</label>
							<select name="unit" id="ppress-discount-type">
								<option value="percent" %7$s>&#37;</option>
								<option value="flat" %8$s>%5$s</option>
							</select>
						</span>',
                    esc_attr($field_id),
                    esc_attr($field_data),
                    esc_attr($this->field_class),
                    esc_html__('Discount Type', 'wp-user-avatar'),
                    ppress_get_currency_symbol(),
                    $this->dbData->amount,
                    selected($this->dbData->unit, CouponUnit::PERCENTAGE, false),
                    selected($this->dbData->unit, CouponUnit::FLAT, false)
                );
                break;
            case 'wp_editor':
                // Remove all TinyMCE plugins.
                remove_all_filters('mce_buttons', 10);
                remove_all_filters('mce_external_plugins', 10);
                remove_all_actions('media_buttons');
                // add core media button back.
                add_action('media_buttons', 'media_buttons');

                wp_editor(wp_kses_post($field_data), sanitize_text_field($field_id), ["editor_height" => 100, 'editor_class' => 'ppress-plan-control']);
                break;
            case 'select':
                if (is_array($config['options']) && ! empty($config['options'])) {
                    $is_multiple = ppress_var($config, 'multiple') === true;
                    $name        = $is_multiple ? $config['id'] . '[]' : $config['id'];
                    printf('<select class="%2$s" name="%1$s" id="%3$s"%4$s>', esc_attr($name), esc_attr($this->field_class), $config['id'], $is_multiple ? ' multiple' : '');
                    foreach ($config['options'] as $option_id => $option_name) {
                        if (is_array($field_data) || ppress_var($config, 'multiple') === true) {
                            $selected = in_array($option_id, $field_data) ? 'selected' : '';
                        } else {
                            $selected = selected($option_id, $field_data, false);
                        }
                        printf('<option value="%1$s" %3$s>%2$s</option>', $option_id, $option_name, $selected);
                    }
                    echo '</select>';
                }
                break;
            case 'radio':
                if (is_array($config['options']) && ! empty($config['options'])) {
                    foreach ($config['options'] as $option_id => $option_name) {
                        printf('<label><input type="radio" name="%4$s" value="%1$s" %3$s>%2$s</label>', $option_id, $option_name, checked($option_id, $field_data, false), $field_id);
                    }
                }
                break;
        }
    }

    public function build()
    {
        ?>
        <table class="form-table">
            <tbody>
            <?php foreach ($this->config as $config) : ?>
                <tr class="form-field" id="field-role-<?= esc_attr($config['id']) ?>">
                    <th scope="row" valign="top">
                        <label for="<?= esc_attr($config['id']) ?>"><?= sanitize_text_field($config['label']) ?></label>
                    </th>
                    <td>
                        <?php $this->field_output($config); ?>
                        <?php if ( ! empty($config['description'])) : ?>
                            <p class="description"><?php echo esc_attr($config['description']); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}