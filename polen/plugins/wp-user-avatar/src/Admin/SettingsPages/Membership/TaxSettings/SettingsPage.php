<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\TaxSettings;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Custom_Settings_Page_Api;

class SettingsPage
{
    public function __construct()
    {
        add_filter('ppress_settings_page_submenus_tabs', [$this, 'header_sub_menu_tab']);

        add_action('ppress_admin_settings_submenu_page_payments_taxes', [$this, 'taxes_page']);

        add_action('ppress_register_menu_page_payments_taxes', function () {

            add_filter('ppress_general_settings_admin_page_title', function () {
                return esc_html__('Taxes &lsaquo; Payments', 'wp-user-avatar');
            });

            add_action('admin_enqueue_scripts', [$this, 'enqueue_script']);

            add_action('admin_footer', [$this, 'js_template']);
        });
    }

    public function header_sub_menu_tab($tabs)
    {
        $tabs[175] = ['parent' => 'payments', 'id' => 'taxes', 'label' => esc_html__('Taxes', 'wp-user-avatar')];

        return $tabs;
    }

    public function tax_rate_setup_ui()
    {
        ob_start();
        require dirname(dirname(__FILE__)) . '/views/tax-rates-setup.php';

        return ob_get_clean();
    }

    public function taxes_page()
    {
        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name(PPRESS_TAXES_OPTION_NAME);
        $instance->page_header(esc_html__('Taxes', 'wp-user-avatar'));
        $instance->add_view_classes('ppress-tax-rate-list');

        add_action('wp_cspa_after_settings_tab', function ($option_name) {
            if ($option_name !== PPRESS_TAXES_OPTION_NAME) return;
            echo '<div class="notice notice-info">';
            echo '<p>';
            echo sprintf(__('%1$sDisclaimer %2$s- By using this feature, you\'ve agreed that the use of this feature cannot be considered as tax advice. We recommend consulting a local tax professional for tax compliance or if you have any tax specific questions.', 'wp-user-avatar'), '<strong>', '</strong>');
            echo '</p>';
            echo '</div>';
        });

        $settings = [
            [
                'enable_tax'         => [
                    'label'          => esc_html__('Set up Taxes', 'wp-user-avatar'),
                    'checkbox_label' => esc_html__('Enable Taxes', 'wp-user-avatar'),
                    'description'    => esc_html__('With taxes enabled, customers will be taxed based on the rates you define, and are required to input their address on checkout so rates can be calculated accordingly.', 'wp-user-avatar'),
                    'type'           => 'checkbox'
                ],
                'prices_include_tax' => [
                    'label'   => esc_html__('Prices Include Tax', 'wp-user-avatar'),
                    'type'    => 'select',
                    'options' => [
                        'no'  => esc_html__('No, I will enter prices exclusive of tax', 'wp-user-avatar'),
                        'yes' => esc_html__('Yes, I will enter prices inclusive of tax', 'wp-user-avatar'),
                    ]
                ],
                'tax_based_on'       => [
                    'label'       => esc_html__('Calculate Tax Based On', 'wp-user-avatar'),
                    'type'        => 'select',
                    'options'     => [
                        'billing' => esc_html__('Customer Billing Address', 'wp-user-avatar'),
                        'base'    => esc_html__('Shop Base Address', 'wp-user-avatar'),
                    ],
                    'description' => sprintf(
                        esc_html__('If "Shop Base Address" is selected, Tax will be calculated based on the location of your %sbusiness in Settings%s.', 'wp-user-avatar'),
                        '<a target="_blank" href="' . PPRESS_SETTINGS_SETTING_PAGE . '#business_info">', '</a>'
                    )
                ]
            ],
            [
                'section_title'             => esc_html__('EU VAT Settings', 'wp-user-avatar'),
                'enable_eu_vat'             => [
                    'type'        => 'checkbox',
                    'label'       => esc_html__('Enable EU VAT', 'wp-user-avatar'),
                    'description' => esc_html__('When this is checked, VAT taxes will be calculated for any customers who are located in the European Union. The plugin comes with the current standard VAT rate for each EU country. You can change these as required in the Tax Rates section below.', 'wp-user-avatar'),
                ],
                'eu_vat_disable_validation' => [
                    'type'           => 'checkbox',
                    'checkbox_label' => esc_html__('Disable Validation', 'wp-user-avatar'),
                    'label'          => esc_html__('Disable VAT Number Validation', 'wp-user-avatar'),
                    'description'    => esc_html__('When this option is checked, the VAT number will not be validated by VIES online service.', 'wp-user-avatar'),
                ],
                'eu_vat_same_country_rule'  => [
                    'type'        => 'select',
                    'label'       => esc_html__('Same Country Rule', 'wp-user-avatar'),
                    'description' => esc_html__('What should happen if a customer is from the same EU country as your business?.', 'wp-user-avatar'),
                    'options'     => [
                        'no_charge'          => esc_html__('Do not charge tax', 'wp-user-avatar'),
                        'charge_unvalidated' => esc_html__('Charge tax unless VAT number is validated', 'wp-user-avatar'),
                        'charge_always'      => esc_html__('Charge tax even if VAT number is validated', 'wp-user-avatar'),
                    ]
                ],
                'eu_vat_number_label'       => [
                    'type'        => 'text',
                    'value'       => esc_html__('VAT Number', 'wp-user-avatar'),
                    'label'       => esc_html__('VAT Number Field Label', 'wp-user-avatar'),
                    'description' => esc_html__('The label that appears at checkout for the VAT number field.', 'wp-user-avatar'),
                ],
            ],
            [
                'section_title'     => esc_html__('Tax Rates', 'wp-user-avatar'),
                'tax_rates'         => [
                    'label'       => esc_html__('Set up Tax Rates', 'wp-user-avatar'),
                    'description' => esc_html__('Add rates for each region you wish to collect tax in. Enter a percentage, such as 6.5 for 6.5%.', 'wp-user-avatar'),
                    'type'        => 'custom_field_block',
                    'data'        => $this->tax_rate_setup_ui()
                ],
                'fallback_tax_rate' => [
                    'label'       => esc_html__('Fallback Tax Rate', 'wp-user-avatar'),
                    'description' => esc_html__('Customers not in a specific rate will be charged this tax rate. Enter a percentage, such as 6.5 for 6.5%. ', 'wp-user-avatar'),
                    'type'        => 'text',
                ]
            ]
        ];

        $instance->main_content($settings);
        $instance->remove_white_design();
        AbstractSettingsPage::register_core_settings($instance, true);
        $instance->build(true);
    }

    public static function tax_rate_row($index = '0', $country = '', $state = '', $global = '', $rate = '')
    {
        $country_states = ! empty($country) ? ppress_array_of_world_states($country) : [];
        ?>
        <tr data-row-index="<?= $index ?>">
            <td class="ppress-tax-rate-table-country">
                <select name="ppress_taxes[tax_rates][<?= $index ?>][country]">
                    <option value=""><?= esc_html__('Choose a Country', 'wp-user-avatar') ?></option>
                    <?php foreach (ppress_array_of_world_countries() as $id => $label): ?>
                        <option value="<?= $id ?>" <?php selected($id, $country) ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td class="ppress-tax-rate-table-state">
                <?php if ( ! empty($country_states)) : ?>
                    <select name="ppress_taxes[tax_rates][<?= $index ?>][state]">
                        <option value="">&mdash;&mdash;&mdash;</option>
                        <?php foreach ($country_states as $key => $value) : ?>
                            <option value="<?= $key ?>" <?php selected($key, $state) ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <input type="text" name="ppress_taxes[tax_rates][<?= $index ?>][state]" value="<?= esc_attr($state) ?>">
                <?php endif; ?>
            </td>
            <td class="ppress-tax-rate-table-country-wide">
                <input type="hidden" name="ppress_taxes[tax_rates][<?= $index ?>][global]" value="0">
                <input type="checkbox" name="ppress_taxes[tax_rates][<?= $index ?>][global]" id="ppress_taxes[tax_rates][<?= $index ?>][global]" value="1" <?php checked($global, '1') ?>>
                <label for="ppress_taxes[tax_rates][<?= $index ?>][global]">
                    <?= esc_html__('Apply to whole country', 'wp-user-avatar') ?>
                </label>
            </td>
            <td class="ppress-tax-rate-table-rate">
                <input style="width:75px !important" type="number" class="small-text" min="0.0" step="any" name="ppress_taxes[tax_rates][<?= $index ?>][rate]" value="<?= esc_attr($rate) ?>">
            </td>
            <td class="ppress-tax-rate-table-actions">
                <span class="ppress-tax-rate-icon"><span class="dashicons dashicons-no-alt"></span></span>
            </td>
        </tr>
        <?php
    }

    public function enqueue_script()
    {
        wp_enqueue_script(
            'ppress-admin-tax-rate',
            PPRESS_ASSETS_URL . '/js/admin/tax-rate-repeater.js',
            ['jquery', 'wp-util']
        );
    }

    public function js_template()
    {
        ?>
        <script type="text/html" id="tmpl-ppress-tax-rate-row">
            <?php self::tax_rate_row('{{data.index}}'); ?>
        </script>

        <script type="text/html" id="tmpl-ppress-tax-rate-empty-row">
            <tr class="ppress-tax-rate-row--is-empty">
                <td colspan="5"><?php esc_html_e('No rates found.', 'wp-user-avatar') ?></td>
            </tr>
        </script>

        <script type="text/html" id="tmpl-ppress-tax-rate-state-input">
            <input type="text" name="ppress_taxes[tax_rates][{{data.index}}][state]" value="">
        </script>

        <script type="text/html" id="tmpl-ppress-tax-rate-state-select">
            <select type="text" name="ppress_taxes[tax_rates][{{data.index}}][state]">
                <option value="">&mdash;&mdash;&mdash;</option>
                <# jQuery.each(data.options, function(index, value) { #>
                <option value="{{index}}">{{value}}</option>
                <# }); #>
            </select>
        </script>

        <script type="text/javascript">
            var ppress_countries_states = <?php echo wp_json_encode(array_filter(ppress_array_of_world_states())); ?>;
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