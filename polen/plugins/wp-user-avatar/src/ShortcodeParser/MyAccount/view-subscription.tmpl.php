<?php

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\ShortcodeParser\MyAccount\MyAccountTag;

if ( ! defined('ABSPATH')) {
    exit;
}

if ( ! is_user_logged_in()) return;

$sub = SubscriptionFactory::fromId(ppressGET_var('sub_id'));

$customer_id = CustomerFactory::fromUserId(get_current_user_id())->id;

$payment_method = PaymentMethods::get_instance()->get_by_id($sub->get_payment_method());

?>
    <div class="profilepress-myaccount-orders-subs">

        <?php

        if ( ! $sub->exists() || $sub->customer_id != $customer_id) :
            printf('<p class="profilepress-myaccount-alert pp-alert-danger">%s</p>', esc_html__('Invalid subscription', 'wp-user-avatar'));
        else :
            $plan = ppress_get_plan($sub->plan_id);
            $sub_orders = $sub->get_all_orders();

            $parent_order = OrderFactory::fromId($sub->parent_order_id);
            $actions      = [];

            if ( ! $sub->has_cancellation_requested() && $sub->can_cancel()) {
                $actions['cancel'] = esc_html__('Cancel', 'wp-user-avatar');
            }

            /**
             * @todo when plan group launches, do not show resubscribe button if expired/cancelled plan belongs to
             * a plan group where the customer has an active plan
             */
            if ($sub->is_cancelled() || $sub->is_expired()) {
                $actions['resubscribe'] = esc_html__('Resubscribe', 'wp-user-avatar');
            }

            do_action('ppress_myaccount_subscription_action_status', $sub, ppressGET_var('ppress-myac-sub-message'));
            ?>

            <div class="profilepress-myaccount-order-details-wrap">
                <div class="ppress-details-table-wrap">
                    <table class="ppress-details-table">
                        <tbody>
                        <tr>
                            <td><?php esc_html_e('Status', 'wp-user-avatar'); ?></td>
                            <td>
                                <span class="sub-status <?= $sub->status ?>"><?= SubscriptionStatus::get_label($sub->status) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Plan', 'wp-user-avatar'); ?></td>
                            <td><?= $plan->name ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Terms', 'wp-user-avatar'); ?></td>
                            <td><?= $sub->get_subscription_terms() ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Initial Amount', 'wp-user-avatar'); ?></td>
                            <td><?= ppress_display_amount($sub->initial_amount, $parent_order->currency) ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Start Date', 'wp-user-avatar'); ?></td>
                            <td><?= ppress_format_date($sub->created_date) ?></td>
                        </tr>
                        <tr>
                            <td><?php echo ($sub->is_cancelled() || $sub->has_cancellation_requested()) ? esc_html__('Expiration Date', 'wp-user-avatar') : esc_html__('Renewal Date', 'wp-user-avatar'); ?></td>
                            <td><?= $sub->get_formatted_expiration_date() ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Payment', 'wp-user-avatar'); ?></td>
                            <td><?php printf(esc_html__('Via %s', 'wp-user-avatar'), $payment_method->title) ?></td>
                        </tr>
                        <?php if ( ! empty($actions)) : ?>
                            <tr>
                                <td><?php esc_html_e('Actions', 'wp-user-avatar'); ?></td>
                                <td>
                                    <?php foreach ($actions as $action => $label) :
                                        $url = wp_nonce_url(
                                            add_query_arg(['ppress_myac_sub_action' => $action, 'sub_id' => $sub->id]),
                                            $sub->id . $action
                                        ); ?>
                                        <a href="<?php echo esc_url($url); ?>" class="ppress-myac-action ppress-<?php echo sanitize_html_class($action) ?> ppress-confirm-delete"><?php echo esc_html($label); ?></a>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ( ! empty($sub_orders)) : ?>

            <h2><?= sprintf(esc_html__('Subscription Orders', 'wp-user-avatar'), $sub->id) ?></h2>

            <div class="profilepress-myaccount-sub-order-details-wrap">
                <div class="profilepress-myaccount-sub-order-details-table-wrap">
                    <table class="ppress-details-table">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('Order', 'wp-user-avatar') ?></th>
                            <th><?php esc_html_e('Date', 'wp-user-avatar') ?></th>
                            <th><?php esc_html_e('Status', 'wp-user-avatar') ?></th>
                            <th><?php esc_html_e('Total', 'wp-user-avatar') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($sub_orders as $sub_order) : $view_order_url = add_query_arg(['order_key' => $sub_order->order_key], MyAccountTag::get_endpoint_url('list-orders'));
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($view_order_url) ?>">#<?= $sub_order->get_reduced_order_key() ?></a>
                                </td>
                                <td><?= ppress_format_date($sub_order->date_created) ?></td>
                                <td><?= OrderStatus::get_label($sub_order->status) ?></td>
                                <td><?= ppress_display_amount($sub_order->total, $sub_order->currency) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>
        <?php endif; ?>

    </div>
<?php

do_action('ppress_myaccount_view_order_details');