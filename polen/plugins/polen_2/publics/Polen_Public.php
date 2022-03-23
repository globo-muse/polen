<?php

namespace Polen\Publics;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       rodolfoneto.com.br
 * @since      1.0.0
 *
 * @package    Polen
 * @subpackage Polen/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Polen
 * @subpackage Polen/public
 * @author     Rodolfo <rodolfoneto@gmail.com>
 */
use Polen\Includes\Polen_Talent;


class Polen_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Polen_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Polen_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/polen-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Polen_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Polen_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if( is_account_page() && is_user_logged_in() ){
			$polen_talent = new Polen_Talent();
			$current_user = wp_get_current_user();
			if ($polen_talent->is_user_talent($current_user)) {
				wp_register_script( 'polen-item-script', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/polen-public.js', array( 'jquery' ), time(), true );
			}
		}	
		wp_localize_script( 'polen-item-script',	'polen_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script( 'polen-item-script' );
	}
        
        
        /**
         * 
         * @return string
         */
        public function get_path_public_patials()
        {
            return dirname( __FILE__ ) . '/partials/';
        }

        /**
         * 
         * @return string
         */
        static public function static_get_path_public_patials()
        {
            return dirname( __FILE__ ) . '/partials/';
        }

                
        
        /**
         * 
         * @return string
         */
        public function get_url_public_js()
        {
            return plugin_dir_url( __FILE__ ) . 'js/';
        }
}
