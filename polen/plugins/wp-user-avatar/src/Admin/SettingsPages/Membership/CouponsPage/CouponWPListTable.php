<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\CouponsPage;

use ProfilePress\Core\Membership\Models\Coupon\CouponEntity;
use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Core\Membership\Models\Coupon\CouponType;
use ProfilePress\Core\Membership\Models\Coupon\CouponUnit;
use ProfilePress\Core\Membership\Repositories\CouponRepository;

class CouponWPListTable extends \WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'ppress-coupon-code',
            'plural'   => 'ppress-coupon-codes',
            'ajax'     => false
        ));
    }

    public function no_items()
    {
        _e('No coupon code found.', 'wp-user-avatar');
    }

    public function get_columns()
    {
        $columns = [
            'cb'          => '<input type="checkbox" />',
            'coupon_code' => esc_html__('Code', 'wp-user-avatar'),
            'discount'    => esc_html__('Discount', 'wp-user-avatar'),
            'redemption'  => esc_html__('Redemptions', 'wp-user-avatar'),
            'status'      => esc_html__('Status', 'wp-user-avatar'),
            'start_date'  => esc_html__('Start Date', 'wp-user-avatar'),
            'end_date'    => esc_html__('End Date', 'wp-user-avatar'),
        ];

        return $columns;
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param CouponEntity $item
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="coupon_id[]" value="%s" />', $item->id);
    }


    public function column_coupon_code(CouponEntity $item)
    {
        $coupon_id = absint($item->id);

        $is_active = $item->is_active();

        $edit_link       = esc_url(add_query_arg(['ppress_coupon_action' => 'edit', 'id' => $coupon_id], PPRESS_MEMBERSHIP_COUPONS_SETTINGS_PAGE));
        $activate_link   = esc_url(add_query_arg(['ppress_coupon_action' => 'activate', 'id' => $coupon_id, '_wpnonce' => wp_create_nonce('pp_coupon_activate_rule')], PPRESS_MEMBERSHIP_COUPONS_SETTINGS_PAGE));
        $deactivate_link = esc_url(add_query_arg(['ppress_coupon_action' => 'deactivate', 'id' => $coupon_id, '_wpnonce' => wp_create_nonce('pp_coupon_deactivate_rule')], PPRESS_MEMBERSHIP_COUPONS_SETTINGS_PAGE));
        $delete_link     = self::delete_coupon_url($coupon_id);

        $actions = [
            'edit' => sprintf('<a href="%s">%s</a>', $edit_link, esc_html__('Edit', 'wp-user-avatar')),
        ];

        if (true === $is_active) {
            $actions['deactivate'] = sprintf('<a href="%s">%s</a>', $deactivate_link, esc_html__('Deactivate', 'wp-user-avatar'));
        }

        if (false === $is_active) {
            $actions['activate'] = sprintf('<a href="%s">%s</a>', $activate_link, esc_html__('Activate', 'wp-user-avatar'));
        }

        $actions['delete'] = sprintf('<a class="pp-confirm-delete" href="%s">%s</a>', $delete_link, esc_html__('Delete', 'wp-user-avatar'));

        $a = '<a href="' . $edit_link . '">' . sanitize_text_field($item->code) . '</a>';

        $coupon_type = $item->is_recurring() ? esc_html__('Recurring', 'wp-user-avatar') : esc_html__('One-time', 'wp-user-avatar');

        $a .= '&nbsp;<span class="post-state"> — ' . $coupon_type . '</span>';

        return '<strong>' . $a . '</strong>' . $this->row_actions($actions);
    }

    public function column_discount(CouponEntity $item)
    {
        $unit = $item->unit;

        if (CouponUnit::FLAT == $unit) {
            return ppress_display_amount($item->amount);
        }

        return $item->amount . '%';
    }

    public function column_start_date(CouponEntity $item)
    {
        $date = $item->start_date;

        if (empty($date)) return '&mdash;';

        return gmdate(get_option('date_format'), strtotime($date . ' UTC'));
    }

    public function column_end_date(CouponEntity $item)
    {
        $date = $item->end_date;

        if (empty($date)) return '&mdash;';

        return gmdate(get_option('date_format'), strtotime($date . ' UTC'));
    }

    public function column_redemption(CouponEntity $item)
    {
        $usage_limit = absint($item->usage_limit);

        if (empty($usage_limit) || $usage_limit === 0) {
            $usage_limit = esc_html__('Unlimited', 'wp-user-avatar');
        }

        return sprintf('<a href="%s">%s</a>',
            add_query_arg(['coupon_code' => $item->code], PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE),
            $item->get_usage_count() . ' / ' . $usage_limit
        );
    }

    public function column_status(CouponEntity $item)
    {
        $status = sprintf('<span class="dashicons-before dashicons-yes">%s</span>', esc_html__('Active', 'wp-user-avatar'));
        if ( ! $item->is_active()) {
            $status = sprintf('<span class="dashicons-before dashicons-no-alt">%s</span>', esc_html__('Inactive', 'wp-user-avatar'));
        }

        if ($item->is_expired()) {
            $status = sprintf('<span class="dashicons-before dashicons-no-alt">%s</span>', esc_html__('Expired', 'wp-user-avatar'));
        }

        return $status;
    }

    public static function delete_coupon_url($coupon_id)
    {
        $nonce_delete = wp_create_nonce('pp_coupon_delete_rule');

        return add_query_arg(['action' => 'delete', 'id' => $coupon_id, '_wpnonce' => $nonce_delete], PPRESS_MEMBERSHIP_COUPONS_SETTINGS_PAGE);
    }

    public function get_coupons($per_page, $current_page = 1)
    {
        return CouponRepository::init()->retrieveAll($per_page, $current_page);
    }

    public function record_count()
    {
        return CouponRepository::init()->record_count();
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('coupons_per_page', 10);;
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ]);

        $this->items = $this->get_coupons($per_page, $current_page);
    }

    public function current_action()
    {
        if (isset($_REQUEST['filter_action']) && ! empty($_REQUEST['filter_action'])) {
            return false;
        }

        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
            return $_REQUEST['action'];
        }

        if (isset($_REQUEST['ppress_coupon_action']) && -1 != $_REQUEST['ppress_coupon_action']) {
            return $_REQUEST['ppress_coupon_action'];
        }

        return false;
    }

    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete'     => esc_html__('Delete', 'wp-user-avatar'),
            'bulk-activate'   => esc_html__('Activate', 'wp-user-avatar'),
            'bulk-deactivate' => esc_html__('Deactivate', 'wp-user-avatar')
        ];

        return $actions;
    }

    public function process_bulk_action()
    {
        $coupon_id = absint(ppress_var($_GET, 'id', 0));

        $couponObj = CouponFactory::fromId($coupon_id);

        // Bail if user is not an admin or without admin privileges.
        if ( ! current_user_can('manage_options')) return;

        if ('deactivate' === $this->current_action()) {

            check_admin_referer('pp_coupon_deactivate_rule');

            if ( ! current_user_can('manage_options')) return;

            $couponObj->deactivate();
        }

        if ('activate' === $this->current_action()) {

            check_admin_referer('pp_coupon_activate_rule');

            if ( ! current_user_can('manage_options')) return;

            $couponObj->activate();
        }

        if ('delete' === $this->current_action()) {

            check_admin_referer('pp_coupon_delete_rule');

            if ( ! current_user_can('manage_options')) return;

            CouponRepository::init()->delete($coupon_id);
        }

        if ('bulk-delete' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $coupon_ids = array_map('absint', $_POST['coupon_id']);

            foreach ($coupon_ids as $coupon_id) {
                CouponRepository::init()->delete($coupon_id);
            }
        }

        if ('bulk-activate' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $coupon_ids = array_map('absint', $_POST['coupon_id']);

            foreach ($coupon_ids as $coupon_id) {
                CouponFactory::fromId($coupon_id)->activate();
            }
        }

        if ('bulk-deactivate' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $coupon_ids = array_map('absint', $_POST['coupon_id']);

            foreach ($coupon_ids as $coupon_id) {
                CouponFactory::fromId($coupon_id)->deactivate();
            }
        }

        if ($this->current_action() !== false) {
            wp_safe_redirect(PPRESS_MEMBERSHIP_COUPONS_SETTINGS_PAGE);
            exit;
        }
    }

    /**
     * @return array List of CSS classes for the table tag.
     */
    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', 'coupon', 'ppview');
    }
}
