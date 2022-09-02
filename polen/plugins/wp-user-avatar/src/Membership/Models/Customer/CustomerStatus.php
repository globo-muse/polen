<?php

namespace ProfilePress\Core\Membership\Models\Customer;

class CustomerStatus
{
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';

    public static function get_all()
    {
        return apply_filters('ppress_order_statuses', [
            self::ACTIVE   => __('Active', 'wp-user-avatar'),
            self::INACTIVE => __('Inactive', 'wp-user-avatar')
        ]);
    }

    public static function get_label($status)
    {
        return self::get_all()[$status] ?? '';
    }
}