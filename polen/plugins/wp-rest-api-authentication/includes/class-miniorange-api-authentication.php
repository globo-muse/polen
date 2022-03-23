<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       miniorange
 * @since      1.0.0
 *
 * @package    Miniorange_api_authentication
 * @subpackage Miniorange_api_authentication/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Miniorange_api_authentication
 * @subpackage Miniorange_api_authentication/includes
 * @author     miniOrange
 */
class Miniorange_api_authentication {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Miniorange_api_authentication_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'MINIORANGE_API_AUTHENTICATION_VERSION' ) ) {
			$this->version = MINIORANGE_API_AUTHENTICATION_VERSION;
		} else {
			$this->version = '1.3.4';
		}
		$mo_rest_old_version = get_option('mo_api_authentication_current_plugin_version');
		if( $mo_rest_old_version && $mo_rest_old_version != '1.6.0' && $mo_rest_old_version != '1.6.1'){
			update_option('mo_rest_api_protect_migrate', 1);
		}

		update_option( "mo_api_authentication_current_plugin_version ", $this->version );
		$this->plugin_name = 'miniorange-api-authentication';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Miniorange_api_authentication_Loader. Orchestrates the hooks of the plugin.
	 * - Miniorange_api_authentication_i18n. Defines internationalization functionality.
	 * - Miniorange_API_Authentication_Admin. Defines all hooks for the admin area.
	 * - Miniorange_api_authentication_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-miniorange-api-authentication-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-miniorange-api-authentication-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-miniorange-api-authentication-admin.php';

		$this->loader = new Miniorange_api_authentication_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Miniorange_api_authentication_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Miniorange_api_authentication_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Miniorange_API_Authentication_Admin( $this->get_plugin_name(), $this->get_version() );
		// $this->loader->add_action( 'admin_enqueue_scripts',  $plugin_admin, 'plugin_settings_style' );
		// $this->loader->add_action( 'admin_enqueue_scripts',  $plugin_admin, 'plugin_settings_style' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'mo_api_authentication_config_settings');
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'mo_api_auth_admin_menu' );
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'mo_api_auth_initialize_api_flow' );
		$this->loader->add_action( 'wp_ajax_regenerate_token', $plugin_admin, 'regenerate_token' );
		$this->loader->add_action( 'wp_ajax_regenerate_client_credentials', $plugin_admin, 'regenerate_client_credentials' );
		$this->loader->add_action( 'wp_ajax_save_temporary_data', $plugin_admin, 'save_temporary_data' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Miniorange_api_authentication_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
