<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\CouponsPage;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Custom_Settings_Page_Api;

class SettingsPage extends AbstractSettingsPage
{
    public $error_bucket;

    /** @var CouponWPListTable */
    private $couponListTable;

    function __construct()
    {
        add_action('ppress_admin_settings_page_coupons', [$this, 'settings_page_function']);
        add_filter('ppress_membership_plans_settings_page_title', [$this, 'admin_page_title']);
        add_action('ppress_membership_plans_settings_page_register', function ($hook) {
            add_action("load-$hook", array($this, 'add_options'));
        });

        if (ppressGET_var('page') == PPRESS_MEMBERSHIP_PLANS_SETTINGS_SLUG && ppressGET_var('view') == 'coupons') {

            add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
            add_filter('set_screen_option_rules_per_page', [__CLASS__, 'set_screen'], 10, 3);

            add_action('admin_init', function () {
                $this->save_coupon();
            });
        }
    }

    /**
     * @return string|void
     */
    public function save_coupon()
    {
        if ( ! isset($_POST['ppress_save_coupon'])) return;

        check_admin_referer('wp-csa-nonce', 'wp_csa_nonce');

        if ( ! current_user_can('manage_options')) return;

        $required_fields = [
            'amount'             => esc_html__('Discount', 'wp-user-avatar'),
            'unit'               => esc_html__('Discount', 'wp-user-avatar'),
            'coupon_type'        => esc_html__('Coupon Type', 'wp-user-avatar'),
            'coupon_application' => esc_html__('Coupon Application', 'wp-user-avatar')
        ];

        foreach ($required_fields as $field_id => $field_name) {
            if (empty($_POST[$field_id])) {
                return $this->error_bucket = sprintf(esc_html__('%s cannot be empty.', 'wp-user-avatar'), $field_name);
            }
        }

        $coupon_code = empty($_POST['code']) ? strtoupper(wp_generate_password(8, false)) : sanitize_text_field($_POST['code']);

        $coupon                     = CouponFactory::fromId(absint(ppressGET_var('id')));
        $coupon->code               = $coupon_code;
        $coupon->description        = sanitize_textarea_field($_POST['description']);
        $coupon->amount             = sanitize_text_field($_POST['amount']);
        $coupon->unit               = sanitize_text_field($_POST['unit']);
        $coupon->coupon_type        = sanitize_text_field($_POST['coupon_type']);
        $coupon->coupon_application = sanitize_text_field($_POST['coupon_application']);
        $coupon->plan_ids           = ppress_clean(ppressPOST_var('plan_ids', [], true), 'absint');
        $coupon->start_date         = sanitize_text_field($_POST['start_date']);
        $coupon->end_date           = sanitize_text_field($_POST['end_date']);
        $coupon->usage_limit        = absint($_POST['usage_limit']);

        $coupon_id = $coupon->save();

        wp_safe_redirect(add_query_arg(['ppress_coupon_action' => 'edit', 'id' => $coupon_id, 'saved' => 'true'], PPRESS_MEMBERSHIP_COUPONS_SETTINGS_PAGE));
        exit;
    }

    public function admin_page_title($title = '')
    {
        if (ppressGET_var('view') == 'coupons') {

            $title = esc_html__('Coupons', 'wp-user-avatar');

            if (ppressGET_var('ppress_coupon_action') == 'new') {
                $title = esc_html__('Add a Coupon', 'wp-user-avatar');
            }

            if (ppressGET_var('ppress_coupon_action') == 'edit') {
                $title = esc_html__('Edit Coupon', 'wp-user-avatar');
            }
        }

        return $title;
    }

    public static function set_screen($status, $option, $value)
    {
        return $value;
    }

    public function add_options()
    {
        if (ppressGET_var('view') == 'coupons') {

            $args = [
                'label'   => esc_html__('Coupons', 'wp-user-avatar'),
                'default' => 10,
                'option'  => 'coupons_per_page'
            ];

            add_screen_option('per_page', $args);

            $this->couponListTable = new CouponWPListTable();
        }
    }

    public function admin_notices()
    {
        if ( ! isset($_GET['saved']) && ! isset($this->error_bucket)) return;

        $status  = 'updated';
        $message = '';

        if ( ! empty($this->error_bucket)) {
            $message = $this->error_bucket;
            $status  = 'error';
        }

        if (isset($_GET['saved'])) {
            $message = esc_html__('Changes saved.', 'wp-user-avatar');
        }

        printf('<div id="message" class="%s notice is-dismissible"><p>%s</strong></p></div>', $status, $message);
    }

    public function settings_page_function()
    {
        add_action('wp_cspa_main_content_area', [$this, 'admin_settings_page_callback'], 10, 2);
        add_action('wp_cspa_before_closing_header', [$this, 'add_new_button']);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->page_header($this->admin_page_title());
        $instance->build(true);
    }

    public function add_new_button()
    {
        if ( ! isset($_GET['ppress_subp_action'])) {
            $url = esc_url_raw(add_query_arg('ppress_coupon_action', 'new', self::$parent_menu_url_map['coupons']));
            echo "<a class=\"add-new-h2\" href=\"$url\">" . esc_html__('Add New Coupon', 'wp-user-avatar') . '</a>';
        }
    }

    public function admin_settings_page_callback()
    {
        if (in_array(ppressGET_var('ppress_coupon_action'), ['new', 'edit'])) {
            $this->admin_notices();
            require_once dirname(dirname(__FILE__)) . '/views/add-edit-coupon.php';

            return;
        }

        $this->couponListTable->prepare_items(); // has to be here.

        echo '<form method="post">';
        $this->couponListTable->display();
        echo '</form>';

        do_action('ppress_coupon_wp_list_table_bottom');
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