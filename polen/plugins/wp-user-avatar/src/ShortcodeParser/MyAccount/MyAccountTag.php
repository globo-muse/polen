<?php

namespace ProfilePress\Core\ShortcodeParser\MyAccount;

use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Classes\UserAvatar;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Services\SubscriptionService;
use ProfilePress\Core\ShortcodeParser\FormProcessor;

class MyAccountTag extends FormProcessor
{
    public function __construct()
    {
        add_shortcode('profilepress-my-account', [$this, 'parse_shortcode']);

        add_action('init', [$this, 'add_endpoints']);

        add_action('wp', [$this, 'redirect_non_logged_in_users']);

        if ( ! is_admin()) {
            add_filter('query_vars', [$this, 'add_query_vars'], 99);
            add_action('parse_request', [$this, 'parse_request'], 999999999);
            add_action('pre_get_posts', array($this, 'pre_get_posts'));
            add_filter('pre_get_document_title', [$this, 'page_endpoint_title'], 999999999);
            add_filter('wp_title', [$this, 'page_endpoint_title'], 999999999);

            add_action('wp', [$this, 'handle_subscription_actions']);
            add_action('wp', [$this, 'process_myaccount_change_password']);
            add_action('wp', [$this, 'process_edit_profile_form'], 999999999);
        }
    }

    public function redirect_non_logged_in_users()
    {
        // check if the page being viewed contains the "profilepress-my-account" shortcode. if true, redirect to login page
        if (ppress_post_content_has_shortcode('profilepress-my-account')) {
            if ( ! is_user_logged_in()) {
                nocache_headers();
                wp_safe_redirect(ppress_login_url());
                exit;
            }
        }
    }

    public static function myaccount_tabs()
    {
        static $cache = false;

        if ($cache === false) {

            $tabs = [
                'ppmyac-dashboard'   => [
                    'title'    => esc_html__('Dashboard', 'wp-user-avatar'),
                    'priority' => 10,
                    'icon'     => 'home'
                ],
                'list-subscriptions' => [
                    'title'    => esc_html__('Subscriptions', 'wp-user-avatar'),
                    /** @todo implement customizing this endpoint */
                    'endpoint' => esc_html(ppress_settings_by_key('myac_subscriptions_endpoint', 'list-subscriptions', true)),
                    'priority' => 20,
                    'icon'     => 'card_membership',
                    'callback' => [__CLASS__, 'subscriptions_callback']
                ],
                'list-orders'        => [
                    'title'    => esc_html__('Orders', 'wp-user-avatar'),
                    /** @todo implement customizing this endpoint */
                    'endpoint' => esc_html(ppress_settings_by_key('myac_orders_endpoint', 'list-orders', true)),
                    'priority' => 30,
                    'icon'     => 'shopping_cart',
                    'callback' => [__CLASS__, 'orders_callback']
                ],
                'billing-details'    => [
                    'title'    => esc_html__('Billing Address', 'wp-user-avatar'),
                    /** @todo implement customizing this endpoint */
                    'endpoint' => esc_html(ppress_settings_by_key('myac_billing_details_endpoint', 'my-billing-address', true)),
                    'priority' => 40,
                    'icon'     => 'map',
                    'callback' => [__CLASS__, 'billing_details_callback']
                ],
                'edit-profile'       => [
                    'title'    => esc_html__('Account Details', 'wp-user-avatar'),
                    'endpoint' => esc_html(ppress_settings_by_key('myac_edit_account_endpoint', 'edit-profile', true)),
                    'priority' => 45,
                    'icon'     => 'account_box',
                    'callback' => [__CLASS__, 'edit_profile_callback']
                ],
                'change-password'    => [
                    'title'    => esc_html__('Change Password', 'wp-user-avatar'),
                    'endpoint' => esc_html(ppress_settings_by_key('myac_change_password_endpoint', 'change-password', true)),
                    'priority' => 50,
                    'icon'     => 'vpn_key',
                    'callback' => [__CLASS__, 'change_password_callback']
                ],
                'ppmyac-user-logout' => [
                    'title'    => esc_html__('Logout', 'wp-user-avatar'),
                    'priority' => 99,
                    'icon'     => 'exit_to_app'
                ],
            ];

            if ( ! empty(self::email_notification_endpoint_content())) {

                $tabs['email-notifications'] = [
                    'title'    => esc_html__('Email Notifications', 'wp-user-avatar'),
                    'endpoint' => esc_html(ppress_settings_by_key('myac_email_notifications_endpoint', 'email-notifications', true)),
                    'priority' => 55,
                    'icon'     => 'email',
                    'callback' => [__CLASS__, 'email_notification_callback']
                ];
            }

            if ( ! empty(self::account_settings_endpoint_content())) {

                $tabs['account-settings'] = [
                    'title'    => esc_html__('Account Settings', 'wp-user-avatar'),
                    'endpoint' => esc_html(ppress_settings_by_key('myac_account_settings_endpoint', 'account-settings', true)),
                    'priority' => 40,
                    'icon'     => 'settings',
                    'callback' => [__CLASS__, 'account_settings_callback']
                ];
            }

            $tabs = apply_filters('ppress_myaccount_tabs', $tabs);

            $cache = wp_list_sort($tabs, 'priority', 'ASC', true);
        }

        return $cache;
    }

    public static function email_notification_endpoint_content()
    {
        static $cache = false;

        if ( ! $cache) {
            // ['title'=>'Title', 'content' => 'content here']
            $cache = apply_filters('ppmyac_email_notification_endpoint_content', []);
        }

        return $cache;
    }

    public static function account_settings_endpoint_content()
    {
        static $cache = false;

        if ( ! $cache) {
            // ['title'=>'Title', 'content' => 'content here']
            $cache = apply_filters('ppmyac_account_settings_endpoint_content', []);
        }

        return $cache;
    }

    public function email_notification_callback()
    {
        require apply_filters('ppress_my_account_email_notification_template', wp_normalize_path(dirname(__FILE__) . '/email-notifications.tmpl.php'));
    }

    public function account_settings_callback()
    {
        require apply_filters('ppress_my_account_account_settings_template', wp_normalize_path(dirname(__FILE__) . '/account-settings.tmpl.php'));
    }

    public function change_password_callback()
    {
        require apply_filters('ppress_my_account_change_password_template', wp_normalize_path(dirname(__FILE__) . '/change-password.tmpl.php'));
    }

    public function display_name_select_dropdown()
    {
        ?>
        <select name="eup_display_name" id="eup_display_name" class="profilepress-myaccount-form-control">
            <?php
            $profileuser                        = wp_get_current_user();
            $public_display                     = array();
            $public_display['display_nickname'] = $profileuser->nickname;
            $public_display['display_username'] = $profileuser->user_login;

            if ( ! empty($profileuser->first_name)) {
                $public_display['display_firstname'] = $profileuser->first_name;
            }

            if ( ! empty($profileuser->last_name)) {
                $public_display['display_lastname'] = $profileuser->last_name;
            }

            if ( ! empty($profileuser->first_name) && ! empty($profileuser->last_name)) {
                $public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
                $public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
            }

            if ( ! in_array($profileuser->display_name, $public_display, true)) { // Only add this if it isn't duplicated elsewhere.
                $public_display = array('display_displayname' => $profileuser->display_name) + $public_display;
            }

            $public_display = array_map('trim', $public_display);
            $public_display = array_unique($public_display);

            foreach ($public_display as $id => $item) {
                ?>
                <option <?php selected($profileuser->display_name, $item); ?>><?php echo $item; ?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }

    public function edit_profile_callback()
    {
        require apply_filters('ppress_my_account_edit_profile_template', wp_normalize_path(dirname(__FILE__) . '/edit-profile.tmpl.php'));
    }

    public function handle_subscription_actions()
    {
        if (isset($_GET['ppress_myac_sub_action'], $_GET['sub_id'])) {

            $action = sanitize_text_field($_GET['ppress_myac_sub_action']);
            $sub_id = (int)$_GET['sub_id'];
            $sub    = SubscriptionFactory::fromId((int)$_GET['sub_id']);

            check_admin_referer($sub_id . $action);

            if ($action == 'cancel') {
                $sub->cancel(true);
            }

            if ($action == 'resubscribe') {
                wp_safe_redirect(ppress_plan_checkout_url($sub->plan_id));
                exit;
            }

            do_action('ppress_handle_subscription_actions', $action, $sub);

            wp_safe_redirect(
                add_query_arg(
                    ['ppress-myac-sub-message' => $action],
                    SubscriptionService::init()->frontend_view_sub_url($sub_id)
                )
            );
            exit;
        }
    }

    public function subscriptions_callback()
    {
        if ( ! empty($_GET['sub_id'])) {
            add_action('ppress_myaccount_subscription_action_status', function ($sub, $action) {
                switch ($action) {
                    case 'cancel':
                        self::alert_message(esc_html__('Subscription successfully cancelled.', 'wp-user-avatar'));
                        break;
                }
            }, 10, 2);

            require apply_filters('ppress_my_account_view_subscription_template', wp_normalize_path(dirname(__FILE__) . '/view-subscription.tmpl.php'));

            return;
        }

        require apply_filters('ppress_my_account_orders_template', wp_normalize_path(dirname(__FILE__) . '/subscriptions.tmpl.php'));
    }

    public function orders_callback()
    {
        if ( ! empty($_GET['order_key'])) {
            require apply_filters('ppress_my_account_view_order_template', wp_normalize_path(dirname(__FILE__) . '/view-order.tmpl.php'));

            return;
        }

        require apply_filters('ppress_my_account_orders_template', wp_normalize_path(dirname(__FILE__) . '/orders.tmpl.php'));
    }

    public function billing_details_callback()
    {
        require apply_filters('ppress_my_account_billing_details_template', wp_normalize_path(dirname(__FILE__) . '/billing-details.tmpl.php'));
    }

    public static function alert_message($message, $type = 'success')
    {
        $type = $type == 'error' ? 'danger' : $type;
        printf('<div class="profilepress-myaccount-alert pp-alert-%s" role="alert">', $type);
        echo $message;
        echo '</div>';
    }

    public function page_endpoint_title($title)
    {
        if (is_page() && self::is_endpoint()) {
            $endpoint       = $this->get_current_endpoint();
            $endpoint_title = $this->get_endpoint_title($endpoint);
            $title          = ! empty($endpoint_title) ? $endpoint_title . ' - ' . get_bloginfo('name') : $title;
        }

        return $title;
    }

    public function get_endpoint_title($endpoint)
    {
        $title = '';

        $endpoint_args = ppress_var(self::myaccount_tabs(), $endpoint, []);

        if ($endpoint_args['title']) {
            $title = $endpoint_args['title'];
        }

        return apply_filters('ppress_myaccount_endpoint_' . $endpoint . '_title', $title, $endpoint);
    }

    /**
     * @return string
     */
    public function get_current_endpoint()
    {
        global $wp;

        foreach (self::myaccount_tabs() as $key => $value) {
            if (isset($wp->query_vars[$key])) {
                return $key;
            }
        }

        return '';
    }

    /**
     * @return bool
     */
    private function is_showing_page_on_front($q)
    {
        return ($q->is_home() && ! $q->is_posts_page) && 'page' === get_option('show_on_front');
    }

    private function page_on_front_is($page_id)
    {
        return absint(get_option('page_on_front')) === absint($page_id);
    }

    public function remove_post_query()
    {
        remove_action('pre_get_posts', array($this, 'pre_get_posts'));
    }

    /**
     * @param \WP_Query $q Query instance.
     */
    public function pre_get_posts($q)
    {
        // We only want to affect the main query.
        if ( ! $q->is_main_query()) {
            return;
        }

        // Fixes for queries on static homepages.
        if ($this->is_showing_page_on_front($q)) {

            // Fix for endpoints on the homepage.
            if ( ! $this->page_on_front_is($q->get('page_id'))) {
                $_query = wp_parse_args($q->query);
                if ( ! empty($_query) && array_intersect(array_keys($_query), array_keys(self::myaccount_tabs()))) {
                    $q->is_page     = true;
                    $q->is_home     = false;
                    $q->is_singular = true;
                    $q->set('page_id', (int)get_option('page_on_front'));
                    add_filter('redirect_canonical', '__return_false');
                }
            }
        }
    }

    public function parse_request()
    {
        global $wp;

        $tabs = $this->myaccount_tabs();

        if (is_array($tabs)) {
            // Map query vars to their keys, or get them if endpoints are not supported.
            foreach ($tabs as $key => $tab) {
                $endpoint = self::get_tab_endpoint($key);
                if (isset($_GET[$endpoint])) {
                    $wp->query_vars[$key] = sanitize_text_field(wp_unslash($_GET[$endpoint]));
                } elseif (isset($wp->query_vars[$endpoint])) {
                    $wp->query_vars[$key] = $wp->query_vars[$endpoint];
                }
            }
        }
    }

    public function get_endpoints_mask()
    {
        if ('page' === get_option('show_on_front')) {
            $page_on_front     = get_option('page_on_front');
            $myaccount_page_id = ppress_settings_by_key('edit_user_profile_url');

            if (in_array($page_on_front, array($myaccount_page_id))) {
                return EP_ROOT | EP_PAGES;
            }
        }

        return EP_PAGES;
    }

    function add_endpoints()
    {
        $mask = $this->get_endpoints_mask();

        foreach ($this->myaccount_tabs() as $key => $tab) {
            $endpoint = self::get_tab_endpoint($key);
            add_rewrite_endpoint($endpoint, $mask);
        }
    }

    /**
     * Add query vars.
     *
     * @param array $vars Query vars.
     *
     * @return array
     */
    public function add_query_vars($vars)
    {
        foreach ($this->myaccount_tabs() as $key => $var) {
            $vars[] = $key;
        }

        return $vars;
    }

    public static function get_tab_endpoint($tab_key)
    {
        $endpoint = $tab_key;

        $tab = ppress_var(self::myaccount_tabs(), $tab_key);

        if (isset($tab['endpoint'])) $endpoint = $tab['endpoint'];

        return $endpoint;
    }

    public static function is_endpoint($tab_key = false)
    {
        global $wp;

        if ($tab_key) {

            $query_vars = $wp->query_vars;
            unset($query_vars['page']);
            unset($query_vars['pagename']);

            if ($tab_key == 'ppmyac-dashboard' && empty($query_vars)) {
                return true;
            }

            return isset($wp->query_vars[$tab_key]);
        }

        $endpoints = self::myaccount_tabs();

        foreach ($endpoints as $key => $value) {
            if (isset($wp->query_vars[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $tab_key
     *
     * @return callable|mixed|bool
     */
    public static function get_tab_callback($tab_key)
    {
        $tab = ppress_var(self::myaccount_tabs(), $tab_key);

        if (isset($tab['callback'])) {
            return $tab['callback'];
        }

        return false;
    }

    public static function get_endpoint_url($tab_key)
    {
        $endpoint = self::get_tab_endpoint($tab_key);

        if ('ppmyac-dashboard' === $endpoint) {
            return ppress_my_account_url();
        }

        if ('ppmyac-user-logout' === $endpoint) {
            return wp_logout_url();
        }

        $permalink = get_permalink();

        $myac_page_id = ppress_settings_by_key('edit_user_profile_url');

        if ( ! empty($myac_page_id) && get_post_status($myac_page_id)) {
            $permalink = get_permalink($myac_page_id);
        }

        if (get_option('permalink_structure')) {
            if (strstr($permalink, '?')) {
                $query_string = '?' . wp_parse_url($permalink, PHP_URL_QUERY);
                $permalink    = current(explode('?', $permalink));
            } else {
                $query_string = '';
            }

            $url = trailingslashit($permalink);

            $url .= user_trailingslashit($endpoint);

            $url .= $query_string;
        } else {
            $url = add_query_arg($endpoint, '', $permalink);
        }

        return $url;
    }

    /**
     * Shortcode callback function to parse the shortcode.
     *
     * @param $atts
     *
     * @return string
     */
    public function parse_shortcode()
    {
        add_action('wp_footer', [$this, 'js_script']);

        do_action('ppress_my_account_shortcode_callback');

        global $wp;

        ob_start();

        $user_id = get_current_user_id();

        $tabs = $this->myaccount_tabs();

        ?>
        <div id="profilepress-myaccount-wrapper">
            <div class="profilepress-myaccount-row">
                <div class="profilepress-myaccount-col-sm-3">

                    <div class="profilepress-myaccount-avatar-wrap">

                        <div class="profilepress-myaccount-avatar">
                            <a href="<?= ppress_get_frontend_profile_url($user_id) ?>">
                                <?= UserAvatar::get_avatar_img($user_id, 120); ?>
                            </a>
                        </div>

                    </div>

                    <div class="profilepress-myaccount-nav">
                        <?php foreach ($tabs as $key => $tab) :
                            ?>
                            <a class="ppmyac-dashboard-item <?= $key ?><?= self::is_endpoint($key) ? ' isactive' : ''; ?>" href="<?= $this->get_endpoint_url($key); ?>">
                                <i class="ppmyac-icons">
                                    <?= isset($tab['icon']) ? $tab['icon'] : 'settings'; ?>
                                </i>
                                <?= $tab['title'] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="profilepress-myaccount-content">
                    <?php

                    $flag = false;

                    if ( ! empty($wp->query_vars)) {
                        foreach ($wp->query_vars as $key => $value) {
                            // Ignore pagename param.
                            if ('pagename' === $key) {
                                continue;
                            }

                            $callback = self::get_tab_callback($key);

                            if (is_callable($callback)) {
                                $flag = true;
                                call_user_func($callback, $key);
                                break;
                            }
                        }
                    }

                    if ( ! $flag) {
                        require apply_filters('ppress_my_account_dashboard_template', wp_normalize_path(dirname(__FILE__) . '/dashboard.tmpl.php'));
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php

        return ob_get_clean();
    }

    public function js_script()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            jQuery('.ppmyac-custom-file input').change(function (e) {
                var files = [];
                for (var i = 0; i < jQuery(this)[0].files.length; i++) {
                    files.push(jQuery(this)[0].files[i].name);
                }
                jQuery(this).next('.ppmyac-custom-file-label').html(files.join(', '));
            });

            jQuery(document).on('pp_form_edit_profile_success', function (e, parent) {
                parent.find('#pp-avatar, #pp-cover-image').val('');
                parent.find('#pp-cover-image').next('.ppmyac-custom-file-label').text('<?=esc_html__('Cover Photo (min. width: 1000px)', 'wp-user-avatar')?>');
                parent.find('#pp-avatar').next('.ppmyac-custom-file-label').text('<?=esc_html__('Profile Picture', 'wp-user-avatar')?>');
            });
        </script>
        <?php
        echo ppress_minify_js(ob_get_clean());
    }

    public static function get_instance()
    {
        static $instance = false;

        if ( ! $instance) {
            $instance = new self;
        }

        return $instance;
    }
}