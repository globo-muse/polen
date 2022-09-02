<?php

namespace ProfilePress\Core\Membership\Models\Subscription;

class SubscriptionStatus
{
    const ACTIVE = 'active';
    const PENDING = 'pending';
    const CANCELLED = 'cancelled';
    const EXPIRED = 'expired';
    const TRIALLING = 'trialling';
    const COMPLETED = 'completed';

    public static function get_all()
    {
        return apply_filters('ppress_subscription_statuses', [
            self::ACTIVE    => __('Active', 'wp-user-avatar'),
            self::PENDING   => __('Pending', 'wp-user-avatar'),
            self::EXPIRED   => __('Expired', 'wp-user-avatar'),
            self::COMPLETED => __('Completed', 'wp-user-avatar'),
            self::TRIALLING => __('Trialling', 'wp-user-avatar'),
            self::CANCELLED => __('Cancelled', 'wp-user-avatar')
        ]);
    }

    public static function get_label($status)
    {
        return self::get_all()[$status] ?? '';
    }
}