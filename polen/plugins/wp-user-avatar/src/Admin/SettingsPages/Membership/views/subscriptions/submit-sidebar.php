<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\SubscriptionsPage\SubscriptionWPListTable;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;

/** @var int $subscription_id */
/** @var SubscriptionEntity $subscription_data */
?>
<div class="submitbox" id="submitpost">

    <div id="major-publishing-actions">

        <div id="delete-action">
            <?php if (ppressGET_var('ppress_subscription_action') == 'edit') : ?>
                <a class="submitdelete deletion pp-confirm-delete" href="<?= SubscriptionWPListTable::delete_subscription_url($subscription_id); ?>">
                    <?= esc_html__('Delete', 'wp-user-avatar') ?>
                </a>
            <?php endif; ?>
        </div>

        <div id="publishing-action">
            <input type="submit" name="ppress_save_subscription" class="button button-primary button-large" value="<?= esc_html__('Save Subscription', 'wp-user-avatar') ?>">
        </div>
        <div class="clear"></div>
    </div>

</div>