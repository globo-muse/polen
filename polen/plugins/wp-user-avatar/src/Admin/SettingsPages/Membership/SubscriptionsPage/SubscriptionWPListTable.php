<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\SubscriptionsPage;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\SubscriptionService;

class SubscriptionWPListTable extends \WP_List_Table
{
    private $views_count = [];

    public function __construct()
    {
        parent::__construct([
            'singular' => 'ppress-subscription',
            'plural'   => 'ppress-subscriptions',
            'ajax'     => false
        ]);

        $subscription_statuses   = array_keys(SubscriptionStatus::get_all());
        $subscription_statuses[] = 'all';

        foreach ($subscription_statuses as $id) {

            if ('all' == $id) {
                $this->views_count[$id] = SubscriptionRepository::init()->record_count();
            } else {
                $this->views_count[$id] = SubscriptionRepository::init()->get_count_by_status($id);
            }
        }
    }

    public function no_items()
    {
        _e('No subscriptions found.', 'wp-user-avatar');
    }

    public function get_columns()
    {
        $columns = [
            'cb'              => '<input type="checkbox" />',
            'subscription'    => esc_html__('Subscription', 'wp-user-avatar'),
            'plan'            => esc_html__('Plan', 'wp-user-avatar'),
            'status'          => esc_html__('Status', 'wp-user-avatar'),
            'initial_payment' => esc_html__('Initial Order', 'wp-user-avatar'),
            'renewal_date'    => esc_html__('Renewal Date', 'wp-user-avatar'),
        ];

        if (ppressGET_var('status') == SubscriptionStatus::EXPIRED) {
            $columns['renewal_date'] = esc_html__('Expiration Date', 'wp-user-avatar');
        }

        return $columns;
    }

    public function get_views()
    {
        $views = [];

        $args = ['all' => esc_html__('All', 'wp-user-avatar')] + SubscriptionStatus::get_all();

        foreach ($args as $id => $status) {

            $url = $id == 'all' ? PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE : add_query_arg(['status' => $id], PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE);

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
     * @param SubscriptionEntity $subscription
     *
     * @return string
     */
    public function column_cb($subscription)
    {
        return sprintf('<input type="checkbox" name="subscription_id[]" value="%s" />', $subscription->id);
    }

    public static function view_edit_subscription_url($subscription_id)
    {
        return esc_url(add_query_arg(['ppress_subscription_action' => 'edit', 'id' => $subscription_id], PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE));
    }

    public function column_subscription(SubscriptionEntity $subscription)
    {
        $subscription_id = absint($subscription->id);

        $edit_link   = self::view_edit_subscription_url($subscription_id);
        $delete_link = self::delete_subscription_url($subscription_id);

        $actions = [
            'edit' => sprintf('<a href="%s">%s</a>', $edit_link, esc_html__('Edit', 'wp-user-avatar')),
        ];

        $actions['delete'] = sprintf('<a class="pp-confirm-delete" href="%s">%s</a>', $delete_link, esc_html__('Delete', 'wp-user-avatar'));

        $customer_name = CustomerFactory::fromId($subscription->customer_id)->get_name();

        if ( ! empty($customer_name)) {
            $title = sprintf(__('#%1$s - %2$s', 'wp-user-avatar'), $subscription->get_id(), $customer_name);
        } else {
            $title = sprintf(__('#%1$s - No Customer Assigned', 'wp-user-avatar'), $subscription->get_id());
        }

        $title = sprintf('<a class="row-title" href="%s">%s</a>', $edit_link, $title);

        return $title . $this->row_actions($actions);
    }

    public function column_plan(SubscriptionEntity $subscription)
    {
        $planFactory = PlanFactory::fromId($subscription->plan_id);
        $output      = sprintf(
            '<span class="ppress-line-header"><a href="%s">%s</a></span>',
            $planFactory->get_edit_plan_url(),
            $planFactory->get_name()
        );

        $output .= sprintf('<span class="ppress-line-note">%s</span>', $subscription->get_subscription_terms());

        return $output;
    }

    public function column_initial_payment(SubscriptionEntity $subscription)
    {
        $initial_amount = $subscription->get_initial_amount();

        $output = sprintf(
            '<span class="ppress-line-header">%s</span>',
            ppress_display_amount($initial_amount, OrderFactory::fromId($subscription->parent_order_id)->currency)
        );

        $output .= sprintf('<span class="ppress-line-note">%s</span>', ppress_format_date($subscription->created_date));

        return $output;
    }

    public function column_renewal_date(SubscriptionEntity $subscription)
    {
        return $subscription->get_formatted_expiration_date();
    }

    public function column_date_created(SubscriptionEntity $subscription)
    {
        $date = $subscription->created_date;

        if (empty($date)) return '&mdash;';

        return ppress_format_date_time($subscription->created_date);
    }

    public function column_status(SubscriptionEntity $subscription)
    {
        return self::get_subscription_status_badge($subscription->status);
    }

    public static function delete_subscription_url($subscription_id)
    {
        $nonce_delete = wp_create_nonce('pp_subscription_delete_rule');

        return add_query_arg(['action' => 'delete', 'id' => $subscription_id, '_wpnonce' => $nonce_delete], PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE);
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

        $per_page     = $this->get_items_per_page('subscriptions_per_page', 10);
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $search = ! empty($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $start_date = ppressGET_var('start_date');
        $end_date   = ppressGET_var('end_date');
        $status     = ppressGET_var('status');

        $query_args = [
            'number'     => $per_page,
            'offset'     => $offset,
            'search'     => $search,
            'status'     => [$status],
            'start_date' => $start_date,
            'end_date'   => $end_date
        ];

        if (ppressGET_var('by_ci')) {
            $query_args['customer_id'] = absint($_GET['by_ci']);
        }

        $this->items = SubscriptionRepository::init()->retrieveBy($query_args);

        $total_items = SubscriptionRepository::init()->retrieveBy($query_args, true);

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

        if (isset($_REQUEST['ppress_subscription_action']) && -1 != $_REQUEST['ppress_subscription_action']) {
            return $_REQUEST['ppress_subscription_action'];
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
        $subscription_id = absint(ppress_var($_GET, 'id', 0));

        if ( ! current_user_can('manage_options')) return;

        if ('delete' === $this->current_action()) {

            check_admin_referer('pp_subscription_delete_rule');

            if ( ! current_user_can('manage_options')) return;

            SubscriptionService::init()->delete_subscription($subscription_id);
        }

        if ('bulk-delete' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $subscription_ids = array_map('absint', $_GET['subscription_id']);

            foreach ($subscription_ids as $subscription_id) {
                SubscriptionService::init()->delete_subscription($subscription_id);
            }
        }

        if ($this->current_action() !== false) {
            wp_safe_redirect(PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE);
            exit;
        }
    }

    public function filter_bar()
    {
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : null;
        $end_date   = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : null;
        $customer   = isset($_GET['by_ci']) ? absint($_GET['by_ci']) : 'all';

        $status    = ppressGET_var('status');
        $clear_url = PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE;

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

        <span id="ppress-customer-filter">
            <select name="by_ci" class="ppress-select2-field customer_user" style="min-width:180px">
                <option value="all"><?= esc_html__('All Customers', 'wp-user-avatar') ?></option>
                    <?php if ( ! empty($customer) && 'all' != $customer) : ?>
                        <option value="<?= $customer ?>" selected><?= CustomerFactory::fromId($customer)->get_name() ?></option>
                    <?php endif; ?>
            </select>
        </span>

        <span id="ppress-after-core-filters">
			<input type="submit" class="button button-secondary" value="<?php esc_html_e('Filter', 'wp-user-avatar'); ?>"/>

			<?php if ( ! empty($_GET['s']) || ! empty($start_date) || ! empty($end_date) || $customer !== 'all') : ?>
                <a href="<?php echo esc_url($clear_url); ?>" class="button-secondary">
					<?php esc_html_e('Clear', 'wp-user-avatar'); ?>
				</a>
            <?php endif; ?>
		</span>

        <?php if ( ! empty($status)) : ?>
        <input type="hidden" name="status" value="<?php echo esc_attr($status); ?>"/>
    <?php endif;
        echo '</div>';
        $this->search_box(__('Search Subscriptions', 'wp-user-avatar'), 'ppress_subscription_search');
        echo '</div>';
    }

    /**
     * Returns markup for an subscription status badge.
     *
     * @param string $subscription_status
     *
     * @return string
     *
     */
    public static function get_subscription_status_badge($subscription_status)
    {
        ob_start();
        ?>

        <span class="ppress-admin-status-badge ppress-admin-status-badge--<?php echo esc_attr($subscription_status); ?>">
            <span class="ppress-admin-status-badge__text">
                <?php echo SubscriptionStatus::get_label($subscription_status); ?>
            </span>
        </span>
        <?php return ob_get_clean();
    }

    /**
     * @return array List of CSS classes for the table tag.
     */
    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', 'subscription', 'ppview');
    }
}
