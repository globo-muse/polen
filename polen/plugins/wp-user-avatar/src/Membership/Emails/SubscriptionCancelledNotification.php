<?php

namespace ProfilePress\Core\Membership\Emails;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;

class SubscriptionCancelledNotification extends AbstractMembershipEmail
{
    const ID = 'subscription_cancelled_notification';

    public function __construct()
    {
        add_action('ppress_subscription_cancelled', [$this, 'dispatch_email'], 10, 2);
    }

    /**
     * @param SubscriptionEntity $subscription
     *
     * @return void
     */
    public function dispatch_email($subscription, $old_status)
    {
        if (ppress_get_setting(self::ID . '_email_enabled', 'on') !== 'on') return;

        if (in_array($old_status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIALLING], true)) {

            $placeholders_values = $this->get_subscription_placeholders_values($subscription);

            $subject = apply_filters('ppress_' . self::ID . '_email_subject', $this->parse_placeholders(
                ppress_get_setting(self::ID . '_email_subject', esc_html__('Your subscription has been cancelled.', 'wp-user-avatar'), true),
                $placeholders_values
            ), $subscription);

            $message = apply_filters('ppress_' . self::ID . '_email_content', $this->parse_placeholders(
                ppress_get_setting(self::ID . '_email_content', $this->get_subscription_cancelled_content(), true),
                $placeholders_values
            ), $subscription);

            ppress_send_email(CustomerFactory::fromId($subscription->customer_id)->get_email(), $subject, $message);
        }
    }
}