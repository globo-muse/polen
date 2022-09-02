<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\SubscriptionsPage\SubscriptionWPListTable;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;

/** @var OrderEntity $order_data */
/** @var SubscriptionEntity $subscriptionObj */

?>
<div class="ppress-order-subscription-wrap">
    <p>
        <span class="label">
            <span class="dashicons dashicons-update"></span> <?php esc_html_e('Subscription ID', 'wp-user-avatar') ?>:
            <a href="<?= SubscriptionWPListTable::view_edit_subscription_url($subscriptionObj->id) ?>">
                #<?= $subscriptionObj->id ?>
            </a>
        </span> (<?= SubscriptionStatus::get_label($subscriptionObj->status) ?>)
    </p>
</div>