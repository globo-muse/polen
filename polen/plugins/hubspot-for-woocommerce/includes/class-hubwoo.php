<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

if ( ! class_exists( 'Hubwoo' ) ) {

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
	 * @package    hubspot-for-woocommerce
	 * @subpackage hubspot-for-woocommerce/includes
	 */
	class Hubwoo {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @var      Hubwoo_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
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

			if ( defined( 'HUBWOO_VERSION' ) ) {

				$this->version = HUBWOO_VERSION;
			} else {

				$this->version = '1.0.5';
			}

			$this->plugin_name = 'hubspot-for-woocommerce';
			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Hubwoo_Loader. Orchestrates the hooks of the plugin.
		 * - Hubwoo_I18n. Defines internationalization functionality.
		 * - Hubwoo_Admin. Defines all hooks for the admin area.
		 * - Hubwoo_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-i18n.php';

			/**
			 * The class responsible for handling background data sync from WooCommerce to
			 * HubSpot.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoodatasync.php';

			/**
			 * The class for Managing Enums.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-hubwooenum.php';

			/**
			 * The class for Error Handling.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwooerrorhandling.php';

			/**
			 * The class responsible for plugin constants.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwooconst.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-hubwoo-admin.php';

			/**
			 * The class responsible for defining all actions that occur in the public area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-hubwoo-public.php';

			$this->loader = new Hubwoo_Loader();

			/**
			 * The class responsible for all api actions with hubspot.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwooconnectionmananager.php';

			/**
			 * The class contains all the information related to customer groups and properties.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoocontactproperties.php';

			/**
			 * The class contains are readymade contact details to send it to
			 * hubspot.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoocustomer.php';

			/**
			 * The class responsible for property values.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoopropertycallbacks.php';

			/**
			 * The class responsible for handling ajax requests.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-ajax-handler.php';

			/**
			 * The class responsible for rfm configuration settings.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-rfm-configuration.php';

			/**
			 * The class responsible for manging guest orders.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwooguestordersmanager.php';

			/**
			 * The class responsible for defining all upsert settings and ecomm mappings for deals and line items
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwooecommproperties.php';

			/**
			 * The class responsible for defining functions related to get values/date for ecomm objects
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwooecommobject.php';

			/**
			 * The class responsible for defining functions related to get values/date for ecomm objects
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwooobjectproperties.php';

			/**
			 * The class responsible for defining functions to return values as per ecomm settings upserted
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwooecommpropertycallbacks.php';
		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Hubwoo_I18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 */
		private function set_locale() {

			$plugin_i18n = new Hubwoo_I18n();

			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 */
		private function define_admin_hooks() {

			$plugin_admin = new Hubwoo_Admin( $this->get_plugin_name(), $this->get_version() );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'hubwoo_redirect_from_hubspot' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'hubwoo_pro_add_privacy_message' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'hubwoo_get_plugin_log' );

			$this->loader->add_action( 'admin_init', $plugin_admin, 'hubwoo_check_property_value' );
			$this->loader->add_action( 'admin_notices', $plugin_admin, 'hubwoo_cron_notification' );
			// hubspot deal hooks.
			if ( 'yes' == get_option( 'hubwoo_ecomm_deal_enable', 'yes' ) ) {

				$this->loader->add_filter( 'manage_edit-shop_order_columns', $plugin_admin, 'hubwoo_order_cols', 11 );
				$this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin, 'hubwoo_order_cols_value', 10, 2 );
			}

			// hubspot abandon carts.
			$this->loader->add_filter( 'hubwoo_users', $plugin_admin, 'hubwoo_abncart_users' );
			$this->loader->add_filter( 'hubwoo_contact_modified_fields', $plugin_admin, 'hubwoo_abncart_contact_properties', 10, 2 );
			$this->loader->add_filter( 'hubwoo_pro_track_guest_cart', $plugin_admin, 'hubwoo_abncart_process_guest_data', 10, 2 );
			if ( get_option( 'hubwoo_abncart_delete_old_data', 'off' ) == 'on' ) {

				$this->loader->add_action( 'huwoo_abncart_clear_old_cart', $plugin_admin, 'huwoo_abncart_clear_old_cart' );
			}

			if ( get_option( 'hubwoo_checkout_form_created', 'no' ) == 'yes' ) {

				$this->loader->add_action( 'woocommerce_checkout_process', $plugin_admin, 'hubwoo_submit_checkout_form' );
			}

			if ( $this->is_plugin_enable() == 'yes' ) {

				if ( $this->is_setup_completed() ) {

					$this->loader->add_action( 'hubwoo_cron_schedule', $plugin_admin, 'hubwoo_cron_schedule' );
					$this->loader->add_filter( 'hubwoo_unset_workflow_properties', $plugin_admin, 'hubwoo_reset_workflow_properties' );
					$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'hubwoo_update_order_changes' );
					$this->loader->add_action( 'set_user_role', $plugin_admin, 'hubwoo_add_user_toupdate', 10 );
				}

				if ( $this->hubwoo_subs_active() ) {

					$this->loader->add_filter( 'hubwoo_contact_groups', $plugin_admin, 'hubwoo_subs_groups' );
					$this->loader->add_filter( 'hubwoo_active_groups', $plugin_admin, 'hubwoo_active_subs_groups' );
				}

				// HubSpot Deals.
				if ( false === wp_cache_get( 'hubwoo_product_update_lock' ) ) {
					wp_cache_set( 'hubwoo_product_update_lock', true );
					$this->loader->add_action( 'save_post', $plugin_admin, 'hubwoo_ecomm_update_product', 10, 2 );
				}
				if ( 'yes' == get_option( 'hubwoo_ecomm_deal_enable', 'yes' ) ) {

					$this->loader->add_action( 'hubwoo_ecomm_deal_upsert', $plugin_admin, 'hubwoo_ecomm_deal_upsert' );
					$this->loader->add_action( 'hubwoo_deals_sync_check', $plugin_admin, 'hubwoo_deals_sync_check' );
					$this->loader->add_action( 'hubwoo_products_sync_check', $plugin_admin, 'hubwoo_products_sync_check' );
					$this->loader->add_action( 'hubwoo_deals_sync_background', $plugin_admin, 'hubwoo_deals_sync_background', 10, 2 );
					$this->loader->add_action( 'hubwoo_products_sync_background', $plugin_admin, 'hubwoo_products_sync_background' );
					$this->loader->add_action( 'hubwoo_products_status_background', $plugin_admin, 'hubwoo_products_status_background' );
					$this->loader->add_action( 'hubwoo_deal_update_schedule', $plugin_admin, 'hubwoo_deal_update_schedule' );
				}

				$this->loader->add_action( 'hubwoo_contacts_sync_background', $plugin_admin, 'hubwoo_contacts_sync_background' );
				$this->loader->add_action( 'hubwoo_update_contacts_vid', $plugin_admin, 'hubwoo_update_contacts_vid' );
			}
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 */
		private function define_public_hooks() {

			$plugin_public = new Hubwoo_Public( $this->get_plugin_name(), $this->get_version() );

			if ( $this->is_plugin_enable() == 'yes' ) {

				$this->loader->add_action( 'profile_update', $plugin_public, 'hubwoo_woocommerce_save_account_details' );
				$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'hubwoo_add_hs_script' );
				$this->loader->add_action( 'user_register', $plugin_public, 'hubwoo_woocommerce_save_account_details' );
				$this->loader->add_action( 'woocommerce_customer_save_address', $plugin_public, 'hubwoo_woocommerce_save_account_details' );
				$this->loader->add_action( 'woocommerce_checkout_update_user_meta', $plugin_public, 'hubwoo_woocommerce_save_account_details' );
				if ( 'yes' == get_option( 'hubwoo_pro_guest_sync_enable', 'yes' ) ) {
					$this->loader->add_action( 'woocommerce_update_order', $plugin_public, 'hubwoo_pro_woocommerce_guest_orders' );
				}
				if ( 'yes' == get_option( 'hubwoo_checkout_optin_enable', 'no' ) ) {
					$this->loader->add_action( 'woocommerce_after_checkout_billing_form', $plugin_public, 'hubwoo_pro_checkout_field' );
					$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_public, 'hubwoo_pro_process_checkout_optin' );
				}
				if ( 'yes' == get_option( 'hubwoo_registeration_optin_enable', 'no' ) ) {
					$this->loader->add_action( 'woocommerce_register_form', $plugin_public, 'hubwoo_pro_register_field' );
					$this->loader->add_action( 'woocommerce_created_customer', $plugin_public, 'hubwoo_save_register_optin' );
				}
				$this->loader->add_action( 'wp_loaded', $plugin_public, 'hubwoo_add_abncart_products', 10 );

				$subs_enable = get_option( 'hubwoo_subs_settings_enable', 'yes' );

				if ( 'yes' == $subs_enable && $this->hubwoo_subs_active() ) {

					$this->loader->add_action( 'woocommerce_renewal_order_payment_complete', $plugin_public, 'hubwoo_pro_save_renewal_orders' );
					$this->loader->add_action( 'woocommerce_scheduled_subscription_payment', $plugin_public, 'hubwoo_pro_save_renewal_orders' );
					$this->loader->add_action( 'woocommerce_subscription_renewal_payment_complete', $plugin_public, 'hubwoo_pro_update_subs_changes' );
					$this->loader->add_action( 'woocommerce_subscription_payment_failed', $plugin_public, 'hubwoo_pro_update_subs_changes' );
					$this->loader->add_action( 'woocommerce_subscription_renewal_payment_failed', $plugin_public, 'hubwoo_pro_update_subs_changes' );
					$this->loader->add_action( 'woocommerce_subscription_payment_complete', $plugin_public, 'hubwoo_pro_update_subs_changes' );
					$this->loader->add_action( 'woocommerce_subscription_status_updated', $plugin_public, 'hubwoo_pro_update_subs_changes' );
					$this->loader->add_action( 'woocommerce_customer_changed_subscription_to_cancelled', $plugin_public, 'hubwoo_save_changes_in_subs' );
					$this->loader->add_action( 'woocommerce_customer_changed_subscription_to_active', $plugin_public, 'hubwoo_save_changes_in_subs' );
					$this->loader->add_action( 'woocommerce_customer_changed_subscription_to_on-hold', $plugin_public, 'hubwoo_save_changes_in_subs' );
					$this->loader->add_action( 'init', $plugin_public, 'hubwoo_subscription_switch' );
				}
				// HubSpot deals hooks.
				if ( 'yes' == get_option( 'hubwoo_ecomm_deal_enable', 'yes' ) && 'yes' == get_option( 'hubwoo_ecomm_setup_completed', 'no' ) ) {

					if ( false === wp_cache_get( 'hubwoo_order_update_lock' ) ) {
						wp_cache_set( 'hubwoo_order_update_lock', true );
						$this->loader->add_action( 'woocommerce_update_order', $plugin_public, 'hubwoo_ecomm_deal_on_new_order' );
					}
				}

				// Update existing order on admin.
				$this->loader->add_action( 'woocommerce_update_order', $plugin_public, 'hubwoo_ecomm_deal_update_order' );

				// HubSpot Abandon Carts.
				if ( get_option( 'hubwoo_abncart_enable_addon', 'yes' ) == 'yes' ) {

					if ( get_option( 'hubwoo_abncart_guest_cart', 'yes' ) == 'yes' ) {

						$this->loader->add_action( 'init', $plugin_public, 'hubwoo_abncart_start_session', 10 );
						$this->loader->add_action( 'template_redirect', $plugin_public, 'hubwoo_track_cart_for_formuser' );
						$this->loader->add_action( 'wp_ajax_nopriv_hubwoo_save_guest_user_cart', $plugin_public, 'hubwoo_save_guest_user_cart' );
						$this->loader->add_action( 'woocommerce_after_checkout_billing_form', $plugin_public, 'hubwoo_track_email_for_guest_users', 10 );
						$this->loader->add_action( 'wp_ajax_nopriv_get_order_detail', $plugin_public, 'get_order_detail' );
						$this->loader->add_action( 'woocommerce_after_checkout_billing_form', $plugin_public, 'get_email_checkout_page' );
						$this->loader->add_action( 'woocommerce_new_order', $plugin_public, 'hubwoo_abncart_woocommerce_new_orders' );
						$this->loader->add_action( 'woocommerce_cart_updated', $plugin_public, 'hubwoo_abncart_track_guest_cart', 99, 0 );
						$this->loader->add_action( 'user_register', $plugin_public, 'hubwoo_abncart_user_registeration' );
						$this->loader->add_action( 'wp_logout', $plugin_public, 'hubwoo_clear_session' );
					}
					$this->loader->add_filter( 'woocommerce_update_cart_action_cart_updated', $plugin_public, 'hubwoo_guest_cart_updated' );
					$this->loader->add_action( 'woocommerce_add_to_cart', $plugin_public, 'hubwoo_abncart_woocommerce_add_to_cart', 20, 0 );
				}
			}
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
		 * @return    Hubwoo_Loader    Orchestrates the hooks of the plugin.
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

		/**
		 * Predefined default hubwoo tabs.
		 *
		 * @since     1.0.0
		 */
		public function hubwoo_default_tabs() {

			$default_tabs = array();

			$common_dependency = array( 'is_oauth_success', 'is_valid_client_ids_stored', 'is_field_setup_completed' );

			$default_tabs['hubwoo-overview'] = array(
				'name'       => __( 'Dashboard', 'hubspot-for-woocommerce' ),
				'dependency' => array(),
				'title'      => esc_html__( 'Integrate your WooCommerce store with HubSpot', 'hubspot-for-woocommerce' ),
			);

			$default_tabs['hubwoo-sync-contacts'] = array(
				'name'       => __( 'Contacts', 'hubspot-for-woocommerce' ),
				'dependency' => $common_dependency,
				'title'      => esc_html__( 'Sync all your woocommerce data to HubSpot', 'hubspot-for-woocommerce' ),
			);

			$default_tabs['hubwoo-deals'] = array(
				'name'       => __( 'Deals', 'hubspot-for-woocommerce' ),
				'dependency' => $common_dependency,
				'title'      => esc_html__( 'Sync All of your woocommerce orders as HubSpot Deals', 'hubspot-for-woocommerce' ),
			);

			$default_tabs['hubwoo-abncart'] = array(
				'name'       => __( 'Abandoned Carts', 'hubspot-for-woocommerce' ),
				'dependency' => $common_dependency,
				'title'      => esc_html__( 'Sync all of the cart abandoners on your website', 'hubspot-for-woocommerce' ),
			);

			$default_tabs['hubwoo-automation'] = array(
				'name'       => __( 'Automation', 'hubspot-for-woocommerce' ),
				'dependency' => $common_dependency,
				'title'      => esc_html__( 'Create Workflows to Track ROI and Retrieve Abandoned Carts', 'hubspot-for-woocommerce' ),
			);

			$default_tabs['hubwoo-add-ons']          = array(
				'name'       => __( 'Add Ons', 'hubspot-for-woocommerce' ),
				'dependency' => '',
				'title'      => esc_html__( 'Add-ons for the HubSpot Integrations', 'hubspot-for-woocommerce' ),
			);
			$default_tabs['hubwoo-general-settings'] = array(
				'name'       => __( 'Settings', 'hubspot-for-woocommerce' ),
				'dependency' => array( 'is_oauth_success', 'is_valid_client_ids_stored' ),
				'title'      => esc_html__( 'General And Advanced Settings', 'hubspot-for-woocommerce' ),
			);

			$default_tabs = apply_filters( 'hubwoo_navigation_tabs', $default_tabs );

			$default_tabs['hubwoo-support'] = array(
				'name'       => __( 'Support', 'hubspot-for-woocommerce' ),
				'dependency' => array( 'is_oauth_success', 'is_valid_client_ids_stored' ),
				'title'      => esc_html__( 'Support', 'hubspot-for-woocommerce' ),
			);

			return $default_tabs;
		}

		/**
		 * Checking dependencies for tabs.
		 *
		 * @since     1.0.0
		 * @param array $dependency list of dependencies of function.
		 */
		public function check_dependencies( $dependency = array() ) {

			$flag = true;

			global $hubwoo;

			if ( count( $dependency ) ) {

				foreach ( $dependency as $single_dependency ) {

					if ( ! empty( $hubwoo->$single_dependency() ) ) {
						$flag = $flag & $hubwoo->$single_dependency();
					}
				}
			}

			return $flag;
		}

		/**
		 * Get started with setup.
		 *
		 * @since     1.0.0
		 */
		public static function hubwoo_pro_get_started() {

			$last_version = self::hubwoo_pro_last_version();

			if ( HUBWOO_VERSION != $last_version ) {

				return true;
			} elseif ( HUBWOO_VERSION != $last_version && ! get_option( 'hubwoo_pro_get_started', false ) ) {

				return false;
			} elseif ( HUBWOO_VERSION == $last_version && get_option( 'hubwoo_pro_get_started', false ) ) {

				return true;
			} else {

				return get_option( 'hubwoo_pro_get_started', false );
			}
		}

		/**
		 * Fetching the last version from user database
		 *
		 * @since 1.0.0
		 */
		public static function hubwoo_pro_last_version() {

			if ( self::is_setup_completed() ) {

				return get_option( 'hubwoo_pro_version', '1.0.5' );
			} else {

				return HUBWOO_VERSION;
			}
		}

		/**
		 * Verify if the hubspot setup is completed.
		 *
		 * @since 1.0.0
		 * @return boolean true/false
		 */
		public static function is_setup_completed() {

			return get_option( 'hubwoo_pro_setup_completed', false );
		}

		/**
		 * Check if hubspot oauth has been successful.
		 *
		 * @since  1.0.0
		 * @return boolean true/false
		 */
		public function is_oauth_success() {

			return get_option( 'hubwoo_pro_oauth_success', false );
		}

		/**
		 * Check if plugin feature is enbled or not.
		 *
		 * @since  1.0.0
		 * @return boolean true/false
		 */
		public function is_plugin_enable() {

			return get_option( 'hubwoo_pro_settings_enable', 'yes' );
		}

		/**
		 * Check if valid hubspot client Ids is stored.
		 *
		 * @since  1.0.0
		 * @return boolean true/false
		 */
		public static function is_valid_client_ids_stored() {

			$hapikey = HUBWOO_CLIENT_ID;
			$hseckey = HUBWOO_SECRET_ID;

			if ( $hapikey && $hseckey ) {

				return get_option( 'hubwoo_pro_valid_client_ids_stored', false );
			}

			return false;
		}

		/**
		 * Checking the properties setup status.
		 *
		 * @since 1.0.0
		 */
		public function is_field_setup_completed() {

			$last_version = self::hubwoo_pro_last_version();

			if ( HUBWOO_VERSION != $last_version ) {

				return true;
			} else {

				return get_option( 'hubwoo_fields_setup_completed', false );
			}
		}

		/**
		 * Locate and load appropriate tempate.
		 *
		 * @since   1.0.0
		 * @param string $path path of the file.
		 * @param string $tab tab name.
		 */
		public function load_template_view( $path, $tab = '' ) {

			$file_path = HUBWOO_ABSPATH . $path;

			if ( file_exists( $file_path ) ) {

				include $file_path;
			} else {

				$file_path = apply_filters( 'hubwoo_load_template_path', $tab );

				if ( file_exists( $file_path ) ) {

					include $file_path;
				} else {

					/* translators: %s: file path */
					$notice = sprintf( esc_html__( 'Unable to locate file path at location %s some features may not work properly in HubSpot Integration, please contact us!', 'hubspot-for-woocommerce' ), $file_path );
					$this->hubwoo_notice( $notice, 'error' );
				}
			}
		}

		/**
		 * Show admin notices.
		 *
		 * @param  string $message    Message to display.
		 * @param  string $type       notice type, accepted values - error/update/update-nag.
		 * @since  1.0.0
		 */
		public static function hubwoo_notice( $message, $type = 'error' ) {

			$classes = 'notice ';

			switch ( $type ) {

				case 'update':
					$classes .= 'updated';
					break;

				case 'update-nag':
					$classes .= 'update-nag';
					break;

				case 'success':
					$classes .= 'notice-success is-dismissible';
					break;
				case 'hubwoo-notice':
					$classes .= 'hubwoo-notice';
					break;
				default:
					$classes .= 'error';
			}

			$notice  = '<div class="' . $classes . '">';
			$notice .= '<p>' . $message . '</p>';
			$notice .= '</div>';

			echo wp_kses_post( $notice );
		}

		/**
		 * Fetch owner email info from HubSpot.
		 *
		 * @since 1.0.0
		 * @return boolean true/false
		 */
		public function hubwoo_owners_email_info() {

			$owner_email = get_option( 'hubwoo_pro_hubspot_id', '' );

			if ( empty( $owner_email ) ) {

				if ( self::is_valid_client_ids_stored() ) {

					$flag = true;

					if ( self::is_access_token_expired() ) {

						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

						if ( ! $status ) {

							$flag = false;
						}
					}

					if ( $flag ) {

						$owner_email = HubWooConnectionMananager::get_instance()->hubwoo_get_owners_info();

						if ( ! empty( $owner_email ) ) {

							update_option( 'hubwoo_pro_hubspot_id', $owner_email );
						}
					}
				}
			}

			return $owner_email;
		}

		/**
		 * Check if access token is expired.
		 *
		 * @since     1.0.0
		 * @return boolean true/false
		 */
		public static function is_access_token_expired() {

			$get_expiry = get_option( 'hubwoo_pro_token_expiry', false );

			if ( $get_expiry ) {

				$current_time = time();

				if ( ( $get_expiry > $current_time ) && ( $get_expiry - $current_time ) <= 50 ) {

					return true;
				} elseif ( ( $current_time > $get_expiry ) ) {

					return true;
				}
			}

			return false;
		}

		/**
		 * Reset saved options for setup.
		 *
		 * @param bool $redirect whether to redirect.
		 * @param bool $delete_meta whether to delete meta.
		 * @since     1.0.0
		 */
		public function hubwoo_switch_account( $redirect = true, $delete_meta = false ) {

			global $wpdb;
			$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '%hubwoo%'" );
			as_unschedule_action( 'hubwoo_contacts_sync_background' );
			as_unschedule_action( 'hubwoo_deals_sync_background' );
			as_unschedule_action( 'hubwoo_products_sync_background' );
			as_unschedule_action( 'hubwoo_products_status_background' );
			as_unschedule_action( 'hubwoo_update_contacts_vid' );

			if ( $delete_meta ) {
				delete_option( 'WooCommerce: set Order Recency 1 Ratings' );
				delete_option( 'WooCommerce: set Order Recency 2 Ratings' );
				delete_option( 'WooCommerce: set Order Recency 3 Ratings' );
				delete_option( 'WooCommerce: set Order Recency 4 Ratings' );
				delete_option( 'WooCommerce: set Order Recency 5 Ratings' );
				delete_option( 'WooCommerce: MQL to Customer lifecycle stage Conversion' );
				delete_option( 'WooCommerce: Welcome New Customer & Get a 2nd Order' );
				delete_option( 'WooCommerce: 2nd Order Thank You & Get a 3rd Order' );
				delete_option( 'WooCommerce: 3rd Order Thank You' );
				delete_option( 'WooCommerce: ROI Calculation' );
				delete_option( 'WooCommerce: Order Workflow' );
				delete_option( 'WooCommerce: Update Historical Order Recency Rating' );
				delete_option( 'WooCommerce: After order Workflow' );
				delete_option( 'WooCommerce: Enroll Customers for Recency Settings' );
				delete_metadata( 'user', 0, 'hubwoo_pro_user_data_change', '', true );
				delete_metadata( 'user', 0, 'hubwoo_user_vid', '', true );
				delete_metadata( 'post', 0, 'hubwoo_pro_user_data_change', '', true );
				delete_metadata( 'post', 0, 'hubwoo_pro_guest_order', '', true );
				delete_metadata( 'post', 0, 'hubwoo_ecomm_deal_id', '', true );
				delete_metadata( 'post', 0, 'hubwoo_ecomm_pro_id', '', true );
				delete_metadata( 'post', 0, 'hubwoo_ecomm_deal_created', '', true );
				delete_metadata( 'post', 0, 'hubwoo_product_synced', '', true );
				delete_metadata( 'post', 0, 'hubwoo_user_vid', '', true );
			}

			if ( $redirect ) {
				wp_safe_redirect( admin_url( 'admin.php?page=hubwoo' ) );
			} else {
				update_option( 'hubwoo_clear_previous_options', 'yes' );
			}
			exit();
		}

		/**
		 * Getting the final groups after the setup.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_get_final_groups() {

			$final_groups = array();

			$hubwoo_groups = HubWooContactProperties::get_instance()->_get( 'groups' );

			$last_version = self::hubwoo_pro_last_version();

			if ( HUBWOO_VERSION != $last_version && '2.0.0' > $last_version ) {

				if ( is_array( $hubwoo_groups ) && count( $hubwoo_groups ) ) {

					foreach ( $hubwoo_groups as $single_group ) {

						if ( 'subscriptions_details' == $single_group['name'] && ! self::is_subs_group_setup_completed() ) {

							$final_groups[] = array(
								'detail' => $single_group,
								'status' => 'false',
							);
						} else {

							$final_groups[] = array(
								'detail' => $single_group,
								'status' => 'created',
							);
						}
					}
				}
			} else {

				$added_groups = get_option( 'hubwoo-groups-created', array() );

				if ( get_option( 'hubwoo_abncart_added', 0 ) == 1 ) {
					$added_groups = apply_filters( 'hubwoo_active_groups', $added_groups );
				}

				if ( is_array( $hubwoo_groups ) && count( $hubwoo_groups ) ) {

					foreach ( $hubwoo_groups as $single_group ) {

						if ( in_array( $single_group['name'], $added_groups ) ) {

							$final_groups[] = array(
								'detail' => $single_group,
								'status' => 'created',
							);
						} else {

							$final_groups[] = array(
								'detail' => $single_group,
								'status' => 'false',
							);
						}
					}
				}
			}

			return $final_groups;
		}

		/**
		 * Verify if the hubspot subscription group setup is completed.
		 *
		 * @since 1.0.0
		 * @return boolean true/false
		 */

		public static function is_subs_group_setup_completed() {

			$last_version = self::hubwoo_pro_last_version();

			if ( HUBWOO_VERSION != $last_version ) {

				if ( get_option( 'hubwoo_subs_setup_completed', false ) ) {

					return true;
				} else {

					if ( in_array( 'subscriptions_details', get_option( 'hubwoo-groups-created', array() ) ) ) {

						return true;
					} else {

						return false;
					}
				}
			}
		}

		/**
		 * Required groups for lists and workflows.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_workflows_and_list_groups() {

			return array( 'rfm_fields', 'roi_tracking', 'customer_group', 'order', 'abandoned_cart' );
		}

		/**
		 * Required properties for lists abd workflows.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_workflows_and_list_properties() {

			$required_fields = array();

			$roi_tracking_properties = HubWooContactProperties::get_instance()->_get( 'properties', 'roi_tracking' );

			if ( ! empty( $roi_tracking_properties ) ) {

				foreach ( $roi_tracking_properties as $single_property ) {

					if ( isset( $single_property['name'] ) ) {

						$required_fields[] = $single_property['name'];
					}
				}
			}

			$required_fields[] = 'newsletter_subscription';
			$required_fields[] = 'total_number_of_orders';
			$required_fields[] = 'last_order_date';
			$required_fields[] = 'last_order_value';
			$required_fields[] = 'average_days_between_orders';
			$required_fields[] = 'monetary_rating';
			$required_fields[] = 'order_frequency_rating';
			$required_fields[] = 'order_recency_rating';

			return $required_fields;
		}

		/**
		 * Get final lists to be created on HubSpot.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_get_final_lists() {

			$final_lists = array();

			$hubwoo_lists = HubWooContactProperties::get_instance()->_get( 'lists' );
			if ( is_array( $hubwoo_lists ) && count( $hubwoo_lists ) ) {

				foreach ( $hubwoo_lists as $single_list ) {
					$list_filter_created = self::is_list_filter_created( $single_list['filters'] );
					if ( $list_filter_created ) {

						$final_lists[] = $single_list;
					}
				}
			}

			if ( count( $final_lists ) ) {

				$add_lists = get_option( 'hubwoo-lists-created', array() );

				$filtered_final_lists = array();

				foreach ( $final_lists as $single_list ) {

					if ( in_array( $single_list['name'], $add_lists ) ) {

						$filtered_final_lists[] = array(
							'detail' => $single_list,
							'status' => 'created',
						);
					} else {

						$filtered_final_lists[] = array(
							'detail' => $single_list,
							'status' => 'false',
						);
					}
				}
			}

			return $filtered_final_lists;
		}

		/**
		 * Checking the list filter to be created.
		 *
		 * @since 1.0.0
		 * @param array $filters list of filters in a list.
		 */
		public function is_list_filter_created( $filters ) {

			$status = true;

			if ( is_array( $filters ) && count( $filters ) ) {

				foreach ( $filters as $key => $single_filter ) {

					foreach ( $single_filter as $single_filter_detail ) {

						if ( isset( $single_filter_detail['property'] ) ) {

							$status &= self::check_field_existence( $single_filter_detail['property'] );
						}
					}
				}
			}

			return $status;
		}

		/**
		 * Checking field existense for the lists.
		 *
		 * @since 1.0.0
		 * @param string $field name of the field.
		 */
		public static function check_field_existence( $field = '' ) {

			$status = false;

			if ( 'lifecyclestage' == $field ) {

				return true;
			}

			global $hubwoo;

			$hubwoo_fields = get_option( 'hubwoo-properties-created', array() );

			$status = in_array( $field, $hubwoo_fields ) ? true : false;

			return $status;
		}

		/**
		 * Checking the lists setup status.
		 *
		 * @since 1.0.0
		 */
		public function is_list_setup_completed() {

			return get_option( 'hubwoo_pro_lists_setup_completed', false );
		}

		/**
		 * Workflow description.
		 *
		 * @since 1.0.0
		 */
		public function get_workflow_description() {
			return array(
				'WooCommerce: MQL to Customer lifecycle stage Conversion' => 'It is designed to get a qualified lead to make the first purchase.',
				'WooCommerce: Welcome New Customer & Get a 2nd Order' => 'This workflow triggers shortly after a first purchase, and are designed to push the customer towards a 2nd order.',
				'WooCommerce: 2nd Order Thank You & Get a 3rd Order' => 'This workflow triggers shortly after the 2nd Purchase and is designed to thank customers to become repeat buyers.',
				'WooCommerce: 3rd Order Thank You'         => 'This workflow triggers for those customers who have placed their order for at least 3 times.',
				'WooCommerce: ROI Calculation'             => 'This workflow triggers to track conversions in your marketing system by knowing Return-On-Investment.',
				'WooCommerce: After order Workflow'        => 'This workflow triggers when any new order gets placed.',
				'WooCommerce: Order Workflow'              => 'This workflow triggers to track purchase if a prospect gets convert into a customer.',
				'WooCommerce: set Order Recency 1 Ratings' => 'This workflow triggers for those customers with Order Recency Rating - 1.',
				'WooCommerce: set Order Recency 2 Ratings' => 'This workflow triggers for those customers with Order Recency Rating - 2',
				'WooCommerce: set Order Recency 3 Ratings' => 'This workflow triggers for those customers with Order Recency Rating - 3',
				'WooCommerce: set Order Recency 4 Ratings' => 'This workflow triggers for those customers with Order Recency Rating - 4',
				'WooCommerce: set Order Recency 5 Ratings' => 'This workflow triggers for those customers with Order Recency Rating - 5',
				'WooCommerce: Update Historical Order Recency Rating' => 'This workflow triggers when any historical order recency rating gets updated.',
				'WooCommerce: Enroll Customers for Recency Settings' => 'This workflow triggers when any customer has made its first purchase and enrolled for Order Recency.',
				'WooCommerce: Abandoned Cart Recovery'     => 'This workflow triggers when any user abandons their cart on your store.',
			);
		}

		/**
		 * Checking the lists setup status.
		 *
		 * @since 1.0.0
		 */
		public function get_lists_description() {

			return array(
				'Repeat Buyers'                 => 'Repeat Buyers is the smart list of HubSpot which helps to segment customers those who shop on your store regularly and their HubSpot property Average days between orders is also under the count of 30 days.',
				'DisEngaged Customers'          => 'Disengaged Customers is the smart list where you can see the list of customers that didn’t reach you from more than 60-180 days. It is the most useful list where you can target your those customers who are disengaged for a long period of time.',
				'Abandoned Cart'                => 'Send Reminders, Capture emails and Recover Lost Sales in real-time with an Automated Cart Recovery Solution for your WooCommerce store.',
				'Engaged Customers'             => 'Engaged Customers is the smart list of HubSpot that will list all your contacts whose last brought item is less than 60days. It will show the list of your loyal and regular customers.',
				'Customers'                     => 'This list will enroll customers according to their customer’s lifecycle stage. Whenever any customer’s lifecycle changes it would filter all those customers.',
				'Marketing Qualified Leads'     => 'It will enlist all those marketing qualified lead (MQL) who has been deemed more likely to become a customer compared to other leads. This qualification is based on what web pages a person has visited, what they’ve downloaded, and similar engagement with the business’s content.',
				'Leads'                         => 'It will list all those leads who have indicated interest in your company’s product or service in some way, shape, or form.',
				'Bought four or more times'     => 'It will list all those customers who have purchased 4 times from your store. You can provide special benefits to those customers.',
				'Three time purchase customers' => 'It will list all those customers who have purchased only three times from your store. You can encourage them to buy more frequently.',
				'Two time purchase customers'   => 'It will list all those customers who have brought only 2 times from your store. You can pay special attention to those customers as they are interested but you have to educate them about your product and service.',
				'One time purchase customers'   => 'It will list all those customers whose total number of order is 1. As the list shows, the total number of order is 1, so you have to work hard on these customers and start nurturing them and educate them about your product and services.',
				'Newsletter Subscriber'         => 'It will list all those newsletter subscribers who have subscribed for a printed report containing news (information) of the activities of a business or an organization (institutions, societies, associations) that is sent by mail regularly to all its members, customers, employees or people, who are interested.',
				'Low Spenders'                  => 'This list shows the contact property whose Monetary rating is equal to 1. It notifies that your customer is not spending much on your store.',
				'Mid Spenders'                  => 'This list shows the contact property whose Monetary rating is equal to 3. It means that he not frequently buying from your store.',
				'Big Spenders'                  => 'This list shows the contact property whose Monetary rating is equal to 5. These are the customers who are spending lavishly and purchasing more often from your store.',
				'About to Sleep'                => 'It is the list in which customer whose Recency Frequency and Monetary (RFM) value lie between 1 & 2 and they are about to sleep. It means that their engagement with your website is getting less on each successive day.',
				'Customers needing attention'   => 'In this list, Monetary and Frequency of the customer are 3 but Recency lies between 1 & 2. This list shows that customer has spent time and money both on your website but his last order was long-ago.',
				'New Customers'                 => 'This list shows new contact whose Frequency and Recency is 1. They are the new customer they are not yet engaged with your website.',
				'Low Value Lost Customers'      => 'It is the list of those customers whose Recency, Frequency & Monetary is 1. These are the customer who is on the verge of getting lost as their engagement with the website is very low.',
				'Churning Customers'            => 'It is the list of those customers whose Monetary and Order frequency is 5 but Recency is 1. The churning rate, also known as the rate of attrition, it is the percentage of subscribers to a service who discontinue their subscriptions to the service within a given time period.',
				'Loyal Customers'               => 'It is the list of those customers whose Frequency and Recency of order is 5. It is the list which exhibits customer loyalty when they consistently purchase a certain product or brand over an extended period of time and describes the loyalty which is established between a customer and companies.',
				'Best Customers'                => 'It is the list of those customers whose RFM (Recency, Frequency & Monetary) rating is perfect 5. It is the list of your loyal customers that are consistently positive & emotional, physical attribute-based satisfaction and perceived value of an experience, which includes the product or services.',
			);
		}

		/**
		 * Required lists to create.
		 *
		 * @since 1.0.0
		 * @param string $list_name name of the list.
		 */
		public function required_lists_to_create( $list_name ) {

			$required_lists = array( 'Customers', 'Leads', 'Abandoned Cart' );

			return in_array( $list_name, $required_lists ) ? "checked='checked'" : '';
		}

		/**
		 * Getting the final groups after the setup.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_get_final_workflows() {

			$final_workflows = array();

			$hubwoo_workflows = HubWooContactProperties::get_instance()->_get( 'workflows' );

			$add_workflows = get_option( 'hubwoo-workflows-created', array() );

			if ( is_array( $hubwoo_workflows ) && count( $hubwoo_workflows ) ) {

				foreach ( $hubwoo_workflows as $single_workflow ) {

					if ( in_array( $single_workflow['name'], $add_workflows ) ) {

						$final_workflows[] = array(
							'detail' => $single_workflow,
							'status' => 'created',
						);
					} else {

						$final_workflows[] = array(
							'detail' => $single_workflow,
							'status' => 'false',
						);
					}
				}
			}

			return $final_workflows;
		}

		/**
		 * All dependencies for workflows.
		 *
		 * @since 1.0.0
		 */
		public static function hubwoo_workflows_dependency() {

			$workflows = array();

			$workflows[] = array(
				'workflow'     => 'WooCommerce: set Order Recency 1 Ratings',
				'dependencies' => array( 'WooCommerce: Order Workflow' ),
			);

			$workflows[] = array(
				'workflow'     => 'WooCommerce: set Order Recency 2 Ratings',
				'dependencies' => array( 'WooCommerce: set Order Recency 1 Ratings' ),
			);

			$workflows[] = array(
				'workflow'     => 'WooCommerce: set Order Recency 3 Ratings',
				'dependencies' => array( 'WooCommerce: set Order Recency 2 Ratings' ),
			);

			$workflows[] = array(
				'workflow'     => 'WooCommerce: set Order Recency 4 Ratings',
				'dependencies' => array( 'WooCommerce: set Order Recency 3 Ratings' ),
			);

			$workflows[] = array(
				'workflow'     => 'WooCommerce: set Order Recency 5 Ratings',
				'dependencies' => array( 'WooCommerce: set Order Recency 4 Ratings', 'WooCommerce: Order Workflow' ),
			);

			$workflows[] = array(
				'workflow'     => 'WooCommerce: Enroll Customers for Recency Settings',
				'dependencies' => array( 'WooCommerce: Update Historical Order Recency Rating' ),
			);

			$workflows[] = array(
				'workflow'     => 'WooCommerce: Update Historical Order Recency Rating',
				'dependencies' => array( 'WooCommerce: set Order Recency 5 Ratings' ),
			);

			$workflows[] = array(
				'workflow'     => 'WooCommerce: Order Workflow',
				'dependencies' => array( 'WooCommerce: ROI Calculation', 'WooCommerce: After order Workflow' ),
			);

			$workflows[] = array(
				'workflow'     => 'WooCommerce: After order Workflow',
				'dependencies' => array( 'WooCommerce: 3rd Order Thank You', 'WooCommerce: 2nd Order Thank You & Get a 3rd Order', 'WooCommerce: Welcome New Customer & Get a 2nd Order' ),
			);

			$workflows[] = array(
				'workflow'     => 'WooCommerce: Abandoned Cart Recovery',
				'dependencies' => array( 'WooCommerce: Order Workflow' ),
			);

			return $workflows;
		}

		/**
		 * Checking workflow dependency.
		 *
		 * @since 1.0.0
		 * @param string $workflow name of workflow.
		 */
		public function is_workflow_dependent( $workflow = '' ) {

			if ( ! empty( $workflow ) ) {

				$workflow_dependencies = self::hubwoo_workflows_dependency();

				$dependencies = array();

				$status = true;

				if ( ! empty( $workflow_dependencies ) ) {

					foreach ( $workflow_dependencies as $single_workflow ) {

						if ( isset( $single_workflow['workflow'] ) && $workflow == $single_workflow['workflow'] ) {

							$dependencies = $single_workflow['dependencies'];
							break;
						}
					}
				}

				if ( ! empty( $dependencies ) ) {

					foreach ( $dependencies as $single_dependency ) {

						$status &= self::is_hubwoo_workflow_exists( $single_dependency );
					}
				}

				return $status;
			}
		}

		/**
		 * Check for workflow existence.
		 *
		 * @since 1.0.0
		 * @param string $workflow workflow name.
		 */
		public static function is_hubwoo_workflow_exists( $workflow = '' ) {

			$status = false;

			if ( ! empty( $workflow ) ) {

				return in_array( $workflow, get_option( 'hubwoo-workflows-created', array() ) );
			}

			return $status;
		}

		/**
		 * Checking for workflows scope.
		 *
		 * @since     1.0.0
		 */
		public function is_automation_enabled() {

			$scopes = get_option( 'hubwoo_pro_account_scopes', array() );

			if ( empty( $scopes ) ) {

				HubWooConnectionMananager::get_instance()->hubwoo_pro_get_access_token_info();
			}

			if ( in_array( 'automation', $scopes ) ) {

				return true;
			}
		}


		/**
		 * Get array of all workflows with id and name.
		 *
		 * @since 1.0.0
		 */
		public static function hubwoo_get_all_workflows_id_name() {

			$all_workflows = array();

			$all_workflows = HubWooConnectionMananager::get_instance()->get_workflows();

			return $all_workflows;
		}

		/**
		 * Get selected order action.
		 *
		 * @since 1.0.0
		 * @param string $action name of action to get html.
		 */
		public static function hubwoo_get_selected_order_action( $action = '' ) {

			$actions_for_workflows = self::hubwoo_order_actions_for_workflows();

			$option = '';

			if ( ! empty( $actions_for_workflows ) ) {

				foreach ( $actions_for_workflows as $key => $value ) {

					if ( $key == $action ) {

						$option .= '<option selected value="' . $key . '">' . $value . '</option>';
					} else {

						$option .= '<option value="' . $key . '">' . $value . '</option>';
					}
				}
			}

			return $option;
		}

		/**
		 * Get selected workflow.
		 *
		 * @since 1.0.0
		 * @param array  $all_workflows list of all workflows.
		 * @param string $workflow workflow name.
		 */
		public static function hubwoo_get_selected_workflow( $all_workflows, $workflow = '' ) {

			$option = '';

			if ( ! empty( $all_workflows ) ) {

				foreach ( $all_workflows as $key => $value ) {

					if ( $key == $workflow ) {

						$option .= '<option selected value="' . $key . '">' . $value . '</option>';
					} else {

						$option .= '<option value="' . $key . '">' . $value . '</option>';
					}
				}
			}

			return $option;
		}



		/**
		 * Get selected customer action.
		 *
		 * @since 1.0.0
		 * @param string $action key to be make selected.
		 */
		public static function hubwoo_get_selected_customer_action( $action = '' ) {

			$actions_for_workflows = self::hubwoo_customer_actions_for_workflows();

			$option = '';

			if ( ! empty( $actions_for_workflows ) ) {

				foreach ( $actions_for_workflows as $key => $value ) {

					if ( $key == $action ) {

						$option .= '<option selected value="' . $key . '">' . $value . '</option>';
					} else {

						$option .= '<option value="' . $key . '">' . $value . '</option>';
					}
				}
			}

			return $option;
		}

		/**
		 * Get array of customer activity actions.
		 *
		 * @since 1.0.0
		 */
		public static function hubwoo_customer_actions_for_workflows() {

			$actions = array();

			$actions['user_register'] = esc_html__( 'New User Registration', 'hubspot-for-woocommerce' );

			return apply_filters( 'hubwoo_customer_actions_for_workflows', $actions );
		}

		/**
		 * Get array of order status transition actions.
		 *
		 * @since 1.0.0
		 */
		public static function hubwoo_order_actions_for_workflows() {

			$actions = array();

			$actions['woocommerce_order_status_completed']  = esc_html__( 'When order status changes to Completed', 'hubspot-for-woocommerce' );
			$actions['woocommerce_order_status_processing'] = esc_html__( 'When order status changes to Processing', 'hubspot-for-woocommerce' );
			$actions['woocommerce_order_status_failed']     = esc_html__( 'When order status changes to Failed', 'hubspot-for-woocommerce' );
			$actions['woocommerce_order_status_on-hold']    = esc_html__( 'When order status changes to On-hold', 'hubspot-for-woocommerce' );
			$actions['woocommerce_order_status_refunded']   = esc_html__( 'When order status changes to Refunded', 'hubspot-for-woocommerce' );
			$actions['woocommerce_order_status_cancelled']  = esc_html__( 'When order status changes to Cancelled', 'hubspot-for-woocommerce' );

			return apply_filters( 'hubwoo_order_actions_for_workflows', $actions );
		}

		/**
		 * Get array of all static lists with id and name.
		 *
		 * @since 1.0.0
		 */
		public static function hubwoo_get_all_static_lists_id_name() {

			$all_lists = array();

			$all_lists = HubWooConnectionMananager::get_instance()->get_static_list();

			return $all_lists;
		}

		/**
		 * Get selected list.
		 *
		 * @since 1.0.0
		 * @param array  $all_lists array of all lists.
		 * @param string $list name of list.
		 */
		public static function hubwoo_get_selected_list( $all_lists, $list = '' ) {

			$option = '';

			if ( ! empty( $all_lists ) ) {

				foreach ( $all_lists as $key => $value ) {

					if ( $key == $list ) {

						$option .= '<option selected value="' . $key . '">' . $value . '</option>';
					} else {

						$option .= '<option value="' . $key . '">' . $value . '</option>';
					}
				}
			}

			return $option;
		}

		/**
		 * Get array of all user roles of WordPress.
		 *
		 * @since 1.0.0
		 */
		public static function hubwoo_get_user_roles() {

			global $wp_roles;

			$exiting_user_roles = array();

			$user_roles = ! empty( $wp_roles->role_names ) ? $wp_roles->role_names : array();

			if ( is_array( $user_roles ) && count( $user_roles ) ) {

				foreach ( $user_roles as $role => $role_info ) {

					$role_label = ! empty( $role_info ) ? $role_info : $role;

					$exiting_user_roles[ $role ] = $role_label;
				}

				$exiting_user_roles['guest_user'] = 'Guest User';
			}

			return $exiting_user_roles;
		}

		/**
		 * Check whether subscriptions are active or not.
		 *
		 * @since 1.0.0
		 * @return boolean true/false
		 */
		public static function hubwoo_subs_active() {

			$flag = false;

			if ( in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

				$flag = true;
			}

			return $flag;
		}

		/**
		 * Get full country name.
		 *
		 * @since 1.0.0
		 * @param string $value country abbreviation.
		 */
		public static function map_country_by_abbr( $value ) {

			if ( ! empty( $value ) ) {
				if ( class_exists( 'WC_Countries' ) ) {
					$wc_countries = new WC_Countries();
					$countries    = $wc_countries->__get( 'countries' );
				}
				if ( ! empty( $countries ) ) {
					foreach ( $countries as $abbr => $country_name ) {
						if ( $value == $abbr ) {
							$value = $country_name;
							break;
						}
					}
				}
			}
			return $value;
		}

		/**
		 * Get full state name.
		 *
		 * @since 1.0.0
		 * @param string $value abbrevarion for state.
		 * @param string $country name of country.
		 */
		public static function map_state_by_abbr( $value, $country ) {

			if ( ! empty( $value ) && ! empty( $country ) ) {
				if ( class_exists( 'WC_Countries' ) ) {
					$wc_countries = new WC_Countries();
					$states       = $wc_countries->__get( 'states' );
				}
				if ( ! empty( $states ) ) {
					foreach ( $states as $country_abbr => $country_states ) {
						if ( $country == $country_abbr ) {
							foreach ( $country_states as $state_abbr => $state_name ) {
								if ( $value == $state_abbr ) {
									$value = $state_name;
									break;
								}
							}
							break;
						}
					}
				}
			}
			return $value;
		}

		/**
		 * Filter contact properties with the help of created properties.
		 *
		 * @since 1.0.0
		 * @param array $properties list of contact properties.
		 */
		public function hubwoo_filter_contact_properties( $properties = array() ) {

			$filtered_properties = array();

			$created_properties = array_map(
				function( $property ) {
					return str_replace( "'", '', $property );
				},
				get_option( 'hubwoo-properties-created', array() )
			);

			if ( ! empty( $properties ) && count( $properties ) ) {

				foreach ( $properties as $single_property ) {

					if ( ! empty( $single_property['property'] ) ) {

						if ( in_array( $single_property['property'], $created_properties ) ) {

							$filtered_properties[] = $single_property;
						}
					}
				}
			}

			return $filtered_properties;
		}

		/**
		 * Returning saved access token.
		 *
		 * @since 1.0.0
		 */
		public static function hubwoo_get_access_token() {

			if ( self::is_valid_client_ids_stored() ) {

				if ( self::is_access_token_expired() ) {
					$hapikey = HUBWOO_CLIENT_ID;
					$hseckey = HUBWOO_SECRET_ID;
					$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );
				}
			}

			return get_option( 'hubwoo_pro_access_token', false );
		}

		/**
		 * Get auth url.
		 *
		 * @since 1.0.0
		 */
		public static function hubwoo_get_auth_url() {

			$url = 'https://app.hubspot.com/oauth/authorize';

			$hapikey = HUBWOO_CLIENT_ID;

			$hubspot_url = add_query_arg(
				array(
					'client_id'      => $hapikey,
					'optional_scope' => 'automation%20files%20timeline%20content%20forms%20integration-sync%20e-commerce',
					'scope'          => 'oauth%20crm.objects.owners.read%20crm.objects.contacts.write%20crm.objects.companies.write%20crm.lists.write%20crm.objects.companies.read%20crm.lists.read%20crm.objects.deals.read%20crm.objects.deals.write%20crm.objects.contacts.read%20crm.schemas.companies.write%20crm.schemas.contacts.write%20crm.schemas.deals.read%20crm.schemas.deals.write%20crm.schemas.contacts.read%20crm.schemas.companies.read',
					'redirect_uri'   => admin_url() . 'admin.php?type=hs-auth',
				),
				$url
			);

			return $hubspot_url;
		}

		/**
		 * Get selected deal stage by order key.
		 *
		 * @since 1.0.0
		 * @param string $order_key order key.
		 */
		public static function get_selected_deal_stage( $order_key ) {

			$deal_stage = array();
			if ( ! empty( $order_key ) ) {
				$saved_mapping = get_option( 'hubwoo_ecomm_final_mapping', array() );
				if ( ! empty( $saved_mapping ) ) {
					foreach ( $saved_mapping as $single_mapping ) {
						if ( $order_key == $single_mapping['status'] ) {
							$deal_stage = $single_mapping['deal_stage'];
							break;
						}
					}
				}
			}
			return $deal_stage;
		}

		/**
		 * Get contact sync status.
		 *
		 * @since 1.0.0
		 */
		public static function get_sync_status() {

			$sync_status['current'] = get_option( 'hubwoo_deals_current_sync_count', 0 );
			$sync_status['total']   = get_option( 'hubwoo_deals_current_sync_total', 0 );
			$sync_status['eta_deals_sync'] = '';

			if ( $sync_status['total'] ) {
				$perc                          = round( ( $sync_status['current'] / $sync_status['total'] ) * 100 );
				$sync_status['deals_progress'] = $perc > 100 ? 99 : $perc;
				$sync_status['eta_deals_sync'] = self::hubwoo_create_sync_eta( $sync_status['current'], $sync_status['total'], 5, 5 );
			}

			if ( ( $sync_status['current'] == $sync_status['total'] ) || ( $sync_status['current'] > $sync_status['total'] ) ) {
				self::hubwoo_stop_sync( 'stop-deal' );
			}
			return $sync_status;
		}

		/**
		 * Get contact sync status.
		 *
		 * @since 1.0.0
		 * @param string $query_action which query to run.
		 * @return array|string query result.
		 */
		public static function hubwoo_make_db_query( $query_action ) {

			global $wpdb;

			switch ( $query_action ) {
				case 'total_products_to_sync':
					return $wpdb->get_results( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type IN ( 'product', 'product_variation' ) AND ID NOT IN (SELECT post_parent FROM {$wpdb->posts} WHERE post_type IN ( 'product', 'product_variation' ) ) AND post_status  IN ( 'publish', 'draft' )" );
				case 'total_synced_products':
					return $wpdb->get_results( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE meta_key LIKE 'hubwoo_ecomm_pro_id'" );
				case 'total_synced_deals':
					return $wpdb->get_results( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE meta_key LIKE 'hubwoo_ecomm_deal_created'" );
				case 'total_synced_contacts':
					return $wpdb->get_results( "SELECT COUNT(user_id) FROM {$wpdb->usermeta} WHERE meta_key = 'hubwoo_pro_user_data_change' AND meta_value = 'synced'" );
				case 'total_synced_guest_contacts':
					return $wpdb->get_results( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE meta_key = 'hubwoo_pro_guest_order' AND meta_value = 'synced'" );
				default:
					return '';
			}
		}

		/**
		 * Get contact sync status.
		 *
		 * @since 1.0.0
		 */
		public static function get_deals_presenter() {

			$synced_products = 0;
			$percentage_done = 0;
			$display_data    = array();
			$total_products  = get_option( 'hubwoo_products_to_sync', 0 );

			if ( 0 == $total_products ) {
				$result = self::hubwoo_make_db_query( 'total_products_to_sync' );
				if ( ! empty( $result ) ) {
					$result         = (array) $result[0];
					$total_products = $result['COUNT(ID)'];
					update_option( 'hubwoo_products_to_sync', $total_products );
				}
			}
			$display_data['total_products'] = $total_products;

			if ( 'yes' == get_option( 'hubwoo_ecomm_setup_completed', 'no' ) ) {
				$display_data['view_all']     = 'block';
				$display_data['view_mapping'] = 'none';
				$display_data['view_button']  = 'inline-block';
			} else {
				$display_data['view_mapping'] = 'block';
				$display_data['view_all']     = 'none';
				$display_data['view_button']  = 'none';
				$display_data['p_run_sync']   = 'block';
			}

			$display_data['is_psync_running'] = 'no';
			if ( 'yes' == get_option( 'hubwoo_start_product_sync', 'no' ) ) {

				$display_data['p_run_sync']       = 'block';
				$display_data['is_psync_running'] = 'yes';
				$display_data['view_btn_mapping'] = 'none';
				$display_data['h_sync']           = 'none';
				$display_data['heading']          = 'eCommerce Pipeline setup is now running, please wait.';
				$sync_result                      = self::hubwoo_make_db_query( 'total_synced_products' );

				if ( ! empty( $sync_result ) ) {
					$sync_result     = (array) $sync_result[0];
					$synced_products = $sync_result['COUNT(post_id)'];
				}
				if ( 0 != $total_products ) {
					$percentage_done = round( $synced_products * 100 / $total_products );
					$percentage_done = $percentage_done > 100 ? 99 : $percentage_done;
				}

				$display_data['eta_product_sync'] = self::hubwoo_create_sync_eta( $synced_products, $total_products, 3, 5 );
				$display_data['percentage_done']  = $percentage_done;
				if ( 100 == $percentage_done ) {
					self::hubwoo_stop_sync( 'stop-product-sync' );
				}
			} else {
				$display_data['heading']    = 'Connect with eCommerce Pipeline';
				$display_data['h_sync']     = 'block';
				$display_data['p_run_sync'] = 'none';
			}

			$display_data['is_dsync'] = 'no';
			if ( 1 == get_option( 'hubwoo_deals_sync_running', 0 ) ) {
				$display_data['is_dsync'] = 'yes';
				$display_data['message']  = 'block';
				$display_data['button']   = 'none';
				$display_data['btn_data'] = 'stop-deal';
				$display_data['btn_text'] = 'Stop Sync';
			} else {
				$display_data['btn_text'] = 'Start Sync';
				$display_data['btn_data'] = 'start-deal';
				$display_data['message']  = 'none';
				$display_data['button']   = 'inline-block';
			}

			$scopes = get_option( 'hubwoo_pro_account_scopes', array() );

			$display_data['scope_notice'] = 'none';
			if ( ! in_array( 'integration-sync', $scopes ) ) {
				$display_data['scope_notice'] = 'block';
			}

			return $display_data;
		}

		/**
		 * Get all deal stages
		 *
		 * @since 1.0.0
		 * @param bool $update true/false.
		 */
		public static function get_all_deal_stages( $update = false ) {

			$deal_stages = get_option( 'hubwoo_fetched_deal_stages', '' );
			if ( empty( $deal_stages ) || $update ) {

				$deal_stages = self::fetch_deal_stages_from_pipeline();
				if ( ! empty( $deal_stages ) ) {
					update_option( 'hubwoo_fetched_deal_stages', $deal_stages );
				}
			}
			return $deal_stages;
		}

		/**
		 * Get deal stages from sales pipeline.
		 *
		 * @since 1.0.0
		 * @param string $pipeline_label name of pipeline ( default Ecommerce Pipline).
		 * @param bool   $only_stages return only stages (default true).
		 */
		public static function fetch_deal_stages_from_pipeline( $pipeline_label = 'Ecommerce Pipeline', $only_stages = true ) {

			$all_deal_pipelines = HubWooConnectionMananager::get_instance()->fetch_all_deal_pipelines();
			$fetched_pipeline   = '';
			if ( ! empty( $all_deal_pipelines['results'] ) ) {

				array_map(
					function( $single_pipeline ) use ( $pipeline_label, &$fetched_pipeline, $only_stages ) {

						if ( $single_pipeline['label'] == $pipeline_label ) {

							$fetched_pipeline = $only_stages ? $single_pipeline['stages'] : $single_pipeline;
						}
					},
					$all_deal_pipelines['results']
				);
			}

			return $fetched_pipeline;
		}

		/**
		 * Stop deals sync.
		 *
		 * @since 1.0.0
		 * @param string $type stop a specific task.
		 * @return void
		 */
		public static function hubwoo_stop_sync( $type ) {

			if ( 'stop-contact' == $type ) {

				update_option( 'hubwoo_ocs_data_synced', true );
				delete_option( 'hubwoo_background_process_running' );
				delete_option( 'hubwoo_total_ocs_need_sync' );
				as_unschedule_action( 'hubwoo_contacts_sync_background' );

			} elseif ( 'stop-deal' == $type ) {

				delete_option( 'hubwoo_deals_sync_total' );
				delete_option( 'hubwoo_deals_sync_running' );
				delete_option( 'hubwoo_deals_current_sync_count' );
				as_unschedule_action( 'hubwoo_deals_sync_background' );
			} elseif ( 'stop-product-sync' ) {
				update_option( 'hubwoo_ecomm_setup_completed', 'yes' );
				delete_option( 'hubwoo_start_product_sync' );
				delete_option( 'hubwoo_products_to_sync' );
				as_unschedule_action( 'hubwoo_products_sync_background' );
				as_unschedule_action( 'hubwoo_products_status_background' );
			}
		}

		/**
		 * Get the eCommerce Store Data.
		 *
		 * @since 1.0.0
		 * @return array store data .
		 */
		public static function get_store_data() {

			$blog_name = get_bloginfo( 'name' );
			$blog_id   = preg_replace( '/[^a-zA-Z0-9]/', '', $blog_name );
			$store     = array(
				'id'       => $blog_id . '-' . get_current_blog_id(),
				'label'    => $blog_name,
				'adminUri' => get_admin_url(),
			);
			return $store;
		}

		/**
		 * Stop deals sync.
		 *
		 * @since 1.0.0
		 * @param int $number_of_products number of products to prepare.
		 * @return array products info as eCommerce products.
		 */
		public static function hubwoo_get_product_data( $number_of_products = 5 ) {

			$contraints = array(
				array(
					'key'     => 'hubwoo_ecomm_pro_id',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'hubwoo_ecomm_invalid_pro',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'hubwoo_product_synced',
					'compare' => 'NOT EXISTS',
				),
				'relation' => 'AND',
			);

			$products = self::hubwoo_ecomm_get_products( $number_of_products, $contraints );

			$products_info = array();

			if ( is_array( $products ) && count( $products ) ) {

				$product_ids = array();
				$object_type = 'PRODUCT';

				foreach ( $products as $product_id ) {

					if ( ! empty( $product_id ) ) {

						$product      = wc_get_product( $product_id );
						$product_type = $product->get_type();

						if ( 'variable' == $product_type && ( ! empty( $product_type ) ) || 'variable-subscription' == $product_type || null == $product_type ) {
							update_post_meta( $product_id, 'hubwoo_ecomm_invalid_pro', 'yes' );
							continue;
						} else {

							$hubwoo_ecomm_product = new HubwooEcommObject( $product_id, $object_type );
							$properties           = $hubwoo_ecomm_product->get_object_properties();
							$properties           = apply_filters( 'hubwoo_map_ecomm_' . $object_type . '_properties', $properties, $product_id );
							$product_ids[]        = $product_id;
							$products_info[]      = array(
								'action'           => 'UPSERT',
								'changedAt'        => strtotime( gmdate( 'Y-m-d H:i:s ', time() ) ) . '000',
								'externalObjectId' => $product_id,
								'properties'       => $properties,
							);
						}
					}
				}
			}
			return $products_info;
		}

		/**
		 * Retrieve all of the products.
		 *
		 * @since 1.0.0
		 * @param int   $post_per_page number of products to prepare.
		 * @param array $constraints meta query constraints.
		 * @return array products ids.
		 */
		public static function hubwoo_ecomm_get_products( $post_per_page = 10, $constraints = array() ) {

			$response = array(
				'status_code' => 400,
				'reponse'     => 'No Products Found',
			);
			$query    = new WP_Query();
			$request  = array(
				'post_type'           => array( 'product', 'product_variation' ),
				'posts_per_page'      => $post_per_page,
				'post_status'         => array( 'publish', 'draft' ),
				'orderby'             => 'date',
				'order'               => 'desc',
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			);

			if ( ! empty( $constraints ) ) {
				if ( isset( $constraints['relation'] ) ) {
					$request['meta_query']['relation'] = array_pop( $constraints );
				}
				$request['meta_query'] = array_merge( $constraints );
			}

			$response = $query->query( $request );
			return $response;
		}

		/**
		 * Update the eCommerce pipeline deal stages
		 * with WooCommerce Deal stages and probability.
		 *
		 * @since 1.0.0
		 * @return array deal stage data.
		 */
		public static function hubwoo_deal_stage_model() {
			return array(
				'checkout_pending'   => array(
					'label'    => esc_html__( 'Payment Pending/Failed', 'hubspot-for-woocommerce' ),
					'metadata' => array(
						'isClosed'    => false,
						'probability' => 0.2,
					),
				),
				'checkout_completed' => array(
					'label'    => esc_html__( 'On hold', 'hubspot-for-woocommerce' ),
					'metadata' => array(
						'isClosed'    => false,
						'probability' => 0.6,
					),
				),
				'processed'          => array(
					'label'    => esc_html__( 'Processing', 'hubspot-for-woocommerce' ),
					'metadata' => array(
						'isClosed'    => true,
						'probability' => 1,
					),
				),
				'shipped'            => array(
					'label'    => esc_html__( 'Completed', 'hubspot-for-woocommerce' ),
					'metadata' => array(
						'isClosed'    => true,
						'probability' => 1,
					),
				),
				'cancelled'          => array(
					'label'    => esc_html__( 'Refunded/Cancelled', 'hubspot-for-woocommerce' ),
					'metadata' => array(
						'isClosed'    => true,
						'probability' => 0,
					),
				),
			);
		}

		/**
		 * Get the default model of order status and deal stage.
		 *
		 * @since 1.0.0
		 * @return array mapped deal stage and order status.
		 */
		public static function hubwoo_deals_mapping() {

			$mapping = array();

			$default_dealstages = array(
				'wc-pending'    => 'checkout_pending',
				'wc-processing' => 'processed',
				'wc-on-hold'    => 'checkout_completed',
				'wc-completed'  => 'shipped',
				'wc-cancelled'  => 'cancelled',
				'wc-refunded'   => 'cancelled',
				'wc-failed'     => 'checkout_pending',
			);

			$mapping = array_map(
				function( $order_status ) use ( $default_dealstages ) {
					$mapped_data['status'] = $order_status;
					if ( array_key_exists( $order_status, $default_dealstages ) ) {
						  $mapped_data['deal_stage'] = $default_dealstages[ $order_status ];
					} else {
						$mapped_data['deal_stage'] = 'checkout_completed';
					}
					return $mapped_data;
				},
				array_keys( wc_get_order_statuses() )
			);
			return $mapping;
		}

		/**
		 * Setup the overview section of the dashboard.
		 *
		 * @since 1.0.0
		 * @param bool $install_plugin default ( true ).
		 * @return array|void $display_data  display data for the HS plugin.
		 */
		public function hubwoo_setup_overview( $install_plugin = false ) {

			global $hubwoo;

			if ( 'no' == get_option( 'hubwoo_checkout_form_created', 'no' ) ) {
				$form_data = self::form_data_model( HubwooConst::CHECKOUTFORM );
				$flag = true;
				if ( self::is_access_token_expired() ) {

					$hapikey = HUBWOO_CLIENT_ID;
					$hseckey = HUBWOO_SECRET_ID;
					$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

					if ( ! $status ) {

						$flag = false;
					}
				}

				if ( $flag ) {
					$res       = HubWooConnectionMananager::get_instance()->create_form_data( $form_data );
					if ( 200 == $res['status_code'] ) {
						update_option( 'hubwoo_checkout_form_created', 'yes' );
						$res = json_decode( $res['body'], true );
						if ( isset( $res['guid'] ) ) {
							update_option( 'hubwoo_checkout_form_id', $res['guid'] );
						}
					} else {
						HubwooErrorHandling::get_instance()->hubwoo_handle_response( $res, HubwooConst::CHECKOUTFORM );
					}
				}
			}

			if ( $install_plugin ) {

				WC_Install::background_installer(
					'leadin',
					array(
						'name'      => esc_html__( 'HubSpot All-In-One Marketing - Forms, Popups, Live Chat', 'hubspot-for-woocommerce' ),
						'repo-slug' => 'leadin',
					)
				);
				?>
				<script type="text/javascript">
					window.open( "<?php echo esc_url( admin_url( 'admin.php?page=leadin' ) ); ?>" )
					window.location.href = "<?php echo esc_url( admin_url( 'admin.php?page=hubwoo' ) ); ?>"
				</script>
				<?php
				return;
			}

			$display_data = array();
			if ( ! in_array( 'leadin/leadin.php', get_option( 'active_plugins' ), true ) ) {
				$display_data['plugin-install']['label'] = 'Install and Activate';
				$display_data['plugin-install']['href']  = '?page=hubwoo&task=install-plugin';
			} else {
				$display_data['plugin-install']['label'] = 'Activated';
				$display_data['plugin-install']['href']  = 'javascript:void(0)';
			}

			$last_sync = get_option( 'hubwoo_last_sync_date', '' );

			$display_data['last_sync'] = 'Last Sync: Waiting to sync';

			if ( ! empty( $last_sync ) ) {
				$date = new DateTime();
				$date->setTimestamp( $last_sync );
				$display_data['last_sync'] = 'Last Sync: ' . date_format( $date, 'jS F Y \a\t g:ia ' );
			}

			$query = new WP_Query();

			$customer_orders = $query->query(
				array(
					'post_type'           => 'shop_order',
					'posts_per_page'      => -1,
					'post_status'         => array_keys( wc_get_order_statuses() ),
					'fields'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
					'meta_query'          => array(
						array(
							'key'     => 'hubwoo_ecomm_deal_created',
							'compare' => 'NOT EXISTS',
						),
					),
				)
			);

			$display_data['deals_left'] = empty( $customer_orders ) ? 'Sync completed' : count( $customer_orders ) . ' waiting to sync';

			$roles = get_option( 'hubwoo_customers_role_settings', array() );

			if ( empty( $roles ) ) {

				$roles = array_keys( $hubwoo->hubwoo_get_user_roles() );
			}

			$guest_key = array_search( 'guest_user', $roles );

			if ( false !== $guest_key ) {
				unset( $roles[ $guest_key ] );
			}

			$args['meta_query'] = array(
				array(
					'key'     => 'hubwoo_pro_user_data_change',
					'compare' => 'NOT EXISTS',
				),
			);
			$args['role__in']   = $roles;
			$args['number']     = -1;
			$args['fields']     = 'ID';

			$users = get_users( $args );

			$display_data['contacts_left'] = empty( $users ) ? 'Sync completed' : count( $users ) . ' waiting to sync';

			$object_data['reg_users']      = self::hubwoo_make_db_query( 'total_synced_contacts' );
			$object_data['guest_users']    = self::hubwoo_make_db_query( 'total_synced_guest_contacts' );
			$object_data['deal']           = self::hubwoo_make_db_query( 'total_synced_deals' );
			$object_data['product']        = self::hubwoo_make_db_query( 'total_synced_products' );
			$object_data['total_products'] = self::hubwoo_make_db_query( 'total_products_to_sync' );

			array_walk(
				$object_data,
				function( $data, $type ) use ( &$display_data ) {
					if ( ! empty( $data ) ) {
						$data                  = (array) $data[0];
						$data                  = array_pop( $data );
						$display_data[ $type ] = ! empty( $data ) ? $data : 0;
					}
				}
			);
			$display_data['products_left'] = intval( $display_data['total_products'] ) - intval( $display_data['product'] );
			$display_data['products_left'] = $display_data['products_left'] > 0 ? $display_data['products_left'] . ' waiting to sync' : 'Sync completed';
			return $display_data;
		}

		/**
		 * Create an ETA for the current running sync.
		 *
		 * @since 1.0.0
		 * @param int $current current sync count.
		 * @param int $total total sync count.
		 * @param int $timer scheduled timer.
		 * @param int $limiter limiter of the sync.
		 * @return string $eta_string  returns the calculated eta string.
		 */
		public static function hubwoo_create_sync_eta( $current, $total, $timer, $limiter ) {
			$eta_string       = '';
			$left_items_timer = round( ( $total - $current ) / $limiter ) * $timer;

			if ( $left_items_timer > 90 ) {
				$float_timer = number_format( ( $left_items_timer / 60 ), 2 );
				$hours       = intval( $float_timer );
				$minutes     = round( ( $float_timer - $hours ) * 0.6 );
				$eta_string  = "{$hours} hours and {$minutes} minutes ";
			} elseif ( 0 == $left_items_timer ) {
				$eta_string = 'less than a minute';
			} else {
				$eta_string = "{$left_items_timer} minutes";
			}
			return $eta_string;
		}

		/**
		 * Handle the Contact sync for failed cases.
		 *
		 * @since 1.0.0
		 * @param array $response response from HubSpot.
		 * @param array $contact_data prepared contact data.
		 * @param array $args data and type of sync object.
		 * @return void.
		 */
		public static function hubwoo_handle_contact_sync( $response, &$contact_data, $args = array() ) {

			$response = json_decode( $response['body'], true );

			if ( ! empty( $response['invalidEmails'] ) ) {

				$failed_indexes = array_column( $response['failureMessages'], 'index' );

				if ( ! empty( $failed_indexes ) ) {

					array_walk(
						$failed_indexes,
						function( $index ) use ( &$contact_data ) {
							unset( $contact_data[ $index ] );
						}
					);

					$contact_data = array_values( $contact_data );

					HubWooConnectionMananager::get_instance()->create_or_update_contacts( $contact_data, $args );
				}
			} else {
				Hubwoo_Admin::hubwoo_split_contact_batch( $contact_data );
			}
		}

		/**
		 * Handle the Contact sync for failed cases.
		 *
		 * @since 1.0.0
		 * @param array  $ids object ids to be marked.
		 * @param string $type type of object id.
		 * @return void.
		 */
		public static function hubwoo_marked_sync( $ids, $type ) {

			if ( ! empty( $ids ) ) {

				$emails    = '';
				$user_data = array();

				$method_calls['user']  = array(
					'get'        => 'get_user_meta',
					'update'     => 'update_user_meta',
					'get_key'    => 'billing_email',
					'update_key' => 'hubwoo_pro_user_data_change',
				);
				$method_calls['order'] = array(
					'get'        => 'get_post_meta',
					'update'     => 'update_post_meta',
					'get_key'    => '_billing_email',
					'update_key' => 'hubwoo_pro_guest_order',
				);
				$unsynced_ids          = array_filter(
					$ids,
					function( $id ) use ( &$method_calls, &$type ) {
						return empty( $method_calls[ $type ]['get']( $id, 'hubwoo_user_vid', true ) );
					}
				);

				if ( empty( $unsynced_ids ) && 'user' == $type ) {
					foreach ( $ids as $id ) {
						$method_calls[ $type ]['update']( $id, $method_calls[ $type ]['update_key'], 'synced' );
					}
					return;
				}

				switch ( $type ) {
					case 'user':
						foreach ( $unsynced_ids as $id ) {
							$user      = get_user_by( 'id', $id );
							$usr_email = $user->data->user_email;
							if ( ! empty( $usr_email ) ) {
								$usr_email               = strtolower( $usr_email );
								$user_data[ $usr_email ] = $id;
								$emails                 .= 'email=' . $usr_email . '&';
							}
						}
						break;
					case 'order':
						foreach ( $unsynced_ids as $id ) {
							$usr_email = $method_calls[ $type ]['get']( $id, $method_calls[ $type ]['get_key'], true );
							if ( ! empty( $usr_email ) ) {
								$usr_email               = strtolower( $usr_email );
								$user_data[ $usr_email ] = $id;
								$emails                 .= 'email=' . $usr_email . '&';
							}
						}
						break;
					default:
						return;
				}

				$response = HubWooConnectionMananager::get_instance()->hubwoo_get_batch_vids( $emails );

				if ( 200 == $response['status_code'] && ! empty( $response['body'] ) ) {
					$users = json_decode( $response['body'], true );
					if ( 0 == count( $users ) ) {
						return; }

					foreach ( $users as $vid => $data ) {
						if ( ! empty( $data['properties']['email'] ) ) {
							if ( array_key_exists( $data['properties']['email']['value'], $user_data ) ) {
								$method_calls[ $type ]['update']( $user_data[ $data['properties']['email']['value'] ], 'hubwoo_user_vid', $vid );
								$method_calls[ $type ]['update']( $user_data[ $data['properties']['email']['value'] ], $method_calls[ $type ]['update_key'], 'synced' );
							}
						}
					}
				}
			}
		}

		/**
		 * Displays Cron status.
		 *
		 * @since 1.0.0
		 * @return array.
		 */
		public static function hubwoo_cron_status() {

			$cron_status = array(
				'status' => true,
				'type'   => 'Cron Events are working fine on your website.',
			);

			if ( ! as_next_scheduled_action( 'hubwoo_cron_schedule' ) ) {
				as_schedule_recurring_action( time(), 300, 'hubwoo_cron_schedule' );
			}

			if ( ! as_next_scheduled_action( 'hubwoo_deals_sync_check' ) ) {
				as_schedule_recurring_action( time(), 300, 'hubwoo_deals_sync_check' );
			}

			if ( ! as_next_scheduled_action( 'hubwoo_products_sync_check' ) ) {
				as_schedule_recurring_action( time(), 300, 'hubwoo_products_sync_check' );
			}

			if ( ! as_next_scheduled_action( 'hubwoo_cron_schedule' ) || ! as_next_scheduled_action( 'hubwoo_deals_sync_check' ) ) {
				$cron_status['status'] = false;
				$cron_status['type']   = esc_html__( 'You are having issues with your HubSpot for WooCommerce sync. Please read this doc on how to fix your integration.', 'hubspot-for-woocommerce' );
			}

			return $cron_status;
		}

		/**
		 * Onboarding questionaire Model.
		 *
		 * @since 1.0.0
		 * @return array.
		 */
		public static function hubwoo_onboarding_questionaire() {

			return array(
				'mwb_hs_familarity'    => array(
					'allow'   => '',
					'label'   => 'Which of these sounds most like your HubSpot ability?',
					'options' => array(
						'',
						'I have never used a CRM before',
						'I\'m new to HubSpot, but I have used a CRM before',
						'I know my way around HubSpot pretty well',
					),
				),
				'mwb_woo_familarity'   => array(
					'allow'   => '',
					'label'   => 'Which of these sounds most like your WooCommerce ability?',
					'options' => array(
						'',
						'I have never used an e-Commerce platform before',
						'I\'m new to WooCommerce, but I have used an e-Commerce platform before',
						'I know my way around WooCommerce pretty well',
					),
				),
				'which_hubspot_packages_do_you_currently_use_' => array(
					'allow'   => 'multiple',
					'label'   => 'Which HubSpot plan you are using?',
					'options' => array(
						'I don’t currently use HubSpot',
						'HubSpot Free',
						'Marketing Starter',
						'Marketing Pro',
						'Marketing Enterprise',
						'Sales Starter',
						'Sales Pro',
						'Sales Enterprise',
						'Service Starter',
						'Service Pro',
						'Service Enterprise',
						'CMS Hub',
						'Other',
					),
				),
			);
		}

		/**
		 * Form Model Data.
		 *
		 * @since 1.0.0
		 * @param stgring $type type of form model.
		 * @return array.
		 */
		public static function form_data_model( $type ) {
			switch ( $type ) {
				case HubwooConst::CHECKOUTFORM:
					return array(
						array(
							'name'            => HubwooConst::CHECKOUTFORMNAME,
							'submitText'      => 'Submit',
							'formFieldGroups' => array(
								array(
									'fields' => array(
										array(
											'name'     => 'firstname',
											'label'    => 'First Name',
											'required' => false,
										),
										array(
											'name'     => 'lastname',
											'label'    => 'Last Name',
											'required' => false,
										),
										array(
											'name'     => 'email',
											'label'    => 'Email',
											'required' => false,
										),
									),
								),
							),
						),
					);
				default:
					return array();
			}
		}

		/**
		 * Hubwoo update product
		 *
		 * @param array $properties product properties.
		 * @param int $hubwoo_ecomm_pro_id is the id of product.
		 * @return array $response.
		 */
		public static function hubwoo_update_products( $properties, $hubwoo_ecomm_pro_id ) {
			if ( ! empty( $properties ) ) {
				$flag = true;

				if ( self::is_access_token_expired() ) {
					$hapikey = HUBWOO_CLIENT_ID;
					$hseckey = HUBWOO_SECRET_ID;
					$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

					if ( ! $status ) {
						$flag = false;
					}
				}
				if ( $flag ) {
					$response = HubWooConnectionMananager::get_instance()->update_existing_products( $hubwoo_ecomm_pro_id, $properties );
				}
				return $response;
			}
		}

		/**
		 * Get contact sync status.
		 *
		 * @since 1.2.7
		 * @return int $unique_users number of unique users.
		 */
		public static function hubwoo_get_total_contact_need_sync() {

			$unique_users = count( get_users() );

			$order_args = array(
				'return'                 => 'ids',
				'limit'                  => -1,
				'type'                   => wc_get_order_types(),
				'status'                 => array_keys( wc_get_order_statuses() ),
				'customer'               => 0,
			);

			$guest_orders = wc_get_orders( $order_args );
			$unique_users += count($guest_orders);

			return $unique_users;
		}
	}
}
