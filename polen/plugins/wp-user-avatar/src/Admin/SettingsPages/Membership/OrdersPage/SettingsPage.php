<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\OrdersPage;

use ProfilePress\Core\Admin\SettingsPages\AbstractSettingsPage;
use ProfilePress\Core\Admin\SettingsPages\Membership\ContextualStateChangeHelper;
use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Core\Membership\Models\Coupon\CouponUnit;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\PaymentMethods\StoreGateway;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\Membership\Repositories\PlanRepository;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\CouponService;
use ProfilePress\Core\Membership\Services\OrderService;
use ProfilePress\Core\Membership\Services\SubscriptionService;
use ProfilePress\Core\Membership\Services\TaxService;
use ProfilePress\Custom_Settings_Page_Api;
use ProfilePressVendor\Carbon\CarbonImmutable;

class SettingsPage extends AbstractSettingsPage
{
    public $error_bucket;

    /** @var OrderWPListTable */
    private $orderListTable;

    function __construct()
    {
        add_action('ppress_register_menu_page', [$this, 'register_cpf_settings_page']);
        add_action('ppress_admin_settings_page_orders', [$this, 'settings_page_function']);

        if (ppressGET_var('page') == PPRESS_MEMBERSHIP_ORDERS_SETTINGS_SLUG) {
            add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
            add_filter('set_screen_option_rules_per_page', [__CLASS__, 'set_screen'], 10, 3);

            add_action('admin_init', function () {
                $this->save_order();
                $this->add_order();
                $this->refund_order();
            });
        }

        add_action('wp_ajax_ppress_mb_search_customers', [$this, 'search_customers']);
        add_action('wp_ajax_ppress_delete_order_note', [$this, 'delete_order_note']);
        add_action('wp_ajax_ppress_mb_order_modal_search', [$this, 'search_plan_coupon']);
        add_action('wp_ajax_ppress_modal_replace_order_item', [$this, 'replace_order_item_modal']);
    }

    public function default_header_menu()
    {
        return 'orders';
    }

    public function register_cpf_settings_page()
    {
        $hook = add_submenu_page(
            PPRESS_DASHBOARD_SETTINGS_SLUG,
            $this->admin_page_title() . ' - ProfilePress',
            esc_html__('Orders', 'wp-user-avatar'),
            'manage_options',
            PPRESS_MEMBERSHIP_ORDERS_SETTINGS_SLUG,
            array($this, 'admin_page_callback'));

        add_action("load-$hook", array($this, 'add_options'));

        do_action('ppress_membership_orders_settings_page_register', $hook);
    }

    public function search_customers()
    {
        if ( ! current_user_can('manage_options')) return;

        check_ajax_referer('ppress-admin-nonce', 'nonce');

        global $wpdb;

        $wp_user_table  = $wpdb->users;
        $wp_user_meta   = $wpdb->usermeta;
        $customer_Table = Base::customers_db_table();

        $search = '%' . $wpdb->esc_like(sanitize_text_field($_GET['search'])) . '%';


        $results['results'] = [];

        $users = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT customer.id AS customer_id  FROM $wp_user_table AS user 
                LEFT JOIN $wp_user_meta AS usermeta on usermeta.user_id = user.id 
                LEFT JOIN $customer_Table AS customer on customer.user_id = user.id 
                WHERE user_email LIKE %s OR (usermeta.meta_key = 'first_name' AND usermeta.meta_value LIKE %s) OR (usermeta.meta_key = 'last_name' AND usermeta.meta_value LIKE %s)",
                [$search, $search, $search]
            ),
            ARRAY_A
        );

        if (is_array($users) && ! empty($users)) {
            foreach ($users as $user) {
                if ( ! empty($user['customer_id'])) {
                    $customer_id                      = (int)$user['customer_id'];
                    $results['results'][$customer_id] = array(
                        'id'   => $customer_id,
                        'text' => CustomerFactory::fromId($customer_id)->get_name(),
                    );
                }
            }
        }

        $results['results'] = array_values($results['results']);

        wp_send_json($results, 200);
    }

    public function search_plan_coupon()
    {
        if ( ! current_user_can('manage_options')) return;

        check_ajax_referer('ppress-admin-nonce', 'nonce');

        global $wpdb;

        $search = sanitize_text_field(ppressGET_var('search'));

        $results['results'] = [];

        if ( ! empty($search)) {

            if (ppressGET_var('type') == 'subscription-plans') {
                $table  = Base::subscription_plans_db_table();
                $result = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT id, name, price FROM $table WHERE name LIKE %s",
                        '%' . $wpdb->esc_like($search) . '%'
                    )
                );

                if ( ! empty($result)) {
                    foreach ($result as $plan) {
                        $results['prices'][$plan->id] = ppress_sanitize_amount($plan->price);
                        $results['results'][]         = array(
                            'id'   => $plan->id,
                            'text' => $plan->name
                        );
                    }
                }
            }

            if (ppressGET_var('type') == 'order_coupon') {
                $table  = Base::coupons_db_table();
                $result = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT id, code FROM $table WHERE code LIKE %s",
                        '%' . $wpdb->esc_like($search) . '%'
                    )
                );

                if ( ! empty($result)) {
                    foreach ($result as $coupon) {
                        $results['results'][] = array(
                            'id'   => $coupon->id,
                            'text' => $coupon->code,
                        );
                    }
                }
            }
        }

        wp_send_json($results, 200);
    }

    public function delete_order_note()
    {
        if ( ! current_user_can('manage_options')) return;

        check_ajax_referer('ppress-admin-nonce', 'security');

        OrderService::init()->delete_order_note_by_id(intval($_POST['note_id']));

        wp_send_json_success();
    }

    public function replace_order_item_modal()
    {
        if ( ! current_user_can('manage_options')) return;

        check_ajax_referer('ppress-admin-nonce', 'security');

        $order_id   = (int)$_POST['order_id'];
        $plan_id    = (int)$_POST['plan'];
        $plan_price = sanitize_text_field($_POST['plan_price']);
        $coupon_id  = (int)$_POST['coupon_code'];
        $tax_amount = sanitize_text_field($_POST['tax']);

        $plan_price = ! empty($plan_price) ? $plan_price : PlanFactory::fromId($plan_id)->get_price();

        $order              = OrderFactory::fromId($order_id);
        $order->plan_id     = ppress_sanitize_amount($plan_id);
        $order->coupon_code = '';
        $order->discount    = '0.00';
        $order->tax         = '0.00';
        $order->subtotal    = $plan_price;

        if ( ! empty($coupon_id)) {

            $couponObj = CouponFactory::fromId($coupon_id);

            $order->coupon_code = $couponObj->code;
            $order->discount    = $couponObj->get_amount();

            if ($couponObj->unit == CouponUnit::PERCENTAGE) {

                $order->discount = CouponService::init()->get_coupon_percentage_fee(
                    $couponObj->get_amount(),
                    Calculator::init($order->subtotal)->val()
                );
            }
        }

        if (TaxService::init()->is_tax_enabled() && ! empty($tax_amount)) {
            $order->tax = ppress_sanitize_amount($tax_amount);
        }

        $order_total = Calculator::init($order->subtotal)->plus($order->tax)->minus($order->discount);

        if (TaxService::init()->is_tax_enabled() && ! empty($tax_amount) && TaxService::init()->is_price_inclusive_tax()) {

            $subtotal = Calculator::init($plan_price)->minus($order->discount)->minus($tax_amount);

            $order->subtotal = $subtotal->val();

            if ($subtotal->isNegativeOrZero()) $order->subtotal = '0.00';

            $order_total = Calculator::init($order->subtotal)->plus($order->tax);
        }

        $order->total = $order_total->val();

        if ($order_total->isNegativeOrZero()) $order->total = '0.00';

        $order->save();

        wp_send_json_success();
    }

    public function refund_order()
    {
        if (ppressGET_var('ppress_order_action') == 'refund_order') {

            check_admin_referer('ppress-cancel-order');

            if (current_user_can('manage_options')) {

                $order_id = intval($_GET['id']);

                $order = OrderRepository::init()->retrieve($order_id);

                if ($order->exists() && $order->is_refundable()) {

                    $payment_method = ppress_get_payment_method($order->payment_method);

                    if (method_exists($payment_method, 'process_refund')) {

                        $response = $payment_method->process_refund(
                            $order->get_id(),
                            $order->get_total()
                        );

                        if ($response === true) {
                            $order->refund_order();
                            SubscriptionRepository::init()->retrieve($order->subscription_id)->cancel(true);
                        }
                    }
                }

                wp_safe_redirect(add_query_arg(['ppress_order_action' => 'edit', 'id' => $order_id, 'saved' => 'true'], PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE));
                exit;
            }
        }
    }

    /**
     * @return void
     */
    public function save_order()
    {
        if ( ! isset($_POST['ppress_save_order'])) return;

        ppress_verify_nonce();

        if ( ! current_user_can('manage_options')) return;

        $order      = OrderFactory::fromId(absint(ppressGET_var('id')));
        $old_status = $order->status;

        $order->date_created = ppress_local_datetime_to_utc(sanitize_text_field($_POST['order_date']));
        if (in_array($_POST['order_status'], array_keys(OrderStatus::get_all()))) {
            $order->status = sanitize_text_field($_POST['order_status']);
        }

        $order->customer_id = absint($_POST['customer_user']);

        foreach ($_POST as $key => $value) {

            if (in_array($key, array_keys(CheckoutFields::standard_billing_fields()))) {

                $key = str_replace('ppress_', '', $key);

                $order->$key = ppress_clean($value, 'sanitize_text_field');
            }
        }

        $order_id = $order->save();

        if ($order_id && OrderStatus::PENDING == $old_status && OrderStatus::COMPLETED == $order->status) {
            $order->complete_order();
        }

        wp_safe_redirect(add_query_arg(['ppress_order_action' => 'edit', 'id' => $order_id, 'saved' => 'true'], PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE));
        exit;
    }

    /**
     * @return void|string
     */
    public function add_order()
    {
        if ( ! isset($_POST['save_ppress_orders'])) return;

        if ( ! current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'wp-user-avatar'), __('Error', 'wp-user-avatar'), array('response' => 403));
        }

        check_admin_referer('wp-csa-nonce', 'wp_csa_nonce');

        global $wpdb;

        $order_data = ppressPOST_var('ppress_orders', []);

        $date_created = current_time('mysql');

        if ( ! empty($order_data['order_date'])) {
            $date_created = CarbonImmutable::parse(sanitize_text_field($order_data['order_date']), wp_timezone())
                                           ->setTimeFromTimeString(CarbonImmutable::now(wp_timezone())->toTimeString())
                                           ->utc()->toDateTimeString();
        }

        $plan_obj = ppress_get_plan((int)$order_data['plan']);

        $customer_id = (int)$order_data['customer'];

        if (CustomerFactory::fromId($customer_id)->has_active_subscription($plan_obj->id)) {
            wp_die(
                sprintf(__('Customer already has an active subscription for %s.', 'wp-user-avatar'), $plan_obj->name),
                __('Error', 'wp-user-avatar'),
                ['response' => 403]
            );
        }

        $order_data['payment_method'] = ! empty($order_data['payment_method']) ?
            $order_data['payment_method'] :
            StoreGateway::get_instance()->get_id();

        $order                 = new OrderEntity();
        $order->plan_id        = $plan_obj->id;
        $order->customer_id    = $customer_id;
        $order->total          = ppress_sanitize_amount($order_data['amount']);
        $order->status         = sanitize_text_field($order_data['order_status']);
        $order->payment_method = sanitize_text_field($order_data['payment_method']);
        $order->transaction_id = sanitize_text_field($order_data['transaction_id']);
        $order->date_created   = $date_created;
        $order_id              = $order->save();

        if ( ! $order_id) {

            wp_die(! empty($wpdb->last_error) ? $wpdb->last_error : esc_html__('Unable to add new order. Please try again', 'wp-user-avatar'),
                __('Error', 'wp-user-avatar'),
                array('response' => 400)
            );
        }

        $subscription                    = new SubscriptionEntity();
        $subscription->parent_order_id   = $order_id;
        $subscription->customer_id       = $customer_id;
        $subscription->plan_id           = (int)$order_data['plan'];
        $subscription->billing_frequency = $plan_obj->billing_frequency;
        $subscription->initial_amount    = ppress_sanitize_amount($order_data['amount']);
        $subscription->recurring_amount  = $plan_obj->price;
        $subscription->expiration_date   = SubscriptionService::init()->get_plan_expiration_datetime($plan_obj->id);

        if ($order->is_completed()) {
            $subscription_id = $subscription->activate_subscription();
        } else {
            $subscription_id = $subscription->save();
        }

        if ( ! $subscription_id) {

            wp_die(! empty($wpdb->last_error) ? $wpdb->last_error : esc_html__('Unable to add new subscription. Please try again', 'wp-user-avatar'),
                __('Error', 'wp-user-avatar'),
                array('response' => 400)
            );
        }

        $order->id              = $order_id;
        $order->subscription_id = $subscription_id;
        if ($order->is_completed()) {
            $order->date_completed = current_time('mysql');
        }
        $order->save();

        if ($order_data['send_receipt'] == 'true' && $order->is_completed()) {
            // important we call complete_order with synced/updated order object.
            OrderFactory::fromId($order_id)->complete_order();
        }

        wp_safe_redirect(esc_url_raw(OrderWPListTable::view_edit_order_url($order_id)));
        exit;
    }

    public function admin_page_title()
    {
        $title = esc_html__('Orders', 'wp-user-avatar');

        if (ppressGET_var('ppress_order_action') == 'new') {
            $title = esc_html__('Add New Order', 'wp-user-avatar');
        }

        if (ppressGET_var('ppress_order_action') == 'edit') {
            $title = esc_html__('Edit Order', 'wp-user-avatar');
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
            'label'   => esc_html__('Orders', 'wp-user-avatar'),
            'default' => 10,
            'option'  => 'orders_per_page'
        ];

        add_screen_option('per_page', $args);

        $this->orderListTable = new OrderWPListTable();
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
            if ($option_name == 'ppress_orders') {
                printf(' action="%s"', ppress_get_current_url_query_string());
            }
        });

        $instance = Custom_Settings_Page_Api::instance();
        if ( ! isset($_GET['ppress_order_action'])) {
            $instance->form_method('get');
            $instance->remove_nonce_field();
        }

        if (ppressGET_var('ppress_order_action') == 'new') {

            $instance->main_content([
                [
                    'plan'           => [
                        'label'   => __('Membership Plan', 'wp-user-avatar'),
                        'type'    => 'select',
                        'options' => (function () {
                            return array_reduce(PlanRepository::init()->retrieveAll(), function ($carry, $item) {
                                $carry[$item->id] = $item->name;

                                return $carry;
                            }, ['' => '&mdash;&mdash;&mdash;&mdash;']);
                        })()
                    ],
                    'customer'       => [
                        'label'      => __('Customer', 'wp-user-avatar'),
                        'type'       => 'select',
                        'options'    => [],
                        'attributes' => ['class' => 'ppress-select2-field customer_user']
                    ],
                    'amount'         => [
                        'label'       => __('Amount', 'wp-user-avatar'),
                        'type'        => 'text',
                        'placeholder' => ppress_display_amount('0.00'),
                        'description' => esc_html__('Enter the total order amount, or leave blank to use the price of the selected plan.', 'wp-user-avatar')
                    ],
                    'order_status'   => [
                        'label'   => __('Order Status', 'wp-user-avatar'),
                        'type'    => 'select',
                        'options' => OrderStatus::get_all()
                    ],
                    'payment_method' => [
                        'label'   => __('Payment Method', 'wp-user-avatar'),
                        'type'    => 'select',
                        'options' => (function () {
                            return array_reduce(PaymentMethods::get_instance()->get_all(), function ($carry, $item) {
                                $carry[$item->id] = $item->method_title;

                                return $carry;
                            }, ['' => '&mdash;&mdash;&mdash;&mdash;']);
                        })()
                    ],
                    'transaction_id' => [
                        'label'       => __('Transaction ID', 'wp-user-avatar'),
                        'type'        => 'text',
                        'description' => esc_html__('Enter the transaction ID, if any.', 'wp-user-avatar')
                    ],
                    'order_date'     => [
                        'label'       => __('Date', 'wp-user-avatar'),
                        'type'        => 'text',
                        'class'       => 'ppress_datepicker',
                        'description' => esc_html__("Enter the purchase date, or leave blank for today's date.", 'wp-user-avatar')
                    ],
                    'send_receipt'   => [
                        'label'          => __('Send Receipt', 'wp-user-avatar'),
                        'checkbox_label' => __('Check to send the order receipt to the customer.', 'wp-user-avatar'),
                        'type'           => 'checkbox',
                        'default_value'  => 'true'
                    ]
                ]
            ]);

            $instance->remove_white_design();

            $instance->sidebar(self::sidebar_args());
        }

        $instance->add_view_classes('ppview');
        $instance->option_name('ppress_orders');
        $instance->page_header($this->admin_page_title());
        $instance->build(ppressGET_var('ppress_order_action') != 'new');
    }

    public function add_new_button()
    {
        if ( ! isset($_GET['ppress_order_action'])) {
            $url = esc_url_raw(add_query_arg('ppress_order_action', 'new', PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE));
            echo "<a class=\"add-new-h2\" href=\"$url\">" . esc_html__('Add New Order', 'wp-user-avatar') . '</a>';
        }

        if (ppressGET_var('ppress_order_action') == 'edit') {
            $url = esc_url_raw(PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE);
            echo "<a class=\"add-new-h2\" href=\"$url\">" . esc_html__('Back to Orders', 'wp-user-avatar') . '</a>';
        }
    }

    public function admin_settings_page_callback($content)
    {
        if (ppressGET_var('ppress_order_action') == 'new') {
            return $content;
        }

        if (ppressGET_var('ppress_order_action') == 'edit') {
            $this->admin_notices();
            ContextualStateChangeHelper::init();
            require_once dirname(dirname(__FILE__)) . '/views/orders/add-edit-order.php';

            return;
        }

        $this->orderListTable->prepare_items();

        echo '<input type="hidden" name="page" value="' . PPRESS_MEMBERSHIP_ORDERS_SETTINGS_SLUG . '" />';
        echo '<input type="hidden" name="view" value="orders" />';
        if (isset($_GET['status'])) {
            echo '<input type="hidden" name="status" value="' . sanitize_text_field($_GET['status']) . '" />';
        }
        if (isset($_GET['by_ci'])) {
            echo '<input type="hidden" name="by_ci" value="' . sanitize_text_field($_GET['by_ci']) . '" />';
        }
        $this->orderListTable->views();
        $this->orderListTable->filter_bar();
        $this->orderListTable->display();

        do_action('ppress_order_wp_list_table_bottom');
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