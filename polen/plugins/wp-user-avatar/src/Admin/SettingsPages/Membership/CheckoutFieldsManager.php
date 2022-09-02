<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Custom_Settings_Page_Api;

class CheckoutFieldsManager
{
    public function __construct()
    {
        add_filter('ppress_settings_page_submenus_tabs', [$this, 'header_sub_menu_tab']);

        add_action('ppress_admin_settings_submenu_page_payments_checkout-fields', [$this, 'checkout_fields_page']);

        add_action('ppress_register_menu_page_payments_checkout-fields', function () {

            add_filter('ppress_general_settings_admin_page_title', function () {
                return esc_html__('Checkout Fields &lsaquo; Payments', 'wp-user-avatar');
            });

            add_action('admin_init', [$this, 'save_checkout_fields']);

            add_action('admin_footer', [$this, 'js_template']);
            add_action('admin_head', [$this, 'global_js_vars']);
        });
    }

    public function header_sub_menu_tab($tabs)
    {
        $tabs[174] = ['parent' => 'payments', 'id' => 'checkout-fields', 'label' => esc_html__('Checkout Fields', 'wp-user-avatar')];

        return $tabs;
    }

    public function save_checkout_fields()
    {
        if (isset($_POST['accountInfo']) || isset($_POST['billing'])) {

            check_admin_referer('wp-csa-nonce', 'wp_csa_nonce');

            update_option(CheckoutFields::DB_OPTION_NAME, [
                'accountInfo' => ppress_clean(ppressPOST_var('accountInfo', []), 'wp_kses_post'),
                'billing'     => ppress_clean(ppressPOST_var('billing', []), 'wp_kses_post'),
            ]);

            wp_safe_redirect(esc_url_raw(add_query_arg('settings-updated', 'true')));
            exit;
        }
    }

    public function checkout_fields_page()
    {
        add_filter('wp_cspa_main_content_area', function () {
            ob_start();
            require dirname(__FILE__) . '/views/checkout-fields.php';

            return ob_get_clean();
        });

        add_action('wp_cspa_form_tag', function () {
            echo 'id="ppress-checkout-field-manager-form"';
        });

        add_action('wp_cspa_before_post_body_content', function () {
            echo '<div class="ppress-submit-wrap">';
            printf('<input type="submit" name="submit" id="ppress-cfm-submit-btn" class="button button-primary" value="%s">', esc_html__('Save Changes', 'wp-user-avatar'));
            echo '</div>';
        });

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name(CheckoutFields::DB_OPTION_NAME);
        $instance->page_header(esc_html__('Checkout Fields', 'wp-user-avatar'));
        AbstractSettingsPage::register_core_settings($instance);
        $instance->build();
    }

    public static function checkout_field_addition_dropdown($fieldGroup = 'accountInfo')
    {
        $standard_fields = CheckoutFields::standard_account_info_fields();
        $custom_fields = CheckoutFields::standard_custom_fields();

        if ($fieldGroup == 'billing') {
            $standard_fields = CheckoutFields::standard_billing_fields();
            $custom_fields = [];
        }

        echo '<select class="ppress-checkout-field-list">';
        printf('<option value="">%s</option>', esc_html__('Select...', 'wp-user-avatar'));

        if ( ! empty($standard_fields)) {
            printf('<optgroup label="%s">', esc_html__('Standard Fields', 'wp-user-avatar'));
            foreach ($standard_fields as $field_key => $field) {
                printf('<option value="%s">%s</option>', $field_key, $field['label']);
            }
            echo '</optgroup>';
        }

        if ( ! empty($custom_fields)) {
            printf('<optgroup label="%s">', esc_html__('Custom Fields', 'wp-user-avatar'));
            foreach ($custom_fields as $field_key => $field) {
                printf('<option value="%s">%s</option>', $field_key, $field['label']);
            }
            echo '</optgroup>';
        }

        echo '</select>';
    }

    public function global_js_vars()
    {
        echo '<script type="text/javascript">';
        printf('var ppress_standard_acc_info_fields = %s;', wp_json_encode(CheckoutFields::standard_account_info_fields()));
        printf('var ppress_standard_billing_fields = %s;', wp_json_encode(CheckoutFields::standard_billing_fields()));
        printf('var ppress_custom_fields = %s;', wp_json_encode(CheckoutFields::standard_custom_fields()));
        printf('var ppress_account_info_fields = %s;', wp_json_encode(CheckoutFields::account_info_fields()));
        printf('var ppress_blling_address_fields = %s;', wp_json_encode(CheckoutFields::billing_fields()));
        printf('var ppress_logged_in_hidden_fields = %s;', wp_json_encode(CheckoutFields::logged_in_hidden_fields()));
        echo '</script>';
    }

    public function js_template()
    {
        ?>
        <script type="text/html" id="tmpl-ppress-checkout-field-item">
            <?php require dirname(__FILE__) . '/views/checkout-field-item.php'; ?>
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