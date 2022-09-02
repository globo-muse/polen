<?php

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\ShortcodeParser\MyAccount\MyAccountTag;

if ( ! defined('ABSPATH')) {
    exit;
}

if ( ! is_user_logged_in() ) return;

$current_user_id = get_current_user_id();

$per_page = apply_filters('ppress_myaccount_subscription_list_per_page', 10);

$current_page = absint(max(1, ppressGET_var('slpage', 1, true)));

$offset = $current_page > 1 ? ($current_page - 1) * $per_page : 0;

$order_args = [
    'offset' => $offset,
    'number' => $per_page
];

$subs = CustomerFactory::fromUserId($current_user_id)->get_subscriptions([], $order_args);

$total_subs = CustomerFactory::fromUserId($current_user_id)->get_subscriptions([], $order_args, $count = true);

$total_pages = ceil(absint($total_subs) / absint($per_page));

?>
    <div class="profilepress-myaccount-orders-subs">

        <h2><?= esc_html__('Subscriptions', 'wp-user-avatar') ?></h2>

        <div class="profilepress-myaccount-orders-subs-wrap">
            <?php if (empty($subs)) : ?>
                <?php printf('<p class="profilepress-myaccount-alert pp-alert-danger">%s</p>', __('You have no subscription.', 'wp-user-avatar')); ?>
            <?php else: ?>
                <?php foreach ($subs as $sub) :
                    $plan = ppress_get_plan($sub->plan_id);
                    $parent_order = OrderFactory::fromId($sub->parent_order_id);
                    $view_order_url = add_query_arg(['sub_id' => $sub->id], MyAccountTag::get_endpoint_url('list-subscriptions'));
                    ?>
                    <div class="ppress-my-account-order-sub-wrap">
                        <div class="ppress-my-account-order-sub-header-wrap">
                            <dl class="ppress-my-account-order-sub-header--details">
                                <div>
                                    <dt><?php esc_html_e('Initial Amount', 'wp-user-avatar'); ?></dt>
                                    <dd class="ppress-item-amount">
                                        <?php echo ppress_display_amount($sub->get_initial_amount(), $parent_order->currency) ?>
                                    </dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Status', 'wp-user-avatar'); ?></dt>
                                    <dd class="<?= $sub->status ?>">
                                        <span><?php echo SubscriptionStatus::get_label(($sub->status)) ?></span>
                                    </dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Renewal Date', 'wp-user-avatar'); ?></dt>
                                    <dd>
                                        <?php echo $sub->get_formatted_expiration_date() ?>
                                    </dd>
                                </div>
                            </dl>
                            <div class="ppress-my-account-order-sub-header--actions">
                                <a href="<?php echo esc_url($view_order_url) ?>" class="ppress-my-account-order-sub-header--actions--link">
                                    <span><?= esc_html__('View Subscription', 'wp-user-avatar') ?></span>
                                </a>
                            </div>
                        </div>

                        <div class="ppress-my-account-order-sub-body-wrap">
                            <div class="ppress-my-account-order-sub-body-content">
                                <div class="ppress-my-account-order-sub-body-content-header">
                                    <div class="ppress-my-account-order-sub-product-name"><?php echo $plan->name; ?></div>
                                    <p class="ppress-my-account-order-sub-product-price"><?php echo $sub->get_subscription_terms() ?></p>
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
                'format'    => '?slpage' . '=%#%',
                'prev_text' => '<span class="ppress-material-icons">keyboard_arrow_left</span>',
                'next_text' => '<span class="ppress-material-icons">keyboard_arrow_right</span>',
            ]);
            echo '</div>';
        }
        ?>

    </div>
<?php

do_action('ppress_myaccount_subscription_list');