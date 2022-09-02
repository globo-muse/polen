<?php

namespace ProfilePress\Core\Membership\Models\Order;

class OrderMode
{
    const LIVE = 'live';
    const TEST = 'test';

    public static function get_all()
    {
        return apply_filters('ppress_order_modes', [
            self::LIVE => __('Live', 'wp-user-avatar'),
            self::TEST => __('Test', 'wp-user-avatar'),
        ]);
    }

    public static function get_label($status)
    {
        return self::get_all()[$status] ?? '';
    }
}