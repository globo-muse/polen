<?php

namespace ProfilePress\Core\Membership\Models\Order;

class OrderType
{
    const NEW_ORDER = 'new';
    const RENEWAL_ORDER = 'renewal';
    const UPGRADE = 'upgrade';
    const DOWNGRADE = 'downgrade';

    public static function get_all()
    {
        return apply_filters('ppress_order_types', [
            self::NEW_ORDER     => __('New Order', 'wp-user-avatar'),
            self::RENEWAL_ORDER => __('Renewal', 'wp-user-avatar'),
            self::UPGRADE       => __('Upgrade', 'wp-user-avatar'),
            self::DOWNGRADE     => __('Downgrade', 'wp-user-avatar')
        ]);
    }

    public static function get_label($status)
    {
        return self::get_all()[$status] ?? '';
    }
}