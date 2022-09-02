<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\SubscriptionsPage;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Custom_Settings_Page_Api;
use ProfilePressVendor\Carbon\CarbonImmutable;

class SettingsPage extends AbstractSettingsPage
{
    public $error_bucket;

    /** @var SubscriptionWPListTable */
    private $subscriptionListTable;

    function __construct()
    {
        add_action('ppress_register_menu_page', [$this, 'register_cpf_settings_page']);
        add_action('ppress_admin_settings_page_subscriptions', [$this, 'settings_page_function']);

        if (ppressGET_var('page') == PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_SLUG) {
            add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
            add_filter('set_screen_option_rules_per_page', [__CLASS__, 'set_screen'], 10, 3);

            add_action('admin_init', function () {
                $this->save_subscription();
            });
        }
    }

    public function default_header_menu()
    {
        return 'subscriptions';
    }

    public function register_cpf_settings_page()
    {
        $hook = add_submenu_page(
            PPRESS_DASHBOARD_SETTINGS_SLUG,
            $this->admin_page_title() . ' - ProfilePress',
            esc_html__('Subscriptions', 'wp-user-avatar'),
            'manage_options',
            PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_SLUG,
            array($this, 'admin_page_callback'));

        add_action("load-$hook", array($this, 'add_options'));

        do_action('ppress_membership_subscriptions_settings_page_register', $hook);
    }

    /**
     * @return void|string
     * @throws \Exception
     */
    public function save_subscription()
    {
        if ( ! isset($_POST['ppress_save_subscription'])) return;

        ppress_verify_nonce();

        if ( ! current_user_can('manage_options')) return;

        $subscription               = SubscriptionFactory::fromId(absint(ppressGET_var('id')));
        $cloned_subscription        = clone $subscription;
        $subscription->created_date = ppress_local_datetime_to_utc(sanitize_text_field($_POST['sub_created_date']));
        if ( ! $subscription->is_lifetime()) {
            $expiration_date = CarbonImmutable::createFromFormat('Y-m-d', sanitize_text_field($_POST['sub_expiration_date']), wp_timezone())
                                              ->toDateTimeString();

            $subscription->expiration_date = $expiration_date;
        }
        $subscription->profile_id  = sanitize_text_field($_POST['sub_profile_id']);
        $subscription->customer_id = absint($_POST['sub_customer_user']);

        if (in_array($_POST['sub_status'], array_keys(SubscriptionStatus::get_all()))) {
            $subscription->status = sanitize_text_field($_POST['sub_status']);
        }

        $subscription->initial_amount = ppress_sanitize_amount($_POST['sub_initial_amount']);

        if ( ! empty($_POST['sub_initial_tax'])) {
            $subscription->initial_tax = ppress_sanitize_amount($_POST['sub_initial_tax']);
        }

        if ( ! empty($_POST['sub_initial_tax_rate'])) {
            $subscription->initial_tax_rate = sanitize_text_field($_POST['sub_initial_tax_rate']);
        }

        if ( ! empty($_POST['sub_recurring_amount'])) {
            $subscription->recurring_amount = ppress_sanitize_amount($_POST['sub_recurring_amount']);
        }

        if ( ! empty($_POST['sub_recurring_tax'])) {
            $subscription->recurring_tax = ppress_sanitize_amount($_POST['sub_recurring_tax']);
        }

        if ( ! empty($_POST['sub_recurring_tax_rate'])) {
            $subscription->recurring_tax_rate = ppress_sanitize_amount($_POST['sub_recurring_tax_rate']);
        }

        $subscription_id = $subscription->save();

        switch ($subscription->status) {
            case SubscriptionStatus::EXPIRED :
                $cloned_subscription->expire(true);
                break;
            case SubscriptionStatus::CANCELLED :
                $cloned_subscription->cancel(
                    $cloned_subscription->has_cancellation_requested() === false
                );
                break;
            case SubscriptionStatus::COMPLETED :
                $cloned_subscription->complete();
                break;
        }

        wp_safe_redirect(add_query_arg(['ppress_subscription_action' => 'edit', 'id' => $subscription_id, 'saved' => 'true'], PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE));
        exit;
    }

    public function admin_page_title()
    {
        $title = esc_html__('Subscriptions', 'wp-user-avatar');

        if (ppressGET_var('ppress_subscription_action') == 'edit') {
            $title = esc_html__('Edit Subscription', 'wp-user-avatar');
        }

        return $title;
    }

    public static function set_screen($status, $option, $value)
    {
        return $value;
    }

    public function add_options()
    {
        $args = [
            'label'   => esc_html__('Subscriptions', 'wp-user-avatar'),
            'default' => 10,
            'option'  => 'subscriptions_per_page'
        ];

        add_screen_option('per_page', $args);

        $this->subscriptionListTable = new SubscriptionWPListTable();
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
        add_action('wp_cspa_form_tag', function ($option_name) {
            if ($option_name == 'ppress_subscriptions') {
                printf(' action="%s"', ppress_get_current_url_query_string());
            }
        });

        add_action('wp_cspa_after_settings_tab', function ($option_name) {
            if ($option_name == 'ppress_subscriptions' && ppressGET_var('ppress_subscription_action') == 'edit') {

                echo '<div class="notice notice-info">';
                echo '<p>';
                echo sprintf(__('%1$sNote%2$s - Be careful modifying details here. For example, changing the gateway subscription ID can result in renewals not being processed. While changing the expiration date will not affect when renewal payments are processed because your payment processor determines when.', 'wp-user-avatar'), '<strong>', '</strong>');
                echo '</p>';
                echo '</div>';
            }
        });

        $instance = Custom_Settings_Page_Api::instance();
        if ( ! isset($_GET['ppress_subscription_action'])) {
            $instance->form_method('get');
            $instance->remove_nonce_field();
        }
        $instance->add_view_classes('ppview');
        $instance->option_name('ppress_subscriptions');
        $instance->page_header($this->admin_page_title());
        $instance->build(true);
    }

    public function add_new_button()
    {
        if (ppressGET_var('ppress_subscription_action') == 'edit') {
            $url = esc_url_raw(PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE);
            echo "<a class=\"add-new-h2\" href=\"$url\">" . esc_html__('Back to Subscriptions', 'wp-user-avatar') . '</a>';
        }
    }

    public function admin_settings_page_callback()
    {
        if (in_array(ppressGET_var('ppress_subscription_action'), ['new', 'edit'])) {
            $this->admin_notices();
            require_once dirname(dirname(__FILE__)) . '/views/subscriptions/add-edit-subscription.php';

            return;
        }

        $this->subscriptionListTable->prepare_items();

        echo '<input type="hidden" name="page" value="' . PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_SLUG . '" />';
        echo '<input type="hidden" name="view" value="subscriptions" />';
        if (isset($_GET['status'])) {
            echo '<input type="hidden" name="status" value="' . sanitize_text_field($_GET['status']) . '" />';
        }
        $this->subscriptionListTable->views();
        $this->subscriptionListTable->filter_bar();
        $this->subscriptionListTable->display();

        do_action('ppress_subscription_wp_list_table_bottom');
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