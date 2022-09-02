<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\OrdersPage;

use ProfilePress\Core\Admin\SettingsPages\Membership\CustomersPage\CustomerWPListTable;
use ProfilePress\Core\Membership\Emails\NewOrderReceipt;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderMode;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Services\OrderService;

class OrderWPListTable extends \WP_List_Table
{
    private $views_count = [];

    public function __construct()
    {
        parent::__construct([
            'singular' => 'ppress-order',
            'plural'   => 'ppress-orders',
            'ajax'     => false
        ]);

        $order_statuses   = array_keys(OrderStatus::get_all());
        $order_statuses[] = 'all';

        foreach ($order_statuses as $id) {

            if ('all' == $id) {
                $this->views_count[$id] = OrderRepository::init()->record_count();
            } else {
                $this->views_count[$id] = OrderRepository::init()->get_count_by_status($id);
            }
        }
    }

    public function no_items()
    {
        _e('No orders found.', 'wp-user-avatar');
    }

    public function get_columns()
    {
        $columns = [
            'cb'             => '<input type="checkbox" />',
            'order'          => esc_html__('Order', 'wp-user-avatar'),
            'plan'           => esc_html__('Plan', 'wp-user-avatar'),
            'order_total'    => esc_html__('Total', 'wp-user-avatar'),
            'status'         => esc_html__('Status', 'wp-user-avatar'),
            'payment_method' => esc_html__('Payment Method', 'wp-user-avatar'),
            'date_created'   => esc_html__('Date', 'wp-user-avatar')
        ];

        return $columns;
    }

    public function get_views()
    {
        $views = [];

        $args = ['all' => esc_html__('All', 'wp-user-avatar')] + OrderStatus::get_all();

        foreach ($args as $id => $status) {

            $url = $id == 'all' ? PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE : esc_url(add_query_arg(['status' => $id]));

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
     * @param OrderEntity $order
     *
     * @return string
     */
    public function column_cb($order)
    {
        return sprintf('<input type="checkbox" name="order_id[]" value="%s" />', $order->id);
    }


    public function column_order(OrderEntity $order)
    {
        $order_id = absint($order->id);

        $edit_link          = esc_url(self::view_edit_order_url($order_id));
        $view_customer_link = esc_url(CustomerWPListTable::view_customer_url($order->customer_id));
        $delete_link        = self::delete_order_url($order_id);

        $actions = [
            'edit'     => sprintf('<a href="%s">%s</a>', $edit_link, esc_html__('Edit', 'wp-user-avatar')),
            'customer' => sprintf('<a href="%s">%s</a>', $view_customer_link, esc_html__('View Customer', 'wp-user-avatar')),
        ];

        $actions['delete'] = sprintf('<a class="pp-confirm-delete" href="%s">%s</a>', $delete_link, esc_html__('Delete', 'wp-user-avatar'));

        $customer_name = CustomerFactory::fromId($order->customer_id)->get_name();

        if ( ! empty($customer_name)) {
            $title = sprintf(__('#%1$s - %2$s', 'wp-user-avatar'), $order->get_id(), $customer_name);
        } else {
            $title = sprintf(__('#%1$s - No Customer Assigned', 'wp-user-avatar'), $order->get_id());
        }

        $title = sprintf('<a class="row-title" href="%s">%s</a>', $edit_link, $title);

        $title .= '&nbsp;<strong><span class="post-state"> — ' . OrderType::get_label($order->order_type) . '</span></strong>';

        return $title . $this->row_actions($actions);
    }

    public function column_plan(OrderEntity $order)
    {
        return PlanFactory::fromId($order->plan_id)->get_name();
    }

    public function column_payment_method(OrderEntity $order)
    {
        if (empty($order->payment_method)) return '&mdash;';

        return PaymentMethods::get_instance()->get_by_id($order->payment_method)->get_method_title();
    }

    public function column_order_total(OrderEntity $order)
    {
        return ppress_display_amount($order->get_total());
    }

    public function column_date_created(OrderEntity $order)
    {
        $date = $order->date_created;

        if (empty($date)) return '&mdash;';

        return ppress_format_date_time($order->date_created);
    }

    public function column_status(OrderEntity $order)
    {
        return self::get_order_status_badge($order->status);
    }

    public static function delete_order_url($order_id)
    {
        $nonce_delete = wp_create_nonce('pp_order_delete_rule');

        return add_query_arg(['action' => 'delete', 'id' => $order_id, '_wpnonce' => $nonce_delete], PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE);
    }

    public static function view_edit_order_url($order_id)
    {
        return add_query_arg(['ppress_order_action' => 'edit', 'id' => $order_id], PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE);
    }

    public function record_count()
    {
        $status = ppressGET_var('status', 'all');

        if ($status != 'all') {
            return $this->views_count[$status];
        }

        return OrderRepository::init()->record_count();
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('orders_per_page', 10);
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $search = ! empty($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $start_date = ppressGET_var('start_date');
        $end_date   = ppressGET_var('end_date');
        $status     = ppressGET_var('status');
        $mode       = ppressGET_var('mode');
        if ('all' == $mode) $mode = false;

        $payment_method = ppressGET_var('gateway');
        if ('all' == $payment_method) $payment_method = false;

        $query_args = [
            'number'         => $per_page,
            'offset'         => $offset,
            'search'         => $search,
            'status'         => [$status],
            'mode'           => $mode,
            'payment_method' => $payment_method,
            'start_date'     => $start_date,
            'end_date'       => $end_date
        ];

        if (ppressGET_var('by_ci')) {
            $query_args['customer_id'] = absint($_GET['by_ci']);
        }

        if (ppressGET_var('coupon_code')) {
            $query_args['coupon_code'] = sanitize_text_field($_GET['coupon_code']);
        }

        $this->items = OrderRepository::init()->retrieveBy($query_args);

        $total_items = OrderRepository::init()->retrieveBy($query_args, true);

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

        if (isset($_REQUEST['ppress_order_action']) && -1 != $_REQUEST['ppress_order_action']) {
            return $_REQUEST['ppress_order_action'];
        }

        return false;
    }

    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete'    => esc_html__('Delete', 'wp-user-avatar'),
            'resend-receipt' => esc_html__('Resend Order Receipt', 'wp-user-avatar')
        ];

        return $actions;
    }

    public function process_bulk_action()
    {
        $order_id = absint(ppress_var($_GET, 'id', 0));

        // Bail if user is not an admin or without admin privileges.
        if ( ! current_user_can('manage_options')) return;

        if ('delete' === $this->current_action()) {

            check_admin_referer('pp_order_delete_rule');

            if ( ! current_user_can('manage_options')) return;

            OrderService::init()->delete_order($order_id);
        }

        if ('bulk-delete' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $order_ids = array_map('absint', $_GET['order_id']);

            foreach ($order_ids as $order_id) {
                OrderService::init()->delete_order($order_id);
            }
        }

        if ('resend-receipt' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $order_ids = array_map('absint', $_GET['order_id']);

            foreach ($order_ids as $order_id) {
                NewOrderReceipt::init()->dispatch_email(OrderFactory::fromId($order_id));
            }
        }

        if ($this->current_action() !== false) {
            wp_safe_redirect(PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE);
            exit;
        }
    }

    public function filter_bar()
    {
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : null;
        $end_date   = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : null;
        $gateway    = isset($_GET['gateway']) ? sanitize_text_field($_GET['gateway']) : 'all';
        $mode       = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'all';
        $customer   = isset($_GET['by_ci']) ? absint($_GET['by_ci']) : 'all';

        $status    = ppressGET_var('status');
        $clear_url = PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE;

        $payment_methods = apply_filters('ppress_orders_table_payment_methods', PaymentMethods::get_instance()->get_all());
        $order_modes     = OrderMode::get_all();

        echo '<div class="wp-filter" id="ppress-filters">';
        echo '<div class="filter-items">';

        if ( ! empty($order_modes)) : ?>

            <span id="ppress-mode-filter">
                <select name="mode">
                    <option value="all"><?= esc_html__('All Modes', 'wp-user-avatar') ?></option>
                    <?php foreach ($order_modes as $id => $label) : ?>
                        <option value="<?= $id ?>" <?php selected($id, $mode) ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
			</span>

        <?php endif; ?>

        <span id="ppress-date-filters" class="ppress-from-to-wrapper">
            <span class="ppress-start-date-wrap">
                <input type="text" name="start_date" id="start-date" placeholder="<?= _x('From', 'date filter', 'wp-user-avatar') ?>" value="<?= $start_date ?>" class="ppress_datepicker">
            </span>
            <span id="ppress-end-date-wrap">
                <input type="text" name="end_date" id="end-date" value="<?= $end_date ?>" placeholder="<?= _x('To', 'date filter', 'wp-user-avatar') ?>" class="ppress_datepicker">
            </span>
        </span>

        <?php if ( ! empty($payment_methods)) : ?>
        <span id="ppress-gateway-filter">
            <select name="gateway">
                <option value="all"><?= esc_html__('All Payment Methods', 'wp-user-avatar') ?></option>
                    <?php foreach ($payment_methods as $id => $method) : ?>
                        <option value="<?= $id ?>" <?php selected($id, $gateway) ?>><?= $method->method_title ?></option>
                    <?php endforeach; ?>
            </select>
        </span>

    <?php endif; ?>

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

			<?php if ( ! empty($_GET['s']) || ! empty($start_date) || ! empty($end_date) || ('all' !== $gateway) || $mode !== 'all' || $customer !== 'all') : ?>
                <a href="<?php echo esc_url($clear_url); ?>" class="button-secondary">
					<?php esc_html_e('Clear', 'wp-user-avatar'); ?>
				</a>
            <?php endif; ?>
		</span>

        <?php if ( ! empty($status)) : ?>
        <input type="hidden" name="status" value="<?php echo esc_attr($status); ?>"/>
    <?php endif;
        echo '</div>';
        $this->search_box(__('Search Orders', 'wp-user-avatar'), 'ppress_order_search');
        echo '</div>';
    }

    /**
     * Returns markup for an Order status badge.
     *
     * @param string $order_status Order status ID.
     *
     * @return string
     *
     */
    public static function get_order_status_badge($order_status)
    {
        switch ($order_status) {
            case OrderStatus::REFUNDED :
                $icon = '<span class="ppress-admin-status-badge__icon dashicons-before dashicons-undo"></span>';
                break;
            case OrderStatus::FAILED :
                $icon = '<span class="ppress-admin-status-badge__icon dashicons-before dashicons-no-alt"></span>';
                break;
            case OrderStatus::COMPLETED :
                $icon = '<span class="ppress-admin-status-badge__icon dashicons-before dashicons-yes"></span>';
                break;
            default:
                $icon = '';
        }

        $icon = apply_filters('ppress_get_order_status_badge_icon', $icon, $order_status);

        ob_start();
        ?>

        <span class="ppress-admin-status-badge ppress-admin-status-badge--<?php echo esc_attr($order_status); ?>">

	<span class="ppress-admin-status-badge__text">
		<?php echo OrderStatus::get_label($order_status); ?>
	</span>
	<span class="ppress-admin-status-badge__icon">
		<?php
        echo wp_kses(
            $icon,
            array(
                'span'    => array(
                    'class' => true,
                ),
                'svg'     => array(
                    'class'       => true,
                    'xmlns'       => true,
                    'width'       => true,
                    'height'      => true,
                    'viewbox'     => true,
                    'aria-hidden' => true,
                    'role'        => true,
                    'focusable'   => true,
                ),
                'path'    => array(
                    'fill'      => true,
                    'fill-rule' => true,
                    'd'         => true,
                    'transform' => true,
                ),
                'polygon' => array(
                    'fill'      => true,
                    'fill-rule' => true,
                    'points'    => true,
                    'transform' => true,
                    'focusable' => true,
                ),
            )
        );
        ?>
	</span>
</span>
        <?php return ob_get_clean();
    }

    /**
     * @return array List of CSS classes for the table tag.
     */
    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', 'order', 'ppview');
    }
}
