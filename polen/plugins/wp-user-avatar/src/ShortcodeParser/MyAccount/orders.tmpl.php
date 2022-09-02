<?php

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Services\OrderService;
use ProfilePress\Core\ShortcodeParser\MyAccount\MyAccountTag;

if ( ! defined('ABSPATH')) {
    exit;
}

if ( ! is_user_logged_in()) return;

$current_user_id = get_current_user_id();

$orders_per_page = apply_filters('ppress_myaccount_order_list_per_page', 10);

$current_page = absint(max(1, ppressGET_var('olpage', 1, true)));

$offset = $current_page > 1 ? ($current_page - 1) * $orders_per_page : 0;

$order_args = [
    'offset' => $offset,
    'number' => $orders_per_page
];

$orders = CustomerFactory::fromUserId($current_user_id)->get_orders($order_args);

$total_orders = CustomerFactory::fromUserId($current_user_id)->get_orders($order_args, $count = true);

$total_pages = ceil(absint($total_orders) / absint($orders_per_page));

?>
    <div class="profilepress-myaccount-orders-subs">

        <h2><?= esc_html__('Orders', 'wp-user-avatar') ?></h2>

        <div class="profilepress-myaccount-orders-subs-wrap">
            <?php if (empty($orders)) : ?>
                <?php printf('<p class="profilepress-myaccount-alert pp-alert-danger">%s</p>', __('You have not made any order.', 'wp-user-avatar')); ?>
            <?php else: ?>
                <?php foreach ($orders as $order) :
                    $plan = ppress_get_plan($order->plan_id);
                    $view_order_url = OrderService::init()->frontend_view_order_url($order->order_key);
                    ?>
                    <div class="ppress-my-account-order-sub-wrap">
                        <div class="ppress-my-account-order-sub-header-wrap">
                            <dl class="ppress-my-account-order-sub-header--details">
                                <div>
                                    <dt><?php esc_html_e('Date Placed', 'wp-user-avatar'); ?></dt>
                                    <dd>
                                        <?php echo ppress_format_date($order->date_created) ?>
                                    </dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Order Status', 'wp-user-avatar'); ?></dt>
                                    <dd class="<?= $order->status ?>"><span>
                                            <?php if ($order->is_completed() && ! $order->is_new_order()) : ?>
                                                <?php echo OrderType::get_label($order->order_type) ?>
                                            <?php else : ?>
                                                <?php echo OrderStatus::get_label($order->status) ?>
                                            <?php endif; ?>
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Total Amount', 'wp-user-avatar'); ?></dt>
                                    <dd class="ppress-item-amount">
                                        <?php echo ppress_display_amount($order->total, $order->currency) ?>
                                    </dd>
                                </div>
                            </dl>
                            <div class="ppress-my-account-order-sub-header--actions">
                                <a href="<?php echo esc_url($view_order_url) ?>" class="ppress-my-account-order-sub-header--actions--link">
                                    <span><?= esc_html__('View Order', 'wp-user-avatar') ?></span>
                                </a>
                            </div>
                        </div>

                        <div class="ppress-my-account-order-sub-body-wrap">
                            <div class="ppress-my-account-order-sub-body-content">
                                <div class="ppress-my-account-order-sub-body-content-header">
                                    <div class="ppress-my-account-order-sub-product-name"><?php echo $plan->name; ?></div>
                                    <p class="ppress-my-account-order-sub-product-price"><?php echo ppress_display_amount($plan->price); ?></p>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>

        <?php

        if ($total_pages > 1) {

            echo '<div class="ppress-myac-pagination-wrap">';
            echo paginate_links([
                'total'     => $total_pages,
                'current'   => $current_page,
                'format'    => '?olpage' . '=%#%',
                'prev_text' => '<span class="ppress-material-icons">keyboard_arrow_left</span>',
                'next_text' => '<span class="ppress-material-icons">keyboard_arrow_right</span>',
            ]);
            echo '</div>';
        }
        ?>

    </div>
<?php

do_action('ppress_myaccount_order_list');