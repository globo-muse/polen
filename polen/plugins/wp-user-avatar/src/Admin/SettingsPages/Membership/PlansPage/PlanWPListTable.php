<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\PlansPage;

use ProfilePress\Core\Membership\Controllers\SubscriptionPlanController;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\Repositories\PlanRepository;

class PlanWPListTable extends \WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'ppress-membership-plan',
            'plural'   => 'ppress-membership-plans',
            'ajax'     => false
        ));
    }

    public function no_items()
    {
        _e('No membership plan found.', 'wp-user-avatar');
    }

    public function get_columns()
    {
        $columns = [
            'cb'              => '<input type="checkbox" />',
            'name'            => esc_html__('Plan Name', 'wp-user-avatar'),
            'billing_details' => esc_html__('Billing Details', 'wp-user-avatar'),
            'checkout_url'    => esc_html__('Checkout URL', 'wp-user-avatar'),
            'status'          => esc_html__('Status', 'wp-user-avatar'),
        ];

        return $columns;
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param PlanEntity $item
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="plan_id[]" value="%s" />', $item->id);
    }


    public function column_name(PlanEntity $item)
    {
        $plan_id = absint($item->id);

        $is_active = $item->is_active();

        $edit_link       = add_query_arg(['ppress_subp_action' => 'edit', 'id' => $plan_id], PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);
        $duplicate_link  = add_query_arg(['ppress_subp_action' => 'duplicate', 'id' => $plan_id, '_wpnonce' => wp_create_nonce('pp_subscription_plan_duplicate_rule')], PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);
        $activate_link   = add_query_arg(['ppress_subp_action' => 'activate', 'id' => $plan_id, '_wpnonce' => wp_create_nonce('pp_subscription_plan_activate_rule')], PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);
        $deactivate_link = add_query_arg(['ppress_subp_action' => 'deactivate', 'id' => $plan_id, '_wpnonce' => wp_create_nonce('pp_subscription_plan_deactivate_rule')], PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);
        $delete_link     = self::delete_plan_url($plan_id);

        $actions = [
            'id'        => sprintf(__('ID: %d', 'wp-user-avatar'), $plan_id),
            'edit'      => sprintf('<a href="%s">%s</a>', $edit_link, esc_html__('Edit', 'wp-user-avatar')),
            'duplicate' => sprintf('<a href="%s">%s</a>', $duplicate_link, esc_html__('Duplicate', 'wp-user-avatar'))
        ];

        if (true === $is_active) {
            $actions['deactivate'] = sprintf('<a href="%s">%s</a>', $deactivate_link, esc_html__('Deactivate', 'wp-user-avatar'));
        }

        if (false === $is_active) {
            $actions['activate'] = sprintf('<a href="%s">%s</a>', $activate_link, esc_html__('Activate', 'wp-user-avatar'));
        }

        $actions['delete'] = sprintf('<a class="pp-confirm-delete" href="%s">%s</a>', $delete_link, esc_html__('Delete', 'wp-user-avatar'));

        $a = '<a href="' . $edit_link . '">' . sanitize_text_field($item->name) . '</a>';

        return '<strong>' . $a . '</strong>' . $this->row_actions($actions);
    }

    public function column_billing_details(PlanEntity $item)
    {
        $billing_data = wp_json_encode([
            'price'               => ppress_sanitize_amount($item->price),
            'billing_frequency'   => sanitize_text_field($item->billing_frequency),
            'total_payments'      => absint($item->total_payments),
            'signup_fee'          => ppress_sanitize_amount($item->signup_fee),
            'subscription_length' => sanitize_text_field($item->subscription_length),
            'free_trial'          => sanitize_text_field($item->free_trial)
        ]);

        printf('<div class="ppress-plan-billing-details" data-billing-details="%s"></div>', esc_attr($billing_data));
    }

    public function column_checkout_url(PlanEntity $item)
    {
        $url = ppress_plan_checkout_url($item->id);

        if ( ! $url) return esc_html__('Checkout page not found', 'wp-user-avatar');

        return '<input type="text" onfocus="this.select();" readonly="readonly" value="' . esc_url($url) . '" />';
    }

    public function column_status(PlanEntity $item)
    {
        $status = sprintf('<span class="dashicons-before dashicons-yes">%s</span>', esc_html__('Active', 'wp-user-avatar'));
        if ( ! $item->is_active()) {
            $status = sprintf('<span class="dashicons-before dashicons-no-alt">%s</span>', esc_html__('Inactive', 'wp-user-avatar'));
        }

        return $status;
    }

    public static function delete_plan_url($plan_id)
    {
        $nonce_delete = wp_create_nonce('pp_subscription_plan_delete_rule');

        return add_query_arg(['action' => 'delete', 'id' => $plan_id, '_wpnonce' => $nonce_delete], PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);
    }

    public function get_plans($per_page, $current_page = 1)
    {
        return PlanRepository::init()->retrieveAll($per_page, $current_page);
    }

    public function record_count()
    {
        return PlanRepository::init()->record_count();
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('plans_per_page', 10);;
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ]);

        $this->items = $this->get_plans($per_page, $current_page);
    }

    public function current_action()
    {
        if (isset($_REQUEST['filter_action']) && ! empty($_REQUEST['filter_action'])) {
            return false;
        }

        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
            return $_REQUEST['action'];
        }

        if (isset($_REQUEST['ppress_subp_action']) && -1 != $_REQUEST['ppress_subp_action']) {
            return $_REQUEST['ppress_subp_action'];
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
        $plan_id = absint(ppress_var($_GET, 'id', 0));

        $planObj = ppress_get_plan($plan_id);

        // Bail if user is not an admin or without admin privileges.
        if ( ! current_user_can('manage_options')) return;

        if ('deactivate' === $this->current_action()) {

            check_admin_referer('pp_subscription_plan_deactivate_rule');

            if ( ! current_user_can('manage_options')) return;

            SubscriptionPlanController::get_instance()->deactivate_plan($planObj);
        }

        if ('activate' === $this->current_action()) {

            check_admin_referer('pp_subscription_plan_activate_rule');

            if ( ! current_user_can('manage_options')) return;

            SubscriptionPlanController::get_instance()->activate_plan($planObj);
        }

        if ('delete' === $this->current_action()) {

            check_admin_referer('pp_subscription_plan_delete_rule');

            if ( ! current_user_can('manage_options')) return;

            SubscriptionPlanController::get_instance()->delete_plan($planObj);
        }

        if ('duplicate' === $this->current_action()) {

            check_admin_referer('pp_subscription_plan_duplicate_rule');

            if ( ! current_user_can('manage_options')) return;

            SubscriptionPlanController::get_instance()->duplicate_plan($planObj);
        }

        if ('bulk-delete' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $plan_ids = array_map('absint', $_POST['plan_id']);

            foreach ($plan_ids as $plan_id) {

                SubscriptionPlanController::get_instance()->delete_plan($plan_id);
            }
        }

        if ('bulk-activate' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $plan_ids = array_map('absint', $_POST['plan_id']);

            foreach ($plan_ids as $plan_id) {

                SubscriptionPlanController::get_instance()->activate_plan($plan_id);
            }
        }

        if ('bulk-deactivate' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            if ( ! current_user_can('manage_options')) return;

            $plan_ids = array_map('absint', $_POST['plan_id']);

            foreach ($plan_ids as $plan_id) {

                SubscriptionPlanController::get_instance()->deactivate_plan($plan_id);
            }
        }

        if ($this->current_action() !== false) {
            wp_safe_redirect(PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);
            exit;
        }
    }

    /**
     * @return array List of CSS classes for the table tag.
     */
    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', 'subscription_plan', 'ppview');
    }
}
