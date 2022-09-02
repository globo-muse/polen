<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods as PaymentGateways;
use ProfilePress\Custom_Settings_Page_Api;

/** @todo make payment method drag and drop to work */
class PaymentMethods
{
    public function __construct()
    {
        add_filter('ppress_settings_page_submenus_tabs', [$this, 'header_sub_menu_tab']);

        add_action('ppress_admin_settings_submenu_page_payments_payment-methods', [$this, 'payment_methods_page']);

        add_action('ppress_register_menu_page_payments_payment-methods', function () {

            add_filter('ppress_general_settings_admin_page_title', function () {
                return esc_html__('Payment Methods &lsaquo; Payments', 'wp-user-avatar');
            });

            add_action('admin_footer', [$this, 'js_script']);
        });
    }

    public function header_sub_menu_tab($tabs)
    {
        $tabs[173] = ['parent' => 'payments', 'id' => 'payment-methods', 'label' => esc_html__('Payment Methods', 'wp-user-avatar')];

        return $tabs;
    }

    public function payment_method_list()
    {
        ob_start();
        require dirname(__FILE__) . '/views/payment-method-list.php';

        return ob_get_clean();
    }

    protected function get_enabled_payment_methods()
    {
        $methods = PaymentGateways::get_instance()->get_enabled_methods();

        $bucket = ['' => __('&mdash; No gateway &mdash;', 'wp-user-avatar')];

        foreach ($methods as $id => $method) {
            $bucket[$id] = $method->get_method_title();
        }

        return $bucket;
    }

    public function payment_methods_page()
    {
        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name(PPRESS_PAYMENT_METHODS_OPTION_NAME);
        $instance->page_header(esc_html__('Payment Methods', 'wp-user-avatar'));

        if ( ! empty($_GET['method'])) {

            $method = PaymentGateways::get_instance()->get_by_id(
                sanitize_text_field($_GET['method'])
            );

            if ( ! $method) {
                wp_safe_redirect(add_query_arg(['view' => 'payments', 'section' => 'payment-methods'], PPRESS_SETTINGS_SETTING_PAGE));
                exit;
            }

            $instance->page_header($method->get_method_title());

            add_action('wp_cspa_after_header', function () use ($method) {
                echo '<p>' . $method->get_method_description() . '</p>';
            });

            $settings = [];
            foreach ($method->admin_settings() as $key => $setting) {
                $settings[0][$method->id . '_' . $key] = $setting;
            }

        } else {

            $instance->add_view_classes('ppress-payment-methods-list');

            $settings = [
                [
                    'payment_method_list'           => [
                        'type' => 'arbitrary',
                        'data' => $this->payment_method_list(),
                    ],
                    'test_mode'                     => [
                        'label'       => esc_html__('Test Mode', 'wp-user-avatar'),
                        'description' => esc_html__('When test mode is enabled, no live transactions are processed. Use test mode in conjunction with the sandbox/test account for the payment method to test.', 'wp-user-avatar'),
                        'type'        => 'checkbox'
                    ],
                    'test_mode_reconnection_notice' => [
                        'label' => '',
                        'data'  => '<style>#test_mode_reconnection_notice_row {display:none}</style><div class="pp-alert-notice pp-alert-notice-info"><p>' . __('Switching test/live modes might require payment methods reconnection or setup.', 'wp-user-avatar') . '</p></div>',
                        'type'  => 'custom_field_block'
                    ],
                    'default_payment_method'        => [
                        'label'       => esc_html__('Default Payment Method', 'wp-user-avatar'),
                        'description' => esc_html__('Select payment method to automatically load on the checkout page. If empty, the first active method is selected instead.', 'wp-user-avatar'),
                        'type'        => 'select',
                        'options'     => $this->get_enabled_payment_methods()
                    ]
                ]
            ];

            add_action('wp_cspa_after_header', function () {
                echo '<p>' . esc_html__('Installed payment methods are listed below. Drag and drop to control their display order on the frontend.', 'wp-user-avatar') . '</p>';
            });
        }

        $instance->main_content($settings);
        $instance->remove_white_design();
        AbstractSettingsPage::register_core_settings($instance, true);
        $instance->build(true);
    }

    public function js_script()
    {
        ?>
        <script type="text/javascript">
            (function ($) {
                $(function () {
                    $('#test_mode').on('change', function () {
                        $('#test_mode_reconnection_notice_row').show();
                    });
                });
            })(jQuery)
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