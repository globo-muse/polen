<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              rodolfoneto.com.br
 * @since             1.0.0
 * @package           Polen
 *
 * @wordpress-plugin
 * Plugin Name:       Polen
 * Plugin URI:        polen.app
 * Description:       Plugin da Polen, responsavel por todas as regras de negocio do Polen.app
 * Version:           1.0.0
 * Author:            Rodolfo
 * Author URI:        rodolfoneto.com.br
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       polen
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
define( 'POLEN_VERSION', '1.0.0' );

/*
 * Define plugin constants
 */
define( 'PLUGIN_POLEN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_POLEN_URL', plugin_dir_url( __FILE__ ) );
    
require_once plugin_dir_path( __FILE__ ) . './autoload.php';
require_once plugin_dir_path( __FILE__ ) . './vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . './includes/Polen.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-polen-activator.php
 */
function activate_polen()
{
	Polen\Includes\Polen_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-polen-deactivator.php
 */
function deactivate_polen()
{
	Polen\Includes\Polen_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_polen' );
register_deactivation_hook( __FILE__, 'deactivate_polen' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_polen()
{
//	new Polen\Admin\Polen_Admin_DisableMetabox( true );
	$plugin = new Polen\Includes\Polen();
	$plugin->run();
}
run_polen();