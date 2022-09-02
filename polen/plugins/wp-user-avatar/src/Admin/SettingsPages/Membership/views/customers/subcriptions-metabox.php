<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\OrdersPage\OrderWPListTable;
use ProfilePress\Core\Admin\SettingsPages\Membership\SubscriptionsPage\SubscriptionWPListTable;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;

/** @global SubscriptionEntity[] $subscriptions */

?>


<div id="ppress-submetabox-items">
    <div class="ppress-submetabox-items-table-wrapper">
        <table cellpadding="0" cellspacing="0" class="ppress-submetabox-items-table">
            <thead>
            <tr>
                <th class="ppress-submetabox-item-column-plan"><?= esc_html__('Plan', 'wp-user-avatar') ?></th>
                <th class="ppress-submetabox-item-column-status"><?= esc_html__('Status', 'wp-user-avatar') ?></th>
                <th class="ppress-submetabox-item-column-renewal-date"><?= esc_html__('Renewal Date', 'wp-user-avatar') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($subscriptions as $subscription) : ?>
                <?php
                $planFactory  = PlanFactory::fromId($subscription->plan_id);
                $plan         = sprintf('<a href="%s">%s</a>', SubscriptionWPListTable::view_edit_subscription_url($subscription->id), $planFactory->get_name());
                $renewal_date = $subscription->get_formatted_expiration_date();
                ?>
                <tr class="ppress-submetabox-item-row">
                    <td class="ppress-submetabox-item-column-plan"><?= $plan ?></td>
                    <td class="ppress-submetabox-item-column-status"><?= SubscriptionWPListTable::get_subscription_status_badge($subscription->status) ?></td>
                    <td class="ppress-submetabox-item-column-renewal-date"><?= $renewal_date ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>

    </div>
</div>
