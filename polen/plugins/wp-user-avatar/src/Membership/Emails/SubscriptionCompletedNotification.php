<?php

namespace ProfilePress\Core\Membership\Emails;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;

class SubscriptionCompletedNotification extends AbstractMembershipEmail
{
    const ID = 'subscription_completed_notification';

    public function __construct()
    {
        add_action('ppress_subscription_completed', [$this, 'dispatch_email']);
    }

    /**
     * @param SubscriptionEntity $subscription
     *
     * @return void
     */
    public function dispatch_email($subscription)
    {
        if (ppress_get_setting(self::ID . '_email_enabled', 'on') !== 'on') return;

        $placeholders_values = $this->get_subscription_placeholders_values($subscription);

        $subject = apply_filters('ppress_' . self::ID . '_email_subject', $this->parse_placeholders(
            ppress_get_setting(self::ID . '_email_subject', esc_html__('Your subscription is now complete.', 'wp-user-avatar'), true),
            $placeholders_values
        ), $subscription);

        $message = apply_filters('ppress_' . self::ID . '_email_content', $this->parse_placeholders(
            ppress_get_setting(self::ID . '_email_content', $this->get_subscription_completed_content(), true),
            $placeholders_values
        ), $subscription);

        ppress_send_email(CustomerFactory::fromId($subscription->customer_id)->get_email(), $subject, $message);
    }
}