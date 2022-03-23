<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

if ( ! class_exists( 'Hubwoo_Deactivator' ) ) {
	/**
	 * Fired during plugin de activation.
	 *
	 * This class defines all code necessary to run during the plugin's de activation.
	 *
	 * @since      1.0.0
	 * @package    hubspot-for-woocommerce
	 * @subpackage hubspot-for-woocommerce/includes
	 */
	class Hubwoo_Deactivator {

		/**
		 * Clear log file saved for HubSpot API call logging. (use period)
		 *
		 * @since    1.0.0
		 */
		public static function deactivate() {

			as_unschedule_action( 'hubwoo_cron_schedule' );
			as_unschedule_action( 'hubwoo_deals_sync_check' );
			as_unschedule_action( 'hubwoo_products_sync_check' );
			as_unschedule_action( 'hubwoo_deal_update_schedule' );
			as_unschedule_action( 'hubwoo_products_status_background' );
			as_unschedule_action( 'hubwoo_products_sync_background' );
		}
	}
}
