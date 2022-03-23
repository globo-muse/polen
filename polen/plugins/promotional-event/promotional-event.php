<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://polen.me
 * @since             1.0.0
 * @package           Promotional_Event
 *
 * @wordpress-plugin
 * Plugin Name:       Evento Promocional 
 * Plugin URI:        #
 * Description:       Plugin para criação de cupons promocionais
 * Version:           1.0.0
 * Author:            Polen.me
 * Author URI:        https://polen.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       promotional-event
 * Domain Path:       /languages
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
define( 'PROMOTIONAL_EVENT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-promotional-event-activator.php
 */
function activate_promotional_event() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-promotional-event-activator.php';
	Promotional_Event_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-promotional-event-deactivator.php
 */
function deactivate_promotional_event() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-promotional-event-deactivator.php';
	Promotional_Event_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_promotional_event' );
register_deactivation_hook( __FILE__, 'deactivate_promotional_event' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-promotional-event.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_promotional_event() {

	$plugin = new Promotional_Event();
	$plugin->run();

}
run_promotional_event();
