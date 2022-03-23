<?php
/**
 * Plugin Name:       WordPress REST API Authentication
 * Plugin URI:        wp-rest-api-authentication
 * Description:       WordPress REST API Authentication secures rest API access for unauthorized users using OAuth 2.0, Basic Auth, JWT, API Key. Also reduces potential attack factors to the respective site.
 * Version:           2.1.0
 * Author:            miniOrange
 * Author URI:        https://www.miniorange.com
 * License:           MIT/Expat
 * License URI:       https://docs.miniorange.com/mit-license
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MINIORANGE_API_AUTHENTICATION_VERSION', '2.1.0' );
// require_once plugin_dir_path( __FILE__ ) . 'admin/partials/support/class-mo-api-authentication-feedback.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-miniorange-api-authentication-activator.php
 */

function remove_footer_admin () 
{
    echo '';
}
 
if(isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'mo_api_authentication_settings' ){
	add_filter('admin_footer_text', 'remove_footer_admin');
}

function mo_api_auth_activate_miniorange_api_authentication() {
	mo_rest_api_set_cron_job();
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-miniorange-api-authentication-activator.php';
	Miniorange_api_authentication_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-miniorange-api-authentication-deactivator.php
 */
function mo_api_auth_deactivate_miniorange_api_authentication() {
	wp_clear_scheduled_hook( 'mo_api_display_the_popup' );
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-miniorange-api-authentication-deactivator.php';
	Miniorange_api_authentication_Deactivator::mo_api_authentication_deactivate();
}

add_action( 'admin_enqueue_scripts', 'mo_api_auth_plugin_settings_style' );
add_action( 'admin_enqueue_scripts', 'mo_api_auth_plugin_settings_style' );
register_activation_hook( __FILE__, 'mo_api_auth_activate_miniorange_api_authentication' );
register_deactivation_hook( __FILE__, 'mo_api_auth_deactivate_miniorange_api_authentication' );
remove_action( 'admin_notices', 'mo_api_auth_success_message' );
remove_action( 'admin_notices', 'mo_api_auth_error_message' );
add_action( 'admin_footer', 'mo_api_authentication_feedback_request');
add_action( 'mo_api_display_the_popup', 'mo_rest_api_scheduled_task' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-miniorange-api-authentication.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function mo_api_auth_plugin_settings_style() {
	wp_enqueue_style( 'mo_api_authentication_admin_settings_style', plugins_url( 'css/style_settings.css', __FILE__ ) );
	wp_enqueue_style( 'mo_api_authentication_admin_settings_phone_style', plugins_url( 'css/phone.css', __FILE__ ) );
	// wp_enqueue_style( 'mo_api_authentication_admin_settings_datatable', plugins_url( 'css/jquery.dataTables.min.css', __FILE__ ) );
}

function mo_api_authentication_feedback_request() {
	Mo_API_Authentication_Admin_Feedback::mo_api_authentication_display_feedback_form();
}

function run_miniorange_api_authentication() {

	$plugin = new Miniorange_api_authentication();
	$plugin->run();

}
run_miniorange_api_authentication();

function mo_api_authentication_is_customer_registered() {
	$email 			= get_option('mo_api_authentication_admin_email');
	// $phone 			= get_option('mo_api_authentication_admin_phone');
	$customerKey 	= get_option('mo_api_authentication_admin_customer_key');
	// if( ! $email || ! $phone || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
	if( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
	
		return 0;
	} else {
		return 1;
	}
}

function mo_api_auth_success_message() {
	$class = "error";
	$message = get_option('mo_api_auth_message');
	echo "<div class='" . $class . "'> <p>" . $message . "</p></div>";
}

function mo_api_auth_error_message() {
	$class = "updated";
	$message = get_option('mo_api_auth_message');
	echo "<div class='" . $class . "'><p>" . $message . "</p></div>";
}

function mo_api_auth_show_success_message() {
	remove_action( 'admin_notices', 'mo_api_auth_success_message' );
	add_action( 'admin_notices', 'mo_api_auth_error_message' );
}

function mo_api_auth_show_error_message() {
	remove_action( 'admin_notices', 'mo_api_auth_error_message' );
	add_action( 'admin_notices', 'mo_api_auth_success_message' );
}

function mo_rest_api_set_cron_job()
	{
		
		//add_filter( 'cron_schedules', array($this,'add_cron_interval'));// uncomment this for custom intervals
		
		if (!wp_next_scheduled('mo_api_display_the_popup')) {
			
			//$custom_interval=apply_filters('cron_schedules',array('three_minutes'));//uncomment this for custom interval		
      		wp_schedule_event( time() + 60, 'daily', 'mo_api_display_the_popup' );// update timestamp and name according to interval
 		}

	}

	function mo_rest_api_scheduled_task() { 
		update_option('mo_rest_api_show_popup', 1);
	}