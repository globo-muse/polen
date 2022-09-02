<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\PlansPage;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Membership\CurrencyFormatter;
use ProfilePress\Custom_Settings_Page_Api;

class SettingsPage extends AbstractSettingsPage
{
    public $error_bucket;

    /** @var PlanWPListTable */
    private $planListTable;

    function __construct()
    {
        add_action('ppress_register_menu_page', [$this, 'register_cpf_settings_page']);
        add_action('ppress_admin_settings_page_plans', [$this, 'settings_page_function']);

        add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
        add_filter('set_screen_option_rules_per_page', [__CLASS__, 'set_screen'], 10, 3);

        if (ppressGET_var('page') == PPRESS_MEMBERSHIP_PLANS_SETTINGS_SLUG) {

            add_action('admin_init', function () {
                $this->save_subscription_plan();
            });
        }
    }

    public function default_header_menu()
    {
        return 'plans';
    }

    public function register_cpf_settings_page()
    {
        $hook = add_submenu_page(
            PPRESS_DASHBOARD_SETTINGS_SLUG,
            $this->admin_page_title() . ' - ProfilePress',
            esc_html__('Membership Plans', 'wp-user-avatar'),
            'manage_options',
            PPRESS_MEMBERSHIP_PLANS_SETTINGS_SLUG,
            array($this, 'admin_page_callback'));

        add_action("load-$hook", array($this, 'add_options'));

        do_action('ppress_membership_plans_settings_page_register', $hook);
    }

    public function header_menu_tabs()
    {
        $tabs = apply_filters('ppress_membership_plans_settings_page_tabs', [
            5  => ['id' => $this->default_header_menu(), 'url' => PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE, 'label' => esc_html__('Plans', 'wp-user-avatar')],
            10 => ['id' => 'coupons', 'url' => PPRESS_MEMBERSHIP_COUPONS_SETTINGS_PAGE, 'label' => esc_html__('Coupons', 'wp-user-avatar')]
        ]);

        ksort($tabs);

        return $tabs;
    }

    public function admin_page_title()
    {

        $title = esc_html__('Plans', 'wp-user-avatar');

        if (ppressGET_var('ppress_subp_action') == 'new') {
            $title = esc_html__('Add Plan', 'wp-user-avatar');
        }

        if (ppressGET_var('ppress_subp_action') == 'edit') {
            $title = esc_html__('Edit Plan', 'wp-user-avatar');
        }

        return apply_filters('ppress_membership_plans_settings_page_title', $title);
    }

    public function save_subscription_plan()
    {
        if ( ! isset($_POST['ppress_save_subscription_plan'])) return;

        check_admin_referer('wp-csa-nonce', 'wp_csa_nonce');

        if ( ! current_user_can('manage_options')) return;

        $required_fields = [
            'name'                => esc_html__('Plan Name', 'wp-user-avatar'),
            'price'               => esc_html__('Price', 'wp-user-avatar'),
            'billing_frequency'   => esc_html__('Billing Frequency', 'wp-user-avatar'),
            'subscription_length' => esc_html__('Subscription Length', 'wp-user-avatar')
        ];

        foreach ($required_fields as $field_id => $field_name) {
            if (empty($_POST[$field_id])) {
                return $this->error_bucket = sprintf(esc_html__('%s cannot be empty.', 'wp-user-avatar'), $field_name);
            }
        }

        $plan                      = ppress_get_plan(absint(ppressGET_var('id')));
        $plan->name                = sanitize_text_field($_POST['name']);
        $plan->description         = stripslashes(wp_kses_post($_POST['description']));
        $plan->price               = ppress_sanitize_amount($_POST['price']);
        $plan->billing_frequency   = sanitize_text_field($_POST['billing_frequency']);
        $plan->subscription_length = sanitize_text_field($_POST['subscription_length']);
        $plan->total_payments      = absint($_POST['total_payments']);
        if ($plan->subscription_length == 'renew_indefinitely') {
            $plan->total_payments = 0;
        }
        $plan->signup_fee = ppress_sanitize_amount($_POST['signup_fee']);
        $plan->free_trial = sanitize_text_field($_POST['free_trial']);
        $plan_id          = $plan->save();

        wp_safe_redirect(add_query_arg(['ppress_subp_action' => 'edit', 'id' => $plan_id, 'saved' => 'true'], PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE));
        exit;
    }

    public static function set_screen($status, $option, $value)
    {
        return $value;
    }

    public function add_options()
    {
        $args = [
            'label'   => esc_html__('Membership Plans', 'wp-user-avatar'),
            'default' => 10,
            'option'  => 'plans_per_page'
        ];

        add_screen_option('per_page', $args);

        $this->planListTable = new PlanWPListTable();
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
        add_action('admin_footer', [$this, 'js_template']);
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
            $url = esc_url_raw(add_query_arg('ppress_subp_action', 'new', PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE));
            echo "<a class=\"add-new-h2\" href=\"$url\">" . esc_html__('Add New Plan', 'wp-user-avatar') . '</a>';
        }
    }

    public function admin_settings_page_callback()
    {
        if (in_array(ppressGET_var('ppress_subp_action'), ['new', 'edit'])) {
            $this->admin_notices();
            require_once dirname(dirname(__FILE__)) . '/views/add-edit-plan.php';

            return;
        }

        $this->planListTable->prepare_items(); // has to be here.

        echo '<form method="post">';
        $this->planListTable->display();
        echo '</form>';

        do_action('ppress_subscription_plan_wp_list_table_bottom');
    }

    public function js_template()
    {
        $currency_symbol = ppress_get_currency_symbol();
        ?>
        <script type="text/javascript">
            var ppress_transform_plan_frequency = function (val) {
                var bag = {
                    'monthly': '<?php esc_html_e('month', 'wp-user-avatar'); ?>',
                    'weekly': '<?php esc_html_e('week', 'wp-user-avatar'); ?>',
                    'daily': '<?php esc_html_e('day', 'wp-user-avatar'); ?>',
                    '3_month': '<?php esc_html_e('quarter', 'wp-user-avatar'); ?>',
                    '6_month': '<?php esc_html_e('every 6 months', 'wp-user-avatar'); ?>',
                    '1_year': '<?php esc_html_e('year', 'wp-user-avatar'); ?>',
                    'lifetime': '<?php esc_html_e('one-time', 'wp-user-avatar'); ?>',
                };

                return bag[val];
            };

            var ppress_transform_subscription_length = function (val) {
                var bag = {
                    'monthly': '<?php esc_html_e('month', 'wp-user-avatar'); ?>',
                    'weekly': '<?php esc_html_e('week', 'wp-user-avatar'); ?>',
                    'daily': '<?php esc_html_e('day', 'wp-user-avatar'); ?>',
                    '3_month': '<?php esc_html_e('quarter', 'wp-user-avatar'); ?>',
                    '6_month': '<?php esc_html_e('every 6 months', 'wp-user-avatar'); ?>',
                    '1_year': '<?php esc_html_e('year', 'wp-user-avatar'); ?>',
                    'lifetime': '<?php esc_html_e('one-time', 'wp-user-avatar'); ?>',
                };

                return bag[val];
            };

            var ppress_transform_currency = function (price) {
                var placeholder = '<?php echo (new CurrencyFormatter('54321012345'))->apply_symbol()->val(); ?>';
                return placeholder.replace('54321012345', price);
            };

        </script>
        <script type="text/html" id="tmpl-ppress-plan-summary">
            <# var total_payments = data.free_trial != 'disabled' ? data.total_payments - 1 : data.total_payments; #>
            <# var billing_frequency_id = data.billing_frequency; #>
            <# var billing_frequency = ppress_transform_plan_frequency(data.billing_frequency) #>
            <# var billing_frequency_desc = data.billing_frequency == '6_month' ? 6*total_payments + " <?= __('months', 'wp-user-avatar') ?>" :  total_payments+ ' '+billing_frequency+'s' #>
            <# var total_payments_desc = billing_frequency_id != 'lifetime' && total_payments >= 1 ? ', <?= esc_html__('for', 'wp-user-avatar') ?> '+ billing_frequency_desc : ''; #>
            <# var signup_fee_desc = billing_frequency_id != 'lifetime' && data.signup_fee > 0 ? '<?= sprintf(esc_html__(' and a %s signup fee', 'wp-user-avatar'), "'+ppress_transform_currency(data.signup_fee)+'") ?>' : ''; #>
            <# var subscription_length_desc = data.subscription_length != 'fixed' ? '<?= esc_html__('Renews indefinitely', 'wp-user-avatar') ?>' : ''; #>
            <# var subscription_length_desc = billing_frequency_id == 'lifetime' ? '<?= esc_html__('Never expires', 'wp-user-avatar') ?>' : subscription_length_desc; #>
            <# var free_trial_desc = data.free_trial != 'disabled' ? '<?= sprintf(esc_html__('Includes a %s free trial', 'wp-user-avatar'), "'+data.free_trial+'") ?>' : ''; #>
            <# free_trial_desc = free_trial_desc.replace('_', '-'); #>
            <p>{{ ppress_transform_currency(data.price) }} / {{billing_frequency}}{{total_payments_desc}}{{{signup_fee_desc}}}</p>
            <ul>
                <# if(billing_frequency_id != 'lifetime' && '' !== free_trial_desc && 'disabled' !== free_trial_desc ) { #>
                <li style="list-style:disc inside;">{{free_trial_desc}}</li>
                <# } #>
                <# if('' !== subscription_length_desc) { #>
                <li style="list-style:disc inside;">{{subscription_length_desc}}</li>
                <# } #>
            </ul>
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