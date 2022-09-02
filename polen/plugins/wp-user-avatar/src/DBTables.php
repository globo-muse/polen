<?php

namespace ProfilePress\Core;

class DBTables
{
    public static function form_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_forms';
    }

    public static function form_meta_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_formsmeta';
    }

    public static function passwordless_login_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_passwordless';
    }

    public static function meta_data_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_meta_data';
    }

    public static function profile_fields_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_profile_fields';
    }

    public static function subscription_plans_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_plans';
    }

    public static function customers_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_customers';
    }

    public static function orders_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_orders';
    }

    public static function order_meta_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_ordermeta';
    }

    public static function subscriptions_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_subscriptions';
    }

    public static function coupons_db_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'ppress_coupons';
    }
}