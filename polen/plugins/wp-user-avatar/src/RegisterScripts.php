<?php

namespace ProfilePress\Core;

use ProfilePress\Core\Membership\Services\TaxService;

class RegisterScripts
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'public_css']);
        add_action('admin_enqueue_scripts', [$this, 'admin_css']);
        add_action('wp_enqueue_scripts', [$this, 'public_js']);
        add_action('admin_enqueue_scripts', [$this, 'admin_js'], 999999);
    }

    public static function asset_suffix()
    {
        return (defined('W3GUY_LOCAL') && W3GUY_LOCAL) || (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
    }

    function admin_css()
    {
        wp_enqueue_style('ppress-select2', PPRESS_ASSETS_URL . '/select2/select2.min.css');
        wp_enqueue_style('ppress-flatpickr', PPRESS_ASSETS_URL . '/flatpickr/flatpickr.min.css', false, PPRESS_VERSION_NUMBER);

        wp_enqueue_style('wp-color-picker');

        wp_enqueue_style('ppress-admin', PPRESS_ASSETS_URL . '/css/admin.min.css');

        // only load in profilepress settings pages.
        if ( ! ppress_is_admin_page()) return;

        wp_enqueue_style('ppress-hint-tooltip', PPRESS_ASSETS_URL . "/css/hint.min.css", false, PPRESS_VERSION_NUMBER);

        wp_enqueue_style('ppress-form-builder-styles', PPRESS_ASSETS_URL . '/css/form-builder.css');

        wp_enqueue_style('ppress-codemirror', PPRESS_ASSETS_URL . '/codemirror/codemirror.css');

        wp_enqueue_style('ppress-jbox', PPRESS_ASSETS_URL . '/jbox/jBox.all.min.css');
    }

    function public_css()
    {
        $suffix = self::asset_suffix();
        wp_enqueue_style('ppress-frontend', PPRESS_ASSETS_URL . "/css/frontend{$suffix}.css", false, PPRESS_VERSION_NUMBER);
        wp_enqueue_style('ppress-flatpickr', PPRESS_ASSETS_URL . '/flatpickr/flatpickr.min.css', false, PPRESS_VERSION_NUMBER);
        wp_enqueue_style('ppress-select2', PPRESS_ASSETS_URL . '/select2/select2.min.css');
    }

    private function is_tax_enabled_in_checkout()
    {
        if (TaxService::init()->is_tax_enabled()) {
            if (
                TaxService::init()->is_eu_vat_enabled() ||
                ! empty(TaxService::init()->get_tax_rates()) ||
                ! empty(TaxService::init()->get_fallback_tax_rate())
            ) {
                return true;
            }
        }

        return false;
    }

    function public_js()
    {
        $suffix = self::asset_suffix();

        $is_ajax_mode_disabled = ppress_get_setting('disable_ajax_mode') == 'yes' ? 'true' : 'false';

        wp_enqueue_script('jquery');

        if (isset($_GET['pp_preview_form']) ||
            ppress_post_content_has_shortcode('profilepress-registration') ||
            ppress_post_content_has_shortcode('profilepress-password-reset') ||
            ppress_post_content_has_shortcode('profilepress-edit-profile') ||
            ppress_post_content_has_shortcode('profilepress-my-account')
        ) {
            wp_enqueue_script('password-strength-meter');
        }

        wp_enqueue_script('ppress-flatpickr', PPRESS_ASSETS_URL . '/flatpickr/flatpickr.min.js', array('jquery'));

        wp_enqueue_script('ppress-select2', PPRESS_ASSETS_URL . '/select2/select2.min.js', array('jquery'));

        $frontend_dependencies = ['jquery', 'ppress-flatpickr', 'ppress-select2'];
        if (ppress_is_my_account_page()) {
            $frontend_dependencies[] = 'wp-util';
        }
        wp_enqueue_script('ppress-frontend-script', PPRESS_ASSETS_URL . "/js/frontend.min.js", $frontend_dependencies, PPRESS_VERSION_NUMBER, true);
        wp_localize_script('ppress-frontend-script', 'pp_ajax_form', [
            'ajaxurl'           => admin_url('admin-ajax.php'),
            'confirm_delete'    => esc_html__('Are you sure?', 'wp-user-avatar'),
            'deleting_text'     => esc_html__('Deleting...', 'wp-user-avatar'),
            'deleting_error'    => esc_html__('An error occurred. Please try again.', 'wp-user-avatar'),
            'nonce'             => wp_create_nonce('ppress-frontend-nonce'),
            'disable_ajax_form' => apply_filters('ppress_disable_ajax_form', (string)$is_ajax_mode_disabled),
            'is_checkout'       => ppress_is_checkout() ? '1' : '0',
            'is_checkout_tax_enabled'       => $this->is_tax_enabled_in_checkout() ? '1' : '0'
        ]);

        if (isset($_GET['pp_preview_form']) || ppress_post_content_has_shortcode('profilepress-member-directory')) {
            wp_enqueue_script('ppress-member-directory', PPRESS_ASSETS_URL . "/js/member-directory{$suffix}.js", ['jquery', 'jquery-masonry', 'ppress-select2', 'ppress-flatpickr'], PPRESS_VERSION_NUMBER, true);
        }

        do_action('ppress_enqueue_public_js');
    }

    function admin_js($hook)
    {
        global $pagenow, $ppress_customer_page;

        wp_enqueue_script('jquery');
        wp_enqueue_script('backbone');
        wp_enqueue_script('underscore');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('wp-util');

        wp_enqueue_script('ppress-flatpickr', PPRESS_ASSETS_URL . '/flatpickr/flatpickr.min.js', array('jquery'));

        if (in_array($pagenow, ['user-edit.php', 'profile.php']) || ppress_is_admin_page()) {
            wp_enqueue_script('ppress-select2', PPRESS_ASSETS_URL . '/select2/select2.min.js', array('jquery'));
        }

        if ( ! ppress_is_admin_page()) return;

        if ($ppress_customer_page == $hook) {
            // Load the password show/hide feature and strength meter.
            wp_enqueue_script('user-profile');
        }

        wp_enqueue_script('ppress-chartjs', PPRESS_ASSETS_URL . '/js/admin/chart.min.js', array('jquery'));
        wp_enqueue_script('ppress-reports', PPRESS_ASSETS_URL . '/js/admin/reports.js', array('jquery', 'ppress-chartjs'));

        wp_enqueue_media();

        wp_enqueue_script('ppress-jbox', PPRESS_ASSETS_URL . '/jbox/jBox.all.min.js', array('jquery'));
        wp_enqueue_script('ppress-jbox-init', PPRESS_ASSETS_URL . '/jbox/init.js', array('ppress-jbox'));

        wp_enqueue_script('ppress-clipboardjs', PPRESS_ASSETS_URL . '/js/clipboard.min.js');

        wp_enqueue_script('ppress-admin-scripts', PPRESS_ASSETS_URL . '/js/admin.js', array('jquery', 'jquery-ui-sortable'));

        wp_localize_script('ppress-admin-scripts', 'ppress_admin_globals', [
            'nonce' => wp_create_nonce('ppress-admin-nonce')
        ]);

        if (ppressGET_var('section') == 'checkout-fields') {
            wp_enqueue_script('ppress-checkout-field-manager', PPRESS_ASSETS_URL . '/js/checkout-fields-manager.js');
        }

        wp_enqueue_script('ppress-create-form', PPRESS_ASSETS_URL . '/js/create-form.js', array('jquery'));
        wp_enqueue_script('ppress-content-control', PPRESS_ASSETS_URL . '/js/content-control.js', array('jquery'));
        wp_enqueue_script(
            'ppress-form-builder',
            PPRESS_ASSETS_URL . '/js/builder/app.min.js',
            ['jquery', 'backbone', 'wp-util', 'jquery-ui-draggable', 'jquery-ui-core', 'jquery-ui-sortable', 'wp-color-picker']
        );

        wp_localize_script('ppress-form-builder', 'pp_form_builder', [
            'confirm_delete' => esc_html__('Are you sure?', 'wp-user-avatar')
        ]);

        wp_enqueue_script('ppress-jquery-blockui', PPRESS_ASSETS_URL . '/js/jquery.blockUI.js', array('jquery'));

        wp_enqueue_script('ppress-codemirror', PPRESS_ASSETS_URL . '/codemirror/codemirror.js');
        wp_enqueue_script('ppress-codemirror-css', PPRESS_ASSETS_URL . '/codemirror/css.js', ['ppress-codemirror']);
        wp_enqueue_script('ppress-codemirror-javascript', PPRESS_ASSETS_URL . '/codemirror/javascript.js', ['ppress-codemirror']);
        wp_enqueue_script('ppress-codemirror-xml', PPRESS_ASSETS_URL . '/codemirror/xml.js', ['ppress-codemirror']);
        wp_enqueue_script('ppress-codemirror-htmlmixed', PPRESS_ASSETS_URL . '/codemirror/htmlmixed.js', ['ppress-codemirror']);
    }

    /**
     * @return self
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}