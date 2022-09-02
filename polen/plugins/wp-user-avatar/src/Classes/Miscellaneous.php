<?php namespace ProfilePress\Core\Classes;

namespace ProfilePress\Core\Classes;

class Miscellaneous
{
    public function __construct()
    {
        $basename = plugin_basename(PROFILEPRESS_SYSTEM_FILE_PATH);
        $prefix   = is_network_admin() ? 'network_admin_' : '';
        add_filter("{$prefix}plugin_action_links_$basename", [$this, 'action_links'], 10, 4);
        // slim seo compatibility
        add_filter('slim_seo_skipped_shortcodes', [$this, 'skip_ppress_shortcodes']);
        add_action('admin_bar_menu', [$this, 'maybe_add_store_mode_admin_bar_menu'], 9999);

        add_action('wp_print_styles', [$this, 'store_mode_admin_bar_print_link_styles']);
        add_action('admin_print_styles', [$this, 'store_mode_admin_bar_print_link_styles']);

        add_action('admin_init', [$this, 'register_privacy_policy_template']);

        add_filter('display_post_states', [$this, 'add_display_page_states'], 10, 2);
    }

    /**
     * Add a page display state for special ProfilePress pages in the page list table.
     *
     * @param array $post_states An array of post display states.
     * @param \WP_Post $post The current post object.
     *
     * @return array
     */
    function add_display_page_states($post_states, $post)
    {
        $mark = '<img style="width:15px;vertical-align:middle;margin-bottom:4px;" src="' . PPRESS_ASSETS_URL . '/images/logo-icomark.png" title="ProfilePress">';

        switch ($post->ID) {
            case ppress_settings_by_key('checkout_page_id', 0, true):
            case ppress_settings_by_key('set_login_url', 0, true):
            case ppress_settings_by_key('set_registration_url', 0, true):
            case ppress_settings_by_key('set_lost_password_url', 0, true):
            case ppress_settings_by_key('edit_user_profile_url', 0, true):
            case ppress_settings_by_key('set_user_profile_shortcode', 0, true):
            case ppress_settings_by_key('payment_success_page_id', 0, true):
            case ppress_settings_by_key('payment_failure_page_id', 0, true):
            case ppress_settings_by_key('terms_page_id', 0, true):
                $post_states['ppress_page'] = $mark;
                break;
        }

        return $post_states;
    }

    /**
     * Register the template for a privacy policy.
     *
     * Note, this is just a suggestion and should be customized to meet your businesses needs.
     *
     */
    function register_privacy_policy_template()
    {
        if ( ! function_exists('wp_add_privacy_policy_content')) {
            return;
        }

        $content = '<p>' . esc_html__("During user registration and checkout, we'll ask you to provide information, including your name, billing address, email address, phone number, and optional account information like username and password.", 'wp-user-avatar') . '</p>';
        $content .= '<p>' . esc_html__("We may also collect your credit card number, expiration date, and security code and pass them to our payment gateway to process your purchase.", 'wp-user-avatar') . '</p>';

        $content .= '<p>' . esc_html__("We use the information we collect to:", 'wp-user-avatar') . '</p>';

        $content .= '<ul>';
        $content .= '<li>' . esc_html__("Set up your account", 'wp-user-avatar') . '</li>';
        $content .= '<li>' . esc_html__("Process payments and prevent fraud", 'wp-user-avatar') . '</li>';
        $content .= '<li>' . esc_html__("To prepopulate the checkout form for future purchases", 'wp-user-avatar') . '</li>';
        $content .= '<li>' . esc_html__("To get in touch with you if needed to discuss your order", 'wp-user-avatar') . '</li>';
        $content .= '<li>' . esc_html__("Comply with any legal obligations we have, such as calculating taxes", 'wp-user-avatar') . '</li>';
        $content .= '<li>' . esc_html__("Send you information about your account and order", 'wp-user-avatar') . '</li>';
        $content .= '<li>' . esc_html__("Respond to your requests, including refunds and complaints", 'wp-user-avatar') . '</li>';
        $content .= '<li>' . esc_html__("Send you marketing messages, if you choose to receive them", 'wp-user-avatar') . '</li>';
        $content .= '</ul>';

        $content .= '<p>' . esc_html__("We generally store information about you for as long as we need the information for the purposes we collect and use it, and we are not legally required to continue to keep it. For example, we will store your order information for tax and accounting purposes.", 'wp-user-avatar') . '</p>';
        $content .= '<p>' . esc_html__("We'll also use cookies to keep track of cart contents while you're browsing our site.", 'wp-user-avatar') . '</p>';

        wp_add_privacy_policy_content('ProfilePress', wp_kses_post(apply_filters('ppress_privacy_policy_content', $content)));
    }

    public function maybe_add_store_mode_admin_bar_menu($wp_admin_bar)
    {
        // Bail if no admin bar
        if (empty($wp_admin_bar)) {
            return;
        }

        // Bail if user cannot manage shop settings
        if ( ! current_user_can('manage_options')) {
            return;
        }

        if ( ! ppress_is_any_enabled_payment_method()) return;

        $text = ! ppress_is_test_mode() ? __('Live', 'wp-user-avatar') : __('Test Mode', 'wp-user-avatar');

        $mode = ! ppress_is_test_mode() ? 'live' : 'test';

        $wp_admin_bar->add_menu(array(
            'id'     => 'ppress-store-menu',
            'title'  => sprintf(__('ProfilePress %s', 'wp-user-avatar'), '<span class="ppress-mode ppress-mode-' . esc_attr($mode) . '">' . $text . '</span>'),
            'parent' => false,
            'href'   => add_query_arg(
                ['view' => 'payments', 'section' => 'payment-methods'],
                PPRESS_SETTINGS_SETTING_PAGE
            )
        ));
    }

    public function store_mode_admin_bar_print_link_styles()
    {

        // Bail if user cannot manage shop settings
        if ( ! current_user_can('manage_shop_settings')) {
            return;
        } ?>

        <style type="text/css" id="edd-store-menu-styling">
            #wp-admin-bar-ppress-store-menu .ppress-mode {
                color: #fff;
                background-color: #0073aa;
                padding: 3px 7px;
                font-weight: 600;
                border-radius: 3px;
                font-size: 12px;
            }

            #wp-admin-bar-ppress-store-menu .ppress-mode-live {
                background-color: #32CD32;
            }

            #wp-admin-bar-ppress-store-menu .ppress-mode-test {
                background-color: #ffde92;
                color: #a04903;
            }
        </style>

        <?php
    }

    public function action_links($actions, $plugin_file, $plugin_data, $context)
    {
        $custom_actions = array(
            'settings' => sprintf('<a href="%s">%s</a>', PPRESS_SETTINGS_SETTING_PAGE, esc_html__('Settings', 'wp-user-avatar')),
        );

        // add the links to the front of the actions list
        return array_merge($custom_actions, $actions);
    }

    public function skip_ppress_shortcodes($shortcodes)
    {
        $shortcodes[] = 'profilepress-registration';
        $shortcodes[] = 'profilepress-login';
        $shortcodes[] = 'profilepress-password-reset';
        $shortcodes[] = 'profilepress-my-account';
        $shortcodes[] = 'profilepress-member-directory';
        $shortcodes[] = 'profilepress-melange';
        $shortcodes[] = 'profilepress-user-profile';
        $shortcodes[] = 'profilepress-edit-profile';

        return $shortcodes;
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