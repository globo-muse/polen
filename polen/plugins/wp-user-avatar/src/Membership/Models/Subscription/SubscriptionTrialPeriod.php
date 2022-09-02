<?php

namespace ProfilePress\Core\Membership\Models\Subscription;

class SubscriptionTrialPeriod
{
    const DISABLED = 'disabled';
    const THREE_DAYS = '3_day';
    const FIVE_DAYS = '5_day';
    const ONE_WEEK = '1_week';
    const TWO_WEEKS = '2_week';
    const THREE_WEEKS = '3_week';
    const ONE_MONTH = '1_month';

    public static function get_all()
    {
        return apply_filters('ppress_subscription_trial_periods', [
            self::DISABLED    => __('Disabled', 'wp-user-avatar'),
            self::THREE_DAYS  => __('3 Days', 'wp-user-avatar'),
            self::FIVE_DAYS   => __('5 Days', 'wp-user-avatar'),
            self::ONE_WEEK    => __('One Week', 'wp-user-avatar'),
            self::TWO_WEEKS   => __('Two Weeks', 'wp-user-avatar'),
            self::THREE_WEEKS => __('Three Weeks', 'wp-user-avatar'),
            self::ONE_MONTH   => __('One Month', 'wp-user-avatar')
        ]);
    }

    public static function get_label($status)
    {
        return self::get_all()[$status] ?? '';
    }
}