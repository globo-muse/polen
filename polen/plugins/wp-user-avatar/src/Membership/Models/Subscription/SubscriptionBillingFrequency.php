<?php

namespace ProfilePress\Core\Membership\Models\Subscription;

class SubscriptionBillingFrequency
{
    const MONTHLY = 'monthly';
    const WEEKLY = 'weekly';
    const DAILY = 'daily';
    const QUARTERLY = '3_month';
    const EVERY_6_MONTHS = '6_month';
    const YEARLY = '1_year';
    const ONE_TIME = 'lifetime';
    const LIFETIME = 'lifetime';

    public static function get_all()
    {
        return apply_filters('ppress_subscription_billing_frequency', [
            self::MONTHLY        => __('Monthly', 'wp-user-avatar'),
            self::WEEKLY         => __('Weekly', 'wp-user-avatar'),
            self::DAILY          => __('Daily', 'wp-user-avatar'),
            self::QUARTERLY      => __('Quarterly (every 3 months)', 'wp-user-avatar'),
            self::EVERY_6_MONTHS => __('Every 6 months', 'wp-user-avatar'),
            self::YEARLY         => __('Yearly', 'wp-user-avatar'),
            self::ONE_TIME       => __('One-time purchase', 'wp-user-avatar')
        ]);
    }

    public static function get_label($status)
    {
        return self::get_all()[$status] ?? '';
    }
}