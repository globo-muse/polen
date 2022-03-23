<?php
/**
 * Fired during plugin activation
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

if ( ! class_exists( 'Hubwoo_Activator' ) ) {

	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since      1.0.0
	 * @package    hubspot-for-woocommerce
	 * @subpackage hubspot-for-woocommerce/includes
	 */
	class Hubwoo_Activator {

		/**
		 * Schedule the realtime sync for HubSpot WooCommerce Integration
		 *
		 * Create a log file in the WooCommerce defined log directory
		 * and use the same for the logging purpose of our plugin.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {

			update_option( 'hubwoo_plugin_activated_time', time() );

			fopen( WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log', 'a' );

			if ( ! as_next_scheduled_action( 'hubwoo_cron_schedule' ) ) {

				as_schedule_recurring_action( time(), 300, 'hubwoo_cron_schedule' );
			}

			if ( ! as_next_scheduled_action( 'hubwoo_deals_sync_check' ) ) {

				as_schedule_recurring_action( time(), 300, 'hubwoo_deals_sync_check' );
			}

			if ( ! as_next_scheduled_action( 'hubwoo_products_sync_check' ) ) {

				as_schedule_recurring_action( time(), 300, 'hubwoo_products_sync_check' );
			}
		}
	}
}
