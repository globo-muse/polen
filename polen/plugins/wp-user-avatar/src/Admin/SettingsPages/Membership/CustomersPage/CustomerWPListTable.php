<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\CustomersPage;

use ProfilePress\Core\Admin\SettingsPages\Membership\SubscriptionsPage\SubscriptionWPListTable;
use ProfilePress\Core\Membership\Models\Customer\CustomerEntity;
use ProfilePress\Core\Membership\Models\Customer\CustomerStatus;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Repositories\CustomerRepository;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\Membership\Services\CustomerService;
use ProfilePress\Core\Membership\Services\OrderService;
use ProfilePressVendor\Carbon\CarbonImmutable;

class CustomerWPListTable extends \WP_List_Table
{
    private $views_count = [];

    public function __construct()
    {
        parent::__construct([
            'singular' => 'ppress-customer',
            'plural'   => 'ppress-customers',
            'ajax'     => false
        ]);

        $statuses   = array_keys(CustomerStatus::get_all());
        $statuses[] = 'all';

        foreach ($statuses as $id) {

            if ('all' == $id) {
                $this->views_count[$id] = CustomerRepository::init()->record_count();
            } else {
                $this->views_count[$id] = CustomerRepository::init()->get_count_by_status($id);
            }
        }
    }

    public function no_items()
    {
        _e('No customers found.', 'wp-user-avatar');
    }

    public function get_columns()
    {
        $columns = [
            'cb'                     => '<input type="checkbox" />',
            'customer_name'          => esc_html__('Name', 'wp-user-avatar'),
            'customer_email'         => esc_html__('Email', 'wp-user-avatar'),
            'customer_subscriptions' => esc_html__('Active Subscriptions', 'wp-user-avatar'),
            'customer_since'         => esc_html__('Customer Since', 'wp-user-avatar'),
            'customer_last_login'    => esc_html__('Last Login', 'wp-user-avatar'),
        ];

        return $columns;
    }

    public static function delete_customer_url($customer_id)
    {
        $nonce_delete = wp_create_nonce('pp_customer_delete_rule');

        return add_query_arg(['ppress_customer_action' => 'delete', 'id' => $customer_id, '_wpnonce' => $nonce_delete], PPRESS_MEMBERSHIP_CUSTOMERS_SETTINGS_PAGE);
    }

    public static function view_customer_url($customer_id)
    {
        return add_query_arg(['ppress_customer_action' => 'view', 'id' => $customer_id], PPRESS_MEMBERSHIP_CUSTOMERS_SETTINGS_PAGE);
    }

    /**
     * @param CustomerEntity $customer
     *
     * @return string
     */
    public function column_customer_name($customer)
    {
        $customer_id = absint($customer->id);

        $view_link   = esc_url(self::view_customer_url($customer_id));
        $orders_link = OrderService::init()->get_customer_orders_url($customer->id);
        $subs_link   = add_query_arg(['by_ci' => $customer_id], PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE);

        $actions = [
            'view_customer'      => sprintf('<a href="%s">%s</a>', $view_link, esc_html__('Edit', 'wp-user-avatar')),
            'view_orders'        => sprintf('<a href="%s">%s</a>', $orders_link, esc_html__('View Orders', 'wp-user-avatar')),
            'view_subscriptions' => sprintf('<a href="%s">%s</a>', $subs_link, esc_html__('View Subscriptions', 'wp-user-avatar')),
        ];

        $customer_name = $customer->get_name();

        $title = sprintf('<a class="row-title" href="%s">%s</a>', $view_link, $customer_name);

        return $title . $this->row_actions($actions);
    }

    /**
     * @param CustomerEntity $customer
     *
     * @return string
     */
    public function column_customer_email($customer)
    {
        return ! empty($customer->get_email()) ? $customer->get_email() : '&mdash;&mdash;&mdash;';
    }

    /**
     * @param CustomerEntity $customer
     *
     * @return string
     */
    public function column_customer_since($customer)
    {
        return $customer->get_date_created();
    }

    /**
     * @param CustomerEntity $customer
     *
     * @return string
     */
    public function column_customer_last_login($customer)
    {
        $data = $customer->get_last_login();

        return ! empty($data) ? $data : '&mdash;&mdash;&mdash;';
    }

    /**
     * @param CustomerEntity $customer
     *
     * @return string
     */
    public function column_customer_subscriptions($customer)
    {
        $subs = $customer->get_active_subscriptions();

        if (empty($subs)) return '&mdash;&mdash;&mdash;';

        $subs = array_map(function ($sub) {

            return sprintf(
                '<a target="_blank" href="%s">%s</a>',
                SubscriptionWPListTable::view_edit_subscription_url($sub->id),
                PlanFactory::fromId($sub->get_plan_id())->get_name()
            );
        }, $subs);

        return sprintf(
            '<a target="_blank" href="%s">%s</a>',
            add_query_arg(['by_ci' => $customer->id], PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE),
            implode(', ', $subs)
        );
    }

    public function get_views()
    {
        $views = [];

        $args = ['all' => esc_html__('All', 'wp-user-avatar')] + CustomerStatus::get_all();

        foreach ($args as $id => $status) {

            $url = $id == 'all' ? PPRESS_MEMBERSHIP_CUSTOMERS_SETTINGS_PAGE : add_query_arg(['status' => $id], PPRESS_MEMBERSHIP_CUSTOMERS_SETTINGS_PAGE);

            $views[$id] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%s)</span></a>',
                $url,
                ppressGET_var('status', 'all') == $id ? ' class="current"' : '',
                $status,
                $this->views_count[$id]
            );
        }

        return $views;
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param CustomerEntity $customer
     *
     * @return string
     */
    public function column_cb($customer)
    {
        return sprintf('<input type="checkbox" name="customer_id[]" value="%s" />', $customer->id);
    }

    public function record_count()
    {
        $status = ppressGET_var('status', 'all');

        if ($status != 'all') {
            return $this->views_count[$status];
        }

        return SubscriptionRepository::init()->record_count();
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('customers_per_page', 10);
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $search = ! empty($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $start_date = ppressGET_var('start_date');
        $end_date   = ppressGET_var('end_date');
        $status     = ppressGET_var('status');

        $query_args = [
            'number' => $per_page,
            'offset' => $offset,
            'search' => $search,
            'status' => $status
        ];

        if ( ! empty($start_date)) {
            $query_args['start_date'] = CarbonImmutable::parse($start_date, wp_timezone())->startOfDay()->utc()->toDateTimeString();
        }

        if ( ! empty($end_date)) {
            $query_args['end_date'] = CarbonImmutable::parse($end_date, wp_timezone())->endOfDay()->utc()->toDateTimeString();
        }

        $this->items = CustomerRepository::init()->retrieveBy($query_args);

        $total_items = CustomerRepository::init()->retrieveBy($query_args, true);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);
    }

    public function current_action()
    {
        if (isset($_REQUEST['filter_action']) && ! empty($_REQUEST['filter_action'])) {
            return false;
        }

        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
            return $_REQUEST['action'];
        }

        if (isset($_REQUEST['ppress_customer_action']) && -1 != $_REQUEST['ppress_customer_action']) {
            return $_REQUEST['ppress_customer_action'];
        }

        return false;
    }

    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete' => esc_html__('Delete', 'wp-user-avatar')
        ];

        return $actions;
    }

    public function process_bulk_action()
    {
        $customer_id = absint(ppress_var($_GET, 'id', 0));

        if ( ! current_user_can('manage_options')) return;

        if ('delete' === $this->current_action()) {

            check_admin_referer('pp_customer_delete_rule');

            if ( ! current_user_can('manage_options')) return;

            CustomerService::init()->delete_customer($customer_id);
        }

        if ('bulk-delete' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $customer_ids = array_map('absint', $_GET['customer_id']);

            foreach ($customer_ids as $customer_id) {
                CustomerService::init()->delete_customer($customer_id);
            }
        }

        if ($this->current_action() !== false) {
            wp_safe_redirect(PPRESS_MEMBERSHIP_CUSTOMERS_SETTINGS_PAGE);
            exit;
        }
    }

    public function filter_bar()
    {
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : null;
        $end_date   = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : null;

        $status    = ppressGET_var('status');
        $clear_url = PPRESS_MEMBERSHIP_CUSTOMERS_SETTINGS_PAGE;

        echo '<div class="wp-filter" id="ppress-filters">';
        echo '<div class="filter-items">';
        ?>

        <span id="ppress-date-filters" class="ppress-from-to-wrapper">
            <span class="ppress-start-date-wrap">
                <input type="text" name="start_date" id="start-date" placeholder="<?= _x('From', 'date filter', 'wp-user-avatar') ?>" value="<?= $start_date ?>" class="ppress_datepicker">
            </span>
            <span id="ppress-end-date-wrap">
                <input type="text" name="end_date" id="end-date" value="<?= $end_date ?>" placeholder="<?= _x('To', 'date filter', 'wp-user-avatar') ?>" class="ppress_datepicker">
            </span>
        </span>

        <span id="ppress-after-core-filters">
			<input type="submit" class="button button-secondary" value="<?php esc_html_e('Filter', 'wp-user-avatar'); ?>"/>

			<?php if ( ! empty($_GET['s']) || ! empty($start_date) || ! empty($end_date)) : ?>
                <a href="<?php echo esc_url($clear_url); ?>" class="button-secondary">
					<?php esc_html_e('Clear', 'wp-user-avatar'); ?>
				</a>
            <?php endif; ?>
		</span>

        <?php if ( ! empty($status)) : ?>
        <input type="hidden" name="status" value="<?php echo esc_attr($status); ?>"/>
    <?php endif;
        echo '</div>';
        $this->search_box(__('Search Customers', 'wp-user-avatar'), 'ppress_customer_search');
        echo '</div>';
    }

    /**
     * @return array List of CSS classes for the table tag.
     */
    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', 'customer', 'ppview');
    }
}
