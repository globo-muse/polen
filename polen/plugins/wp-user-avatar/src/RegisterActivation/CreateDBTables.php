<?php

namespace ProfilePress\Core\RegisterActivation;

use ProfilePress\Core\Base as CoreBase;

class CreateDBTables
{
    public static function make()
    {
        global $wpdb;

        $collate = '';
        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }

        $forms_table      = CoreBase::form_db_table();
        $forms_meta_table = CoreBase::form_meta_db_table();
        $meta_data_table  = CoreBase::meta_data_db_table();

        $sqls[] = "CREATE TABLE IF NOT EXISTS $forms_table (
                  id bigint(20) NOT NULL AUTO_INCREMENT,
                  name varchar(100) NOT NULL,
                  form_id bigint(20) NOT NULL,
                  form_type varchar(20) NOT NULL DEFAULT '',
                  builder_type varchar(20) NOT NULL DEFAULT '',
                  date datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
                  PRIMARY KEY (id),
                  UNIQUE KEY name (name),
                  KEY form_id (form_id)
				) $collate;
				";
        // max index length is 191 to avoid this error "Index column size too large. The maximum column size is 767 bytes."
        // @see wp_get_db_schema()
        $sqls[] = "CREATE TABLE IF NOT EXISTS $forms_meta_table (
                  meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  form_id bigint(20) NOT NULL,
                  form_type varchar(20) DEFAULT NULL,
                  meta_key varchar(255) DEFAULT NULL,
                  meta_value longtext,
                  PRIMARY KEY (meta_id),
                  KEY form_id (form_id),
                  KEY form_type (form_type),
                  KEY meta_key (meta_key(191))
				) $collate;
				";
        $sqls[] = "CREATE TABLE IF NOT EXISTS $meta_data_table (
                  id bigint(20) NOT NULL AUTO_INCREMENT,
                  meta_key varchar(50) DEFAULT NULL,
                  meta_value longtext,
                  flag varchar(20) DEFAULT NULL,
                  PRIMARY KEY (id),
                  KEY meta_key (meta_key),
                  KEY flag (flag)
				) $collate;
				";

        $sqls = apply_filters('ppress_create_database_tables', $sqls, $collate);

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        foreach ($sqls as $sql) {
            dbDelta($sql);
        }

        self::membership_db_make();
    }

    public static function membership_db_make()
    {
        global $wpdb;

        $collate = '';
        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }

        $subscriptions_table = CoreBase::subscriptions_db_table();
        $plans_table         = CoreBase::subscription_plans_db_table();
        $orders_table        = CoreBase::orders_db_table();
        $order_meta_table    = CoreBase::order_meta_db_table();
        $coupons_table       = CoreBase::coupons_db_table();
        $customers_table     = CoreBase::customers_db_table();

        $sqls[] = "CREATE TABLE IF NOT EXISTS $subscriptions_table (
                      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                      parent_order_id bigint(20) unsigned NOT NULL,
                      plan_id bigint(20) unsigned NOT NULL,
                      customer_id bigint(20) unsigned NOT NULL,
                      billing_frequency varchar(50) NOT NULL,
                      initial_amount decimal(26,8) NOT NULL,
                      initial_tax_rate mediumtext NOT NULL,
                      initial_tax mediumtext NOT NULL,
                      recurring_amount decimal(26,8) NOT NULL,
                      recurring_tax_rate mediumtext NOT NULL,
                      recurring_tax mediumtext NOT NULL,
                      total_payments bigint(20) unsigned NOT NULL DEFAULT '0',
                      trial_period varchar(50) DEFAULT NULL,
                      profile_id varchar(255) NOT NULL,
                      status varchar(20) NOT NULL,
                      notes longtext,
                      created_date datetime DEFAULT CURRENT_TIMESTAMP,
                      expiration_date datetime DEFAULT NULL,
                      PRIMARY KEY (id),
                      KEY plan_id (plan_id),
                      KEY customer_id (customer_id),
                      KEY status (status),
                      KEY parent_order_id (parent_order_id),
                      KEY customer_and_status (customer_id,status),
                      KEY profile_id (profile_id)
                    ) $collate;
				";

        $sqls[] = "CREATE TABLE IF NOT EXISTS $plans_table (
                      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                      name varchar(255) NOT NULL,
                      description text,
                      price decimal(26,8) NOT NULL DEFAULT '0.00000000',
                      billing_frequency varchar(50) NOT NULL,
                      subscription_length varchar(50) NOT NULL,
                      total_payments int(11) DEFAULT NULL,
                      signup_fee decimal(26,8) DEFAULT '0.00000000',
                      free_trial varchar(50) DEFAULT NULL,
                      status enum('true','false') NOT NULL DEFAULT 'true',
                      meta_data longtext,
                      PRIMARY KEY (id)
                    ) $collate;
				";
        $sqls[] = "CREATE TABLE IF NOT EXISTS $orders_table (
                      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                      order_key varchar(64) NOT NULL,
                      plan_id bigint(20) unsigned NOT NULL,
                      customer_id bigint(20) unsigned NOT NULL,
                      subscription_id bigint(20) unsigned NOT NULL DEFAULT '0',
                      order_type varchar(20) NOT NULL DEFAULT '',
                      transaction_id varchar(100) DEFAULT '',
                      payment_method varchar(100) NOT NULL DEFAULT '',
                      status varchar(20) NOT NULL,
                      coupon_code varchar(20) DEFAULT NULL,
                      subtotal decimal(26,8) NOT NULL DEFAULT '0.00000000',
                      tax decimal(26,8) NOT NULL DEFAULT '0.00000000',
                      tax_rate mediumtext NOT NULL,
                      discount decimal(26,8) NOT NULL DEFAULT '0.00000000',
                      total decimal(26,8) NOT NULL DEFAULT '0.00000000',
                      billing_address varchar(200) NOT NULL,
                      billing_city varchar(100) NOT NULL,
                      billing_state varchar(100) NOT NULL,
                      billing_postcode varchar(100) NOT NULL,
                      billing_country varchar(100) NOT NULL,
                      billing_phone varchar(100) NOT NULL,
                      mode enum('live','test') NOT NULL,
                      currency varchar(10) NOT NULL,
                      ip_address varchar(100) NOT NULL DEFAULT '',
                      date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      date_completed datetime DEFAULT NULL,
                      PRIMARY KEY (id),
                      UNIQUE KEY order_key (order_key),
                      KEY status (status),
                      KEY mode (mode),
                      KEY plan_id (plan_id),
                      KEY transaction_id (transaction_id),
                      KEY date (date_created),
                      KEY coupon_code (coupon_code),
                      KEY customer_id (customer_id)
                    ) $collate;
				";
        $sqls[] = "CREATE TABLE IF NOT EXISTS $order_meta_table (
                      meta_id bigint(20) NOT NULL AUTO_INCREMENT,
                      ppress_order_id bigint(20) NOT NULL,
                      meta_key varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                      meta_value longtext COLLATE utf8mb4_unicode_520_ci,
                      PRIMARY KEY (meta_id)
                    ) $collate;
				";
        $sqls[] = "CREATE TABLE IF NOT EXISTS $coupons_table (
                      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                      code varchar(50) NOT NULL,
                      description mediumtext,
                      coupon_application varchar(50) NOT NULL,
                      type varchar(50) NOT NULL,
                      amount mediumtext NOT NULL,
                      unit varchar(50) NOT NULL,
                      plan_ids mediumtext,
                      usage_limit mediumint(8) unsigned DEFAULT NULL,
                      status enum('true','false') NOT NULL DEFAULT 'true',
                      start_date date DEFAULT NULL,
                      end_date date DEFAULT NULL,
                      PRIMARY KEY (id),
                      UNIQUE KEY code (code),
                      KEY type (type),
                      KEY status (status)
                    ) $collate;
				";
        $sqls[] = "CREATE TABLE IF NOT EXISTS $customers_table (
                      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                      user_id bigint(20) unsigned DEFAULT NULL,
                      private_note longtext,
                      total_spend decimal(18,9) NOT NULL DEFAULT '0.000000000',
                      purchase_count bigint(20) unsigned NOT NULL DEFAULT '0',
                      last_login datetime DEFAULT NULL,
                      date_created datetime DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (id),
                      UNIQUE KEY user_id (user_id),
                      KEY date_created (date_created)
                    ) $collate;
				";

        $sqls[] = "CREATE TABLE {$wpdb->prefix}ppress_sessions (
		  session_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  session_key char(32) NOT NULL,
		  session_value LONGTEXT NOT NULL,
		  session_expiry BIGINT(20) UNSIGNED NOT NULL,
		  PRIMARY KEY  (session_key),
		  UNIQUE KEY session_id (session_id)
		) $collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        foreach ($sqls as $sql) {
            dbDelta($sql);
        }
    }
}