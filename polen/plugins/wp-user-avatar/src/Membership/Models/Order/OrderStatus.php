<?php

namespace ProfilePress\Core\Membership\Models\Order;

class OrderStatus
{
    const COMPLETED = 'completed';
    const PENDING = 'pending';
    const REFUNDED = 'refunded';
    const FAILED = 'failed';

    public static function get_all()
    {
        return apply_filters('ppress_order_statuses', [
            self::PENDING   => __('Pending', 'wp-user-avatar'),
            self::COMPLETED => __('Completed', 'wp-user-avatar'),
            self::REFUNDED  => __('Refunded', 'wp-user-avatar'),
            self::FAILED    => __('Failed', 'wp-user-avatar')
        ]);
    }

    public static function get_label($status)
    {
        return self::get_all()[$status] ?? '';
    }
}