<?php

namespace ProfilePress\Core\Membership\Emails;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePressVendor\Carbon\CarbonImmutable;

class SubscriptionAfterExpiredNotification extends AbstractMembershipEmail
{
    const ID = 'subscription_after_expired_reminder';

    public function __construct()
    {
        add_action('ppress_daily_recurring_job', [$this, 'dispatch_email']);
    }

    /**
     * @return void
     */
    public function dispatch_email()
    {
        if (ppress_get_setting(self::ID . '_email_enabled', 'on') !== 'on') return;

        $reminder_days = (int)apply_filters('ppress_' . self::ID . '_reminder_days',
            ppress_get_setting(self::ID . '_reminder_days', '2', true)
        );

        $subDate = CarbonImmutable::now(wp_timezone())->subDays($reminder_days);

        $subscriptions = SubscriptionRepository::init()->retrieveBy([
            'status'      => [SubscriptionStatus::EXPIRED],
            'limit'       => 0,
            'date_column' => 'expiration_date',
            'start_date'  => $subDate->startOfDay()->utc()->toDateTimeString(),
            'end_date'    => $subDate->endOfDay()->utc()->toDateTimeString()
        ]);

        if ( ! is_array($subscriptions) || empty($subscriptions)) return;

        foreach ($subscriptions as $subscription) {

            $placeholders_values = $this->get_subscription_placeholders_values($subscription);

            $subject = apply_filters('ppress_' . self::ID . '_email_subject', $this->parse_placeholders(
                ppress_get_setting(self::ID . '_email_subject', esc_html__('Your subscription has expired.', 'wp-user-avatar'), true),
                $placeholders_values
            ), $subscription);

            $message = apply_filters('ppress_' . self::ID . '_email_content', $this->parse_placeholders(
                ppress_get_setting(self::ID . '_email_content', $this->get_subscription_expired_content(), true),
                $placeholders_values
            ), $subscription);

            ppress_send_email(CustomerFactory::fromId($subscription->customer_id)->get_email(), $subject, $message);
        }
    }
}