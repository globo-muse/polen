<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\DashboardPage;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Customer\CustomerStatus;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Repositories\CustomerRepository;
use ProfilePress\Core\Membership\Repositories\PlanRepository;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\TaxService;
use ProfilePress\Custom_Settings_Page_Api;
use ProfilePressVendor\Carbon\CarbonImmutable;

class SettingsPage extends AbstractSettingsPage
{
    function __construct()
    {
        add_action('ppress_register_menu_page', [$this, 'register_cpf_settings_page']);
        add_action('ppress_admin_settings_page_reports', [$this, 'reports_admin_page']);
    }

    public function admin_page_title()
    {
        return esc_html__('Dashboard', 'wp-user-avatar');
    }

    public function register_cpf_settings_page()
    {
        $hook = add_submenu_page(
            PPRESS_DASHBOARD_SETTINGS_SLUG,
            $this->admin_page_title() . ' - ProfilePress',
            esc_html__('Dashboard', 'wp-user-avatar'),
            'manage_options',
            PPRESS_DASHBOARD_SETTINGS_SLUG,
            array($this, 'admin_page_callback'));

        do_action('ppress_membership_reports_settings_page_register', $hook);
    }

    public function default_header_menu()
    {
        return 'reports';
    }

    public function reports_admin_page()
    {
        add_action('admin_footer', [$this, 'js_script']);
        add_action('wp_cspa_main_content_area', [$this, 'admin_settings_page_callback'], 10, 2);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('ppview'); // adds ppview css class to #poststuff
        $instance->form_method('get');
        $instance->page_header($this->admin_page_title());
        $instance->build(true);
    }

    public function get_filter_data()
    {
        $preset  = ! empty($_GET['preset']) ? sanitize_text_field($_GET['preset']) : 'today';
        $plan_id = ! empty($_GET['plan']) ? absint($_GET['plan']) : '';

        $start_of_week = absint(get_option('start_of_week', 1));
        $end_of_week   = $start_of_week == 0 ? 6 : $start_of_week - 1;

        $carbonNow = CarbonImmutable::now(wp_timezone());

        switch ($preset) {
            case 'today':
                $start_date = $carbonNow->startOfDay()->toDateString();
                $end_date   = $carbonNow->endOfDay()->toDateString();
                break;
            case 'yesterday':
                $start_date = $carbonNow->subDay()->startOfDay()->toDateString();
                $end_date   = $carbonNow->subDay()->endOfDay()->toDateString();
                break;
            case 'this_week':
                $start_date = $carbonNow->startOfWeek($start_of_week)->toDateString();
                $end_date   = $carbonNow->endOfWeek($end_of_week)->toDateString();
                break;
            case 'last_week':
                $start_date = $carbonNow->startOfWeek($start_of_week)->subWeek()->toDateString();
                $end_date   = $carbonNow->endOfWeek($end_of_week)->subWeek()->toDateString();
                break;
            case 'last_30_days':
                $start_date = $carbonNow->subDays(29)->startOfDay()->toDateString();
                $end_date   = $carbonNow->endOfDay()->toDateString();
                break;
            case 'this_month':
                $start_date = $carbonNow->startOfMonth()->toDateString();
                $end_date   = $carbonNow->endOfMonth()->toDateString();
                break;
            case 'last_month':
                $start_date = $carbonNow->subMonthNoOverflow()->startOfMonth()->toDateString();
                $end_date   = $carbonNow->subMonthNoOverflow()->endOfMonth()->toDateString();
                break;
            case 'this_quarter':
                $start_date = $carbonNow->startOfQuarter()->toDateString();
                $end_date   = $carbonNow->endOfQuarter()->toDateString();
                break;
            case 'last_quarter':
                $start_date = $carbonNow->subQuarter()->startOfQuarter()->toDateString();
                $end_date   = $carbonNow->subQuarter()->endOfQuarter()->toDateString();
                break;
            case 'this_year':
                $start_date = $carbonNow->startOfYear()->toDateString();
                $end_date   = $carbonNow->endOfYear()->toDateString();
                break;
            case 'last_year':
                $start_date = $carbonNow->subYear()->startOfYear()->toDateString();
                $end_date   = $carbonNow->subYear()->endOfYear()->toDateString();
                break;
            default:
                $start_date = sanitize_text_field(ppressGET_var('start_date', '', true));
                $end_date   = sanitize_text_field(ppressGET_var('end_date', '', true));
                break;
        }

        $filterData             = new ReportFilterData();
        $filterData->start_date = $start_date;
        $filterData->end_date   = $end_date;
        $filterData->plan_id    = $plan_id;

        return $filterData;
    }

    public function js_script()
    {
        ?>
        <script type="text/javascript">
            var currencySymbol = '<?= ppress_display_amount('00')?>', // 00 is placeholder replaced on the clientside
                revenueStat = <?= wp_json_encode($this->get_revenue()) ?>,
                taxesStat = <?= wp_json_encode($this->get_taxes()) ?>,
                orderStat = <?= wp_json_encode($this->get_orders()) ?>,
                topPlansStat = <?= wp_json_encode($this->get_top_plans()) ?>,
                paymentMethodsStat = <?= wp_json_encode($this->get_payment_methods()) ?>,
                refundStat = <?= wp_json_encode($this->get_refunds()) ?>;
        </script>
        <?php
    }

    public function cache_transformer($key, $callback)
    {
        static $cache = [];

        if ( ! isset($cache[$key])) {
            $cache[$key] = $callback;
        }

        return $cache[$key];
    }

    public function get_revenue()
    {
        return $this->cache_transformer(
            __FUNCTION__,
            (new Revenue($this->get_filter_data()))->val()
        );
    }

    public function get_taxes()
    {
        return $this->cache_transformer(
            __FUNCTION__,
            (new Taxes($this->get_filter_data()))->val()
        );
    }

    public function get_orders()
    {
        return $this->cache_transformer(
            __FUNCTION__,
            (new Orders($this->get_filter_data()))->val()
        );
    }

    public function get_refunds()
    {
        return $this->cache_transformer(
            __FUNCTION__,
            (new Refunds($this->get_filter_data()))->val()
        );
    }

    public function get_top_plans()
    {
        return $this->cache_transformer(
            __FUNCTION__,
            (new TopPlans($this->get_filter_data()))->val()
        );
    }

    public function get_payment_methods()
    {
        return $this->cache_transformer(
            __FUNCTION__,
            (new PaymentMethods($this->get_filter_data()))->val()
        );
    }

    public static function get_lifetime_revenue()
    {
        global $wpdb;

        $order_table = Base::orders_db_table();

        return $wpdb->get_var(
            $wpdb->prepare("SELECT SUM(total) from $order_table WHERE status = %s", OrderStatus::COMPLETED)
        );
    }

    public function admin_settings_page_callback()
    {
        echo '<style>.ppress-admin .ppview h2{display:none;}</style>';
        echo '<input type="hidden" name="page" value="' . PPRESS_DASHBOARD_SETTINGS_SLUG . '" />';
        echo '<div class="ppress-report-charts-container">';
        $this->top_cards();
        $this->range_picker();
        $this->report_charts();
        echo '</div>';
    }

    public function range_picker()
    {
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : null;
        $end_date   = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : null;
        $plan_id    = isset($_GET['plan']) ? sanitize_text_field($_GET['plan']) : 'all';
        $preset     = isset($_GET['preset']) ? sanitize_text_field($_GET['preset']) : 'today';

        $clear_url = PPRESS_DASHBOARD_SETTINGS_PAGE;

        $plans = PlanRepository::init()->retrieveAll();

        $preset_args = [
            'today'        => esc_html__('Today', 'wp-user-avatar'),
            'yesterday'    => esc_html__('Yesterday', 'wp-user-avatar'),
            'this_week'    => esc_html__('This Week', 'wp-user-avatar'),
            'last_week'    => esc_html__('Last Week', 'wp-user-avatar'),
            'last_30_days' => esc_html__('Last 30 Days', 'wp-user-avatar'),
            'this_month'   => esc_html__('This Month', 'wp-user-avatar'),
            'last_month'   => esc_html__('Last Month', 'wp-user-avatar'),
            'this_quarter' => esc_html__('This Quarter', 'wp-user-avatar'),
            'last_quarter' => esc_html__('Last Quarter', 'wp-user-avatar'),
            'this_year'    => esc_html__('This Year', 'wp-user-avatar'),
            'last_year'    => esc_html__('Last Year', 'wp-user-avatar'),
            'custom'       => esc_html__('Custom', 'wp-user-avatar'),
        ];

        echo '<div class="wp-filter" id="ppress-filters">';
        echo '<div class="filter-items">';
        echo '<span id="ppress-mode-filter">';
        echo '<select name="preset">';
        foreach ($preset_args as $index => $arg) {
            printf('<option value="%1$s" %3$s>%2$s</option>', $index, $arg, selected($preset, $index, false));
        }
        echo '</select>';
        echo '</span>';

        echo '<span id="ppress-date-filters" class="ppress-from-to-wrapper" style="display:none">';
        echo '<span class="ppress-start-date-wrap">';
        echo '<input type="text" name="start_date" id="start-date" placeholder="' . _x('From', 'date filter', 'wp-user-avatar') . '" value="' . $start_date . '" class="ppress_datepicker">';
        echo '</span>';
        echo '<span id="ppress-end-date-wrap">';
        echo '<input type="text" name="end_date" id="end-date" value="' . $end_date . '" placeholder="' . _x('To', 'date filter', 'wp-user-avatar') . '" class="ppress_datepicker">';
        echo '</span>';
        echo '</span>';

        if ( ! empty($plans)) {
            echo '<span id="ppress-gateway-filter">';
            echo '<select name="plan">';
            echo '<option value="all">' . esc_html__('All Plans', 'wp-user-avatar') . '</option>';
            foreach ($plans as $plan) {
                printf('<option value="%1$s" %3$s>%2$s</option>', $plan->id, $plan->name, selected($plan->id, $plan_id, false));
            }
            echo '</select>';
            echo '</span>';
        }

        echo '<span id="ppress-after-core-filters">';
        echo '<input type="submit" class="button button-secondary" value="' . esc_html__('Filter', 'wp-user-avatar') . '"/>&nbsp;';

        if ( ! empty($start_date) || ! empty($end_date) || 'all' !== $plan_id || 'today' !== $preset) {
            echo '<a href="' . esc_url($clear_url) . '" class="button-secondary">';
            esc_html_e('Clear', 'wp-user-avatar');
            echo '</a>';
        }
        echo '</span>';

        echo '</div>';
        echo '</div>';
    }

    public function single_report_card($id, $title, $total, $trend = '', $sign = '%')
    {
        ?>
        <div class="ppress-report-chart-bottom-card-item">
            <dt class="ppress-report-chart-bottom-card-item-header">
                <p><?= $title ?></p>
            </dt>
            <dd class="ppress-report-chart-bottom-card-item-content-wrap">
                <?php if ( ! is_null($total)) : ?>
                    <p><?php echo $total ?></p>
                <?php endif; ?>
                <?php if ( ! Calculator::init($trend)->isZero()) : ?>
                    <?php if (Calculator::init($trend)->isNegative()) : ?>
                        <p class="ppress-report-chart-bottom-card-item-trend negative"><?= $trend . $sign ?></p>
                    <?php else : ?>
                        <p class="ppress-report-chart-bottom-card-item-trend">+<?= $trend . $sign ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </dd>
            <div class="ppress-report-chart-bottom-card-item-chart">
                <canvas id="<?= $id ?>"></canvas>
            </div>
        </div>
        <?php
    }

    /**
     * @return void
     */
    public function top_cards()
    {
        ?>
        <div class="ppress-report-chart-top-cards-wrap">
            <dl class="ppress-report-chart-top-card-item-wrap">
                <div class="ppress-report-chart-top-card-item">
                    <dt class="ppress-report-chart-top-card-item-header"><?php esc_html_e('Total Customers', 'wp-user-avatar') ?></dt>
                    <dd class="ppress-report-chart-top-card-item-content-wrap">
                        <div class="ppress-report-chart-top-card-item-content"><?php echo CustomerRepository::init()->record_count() ?></div>
                    </dd>
                </div>

                <div class="ppress-report-chart-top-card-item">
                    <dt class="ppress-report-chart-top-card-item-header"><?php esc_html_e('Active Customers', 'wp-user-avatar') ?></dt>
                    <dd class="ppress-report-chart-top-card-item-content-wrap">
                        <div class="ppress-report-chart-top-card-item-content"><?php echo CustomerRepository::init()->get_count_by_status(CustomerStatus::ACTIVE) ?></div>
                    </dd>
                </div>

                <div class="ppress-report-chart-top-card-item">
                    <dt class="ppress-report-chart-top-card-item-header"><?php esc_html_e('Active Subscriptions', 'wp-user-avatar') ?></dt>
                    <dd class="ppress-report-chart-top-card-item-content-wrap">
                        <div class="ppress-report-chart-top-card-item-content"><?php echo SubscriptionRepository::init()->get_count_by_status(SubscriptionStatus::ACTIVE) ?></div>
                    </dd>
                </div>

                <div class="ppress-report-chart-top-card-item">
                    <dt class="ppress-report-chart-top-card-item-header"><?php esc_html_e('Lifetime Revenue', 'wp-user-avatar') ?></dt>
                    <dd class="ppress-report-chart-top-card-item-content-wrap">
                        <div class="ppress-report-chart-top-card-item-content"><?php echo ppress_display_amount(self::get_lifetime_revenue()) ?></div>
                    </dd>
                </div>

            </dl>
        </div>
        <?php
    }

    public function report_charts()
    {
        $revenue         = $this->get_revenue();
        $orders          = $this->get_orders();
        $refunds         = $this->get_refunds();
        $taxes           = $this->get_taxes();
        $top_plans       = $this->get_top_plans();
        $payment_methods = $this->get_payment_methods();
        ?>
        <div class="ppress-report-chart-bottom-cards-wrap">
            <dl class="ppress-report-chart-bottom-card-item-wrap">

                <?php $this->single_report_card(
                    'ppress-report-revenue',
                    esc_html__('Revenue', 'wp-user-avatar'),
                    ppress_display_amount($revenue['total']),
                    $revenue['trend']
                ) ?>

                <?php $this->single_report_card(
                    'ppress-report-orders',
                    esc_html__('Orders', 'wp-user-avatar'),
                    $orders['total'],
                    $orders['trend']
                ) ?>

                <?php if (TaxService::init()->is_tax_enabled()) :
                    $this->single_report_card(
                        'ppress-report-tax',
                        esc_html__('Taxes', 'wp-user-avatar'),
                        ppress_display_amount($taxes['total']),
                        $taxes['trend']
                    );
                endif; ?>

                <?php $this->single_report_card(
                    'ppress-report-refunds',
                    esc_html__('Refunds', 'wp-user-avatar'),
                    $refunds['total'],
                    $refunds['trend']
                ) ?>

                <?php $this->single_report_card(
                    'ppress-report-top-plans',
                    esc_html__('Top Plans', 'wp-user-avatar'),
                    $top_plans['total'],
                    $top_plans['trend']
                ) ?>

                <?php $this->single_report_card(
                    'ppress-report-payment-methods',
                    esc_html__('Payment Methods', 'wp-user-avatar'),
                    $payment_methods['total'],
                    $payment_methods['trend']
                ) ?>

            </dl>
        </div>
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