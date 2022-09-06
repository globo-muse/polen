<?php

namespace ProfilePress\Core;

use ProfilePress\Core\Classes\ExtensionManager;

class DBUpdates
{
    public static $instance;

    const DB_VER = 3;

    public function init_options()
    {
        get_option('ppress_db_ver', 0);
    }

    public function maybe_update()
    {
        $this->init_options();

        if (get_option('ppress_db_ver') >= self::DB_VER) {
            return;
        }

        // update plugin
        $this->update();
    }

    public function update()
    {
        // no PHP timeout for running updates
        set_time_limit(0);

        // this is the current database schema version number
        $current_db_ver = get_option('ppress_db_ver', 0);

        // this is the target version that we need to reach
        $target_db_ver = self::DB_VER;

        // run update routines one by one until the current version number
        // reaches the target version number
        while ($current_db_ver < $target_db_ver) {
            // increment the current db_ver by one
            $current_db_ver++;

            // each db version will require a separate update function
            $update_method = "update_routine_{$current_db_ver}";

            if (method_exists($this, $update_method)) {
                call_user_func(array($this, $update_method));
            }

            // update the option in the database, so that this process can always
            // pick up where it left off
            update_option('ppress_db_ver', $current_db_ver);
        }
    }

    public function update_routine_1()
    {
        $a                           = get_option(ExtensionManager::DB_OPTION_NAME);
        $a[ExtensionManager::PAYPAL] = 'true';
        update_option(ExtensionManager::DB_OPTION_NAME, $a);
    }

    public function update_routine_2()
    {
        global $wpdb;

        $table1 = DBTables::orders_db_table();
        $table2 = DBTables::subscriptions_db_table();
        $table3 = DBTables::customers_db_table();

        $wpdb->query("ALTER TABLE $table1 CHANGE date_created date_created datetime NOT NULL;");
        $wpdb->query("ALTER TABLE $table2 CHANGE created_date created_date datetime NOT NULL;");
        $wpdb->query("ALTER TABLE $table3 CHANGE date_created date_created datetime NOT NULL;");
    }

    public function update_routine_3()
    {
        global $wpdb;

        $table  = DBTables::coupons_db_table();
        $table2 = DBTables::subscription_plans_db_table();

        $wpdb->query("ALTER TABLE $table CHANGE type coupon_type varchar(50) NULL;");
        $wpdb->query("ALTER TABLE $table2 ADD COLUMN order_note text NULL AFTER description;");
    }

    public static function get_instance()
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}