<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin
 */
class Hubwoo_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// let's modularize our codebase, all the admin actions in one function.
		$this->admin_actions();
	}

	/**
	 * All admin actions.
	 *
	 * @since 1.0.0
	 */
	public function admin_actions() {

		// add submenu hubspot in woocommerce top menu.
		add_action( 'admin_menu', array( &$this, 'add_hubwoo_submenu' ) );
		// add filter.
		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'hubwoo_ignore_guest_synced' ), 10, 2 );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$screen = get_current_screen();

		if ( isset( $screen->id ) && 'woocommerce_page_hubwoo' === $screen->id || 'edit-shop_order' === $screen->id ) {

			wp_enqueue_style( 'hubwoo-admin-style', plugin_dir_url( __FILE__ ) . 'css/hubwoo-admin.css', array(), $this->version, 'all' );
			wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			wp_enqueue_style( 'woocommerce_admin_menu_styles' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_style( 'hubwoo_jquery_ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', array(), $this->version );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$screen = get_current_screen();

		if ( isset( $screen->id ) && 'woocommerce_page_hubwoo' === $screen->id || 'edit-shop_order' === $screen->id ) {

			wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'wc-enhanced-select' ), WC_VERSION, true );
			wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.js', array( 'jquery' ), WC_VERSION, true );

			$locale            = localeconv();
			$decimal           = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
			$decimal_seperator = wc_get_price_decimal_separator();
			$params            = array(
				/* translators: %s: decimal */
				'i18n_decimal_error'               => sprintf( esc_html__( 'Please enter in decimal (%s) format without thousand separators.', 'hubspot-for-woocommerce' ), $decimal ),
				/* translators: %s: decimal_separator */
				'i18n_mon_decimal_error'           => sprintf( esc_html__( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'hubspot-for-woocommerce' ), $decimal_seperator ),
				'i18n_country_iso_error'           => esc_html__( 'Please enter in country code with two capital letters.', 'hubspot-for-woocommerce' ),
				'i18_sale_less_than_regular_error' => esc_html__( 'Please enter in a value less than the regular price.', 'hubspot-for-woocommerce' ),
				'decimal_point'                    => $decimal,
				'mon_decimal_point'                => $decimal_seperator,
				'strings'                          => array(
					'import_products' => esc_html__( 'Import', 'hubspot-for-woocommerce' ),
					'export_products' => esc_html__( 'Export', 'hubspot-for-woocommerce' ),
				),
				'urls'                             => array(
					'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
					'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
				),
			);
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
			wp_enqueue_script( 'woocommerce_admin' );
			wp_register_script( 'hubwoo_admin_script', plugin_dir_url( __FILE__ ) . 'js/hubwoo-admin.js', array( 'jquery' ), $this->version, true );
			wp_localize_script(
				'hubwoo_admin_script',
				'hubwooi18n',
				array(
					'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
					'hubwooSecurity'        => wp_create_nonce( 'hubwoo_security' ),
					'hubwooWentWrong'       => esc_html__( 'Something went wrong, please try again later!', 'hubspot-for-woocommerce' ),
					'hubwooSuccess'         => esc_html__( 'Setup is completed successfully!', 'hubspot-for-woocommerce' ),
					'hubwooMailFailure'     => esc_html__( 'Mail not sent', 'hubspot-for-woocommerce' ),
					'hubwooMailSuccess'     => esc_html__( 'Mail Sent Successfully. We will get back to you soon.', 'hubspot-for-woocommerce' ),
					'hubwooAccountSwitch'   => esc_html__( 'Want to continue to switch to new HubSpot account? This cannot be reverted and will require running the whole setup again.', 'hubspot-for-woocommerce' ),
					'hubwooRollback'        => esc_html__( 'Doing rollback will require running the whole setup again. Continue?' ),
					'hubwooOverviewTab'     => admin_url() . 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-overview',
					'hubwooNoListsSelected' => esc_html__( 'Please select a list to proceed', 'hubspot-for-woocommerce' ),
					'hubwooOcsSuccess'      => esc_html__( 'Congratulations !! Your data has been synced successfully.', 'hubspot-for-woocommerce' ),
					'hubwooOcsError'        => esc_html__( 'Something went wrong, Please check the error log and try re-sync your data.', 'hubspot-for-woocommerce' ),
				)
			);
			wp_enqueue_script( 'hubwoo_admin_script' );
		}
	}

	/**
	 * Add hubspot submenu in woocommerce menu..
	 *
	 * @since 1.0.0
	 */
	public function add_hubwoo_submenu() {

		add_submenu_page( 'woocommerce', esc_html__( 'HubSpot', 'hubspot-for-woocommerce' ), esc_html__( 'HubSpot', 'hubspot-for-woocommerce' ), 'manage_woocommerce', 'hubwoo', array( &$this, 'hubwoo_configurations' ) );
	}

	/**
	 * All the configuration related fields and settings.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_configurations() {

		include_once HUBWOO_ABSPATH . 'admin/templates/hubwoo-main-template.php';
	}

	/**
	 * Handle a custom 'hubwoo_pro_guest_order' query var to get orders with the 'hubwoo_pro_guest_order' meta.
	 *
	 * @param array $query - Args for WP_Query.
	 * @param array $query_vars - Query vars from WC_Order_Query.
	 * @return array modified $query
	 */
	public function hubwoo_ignore_guest_synced( $query, $query_vars ) {

		if ( ! empty( $query_vars['hubwoo_pro_guest_order'] ) ) {

			$query['meta_query'][] = array(
				'key'     => '_billing_email',
				'value'   => '',
				'compare' => 'NOT IN',
			);

			$query['meta_query'] = array(
				array(
					'relation' => 'AND',
					array(
						'key'     => 'hubwoo_pro_guest_order',
						'value'   => esc_attr( $query_vars['hubwoo_pro_guest_order'] ),
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_customer_user',
						'value'   => 0,
						'compare' => '=',
					),
				),
			);
		}

		return $query;
	}

	/**
	 * Generating access token.
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_redirect_from_hubspot() {

		if ( isset( $_GET['code'] ) ) {

			$hapikey = HUBWOO_CLIENT_ID;
			$hseckey = HUBWOO_SECRET_ID;

			if ( $hapikey && $hseckey ) {
				if ( ! Hubwoo::is_valid_client_ids_stored() ) {

					if ( HubWooConnectionMananager::get_instance()->hubwoo_fetch_access_token_from_code( $hapikey, $hseckey ) == true ) {
						$hubwoo_connection_complete = 'yes';
					} else {
						$hubwoo_connection_complete = 'no';
					}

					global $hubwoo;
					$hubwoo->hubwoo_owners_email_info();
					if ( 'yes' == $hubwoo_connection_complete ) {
						update_option( 'hubwoo_connection_complete', 'yes' );
						delete_option( 'hubwoo_connection_issue' );
						wp_safe_redirect( admin_url() . 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-overview&hubwoo_key=grp-pr-setup' );
					} else {
						update_option( 'hubwoo_connection_complete', 'no' );
						update_option( 'hubwoo_connection_issue', 'yes' );
						wp_safe_redirect( admin_url() . 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-overview&hubwoo_key=connection-setup' );
					}
				}
			}
		}
	}

	/**
	 * WooCommerce privacy policy
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_pro_add_privacy_message() {

		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {

			$content = '<p>' . esc_html__( 'We use your email to send your Orders related data over HubSpot.', 'hubspot-for-woocommerce' ) . '</p>';

			$content .= '<p>' . esc_html__( 'HubSpot is an inbound marketing and sales platform that helps companies attract visitors, convert leads, and close customers.', 'hubspot-for-woocommerce' ) . '</p>';

			$content .= '<p>' . esc_html__( 'Please see the ', 'hubspot-for-woocommerce' ) . '<a href="https://www.hubspot.com/data-privacy/gdpr" target="_blank" >' . esc_html__( 'HubSpot Data Privacy', 'hubspot-for-woocommerce' ) . '</a>' . esc_html__( ' for more details.', 'hubspot-for-woocommerce' ) . '</p>';

			if ( $content ) {

				wp_add_privacy_policy_content( esc_html__( 'HubSpot for WooCommerce', 'hubspot-for-woocommerce' ), $content );
			}
		}
	}

	/**
	 * General setting tab fields for hubwoo old customers sync
	 *
	 * @return array  woocommerce_admin_fields acceptable fields in array.
	 * @since 1.0.0
	 */
	public static function hubwoo_customers_sync_settings() {

		$settings = array();

		if ( ! function_exists( 'get_editable_roles' ) ) {

			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$existing_user_roles = self::get_all_user_roles();

		$settings[] = array(
			'title' => esc_html__( 'Export your users and customers to HubSpot', 'hubspot-for-woocommerce' ),
			'id'    => 'hubwoo_customers_settings_title',
			'type'  => 'title',
		);

		$settings[] = array(
			'title'             => esc_html__( 'Select user role', 'hubspot-for-woocommerce' ),
			'id'                => 'hubwoo_customers_role_settings',
			'type'              => 'multiselect',
			'desc'              => esc_html__( 'Select a user role from the dropdown. Default will be all user roles.', 'hubspot-for-woocommerce' ),
			'options'           => $existing_user_roles,
			'desc_tip'          => true,
			'class'             => 'hubwoo-ocs-input-change',
			'custom_attributes' => array(
				'data-keytype' => esc_html__( 'user-role', 'hubspot-for-woocommerce' ),
			),
		);

		$settings[] = array(
			'title' => esc_html__( 'Select a time period', 'hubspot-for-woocommerce' ),
			'id'    => 'hubwoo_customers_manual_sync',
			'class' => 'hubwoo-ocs-input-change',
			'type'  => 'checkbox',
			'desc'  => esc_html__( 'Date range for user / order sync', 'hubspot-for-woocommerce' ),
		);

		$settings[] = array(
			'title'             => esc_html__( 'Users registered from date', 'hubspot-for-woocommerce' ),
			'id'                => 'hubwoo_users_from_date',
			'type'              => 'text',
			'placeholder'       => 'dd-mm-yyyy',
			'default'           => gmdate( 'd-m-Y' ),
			'desc'              => esc_html__( 'From which date you want to sync the users, select that', 'hubspot-for-woocommerce' ),
			'desc_tip'          => true,
			'class'             => 'date-picker hubwoo-date-range hubwoo-ocs-input-change',
			'custom_attributes' => array(
				'data-keytype' => esc_html__( 'from-date', 'hubspot-for-woocommerce' ),
			),
		);
		$settings[] = array(
			'title'             => esc_html__( 'Users registered upto date', 'hubspot-for-woocommerce' ),
			'id'                => 'hubwoo_users_upto_date',
			'type'              => 'text',
			'default'           => gmdate( 'd-m-Y' ),
			'placeholder'       => esc_html__( 'dd-mm-yyyy', 'hubspot-for-woocommerce' ),
			'desc'              => esc_html__( 'Upto which date you want to sync the users, select that date', 'hubspot-for-woocommerce' ),
			'desc_tip'          => true,
			'class'             => 'date-picker hubwoo-date-range hubwoo-ocs-input-change',
			'custom_attributes' => array(
				'data-keytype' => esc_html__( 'upto-date', 'hubspot-for-woocommerce' ),
			),
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'hubwoo_customers_settings_end',
		);
		return $settings;
	}

	/**
	 * Get all WordPress user roles in formatted way
	 *
	 * @return array  $existing_user_roles user roles in an array.
	 * @since 1.0.0
	 */
	public static function get_all_user_roles() {

		$existing_user_roles = array();

		global $wp_roles;

		$user_roles = ! empty( $wp_roles->role_names ) ? $wp_roles->role_names : array();

		if ( is_array( $user_roles ) && count( $user_roles ) ) {

			foreach ( $user_roles as $role => $role_info ) {

				$role_label = ! empty( $role_info ) ? $role_info : $role;

				$existing_user_roles[ $role ] = $role_label;
			}
			$existing_user_roles['guest_user'] = 'Guest User';
		}

		return $existing_user_roles;
	}

	/**
	 * Check if the user has cart as abandoned.
	 *
	 * @param array $properties array of contact properties.
	 * @return bool  $flag true/false.
	 * @since 1.0.0
	 */
	public static function hubwoo_check_for_cart( $properties ) {

		$flag = false;

		if ( ! empty( $properties ) && is_array( $properties ) ) {
			$key = array_search( 'current_abandoned_cart', array_column( $properties, 'property' ) );
			if ( false !== $key ) {
				$value = $properties[ $key ]['value'];
				$flag  = 'yes' === $value ? true : false;
			}
		}

		return $flag;
	}


	/**
	 * Check if the key in properties contains specific values
	 *
	 * @since 1.0.0
	 * @param string $key key name to compare.
	 * @param string $value value of the property to check.
	 * @param array  $properties array of contact properties.
	 * @return bool  $flag true/false.
	 */
	public static function hubwoo_check_for_properties( $key, $value, $properties ) {
		$flag = false;

		if ( is_array( $properties ) ) {

			$prop_index = array_search( $key, array_column( $properties, 'property' ) );

			if ( array_key_exists( $prop_index, $properties ) ) {
				$property_value = $properties[ $prop_index ]['value'];
				$flag           = $property_value == $value ? true : false;
			}
		}

		return $flag;
	}

	/**
	 * Unset the workflow/ROI properties to avoid update on data sync.
	 *
	 * @since    1.0.0
	 * @param    array $properties      contact properties data.
	 */
	public function hubwoo_reset_workflow_properties( $properties ) {

		$workflow_properties = HubWooContactProperties::get_instance()->_get( 'properties', 'roi_tracking' );
		if ( is_array( $workflow_properties ) && count( $workflow_properties ) ) {
			foreach ( $workflow_properties as $single_property ) {
				$group_name = isset( $single_property['name'] ) ? $single_property['name'] : '';
				if ( ! empty( $group_name ) ) {
					if ( 'customer_new_order' !== $group_name ) {
						$properties = self::hubwoo_unset_property( $properties, $group_name );
					}
				}
			}
		}
		return $properties;
	}

	/**
	 * Unset the key from array of properties.
	 *
	 * @since    1.0.0
	 * @param    array $properties      contact properties data.
	 * @param    array $key             key to unset.
	 */
	public static function hubwoo_unset_property( $properties, $key ) {

		if ( ! empty( $properties ) && ! empty( $key ) ) {
			if ( array_key_exists( $key, $properties ) ) {
				unset( $properties[ $key ] );
			}
		}
		return $properties;
	}

	/**
	 * Getting next execution for realtime cron, priorities task for old users.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_cron_notification() {

		if ( 'yes' == get_option( 'hubwoo-cron-notice-dismiss', 'no' ) ) {
			return;
		}

		?>
		<style type="text/css">
		.hubwoo-btn--notific {
			display: inline-block;
			padding: 13px 30px;
			text-decoration: none;
			background: #ff7a59;
			color: #fff!important;
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 1px;
			border-radius: 3px;
			box-shadow: none!important;
			border: 2px solid #ff7a59;
			padding: 5px;
		}
		.hubwoo-pl-notice {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 5px 0px 5px 10px;
		}	
		.hubwoo-wrapper-notice {
			justify-content: space-between;
		}
		.hubwoo-close-size{
			font-size: 20px;
			color: gainsboro;
			vertical-align: middle;
		}
		</style>
		<?php

		if ( ! Hubwoo::hubwoo_cron_status()['status'] ) {
			?>
			<div class="notice notice-error">
				<div class="hubwoo-ocs-options hubwoo-pl-notice">
					<p>
						<strong><?php echo esc_textarea( Hubwoo::hubwoo_cron_status()['type'], 'hubwoo' ); ?></strong>
					</p>
					<div class="hubwoo-wrapper-notice">
						<a target="_blank" href="https://support.makewebbetter.com/hubspot-knowledge-base/how-to-troubleshoot-your-hubspot-for-woocommerce-syncing-issues/" class="hubwoo-btn--notific"><?php esc_html_e( 'View Document', 'hubspot-for-woocommerce' ); ?></a>
						<a class="hubwoo-close-size fa fa-times" href="?action=dismiss-hubwoo-notice"></a>
					</div>
				</div>
			</div>	
			<?php
		}
	}

	/**
	 * Updating users/orders list to be updated on hubspot on order status transition.
	 *
	 * @since 1.0.0
	 * @param int $order_id order id.
	 */
	public function hubwoo_update_order_changes( $order_id ) {

		if ( ! empty( $order_id ) ) {

			$user_id = (int) get_post_meta( $order_id, '_customer_user', true );

			if ( 0 !== $user_id && 0 < $user_id ) {

				update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
			} else {

				if ( 'yes' === get_option( 'hubwoo_pro_guest_sync_enable', 'yes' ) ) {
					update_post_meta( $order_id, 'hubwoo_pro_guest_order', 'yes' );
				}
			}
		}
	}

	/**
	 * Updating users list to be updated on hubspot when admin changes the role forcefully.
	 *
	 * @since 1.0.0
	 * @param int $user_id user id.
	 */
	public function hubwoo_add_user_toupdate( $user_id ) {

		if ( ! empty( $user_id ) ) {

			update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
		}
	}

	/**
	 * New active groups for subscriptions.
	 *
	 * @since 1.0.0
	 * @param array $values list of pre-defined groups.
	 */
	public function hubwoo_subs_groups( $values ) {

		$values[] = array(
			'name'        => 'subscriptions_details',
			'displayName' => __( 'Subscriptions Details', 'hubspot-for-woocommerce' ),
		);

		return $values;
	}

	/**
	 * New active groups for subscriptions
	 *
	 * @since 1.0.0
	 * @param array $active_groups list of active groups.
	 */
	public function hubwoo_active_subs_groups( $active_groups ) {

		$active_groups[] = 'subscriptions_details';

		return $active_groups;
	}

	/**
	 * Realtime sync for HubSpot CRM.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_cron_schedule() {

		if ( 'yes' === get_option( 'hubwoo_connection_issue', 'no' ) ) {
			return;
		}

		$contacts = array();

		$args['meta_query'] = array(

			array(
				'key'     => 'hubwoo_pro_user_data_change',
				'value'   => 'yes',
				'compare' => '==',
			),
		);

		$args['role__in'] = get_option( 'hubwoo-selected-user-roles', array() );

		$args['number'] = 5;

		$args['fields'] = 'ID';

		$hubwoo_updated_user = get_users( $args );
		$hubwoo_unique_users = apply_filters( 'hubwoo_users', $hubwoo_updated_user );
		if ( ! empty( $hubwoo_unique_users ) ) {
			$hubwoo_unique_users = array_unique( $hubwoo_unique_users );
			$contacts            = HubwooDataSync::get_sync_data( $hubwoo_unique_users );
		}

		if ( ! empty( $contacts ) ) {

			$flag = true;

			if ( Hubwoo::is_access_token_expired() ) {
				$hapikey = HUBWOO_CLIENT_ID;
				$hseckey = HUBWOO_SECRET_ID;
				$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

				if ( ! $status ) {

					$flag = false;
				}
			}

			if ( $flag ) {
				unset( $args );
				$args['ids']  = $hubwoo_unique_users;
				$args['type'] = 'user';
				$response     = HubWooConnectionMananager::get_instance()->create_or_update_contacts( $contacts, $args );
				if ( ( count( $contacts ) ) && 400 == $response['status_code'] ) {
					Hubwoo::hubwoo_handle_contact_sync( $response, $contacts, $args );
				}
			}
		}
		unset( $hubwoo_unique_users );
		unset( $contacts );
		unset( $args );

		$query = new WP_Query();

		$contacts = array();

		$hubwoo_orders = $query->query(
			array(
				'post_type'           => 'shop_order',
				'posts_per_page'      => 5,
				'post_status'         => 'any',
				'orderby'             => 'date',
				'order'               => 'desc',
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'meta_query'          => array(
					array(
						'key'     => 'hubwoo_pro_guest_order',
						'compare' => '==',
						'value'   => 'yes',
					),
				),
			)
		);
		$hubwoo_orders = apply_filters( 'hubwoo_guest_orders', $hubwoo_orders );

		if ( ! empty( $hubwoo_orders ) ) {
			$guest_contacts = HubwooDataSync::get_guest_sync_data( $hubwoo_orders );

			$args['type'] = 'order';
			$args['ids']  = $hubwoo_orders;

			$response = HubWooConnectionMananager::get_instance()->create_or_update_contacts( $guest_contacts, $args );

			if ( ( count( $guest_contacts ) ) && 400 == $response['status_code'] ) {
				Hubwoo::hubwoo_handle_contact_sync( $response, $guest_contacts, $args );
			}
		}

		$hubwoo_guest_cart = get_option( 'mwb_hubwoo_guest_user_cart', array() );

		$guest_abandoned_carts = array();

		if ( ! empty( $hubwoo_guest_cart ) ) {
			foreach ( $hubwoo_guest_cart as $key => &$single_cart ) {

				if ( ! empty( $single_cart['email'] ) ) {
					if ( ! empty( $single_cart['sent'] ) && 'yes' == $single_cart['sent'] ) {
						if ( empty( $single_cart['cartData'] ) || empty( $single_cart['cartData']['cart'] ) ) {
							unset( $hubwoo_guest_cart[ $key ] );
						}
						continue;
					}
					$guest_user_properties = apply_filters( 'hubwoo_pro_track_guest_cart', $single_cart['email'], array() );

					if ( self::hubwoo_check_for_cart( $guest_user_properties ) ) {

						$single_cart['sent'] = 'yes';
					} elseif ( ! self::hubwoo_check_for_cart( $guest_user_properties ) && self::hubwoo_check_for_cart_contents( $guest_user_properties ) ) {

						$single_cart['sent'] = 'yes';
					}

					if ( ! empty( $guest_user_properties ) ) {
						$guest_abandoned_carts[] = array(
							'email'      => $single_cart['email'],
							'properties' => $guest_user_properties,
						);
					}
				} else {
					unset( $hubwoo_guest_cart[ $key ] );
				}
			}

			update_option( 'mwb_hubwoo_guest_user_cart', $hubwoo_guest_cart );
		}
		if ( count( $guest_abandoned_carts ) ) {

			$chunked_array = array_chunk( $guest_abandoned_carts, 50, false );

			if ( ! empty( $chunked_array ) ) {

				foreach ( $chunked_array as $single_chunk ) {
					$response = HubWooConnectionMananager::get_instance()->create_or_update_contacts( $single_chunk );
					if ( ( count( $single_chunk ) ) && 400 == $response['status_code'] ) {
						Hubwoo::hubwoo_handle_contact_sync( $response, $single_chunk );
					}
				}
			}
		}
	}

	/**
	 * Check if the user has empty cart.
	 *
	 * @since    1.0.0
	 * @param    array $properties list of properties.
	 */
	public static function hubwoo_check_for_cart_contents( $properties ) {

		$flag = false;

		if ( ! empty( $properties ) ) {

			foreach ( $properties as $single_record ) {

				if ( ! empty( $single_record['property'] ) ) {

					if ( 'abandoned_cart_products' == $single_record['property'] ) {

						if ( empty( $single_record['value'] ) ) {

							$flag = true;
							break;
						}
					}
				}
			}
		}

		return $flag;
	}

	/**
	 * Split contact batch on failure.
	 *
	 * @since    1.0.0
	 * @param    array $contacts array of contacts for batch upload.
	 */
	public static function hubwoo_split_contact_batch( $contacts ) {

		$contacts_chunk = array_chunk( $contacts, ceil( count( $contacts ) / 2 ) );

		$response_chunk = array();

		if ( isset( $contacts_chunk[0] ) ) {

			$response_chunk = HubWooConnectionMananager::get_instance()->create_or_update_contacts( $contacts_chunk[0] );
			if ( isset( $response_chunk['status_code'] ) && 400 == $response_chunk['status_code'] ) {

				$response_chunk = self::hubwoo_single_contact_upload( $contacts_chunk[0] );
			}
		}
		if ( isset( $contacts_chunk[1] ) ) {

			$response_chunk = HubWooConnectionMananager::get_instance()->create_or_update_contacts( $contacts_chunk[1] );
			if ( isset( $response_chunk['status_code'] ) && 400 == $response_chunk['status_code'] ) {

				$response_chunk = self::hubwoo_single_contact_upload( $contacts_chunk[1] );
			}
		}

		return $response_chunk;
	}

	/**
	 * Fallback for single contact.
	 *
	 * @since    1.0.0
	 * @param    array $contacts array of contacts for batch upload.
	 */
	public static function hubwoo_single_contact_upload( $contacts ) {

		if ( ! empty( $contacts ) ) {

			foreach ( $contacts as $single_contact ) {

				$response = HubWooConnectionMananager::get_instance()->create_or_update_contacts( array( $single_contact ) );
			}
		}

		return $response;
	}


	/**
	 * Populating Orders column that has been synced as deal.
	 *
	 * @since    1.0.0
	 * @param    array $column    Array of available columns.
	 * @param    int   $post_id   Current Order post id.
	 */
	public function hubwoo_order_cols_value( $column, $post_id ) {

		$deal_id   = get_post_meta( $post_id, 'hubwoo_ecomm_deal_id', true );
		$portal_id = get_option( 'hubwoo_pro_hubspot_id', '' );
		$user_id   = get_post_meta( $post_id, '_customer_user', true );
		$user_vid  = $user_id > 0 ? get_user_meta( $user_id, 'hubwoo_user_vid', true ) : get_post_meta( $post_id, 'hubwoo_user_vid', true );

		if ( get_option( 'hubwoo_background_process_running', false ) || get_option( 'hubwoo_contact_vid_update', 0 ) ) {
			$contact_tip    = 'Contacts are being synced';
			$contact_status = 'yes';
		} else {
			$contact_tip    = 'Sync Contacts';
			$contact_status = 'no';
		}

		if ( 1 == get_option( 'hubwoo_deals_sync_running', 0 ) ) {
			$deal_tip    = 'Deals are being synced';
			$deal_status = 'yes';
		} else {
			$deal_tip    = 'Sync Deals';
			$deal_status = 'no';
		}

		switch ( $column ) {

			case 'hubwoo-deal-sync':
				?>
				<p style="text-align:center;">				  
				<?php
				if ( ! empty( $user_vid ) ) {
					?>
					<a class="hubwoo-action-icon" data-tip="View Contact in HubSpot" data-status='yes' data-type='contact' target="_blank" href="<?php echo esc_url( 'https://app.hubspot.com/contacts/' . $portal_id . '/contact/' . $user_vid . '/' ); ?>"><img src="<?php echo esc_url( HUBWOO_URL . 'admin/images/contact.png' ); ?>"></a>
					<?php
				} else {
					?>
					<a style="opacity: 0.4;" data-sync-status='<?php echo esc_attr( $contact_status ); ?>' data-tip="<?php echo esc_attr( $contact_tip ); ?>" class="hubwoo-action-icon" data-status='no' data-type='contact' href="javascript:void"><img src="<?php echo esc_url( HUBWOO_URL . 'admin/images/contact.png' ); ?>"></a>
					<?php
				}

				if ( ! empty( $deal_id ) ) {
					?>
					<a class="hubwoo-action-icon" data-tip="View Deal in HubSpot" target="_blank" data-status='yes' data-type='deal' href="<?php echo esc_url( 'https://app.hubspot.com/contacts/' . $portal_id . '/deal/' . $deal_id . '/' ); ?>"><img src="<?php echo esc_url( HUBWOO_URL . 'admin/images/deal.png' ); ?>"></a>
					<?php
				} else {
					?>
					<a style="opacity: 0.4;" data-sync-status='<?php echo esc_attr( $deal_status ); ?>' data-tip="<?php echo esc_attr( $deal_tip ); ?>" class="hubwoo-action-icon" data-status='no' data-type='deal' href="javascript:void"><img src="<?php echo esc_url( HUBWOO_URL . 'admin/images/deal.png' ); ?>"></a>
					<?php
				}
				?>
				</p>
				<?php
				break;
		}
	}

	/**
	 * Adding custom column in orders table at backend.
	 *
	 * @since    1.0.0
	 * @param    array $columns    array of columns on orders table.
	 * @return   array    $columns    array of columns on orders table alongwith deal sync column.
	 */
	public function hubwoo_order_cols( $columns ) {

		$portal_id                   = get_option( 'hubwoo_pro_hubspot_id', '' );
		$columns['hubwoo-deal-sync'] = __( 'HubSpot Actions', 'hubspot-for-woocommerce' );
		return $columns;
	}


	/**
	 * General setting for Abandoned Carts.
	 *
	 * @return array  woocommerce_admin_fields acceptable fields in array.
	 * @since 1.0.0
	 */
	public static function hubwoo_abncart_general_settings() {

		$settings = array();

		$settings[] = array(
			'title' => esc_html__( 'Abandoned Cart Settings', 'hubspot-for-woocommerce' ),
			'id'    => 'hubwoo_abncart_settings_title',
			'type'  => 'title',
			'class' => 'hubwoo-abncart-settings-title',
		);
		$settings[] = array(
			'title'   => esc_html__( 'Enable/Disable', 'hubspot-for-woocommerce' ),
			'id'      => 'hubwoo_abncart_enable_addon',
			'desc'    => esc_html__( 'Track Abandoned Carts', 'hubspot-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
		);

		$settings[] = array(
			'title'   => esc_html__( 'Guest Users ', 'hubspot-for-woocommerce' ),
			'id'      => 'hubwoo_abncart_guest_cart',
			'desc'    => esc_html__( 'Track Guest Abandoned Carts', 'hubspot-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
		);

		$settings[] = array(
			'title'             => esc_html__( 'Cart Timer( Minutes )', 'hubspot-for-woocommerce' ),
			'id'                => 'hubwoo_abncart_timing',
			'type'              => 'number',
			'desc'              => esc_html__( 'Set the timer for abandoned cart. Customers abandoned cart data will be updated over HubSpot after the specified timer. Minimum value is 5 minutes.', 'hubspot-for-woocommerce' ),
			'desc_tip'          => true,
			'custom_attributes' => array( 'min' => '5' ),
			'default'           => '5',
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'hubwoo_abncart_settings_end',
		);

		return apply_filters( 'hubwoo_abn_cart_settings', $settings );
	}

	/**
	 * Updating customer properties for abandoned cart on HubSpot.
	 *
	 * @since 1.0.0
	 * @param array $properties list of contact properties.
	 * @param int   $contact_id user ID.
	 */
	public function hubwoo_abncart_contact_properties( $properties, $contact_id ) {

		$cart_product_skus                                  = array();
		$cart_categories                                    = array();
		$cart_products                                      = array();
		$in_cart_products                                   = array();
		$abncart_properties                                 = array();
		$abncart_properties['current_abandoned_cart']       = 'no';
		$abncart_properties['abandoned_cart_date']          = '';
		$abncart_properties['abandoned_cart_counter']       = 0;
		$abncart_properties['abandoned_cart_url']           = '';
		$abncart_properties['abandoned_cart_products_skus'] = '';
		$abncart_properties['abandoned_cart_products_categories'] = '';
		$abncart_properties['abandoned_cart_products']            = '';
		$abncart_properties['abandoned_cart_tax_value']           = 0;
		$abncart_properties['abandoned_cart_subtotal']            = 0;
		$abncart_properties['abandoned_cart_total_value']         = 0;
		$abncart_properties['abandoned_cart_products_html']       = '';

		$hubwoo_abncart_timer = get_option( 'hubwoo_abncart_timing', 5 );
		$customer_cart        = get_user_meta( $contact_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
		$last_time            = get_user_meta( $contact_id, 'hubwoo_pro_last_addtocart', true );

		if ( isset( $customer_cart['cart'] ) && ! empty( $last_time ) ) {

			if ( count( $customer_cart['cart'] ) ) {

				$current_time = time();

				$time_diff = round( abs( $current_time - $last_time ) / 60, 2 );

				$hubwoo_abncart_timer = (int) $hubwoo_abncart_timer;

				if ( $time_diff <= $hubwoo_abncart_timer ) {
					return $properties;
				}
				$last_date                                 = (int) $last_time;
				$last_date                                 = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( $last_date );
				$abncart_properties['abandoned_cart_date'] = $last_date;
				$locale                                    = get_user_meta( $contact_id, 'hubwoo_pro_cart_locale', true );
				$locale                                    = ! empty( $locale ) ? $locale : get_locale();
				$cart_url                                  = apply_filters( 'wpml_permalink', wc_get_cart_url(), $locale, true );

				$abncart_properties['current_abandoned_cart'] = 'yes';

				$cart_products = self::hubwoo_return_abncart_values( $customer_cart['cart'] );

				if ( count( $cart_products ) ) {

					$abncart_properties['abandoned_cart_products_html'] = self::hubwoo_abncart_product_html( $cart_products );
				}
				$cart_url .= '?hubwoo-abncart-retrieve=';
				$cart_prod = array();
				foreach ( $customer_cart['cart'] as $single_cart_item ) {

					$item_id         = $single_cart_item['product_id'];
					$parent_item_sku = get_post_meta( $item_id, '_sku', true );
					if ( ! empty( $single_cart_item['variation_id'] ) ) {
						$item_id = $single_cart_item['variation_id'];
					}

					$qty         = $single_cart_item['quantity'] ? $single_cart_item['quantity'] : 1;
					$cart_prod[] = $item_id . ':' . $qty;
					if ( get_post_status( $item_id ) == 'trash' || get_post_status( $item_id ) == false ) {

						continue;
					}

					$cart_item_sku = get_post_meta( $item_id, '_sku', true );

					if ( empty( $cart_item_sku ) ) {
						$cart_item_sku = $parent_item_sku;
					}

					if ( empty( $cart_item_sku ) ) {
						$cart_item_sku = $item_id;
					}

					$cart_product_skus[] = $cart_item_sku;

					$product_cats_ids = wc_get_product_term_ids( $item_id, 'product_cat' );

					if ( is_array( $product_cats_ids ) && count( $product_cats_ids ) ) {

						foreach ( $product_cats_ids as $cat_id ) {

							$term              = get_term_by( 'id', $cat_id, 'product_cat' );
							$cart_categories[] = $term->slug;
						}
					}

					$post               = get_post( $item_id );
					$post_name          = isset( $post->post_name ) ? $post->post_name : '';
					$product_name       = $post_name . '-' . $item_id;
					$in_cart_products[] = $product_name;
					$abncart_properties['abandoned_cart_counter']++;
					if ( array_key_exists( 'line_total', $single_cart_item ) ) {
						$abncart_properties['abandoned_cart_subtotal'] += $single_cart_item['line_total'];
					} else {
						$product_obj                                    = wc_get_product( $item_id );
						$abncart_properties['abandoned_cart_subtotal'] += $product_obj->get_price() * $single_cart_item['quantity'];
					}

					$abncart_properties['abandoned_cart_tax_value'] += isset( $single_cart_item['line_tax'] ) ? $single_cart_item['line_tax'] : 0;
				}

				$cart_url .= implode( ',', $cart_prod );

				$abncart_properties['abandoned_cart_url']                 = $cart_url;
				$abncart_properties['abandoned_cart_products_skus']       = HubwooGuestOrdersManager::hubwoo_format_array( $cart_product_skus );
				$abncart_properties['abandoned_cart_products_categories'] = HubwooGuestOrdersManager::hubwoo_format_array( $cart_categories );
				$abncart_properties['abandoned_cart_products']            = HubwooGuestOrdersManager::hubwoo_format_array( $in_cart_products );

				if ( ! empty( $abncart_properties['abandoned_cart_subtotal'] ) || ! empty( $abncart_properties['abandoned_cart_tax_value'] ) ) {

					$abncart_properties['abandoned_cart_total_value'] = floatval( $abncart_properties['abandoned_cart_subtotal'] + $abncart_properties['abandoned_cart_tax_value'] );
				}
			} else {

				delete_user_meta( $contact_id, 'hubwoo_pro_user_left_cart' );
				delete_user_meta( $contact_id, 'hubwoo_pro_last_addtocart' );
				delete_user_meta( $contact_id, 'hubwoo_pro_cart_locale' );
				delete_user_meta( $contact_id, 'hubwoo_pro_user_cart_sent' );
			}
		} else {

			delete_user_meta( $contact_id, 'hubwoo_pro_user_left_cart' );
			delete_user_meta( $contact_id, 'hubwoo_pro_last_addtocart' );
			delete_user_meta( $contact_id, 'hubwoo_pro_cart_locale' );
			delete_user_meta( $contact_id, 'hubwoo_pro_user_cart_sent' );
		}

		$abandoned_property_updated = get_option( 'hubwoo_abandoned_property_update' );

		if ( ! empty( $abandoned_property_updated ) && 'yes' == $abandoned_property_updated ) {
			if ( 'yes' == $abncart_properties['current_abandoned_cart'] ) {
				$abncart_properties['current_abandoned_cart'] = true;
			} else {
				$abncart_properties['current_abandoned_cart'] = false;
			}
		}

		foreach ( $abncart_properties as $property_name => $property_value ) {
			if ( isset( $property_value ) ) {
				$properties[] = array(
					'property' => $property_name,
					'value'    => $property_value,
				);
			}
		}
		return $properties;
	}

	/**
	 * Preparing few parameters value for abandoned cart details.
	 *
	 * @since 1.0.0
	 * @param array $customer_cart cart contents.
	 */
	public static function hubwoo_return_abncart_values( $customer_cart ) {

		$key = 0;

		$cart_products = array();

		$cart_total   = 0;
		$cart_counter = 0;

		if ( ! empty( $customer_cart ) ) {

			foreach ( $customer_cart as $single_cart_item ) {

				$item_id         = $single_cart_item['product_id'];
				$parent_item_img = wp_get_attachment_image_src( get_post_thumbnail_id( $item_id ), 'single-post-thumbnail' );
				$item_img        = '';
				if ( ! empty( $single_cart_item['variation_id'] ) ) {
					$item_id  = $single_cart_item['variation_id'];
					$item_img = wp_get_attachment_image_src( get_post_thumbnail_id( $item_id ), 'single-post-thumbnail' );
				}
				if ( get_post_status( $item_id ) == 'trash' || get_post_status( $item_id ) == false ) {

					continue;
				}
				if ( empty( $item_img ) ) {
					$item_img = $parent_item_img;
				}
				$product                          = wc_get_product( $item_id );
				$cart_products[ $key ]['image']   = $item_img;
				$cart_products[ $key ]['name']    = $product->get_name();
				$cart_products[ $key ]['url']     = get_permalink( $item_id );
				$cart_products[ $key ]['price']   = $product->get_price();
				$cart_products[ $key ]['qty']     = $single_cart_item['quantity'];
				$cart_products[ $key ]['item_id'] = $item_id;
				if ( array_key_exists( 'line_total', $single_cart_item ) ) {
					$cart_products[ $key ]['total'] = floatval( $single_cart_item['line_total'] + $single_cart_item['line_tax'] );
				} else {
					$product_obj                    = wc_get_product( $item_id );
					$cart_products[ $key ]['total'] = $product_obj->get_price() * $single_cart_item['quantity'];
				}
				$key++;
			}
		}

		return $cart_products;
	}

	/**
	 * Preparing cart HTML with products data.
	 *
	 * @since 1.0.0
	 * @param array $cart_products values for products to be used in html.
	 */
	public static function hubwoo_abncart_product_html( $cart_products ) {

		$products_html = '<div><hr></div><!--[if mso]><center><table width="100%" style="width:600px;"><![endif]--><table style="font-size: 14px; font-family: Arial, sans-serif; line-height: 20px; text-align: left; table-layout: fixed;" width="100%"><thead><tr><th style="text-align: center;word-wrap: unset;">' . __( 'Image', 'hubspot-for-woocommerce' ) . '</th><th style="text-align: center;word-wrap: unset;">' . __( 'Item', 'hubspot-for-woocommerce' ) . '</th><th style="text-align: center;word-wrap: unset;">' . __( 'Qty', 'hubspot-for-woocommerce' ) . '</th><th style="text-align: center;word-wrap: unset;">' . __( 'Cost', 'huwboo' ) . '</th><th style="text-align: center;word-wrap: unset;">' . __( 'Total', 'hubspot-for-woocommerce' ) . '</th></tr></thead><tbody>';
		foreach ( $cart_products as $single_product ) {
			$products_html .= '<tr><td width="20" style="max-width: 100%; text-align: center;"><img height="50" width="50" src="' . $single_product['image'][0] . '"></td><td width="50" style="max-width: 100%; text-align: center; font-weight: normal;font-size: 10px;word-wrap: unset;"><a style="display: inline-block;" target="_blank" href="' . $single_product['url'] . '">' . $single_product['name'] . '</a></td><td width="10" style="max-width: 100%;text-align: center;">' . $single_product['qty'] . '</td><td width="10" style="max-width: 100%;text-align: center; font-size: 10px;">' . wc_price( $single_product['price'], array( 'currency' => get_option( 'woocommerce_currency' ) ) ) . '</td><td width="10" style="max-width: 100%;text-align: center; font-size: 10px;">' . wc_price( $single_product['total'], array( 'currency' => get_option( 'woocommerce_currency' ) ) ) . '</td></tr>';
		}
		$products_html .= '</tbody></table><!--[if mso]></table></center><![endif]--><div><hr></div>';

		return apply_filters( 'hubwoo_abandoned_cart_html', $products_html, $cart_products );
	}

	/**
	 * Preparing guest user data for HubSpot.
	 *
	 * @since 1.0.0
	 * @param string $email user email.
	 * @param array  $properties list of properties.
	 */
	public function hubwoo_abncart_process_guest_data( $email, $properties = array() ) {

		if ( ! empty( $email ) ) {
			$cart_product_skus    = array();
			$cart_product_qty     = 0;
			$cart_subtotal        = 0;
			$cart_total           = 0;
			$cart_tax             = 0;
			$last_date            = '';
			$cart_url             = '';
			$cart_status          = 'no';
			$cart_categories      = array();
			$cart_products        = array();
			$hubwoo_abncart_timer = get_option( 'hubwoo_abncart_timing', 5 );
			$products_html        = '';
			$in_cart_products     = array();
			$flag                 = false;

			$existing_guest_users = get_option( 'mwb_hubwoo_guest_user_cart', array() );

			if ( ! empty( $existing_guest_users ) ) {

				foreach ( $existing_guest_users as $key => &$single_cart_data ) {

					$flag = false;

					if ( isset( $single_cart_data['email'] ) && $email == $single_cart_data['email'] ) {

						$last_time            = ! empty( $single_cart_data['timeStamp'] ) ? $single_cart_data['timeStamp'] : '';
						$current_time         = time();
						$time_diff            = round( abs( $current_time - $last_time ) / 60, 2 );
						$hubwoo_abncart_timer = (int) $hubwoo_abncart_timer;
						$flag                 = true;

						$cart_data = ! empty( $single_cart_data['cartData']['cart'] ) ? $single_cart_data['cartData']['cart'] : array();

						if ( count( $cart_data ) ) {

							$cart_products = self::hubwoo_return_abncart_values( $cart_data );
						}

						if ( count( $cart_products ) ) {

							$products_html = self::hubwoo_abncart_product_html( $cart_products );
						}
						if ( ! empty( $cart_data ) ) {
							$cart_status = 'yes';

							$last_date = $last_time;

							$locale   = ! empty( $single_cart_data['locale'] ) ? $single_cart_data['locale'] : get_locale();
							$cart_url = apply_filters( 'wpml_permalink', wc_get_cart_url(), $locale, true );

							$cart_url .= '?hubwoo-abncart-retrieve=';
							$cart_prod = array();

							foreach ( $cart_data as $single_cart_item ) {
								$item_id         = $single_cart_item['product_id'];
								$parent_item_sku = get_post_meta( $item_id, '_sku', true );
								if ( ! empty( $single_cart_item['variation_id'] ) ) {
									$item_id = $single_cart_item['variation_id'];
								}

								$qty         = $single_cart_item['quantity'] ? $single_cart_item['quantity'] : 1;
								$cart_prod[] = $item_id . ':' . $qty;
								if ( get_post_status( $item_id ) == 'trash' || get_post_status( $item_id ) == false ) {

									continue;
								}

								$cart_item_sku = get_post_meta( $item_id, '_sku', true );

								if ( empty( $cart_item_sku ) ) {

									$cart_item_sku = $parent_item_sku;
								}

								if ( empty( $cart_item_sku ) ) {

									$cart_item_sku = $item_id;
								}

								$cart_product_skus[] = $cart_item_sku;

								$product_cats_ids = wc_get_product_term_ids( $item_id, 'product_cat' );

								if ( is_array( $product_cats_ids ) && count( $product_cats_ids ) ) {

									foreach ( $product_cats_ids as $cat_id ) {

										$term              = get_term_by( 'id', $cat_id, 'product_cat' );
										$cart_categories[] = $term->slug;
									}
								}

								$post               = get_post( $item_id );
								$post_name          = isset( $post->post_name ) ? $post->post_name : '';
								$product_name       = $post_name . '-' . $item_id;
								$in_cart_products[] = $product_name;
								$cart_product_qty  += $single_cart_item['quantity'];
								$cart_subtotal     += $single_cart_item['line_total'];
								$cart_tax          += $single_cart_item['line_tax'];
							}
							$cart_url .= implode( ',', $cart_prod );

						} else {

							$this->hubwoo_abncart_clear_data( $email );
						}

						if ( $time_diff <= $hubwoo_abncart_timer ) {
							if ( count( $cart_data ) ) {
								$flag = false;
								break;
							} else {
								$flag = true;
								break;
							}
						}
						break;
					}
				}
			}

			$cart_product_skus = HubwooGuestOrdersManager::hubwoo_format_array( $cart_product_skus );
			$in_cart_products  = HubwooGuestOrdersManager::hubwoo_format_array( $in_cart_products );
			$cart_categories   = HubwooGuestOrdersManager::hubwoo_format_array( $cart_categories );
			$cart_total        = floatval( $cart_tax + $cart_subtotal );

			if ( $flag ) {
				if ( ! empty( $last_date ) ) {

					$last_date    = (int) $last_date;
					$properties[] = array(
						'property' => 'abandoned_cart_date',
						'value'    => HubwooGuestOrdersManager::hubwoo_set_utc_midnight( $last_date ),
					);
				} else {
					$properties[] = array(
						'property' => 'abandoned_cart_date',
						'value'    => '',
					);
				}

				$abandoned_property_updated = get_option( 'hubwoo_abandoned_property_update' );

				if ( ! empty( $abandoned_property_updated ) && 'yes' == $abandoned_property_updated ) {
					if ( 'yes' == $cart_status ) {
						$cart_status = true;
					} else {
						$cart_status = false;
					}
				}

				$properties[] = array(
					'property' => 'current_abandoned_cart',
					'value'    => $cart_status,
				);
				$properties[] = array(
					'property' => 'abandoned_cart_counter',
					'value'    => $cart_product_qty,
				);
				$properties[] = array(
					'property' => 'abandoned_cart_url',
					'value'    => $cart_url,
				);
				$properties[] = array(
					'property' => 'abandoned_cart_products_skus',
					'value'    => $cart_product_skus,
				);
				$properties[] = array(
					'property' => 'abandoned_cart_products_categories',
					'value'    => $cart_categories,
				);
				$properties[] = array(
					'property' => 'abandoned_cart_products',
					'value'    => $in_cart_products,
				);
				$properties[] = array(
					'property' => 'abandoned_cart_tax_value',
					'value'    => $cart_tax,
				);
				$properties[] = array(
					'property' => 'abandoned_cart_subtotal',
					'value'    => $cart_subtotal,
				);
				$properties[] = array(
					'property' => 'abandoned_cart_total_value',
					'value'    => $cart_total,
				);
				$properties[] = array(
					'property' => 'abandoned_cart_products_html',
					'value'    => $products_html,
				);
			}
			return $properties;
		}
	}

	/**
	 * Clear data for email whose cart has been found empty.
	 *
	 * @since 1.0.0
	 * @param string $email contact email.
	 */
	public function hubwoo_abncart_clear_data( $email ) {

		$existing_guest_users = get_option( 'mwb_hubwoo_guest_user_cart', array() );

		if ( ! empty( $existing_guest_users ) ) {

			foreach ( $existing_guest_users as $key => &$single_cart_data ) {

				if ( isset( $single_cart_data['email'] ) && $email == $single_cart_data['email'] ) {

					unset( $existing_guest_users[ $key ] );
					break;
				}
			}
		}

		$existing_guest_users = array_values( $existing_guest_users );
		update_option( 'mwb_hubwoo_guest_user_cart', $existing_guest_users );
	}

	/**
	 * Clear those abandoned carts who have elapsed the saved timer.
	 *
	 * @since    1.0.0
	 */
	public function huwoo_abncart_clear_old_cart() {

		$saved_carts = get_option( 'mwb_hubwoo_guest_user_cart', array() );

		$hubwoo_abncart_delete_after = (int) get_option( 'hubwoo_abncart_delete_after', '' );

		$hubwoo_abncart_delete_after = $hubwoo_abncart_delete_after * ( 24 * 60 * 60 );

		// process the guest cart data.
		if ( ! empty( $hubwoo_abncart_delete_after ) ) {

			if ( ! empty( $saved_carts ) ) {

				foreach ( $saved_carts as $key => &$single_cart ) {

					$cart_time = ! empty( $single_cart['timeStamp'] ) ? $single_cart['timeStamp'] : '';

					if ( ! empty( $cart_time ) ) {

						$time = time();

						if ( $time > $cart_time && ( $time - $cart_time ) >= $hubwoo_abncart_delete_after ) {

							if ( isset( $single_cart['cartData']['cart'] ) ) {

								$single_cart['cartData']['cart'] = '';
							}
						}
					}
				}
			}
		}

		update_option( 'mwb_hubwoo_guest_user_cart', $saved_carts );

		// process the clearing of meta for registered users but don't clear their cart.
		$args['meta_query'] = array(

			array(
				'key'     => 'hubwoo_pro_user_left_cart',
				'value'   => 'yes',
				'compare' => '==',
			),
		);

		$users = wp_list_pluck( get_users( $args ), 'ID' );

		if ( ! empty( $users ) ) {
			foreach ( $users as $user_id ) {
				$cart_time = get_user_meta( $user_id, 'hubwoo_pro_last_addtocart', true );
				if ( ! empty( $cart_time ) ) {
					$time = time();
					if ( $time > $cart_time && ( $time - $cart_time ) >= $hubwoo_abncart_delete_after ) {
						delete_user_meta( $user_id, 'hubwoo_pro_last_addtocart' );
						delete_user_meta( $user_id, 'hubwoo_pro_user_left_cart' );
						delete_user_meta( $user_id, 'hubwoo_pro_cart_locale' );
					}
				}
			}
		}
	}

	/**
	 * Fetching the customers who have abandoned cart.
	 *
	 * @since 1.0.0
	 * @param array $hubwoo_users list of users ready to be synced on hubspot.
	 */
	public function hubwoo_abncart_users( $hubwoo_users ) {

		$args['meta_query']          = array(
			'relation' => 'AND',
			array(
				'key'     => 'hubwoo_pro_user_left_cart',
				'value'   => 'yes',
				'compare' => '==',
			),
			array(
				'relation' => 'OR',
				array(
					'key'     => 'hubwoo_pro_user_cart_sent',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'hubwoo_pro_user_cart_sent',
					'value'   => 'no',
					'compare' => '==',
				),
			),
		);
		$args['number']              = 25;
		$args['fields']              = 'ID';
		$hubwoo_abandoned_cart_users = get_users( $args );
		$hubwoo_new_users            = array();

		if ( count( $hubwoo_abandoned_cart_users ) ) {

			$hubwoo_new_users = array_merge( $hubwoo_users, $hubwoo_abandoned_cart_users );
		} else {

			$hubwoo_new_users = $hubwoo_users;
		}

		return $hubwoo_new_users;
	}
	/**
	 * Get user actions for marketing.
	 *
	 * @return array abandoned cart current status.
	 * @since 1.0.0
	 */
	public static function get_abandoned_cart_status() {

		$cart_status = array();

		$cart_status[] = array(
			'label' => __( 'Yes', 'hubspot-for-woocommerce' ),
			'value' => 'yes',
		);
		$cart_status[] = array(
			'label' => __( 'No', 'hubspot-for-woocommerce' ),
			'value' => 'no',
		);

		$cart_status = apply_filters( 'hubwoo_customer_cart_statuses', $cart_status );

		return $cart_status;
	}

	/**
	 * Prepare params for generating plugin settings.
	 *
	 * @since    1.0.0
	 * @return array $basic_settings array of html settings.
	 */
	public static function hubwoo_get_plugin_settings() {

		global $hubwoo;

		$existing_user_roles = self::get_all_user_roles();

		$basic_settings = array();

		$basic_settings[] = array(
			'title' => esc_html__( 'Plugin Settings', 'hubspot-for-woocommerce' ),
			'id'    => 'hubwoo_checkout_optin_title',
			'type'  => 'title',
		);

		$basic_settings[] = array(
			'title'    => esc_html__( 'Sync with Order Status', 'hubspot-for-woocommerce' ),
			'id'       => 'hubwoo-order-statuses',
			'type'     => 'multiselect',
			'class'    => 'hubwoo-general-settings-fields',
			'desc'     => esc_html__( 'The orders with selected statuses will be synced to HubSpot including Real Time and Historical Sync Orders. Default will be all order statuses.', 'hubspot-for-woocommerce' ),
			'options'  => wc_get_order_statuses(),
			'desc_tip' => true,
		);

		$basic_settings[] = array(
			'title'    => esc_html__( 'Sync with User Role', 'hubspot-for-woocommerce' ),
			'id'       => 'hubwoo-selected-user-roles',
			'class'    => 'hubwoo-general-settings-fields',
			'type'     => 'multiselect',
			'desc'     => esc_html__( 'The users with selected roles will be synced on HubSpot. Default will be all user roles.', 'hubspot-for-woocommerce' ),
			'options'  => $existing_user_roles,
			'desc_tip' => true,

		);

		if ( Hubwoo::hubwoo_subs_active() ) {
			$basic_settings[] = array(
				'title'   => esc_html__( 'WooCommerce Subscription', 'hubspot-for-woocommerce' ),
				'id'      => 'hubwoo_subs_settings_enable',
				'class'   => 'hubwoo-general-settings-fields',
				'desc'    => esc_html__( 'Enable subscriptions data sync', 'hubspot-for-woocommerce' ),
				'type'    => 'checkbox',
				'default' => 'yes',
			);
		}

		$basic_settings[] = array(
			'title'   => esc_html__( 'Show Checkbox on Checkout Page', 'hubspot-for-woocommerce' ),
			'id'      => 'hubwoo_checkout_optin_enable',
			'class'   => 'hubwoo-general-settings-fields',
			'desc'    => esc_html__( 'Show Opt-In checkbox on Checkout Page', 'hubspot-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'no',
		);

		$basic_settings[] = array(
			'title'   => esc_html__( 'Checkbox Label on Checkout Page', 'hubspot-for-woocommerce' ),
			'id'      => 'hubwoo_checkout_optin_label',
			'class'   => 'hubwoo-general-settings-fields',
			'desc'    => esc_html__( 'Label to show for the checkbox', 'hubspot-for-woocommerce' ),
			'type'    => 'text',
			'default' => esc_html__( 'Subscribe', 'hubspot-for-woocommerce' ),
		);

		$basic_settings[] = array(
			'title'   => esc_html__( 'Show Checkbox on My Account Page', 'hubspot-for-woocommerce' ),
			'id'      => 'hubwoo_registeration_optin_enable',
			'class'   => 'hubwoo-general-settings-fields',
			'desc'    => esc_html__( 'Show Opt-In checkbox on My Account Page (Registration form)', 'hubspot-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'no',
		);

		$basic_settings[] = array(
			'title'   => esc_html__( 'Checkbox Label on My Account Page', 'hubspot-for-woocommerce' ),
			'id'      => 'hubwoo_registeration_optin_label',
			'class'   => 'hubwoo-general-settings-fields',
			'desc'    => esc_html__( 'Label to show for the checkbox', 'hubspot-for-woocommerce' ),
			'type'    => 'text',
			'default' => esc_html__( 'Subscribe', 'hubspot-for-woocommerce' ),
		);

		$basic_settings[] = array(
			'title'    => esc_html__( 'Calculate ROI for the Selected Status', 'hubspot-for-woocommerce' ),
			'id'       => 'hubwoo_no_status',
			'class'    => 'hubwoo-general-settings-fields',
			'type'     => 'select',
			'desc'     => esc_html__( 'Select an order status from the dropdown for which the new order property will be set/changed on each sync. Default Order Status is Completed', 'hubspot-for-woocommerce' ),
			'options'  => wc_get_order_statuses(),
			'desc_tip' => true,
			'default'  => 'wc-completed',
		);

		$basic_settings = apply_filters( 'hubwoo_general_settings_options', $basic_settings );

		$basic_settings[] = array(
			'type' => 'sectionend',
			'id'   => 'hubwoo_pro_settings_end',
		);

		return $basic_settings;
	}

	/**
	 * Get products count.
	 *
	 * @since    1.0.0
	 * @return int $counter count of woocommerce products.
	 */
	public static function hubwoo_get_all_products_count() {
		$counter = 0;

		$query = new WP_Query();

		$products = $query->query(
			array(
				'post_type'           => array( 'product', 'product_variation' ),
				'posts_per_page'      => -1,
				'post_status'         => array( 'publish' ),
				'orderby'             => 'date',
				'order'               => 'desc',
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);
		if ( ! empty( $products ) ) {
			$counter = count( $products );
		}
		return $counter;
	}

	/**
	 * Get all users count.
	 *
	 * @since 1.0.0
	 * @param string $constraint ( default = "NOT EXISTS" ).
	 * @return int $users count of all users
	 */
	public static function hubwoo_get_all_users_count( $constraint = 'NOT EXISTS' ) {

		global $hubwoo;

		$roles = get_option( 'hubwoo_customers_role_settings', array() );

		if ( empty( $roles ) ) {
			$roles = array_keys( $hubwoo->hubwoo_get_user_roles() );
			$key   = array_search( 'guest_user', $roles );
			if ( false !== $key ) {
				unset( $roles[ $key ] );
			}
		}

		$args['role__in'] = $roles;

		$args['meta_query'] = array(
			array(
				'key'     => 'hubwoo_pro_user_data_change',
				'compare' => $constraint,
			),
		);

		$args['fields'] = 'ids';

		return count( get_users( $args ) );
	}

	/**
	 * Background sync for Deals.
	 *
	 * @since 1.0.0
	 * @param int $order_id order id.
	 */
	public function hubwoo_ecomm_deal_upsert( $order_id ) {
		if ( empty( $order_id ) ) {
			return;
		}

		HubwooObjectProperties::get_instance()->hubwoo_ecomm_deals_sync( $order_id );
	}

	/**
	 * Background sync to get deal id.
	 *
	 * @since 1.2.5
	 */
	public function hubwoo_deal_update_schedule() {

		$object_type = 'DEAL';
		$count = 0;
		$update_deal_id = get_option( 'mwb_update_deal_ids' );
		$assc_deal_cmpy = get_option( 'hubwoo_assoc_deal_cmpy_enable', 'yes' );

		if ( count( $update_deal_id ) > 0 ) {
			foreach ( $update_deal_id as $key => $order_id ) {

				$count++;
				if ( $count <= 5 ) {

					$contact = '';

					$hubwoo_ecomm_order = wc_get_order( $order_id );
					if ( $hubwoo_ecomm_order instanceof WC_Order ) {
						$customer_id = $hubwoo_ecomm_order->get_customer_id();

						if ( ! empty( $customer_id ) ) {
							$user_info  = json_decode( wp_json_encode( get_userdata( $customer_id ) ), true );
							$user_email = $user_info['data']['user_email'];
							$contact    = $user_email;
							if ( empty( $contact ) ) {
								$contact = $customer_id;
							}
						} else {
							$contact = get_post_meta( $order_id, '_billing_email', true );
						}
					}

					$flag = true;
					if ( Hubwoo::is_access_token_expired() ) {

						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

						if ( ! $status ) {

							$flag = false;
						}
					}

					if ( $flag ) { 

						$contact_vid = '';

						if ( ! empty( $contact ) ) {

							$contact_vid = HubWooConnectionMananager::get_instance()->get_customer_by_email( $contact );
						}

						$response = HubWooConnectionMananager::get_instance()->ecomm_sync_status( $order_id, $object_type );

						if ( 200 == $response['status_code'] ) {

							$response = json_decode( $response['body'], true );
							update_post_meta( $order_id, 'hubwoo_ecomm_deal_id', $response['hubspotId'] );
							if ( ! empty( $contact_vid ) ) {
								HubWooConnectionMananager::get_instance()->create_deal_associations( $response['hubspotId'], $contact_vid );
							}

							unset( $update_deal_id[ $key ] );
							do_action( 'hubwoo_ecomm_deal_created', $order_id );

							if ( 'yes' == $assc_deal_cmpy ) {
								$order = wc_get_order( $order_id );
								if ( $order instanceof WC_Order ) {
									$email = $order->get_billing_email();
									if ( ! empty( $email ) ) {
										$contact_response = HubWooConnectionMananager::get_instance()->get_customer_vid_historical( $email );
										if ( 200 == $contact_response['status_code'] ) {
											$decoded_response = json_decode($contact_response['body']);
											$comp             = $decoded_response->properties;
											$comp_meta        = ( isset( $comp->associatedcompanyid ) ) ? $comp->associatedcompanyid : '' ;
											$company_id       = ( isset( $comp_meta->value ) ) ? $comp_meta->value : '';
											if ( ! empty( $company_id ) ) {
												$updated_response = HubWooConnectionMananager::get_instance()->create_deal_company_associations( $response['hubspotId'], $company_id );
											}
										}	
									}
								}
							}
						}

						$update_deal_id = array_values( $update_deal_id );
						update_option( 'mwb_update_deal_ids', $update_deal_id );
					}
				} else {
					break;
				}
			}
		} else {
			as_unschedule_action( 'hubwoo_deal_update_schedule' );
		}
	}

	/**
	 * Background sync for Deals.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_deals_sync_background() {

		$orders_needs_syncing = self::hubwoo_orders_count_for_deal( 5, false );
		if ( is_array( $orders_needs_syncing ) && count( $orders_needs_syncing ) ) {
			foreach ( $orders_needs_syncing as $order_id ) {
				HubwooObjectProperties::get_instance()->hubwoo_ecomm_deals_sync( $order_id );
			}
		} else {
			Hubwoo::hubwoo_stop_sync( 'stop-deal' );
		}
	}

	/**
	 * Background sync for Products.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_products_sync_background() {

		if ( 'yes' === get_option( 'hubwoo_connection_issue', 'no' ) ) {
			return;
		}

		$product_data = Hubwoo::hubwoo_get_product_data( 10 );
		if ( ! empty( $product_data ) && is_array( $product_data ) ) {
			$product_ids = array_column( $product_data, 'externalObjectId' );
			$response    = HubWooConnectionMananager::get_instance()->ecomm_sync_messages( $product_data, 'PRODUCT' );

			if ( 204 == $response['status_code'] ) {
				if ( ! empty( $product_ids ) ) {
					foreach ( $product_ids as $product_id ) {
						update_post_meta( $product_id, 'hubwoo_product_synced', true );
						do_action( 'hubwoo_update_product_property', $product_id );
					}
				}
			}

			if ( ! as_next_scheduled_action( 'hubwoo_products_status_background' ) ) {
				as_schedule_recurring_action( time(), 180, 'hubwoo_products_status_background' );
			}
		}
	}

	/**
	 * Background sync status for Products.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_products_status_background() {
		global $hubwoo;

		$automation_enabled = $hubwoo->is_automation_enabled();
		$contraints = array(
			array(
				'key'     => 'hubwoo_product_synced',
				'compare' => 'EXISTS',
			),
		);

		$products = Hubwoo::hubwoo_ecomm_get_products( 10, $contraints );
		if ( ! empty( $products ) ) {
			foreach ( $products as $product_id ) {
				$response = HubWooConnectionMananager::get_instance()->ecomm_sync_status( $product_id, 'PRODUCT' );
				if ( 200 == $response['status_code'] ) {
					$response = json_decode( $response['body'], true );
					if ( $response['externalObjectId'] == $product_id ) {
						update_post_meta( $product_id, 'hubwoo_ecomm_pro_id', $response['hubspotId'] );
						if ( $automation_enabled ) {
							self::update_product_image( $product_id, $response['hubspotId'] );
						}
						delete_post_meta( $product_id, 'hubwoo_product_synced' );
					}
				}
			}
		} else {

			$orders_needs_syncing = self::hubwoo_orders_count_for_deal();
			if ( $orders_needs_syncing ) {

				update_option( 'hubwoo_deals_sync_running', 1 );
				update_option( 'hubwoo_deals_sync_total', $orders_needs_syncing );
				if ( ! as_next_scheduled_action( 'hubwoo_deals_sync_background' ) ) {
					as_schedule_recurring_action( time(), 300, 'hubwoo_deals_sync_background' );
				}
			}

			Hubwoo::hubwoo_stop_sync( 'stop-product-sync' );
		}
	}

	/**
	 * Updates the product whenever there is any change
	 *
	 * @since    1.0.0
	 * @param int $post_ID post id of the product.
	 * @param int $post post object.
	 */
	public function hubwoo_ecomm_update_product( $post_ID, $post ) {

		if ( 'yes' === get_option( 'hubwoo_connection_issue', 'no' ) ) {
			return;
		}

		global $hubwoo;

		$automation_enabled = $hubwoo->is_automation_enabled();
		$post_type = $post->post_type;
		if ( 'product' != $post_type ) {
			return;
		}
		$updates     = array();
		$object_type = 'PRODUCT';
		if ( is_ajax() ) {
			return;
		}
		$post_status = get_post_status( $post_ID );
		if ( 'publish' == $post_status ) {
			if ( ! empty( $post_ID ) ) {
				$product      = wc_get_product( $post_ID );
				$product_type = $product->get_type();
				if ( ! empty( $product_type ) && ( 'variable' == $product_type || 'variable-subscription' == $product_type ) ) {
					$variation_args    = array(
						'post_parent' => $post_ID,
						'post_type'   => 'product_variation',
						'numberposts' => -1,
					);
					$wc_products_array = get_posts( $variation_args );
					if ( is_array( $wc_products_array ) && count( $wc_products_array ) ) {
						foreach ( $wc_products_array as $single_var_product ) {
							$hubwoo_ecomm_product = new HubwooEcommObject( $single_var_product->ID, $object_type );
							$properties           = $hubwoo_ecomm_product->get_object_properties();
							$properties           = apply_filters( 'hubwoo_map_ecomm_' . $object_type . '_properties', $properties, $single_var_product->ID );
							$updates[]            = array(
								'action'           => 'UPSERT',
								'changedAt'        => strtotime( gmdate( 'Y-m-d H:i:s ', time() ) ) . '000',
								'externalObjectId' => $single_var_product->ID,
								'properties'       => $properties,
							);

							if ( $automation_enabled ) {
								$hub_pro_id = get_post_meta( $single_var_product->ID, 'hubwoo_ecomm_pro_id', true );
								self::update_product_image( $single_var_product->ID, $hub_pro_id );
							}
						}
					}
				} else {
					$hubwoo_ecomm_product = new HubwooEcommObject( $post_ID, $object_type );
					$properties           = $hubwoo_ecomm_product->get_object_properties();
					$properties           = apply_filters( 'hubwoo_map_ecomm_' . $object_type . '_properties', $properties, $post_ID );
					$updates[]            = array(
						'externalObjectId' => $post_ID,
						'action'           => 'UPSERT',
						'changedAt'        => strtotime( gmdate( 'Y-m-d H:i:s ', time() ) ) . '000',
						'properties'       => $properties,
					);

					if ( $automation_enabled ) {
						$hub_pro_id = get_post_meta( $post_ID, 'hubwoo_ecomm_pro_id', true );
						self::update_product_image( $post_ID, $hub_pro_id );
					}
				}
			}
		}
		if ( count( $updates ) ) {
			HubWooConnectionMananager::get_instance()->ecomm_sync_messages( $updates, $object_type );
		}
	}

	/**
	 * Fetching total order available and returning count. Also excluding orders with deal id.
	 *
	 * @since 1.0.0
	 * @param int  $number_of_posts posts to fetch in one call.
	 * @param bool $count true/false.
	 * @return array $old_orders orders posts.
	 */
	public static function hubwoo_orders_count_for_deal( $number_of_posts = -1, $count = true ) {

		$sync_data['since_date']            = get_option( 'hubwoo_ecomm_order_ocs_from_date', gmdate( 'd-m-Y' ) );
		$sync_data['upto_date']             = get_option( 'hubwoo_ecomm_order_ocs_upto_date', gmdate( 'd-m-Y' ) );
		$sync_data['selected_order_status'] = get_option( 'hubwoo_ecomm_order_ocs_status', array_keys( wc_get_order_statuses() ) );

		$args = array(
			'numberposts' => $number_of_posts,
			'post_type'   => 'shop_order',
			'fields'      => 'ids',
			'post_status' => $sync_data['selected_order_status'],
			'meta_query'  => array(
				array(
					'key'     => 'hubwoo_ecomm_deal_created',
					'compare' => 'NOT EXISTS',
				),
			),
		);

		if ( 'yes' == get_option( 'hubwoo_ecomm_order_date_allow', 'no' ) ) {
			$args['date_query'] = array(
				array(
					'after'     => gmdate( 'd-m-Y', strtotime( $sync_data['since_date'] ) ),
					'before'    => gmdate( 'd-m-Y', strtotime( $sync_data['upto_date'] . ' +1 day' ) ),
					'inclusive' => true,
				),
			);
		}

		$old_orders = get_posts( $args );

		if ( $count ) {

			$orders_count = count( $old_orders );

			return $orders_count;

		} else {

			return $old_orders;
		}
	}

	/**
	 * General setting tab fields.
	 *
	 * @return array  woocommerce_admin_fields acceptable fields in array.
	 * @since 1.0.0
	 */
	public static function hubwoo_ecomm_general_settings() {

		$settings = array();

		$settings[] = array(
			'title' => __( 'Apply your settings for Deals and its stages', 'hubspot-for-woocommerce' ),
			'id'    => 'hubwoo_ecomm_deals_settings_title',
			'type'  => 'title',
			'class' => 'hubwoo-ecomm-settings-title',
		);

		$settings[] = array(
			'title'   => __( 'Enable/Disable', 'hubspot-for-woocommerce' ),
			'id'      => 'hubwoo_ecomm_deal_enable',
			'desc'    => __( 'Allow to sync new deals', 'hubspot-for-woocommerce' ),
			'type'    => 'checkbox',
			'class'   => 'hubwoo-ecomm-settings-checkbox hubwoo_real_time_changes',
			'default' => 'yes',
		);

		$settings[] = array(
			'title'             => __( 'Days required to close a deal', 'hubspot-for-woocommerce' ),
			'id'                => 'hubwoo_ecomm_closedate_days',
			'type'              => 'number',
			'desc'              => __( 'set the minimum number of days in which the pending/open deals can be closed/won', 'hubspot-for-woocommerce' ),
			'desc_tip'          => true,
			'custom_attributes' => array( 'min' => '5' ),
			'default'           => '5',
			'class'             => 'hubwoo-ecomm-settings-text hubwoo_real_time_changes',
		);

		$settings[] = array(
			'title'    => __( 'Winning Deal Stages', 'hubspot-for-woocommerce' ),
			'id'       => 'hubwoo_ecomm_won_stages',
			'type'     => 'multiselect',
			'class'    => 'hubwoo-ecomm-settings-multiselect hubwoo_real_time_changes',
			'desc'     => __( 'select the deal stages of ecommerce pipeline which are won according to your business needs. "Processing" and "Completed" are default winning stages for extension as well as HubSpot', 'hubspot-for-woocommerce' ),
			'desc_tip' => true,
			'options'  => self::hubwoo_ecomm_get_stages(),
		);

		$settings[] = array(
			'title'   => __( 'Enable/Disable', 'hubspot-for-woocommerce' ),
			'id'      => 'hubwoo_assoc_deal_cmpy_enable',
			'desc'    => __( 'Allow to associate deal and company', 'hubspot-for-woocommerce' ),
			'type'    => 'checkbox',
			'class'   => 'hubwoo-ecomm-settings-checkbox hubwoo_real_time_changes',
			'default' => 'yes',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'hubwoo_ecomm_deal_settings_end',
		);

		return apply_filters( 'hubwoo_ecomm_deals_settings', $settings );
	}


	/**
	 * Updates the product whenever there is any change
	 *
	 * @since    1.0.0
	 * @return   array $settings ecomm ocs settings
	 */
	public static function hubwoo_ecomm_order_ocs_settings() {

		$settings = array();

		$settings[] = array(
			'title' => __( 'Export your old orders as Deals on HubSpot', 'hubspot-for-woocommerce' ),
			'id'    => 'hubwoo_ecomm_order_ocs_title',
			'type'  => 'title',
			'class' => 'hubwoo-ecomm-settings-title',
		);

		$settings[] = array(
			'title'    => __( 'Select order status', 'hubspot-for-woocommerce' ),
			'id'       => 'hubwoo_ecomm_order_ocs_status',
			'type'     => 'multiselect',
			'desc'     => __( 'Select a order status from the dropdown and all orders for the selected status will be synced as deals', 'hubspot-for-woocommerce' ),
			'options'  => wc_get_order_statuses(),
			'desc_tip' => true,
			'class'    => 'hubwoo-ecomm-settings-select',
		);

		$settings[] = array(
			'title' => __( 'Select a time period', 'hubspot-for-woocommerce' ),
			'id'    => 'hubwoo_ecomm_order_date_allow',
			'desc'  => __( ' Date range for orders', 'hubspot-for-woocommerce' ),
			'type'  => 'checkbox',
			'class' => 'hubwoo-ecomm-settings-checkbox hubwoo-ecomm-settings-select',
		);

		$settings[] = array(
			'title'       => __( 'Orders from date', 'hubspot-for-woocommerce' ),
			'id'          => 'hubwoo_ecomm_order_ocs_from_date',
			'type'        => 'text',
			'placeholder' => 'dd-mm-yyyy',
			'default'     => gmdate( 'd-m-Y' ),
			'desc'        => __( 'From which date you want to sync the orders, select that date', 'hubspot-for-woocommerce' ),
			'desc_tip'    => true,
			'class'       => 'hubwoo-date-picker hubwoo-date-d-range hubwoo-ecomm-settings-select',
		);

		$settings[] = array(
			'title'       => __( 'Orders up to date', 'hubspot-for-woocommerce' ),
			'id'          => 'hubwoo_ecomm_order_ocs_upto_date',
			'type'        => 'text',
			'default'     => gmdate( 'd-m-Y' ),
			'placeholder' => __( 'dd-mm-yyyy', 'hubspot-for-woocommerce' ),
			'desc'        => __( 'Up to which date you want to sync the orders, select that date', 'hubspot-for-woocommerce' ),
			'desc_tip'    => true,
			'class'       => 'hubwoo-date-picker hubwoo-date-d-range hubwoo-ecomm-settings-select',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'hubwoo_ecomm_order_ocs_end',
		);

		return apply_filters( 'hubwoo_ecomm_order_sync_settings', $settings );
	}

	/**
	 * Updates the product whenever there is any change
	 *
	 * @since    1.0.0
	 * @return array $mapped_array mapped deal stage and order statuses.
	 */
	public static function hubwoo_ecomm_get_stages() {

		$mapped_array = array();
		$stages       = get_option( 'hubwoo_fetched_deal_stages', '' );
		if ( ! empty( $stages ) ) {
			$mapped_array = array_combine( array_column( $stages, 'stageId' ), array_column( $stages, 'label' ) );
		}
		return $mapped_array;
	}

	/**
	 * Updates the product whenever there is any change
	 *
	 * @since    1.0.0
	 * @param bool $redirect redirect to contact page ( default = false).
	 */
	public static function hubwoo_schedule_sync_listener( $redirect = false ) {

		$hubwoodatasync = new HubwooDataSync();

		$unique_users = $hubwoodatasync->hubwoo_get_all_unique_user( true );

		update_option( 'hubwoo_total_ocs_need_sync', $unique_users );

		$hubwoodatasync->hubwoo_start_schedule();

		if ( $redirect ) {
			wp_safe_redirect( admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-sync-contacts' ) );
		}
	}

	/**
	 * Download the log file of the plugin.
	 *
	 * @return void
	 */
	public function hubwoo_get_plugin_log() {

		if ( isset( $_GET['action'] ) && 'download-log' == $_GET['action'] ) {
			$filename = WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log';
			if ( is_readable( $filename ) && file_exists( $filename ) ) {
				header( 'Content-type: text/plain' );
				header( 'Content-Disposition: attachment; filename="' . basename( $filename ) . '"' );
				readfile( $filename );
				exit();
			}
		} elseif ( isset( $_GET['action'] ) && 'dismiss-hubwoo-notice' == $_GET['action'] ) {
			update_option( 'hubwoo-cron-notice-dismiss', 'yes' );
			wp_safe_redirect( admin_url( 'admin.php' ) . '?page=hubwoo' );
		}
	}

	/**
	 * Re-sync Orders as deals.
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_deals_sync_check() {

		if ( 'no' == get_option( 'hubwoo_ecomm_setup_completed', 'no' ) || 'yes' === get_option( 'hubwoo_connection_issue', 'no' ) ) {
			return;
		}

		$query = new WP_Query();

		$args = array(
			'post_type'           => 'shop_order',
			'posts_per_page'      => 3,
			'post_status'         => array_keys( wc_get_order_statuses() ),
			'orderby'             => 'date',
			'order'               => 'desc',
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				array(
					'key'     => 'hubwoo_ecomm_deal_created',
					'compare' => 'NOT EXISTS',
				),
			),
		);

		$activated_time = get_option( 'hubwoo_plugin_activated_time', '' );
		if ( ! empty( $activated_time ) ) {
			$args['date_query'] = array(
				array(
					'after'     => gmdate( 'd-m-Y', strtotime( '-1 day', $activated_time ) ),
					'inclusive' => true,
				),
			);
		}
		$failed_orders = $query->query( $args );

		if ( ! empty( $failed_orders ) ) {
			foreach ( $failed_orders as $order_id ) {
				HubwooObjectProperties::get_instance()->hubwoo_ecomm_deals_sync( $order_id );
			}
		}
	}

	/**
	 * Re-sync Orders as deals.
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_products_sync_check() {

		if ( 'yes' === get_option( 'hubwoo_connection_issue', 'no' ) ) {
			return;
		}

		global $hubwoo;

		$automation_enabled = $hubwoo->is_automation_enabled();
		$product_data = Hubwoo::hubwoo_get_product_data( 10 );

		if ( ! empty( $product_data ) && is_array( $product_data ) ) {
			$product_ids = array_column( $product_data, 'externalObjectId' );
			$response    = HubWooConnectionMananager::get_instance()->ecomm_sync_messages( $product_data, 'PRODUCT' );

			if ( 204 == $response['status_code'] ) {
				if ( ! empty( $product_ids ) ) {
					foreach ( $product_ids as $product_id ) {
						update_post_meta( $product_id, 'hubwoo_product_synced', true );
					}
				}
			}

			$contraints = array(
				array(
					'key'     => 'hubwoo_product_synced',
					'compare' => 'EXISTS',
				),
			);

			$products = Hubwoo::hubwoo_ecomm_get_products( 10, $contraints );

			if ( ! empty( $products ) ) {
				foreach ( $products as $product_id ) {
					$response = HubWooConnectionMananager::get_instance()->ecomm_sync_status( $product_id, 'PRODUCT' );
					if ( 200 == $response['status_code'] ) {
						$response = json_decode( $response['body'], true );
						if ( $response['externalObjectId'] == $product_id ) {
							update_post_meta( $product_id, 'hubwoo_ecomm_pro_id', $response['hubspotId'] );
							if ( $automation_enabled ) {
								self::update_product_image( $product_id, $response['hubspotId'] );
							}
							delete_post_meta( $product_id, 'hubwoo_product_synced' );
						}
					}
					do_action( 'hubwoo_update_product_property', $product_id );
				}
			}
		}
	}

	/**
	 * Product properties updation
	 *
	 * @since 1.2.7
	 * @param string $product_id the id of product.
	 * @param string $hubwoo_ecomm_pro_id hubspot id product.
	 */
	public static function update_product_image( $product_id, $hubwoo_ecomm_pro_id ) {
		if ( ! empty( $product_id ) ) {
			$product = wc_get_product( $product_id );

			$prod_sku = $product->get_data()['sku'];

			$prod_desc = $product->get_short_description();

			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail' );

			$image_url = isset( $image[0] )? $image[0] : '';

			$properties = array();

			if ( ! empty( $image_url ) ) {
				$properties[] = array(
					'name'  => 'hs_images',
					'value' => $image_url,
				);
			}

			if ( ! empty( $prod_sku ) ) {
				$properties[] = array(
					'name'  => 'hs_sku',
					'value' => $prod_sku,
				);
			}

			if ( ! empty( $prod_desc ) ) {
				$properties[] = array(
					'name'  => 'description',
					'value' => $prod_desc,
				);
			}
			
			if ( ! empty( $properties && $hubwoo_ecomm_pro_id ) ) {
				Hubwoo::hubwoo_update_products( $properties, $hubwoo_ecomm_pro_id );
			}
		}
	}

	/**
	 * Contact sync in background.
	 *
	 * @since    1.0.4
	 */
	public function hubwoo_contacts_sync_background() {

		$user_data          = array();
		$hubwoo_datasync    = new HubwooDataSync();
		$users_need_syncing = $hubwoo_datasync->hubwoo_get_all_unique_user();
		if ( ! count( $users_need_syncing ) ) {
			$users_need_syncing = $hubwoo_datasync->hubwoo_get_all_unique_user( false, 'guestOrder' );
			$user_data          = HubwooDataSync::get_guest_sync_data( $users_need_syncing );
			$mark['type']       = 'order';
			$mark['ids']        = $users_need_syncing;
		} else {
			$user_data    = HubwooDataSync::get_sync_data( $users_need_syncing );
			$mark['type'] = 'user';
			$mark['ids']  = $users_need_syncing;
		}

		if ( ! empty( $user_data ) ) {
			$response = HubWooConnectionMananager::get_instance()->create_or_update_contacts( $user_data, $mark );
			if ( ( count( $user_data ) ) && 400 == $response['status_code'] ) {
				Hubwoo::hubwoo_handle_contact_sync( $response, $user_data, $mark );
			}
		} else {
			Hubwoo::hubwoo_stop_sync( 'stop-contact' );
		}
	}

	/**
	 * Re-sync Contacts to fetch Vid.
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_update_contacts_vid() {

		$contact_data = array();

		$mark = array();

		$args['meta_query'] = array(

			array(
				'key'     => 'hubwoo_user_vid',
				'compare' => 'NOT EXISTS',
			),
		);
		$args['role__in']   = get_option( 'hubwoo-selected-user-roles', array() );
		$args['number']     = 20;
		$args['fields']     = 'ID';

		$users = get_users( $args );

		if ( ! empty( $users ) ) {
			$mark['type'] = 'user';
			$mark['ids']  = $users;

			foreach ( $users as $user_id ) {
				$user_data                   = array();
				$user                        = get_user_by( 'id', $user_id );
				$user_data['email']          = $user->data->user_email;
				$user_data['customer_group'] = ! empty( $user->data->roles ) ? HubwooGuestOrdersManager::hubwoo_format_array( $user->data->roles ) : 'customer';
				$user_data['firstname']      = get_user_meta( $user_id, 'first_name', true );
				$user_data['lastname']       = get_user_meta( $user_id, 'last_name', true );
				$contacts[]                  = $user_data;
			}
		} else {

			$query = new WP_Query();

			$customer_orders = $query->query(
				array(
					'post_type'           => 'shop_order',
					'posts_per_page'      => 20,
					'post_status'         => array_keys( wc_get_order_statuses() ),
					'orderby'             => 'date',
					'order'               => 'desc',
					'fields'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
					'meta_query'          => array(
						'relation' => 'AND',
						array(
							'key'   => '_customer_user',
							'value' => 0,
						),
						array(
							'key'     => 'hubwoo_user_vid',
							'compare' => 'NOT EXISTS',
						),
					),
				)
			);

			if ( ! empty( $customer_orders ) ) {

				$mark['type'] = 'order';
				$mark['ids']  = $customer_orders;

				foreach ( $customer_orders as $order_id ) {
					$user_data                   = array();
					$user_data['email']          = get_post_meta( $order_id, '_billing_email', true );
					$user_data['customer_group'] = 'guest';
					$user_data['firstname']      = get_post_meta( $order_id, '_billing_first_name', true );
					$user_data['lastname']       = get_post_meta( $order_id, '_billing_last_name', true );
					$contacts[]                  = $user_data;
				}
			}
		}

		if ( ! empty( $contacts ) ) {

			$prepared_data = array();

			array_walk(
				$contacts,
				function( $user_data ) use ( &$prepared_data ) {
					if ( ! empty( $user_data ) ) {
						$temp_data = array();

						if ( isset( $user_data['email'] ) ) {
							$temp_data['email'] = $user_data['email'];
							unset( $user_data['email'] );
							foreach ( $user_data as $name => $value ) {
								if ( ! empty( $value ) ) {
									$temp_data['properties'][] = array(
										'property' => $name,
										'value'    => $value,
									);
								}
							}
							$prepared_data[] = $temp_data;
						}
					}
				}
			);

			HubWooConnectionMananager::get_instance()->create_or_update_contacts( $prepared_data, $mark );
		} else {
			delete_option( 'hubwoo_contact_vid_update' );
			as_unschedule_action( 'hubwoo_update_contacts_vid' );
		}
	}

	/**
	 * Source tracking from Checkout form.
	 *
	 * @since    1.0.4
	 */
	public function hubwoo_submit_checkout_form() {

		if ( ! empty( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) {

			$request = sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-process-checkout-nonce'] ) );
			$hub_cookie = isset( $_COOKIE['hubspotutk'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['hubspotutk'] ) ) : '';
			$nonce_value = wc_get_var( $request );

			if ( ! empty( $nonce_value ) && wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
				$data          = array();
				$form_id       = get_option( 'hubwoo_checkout_form_id', '' );
				$portal_id     = get_option( 'hubwoo_pro_hubspot_id', '' );
				$form_data     = ! empty( $_POST ) ? map_deep( wp_unslash( $_POST ), 'sanitize_text_field' ) : '';
				$required_data = array(
					'billing_email'      => 'email',
					'billing_first_name' => 'firstname',
					'billing_last_name'  => 'lastname',
				);

				foreach ( $required_data as $key => $name ) {
					if ( array_key_exists( $key, $form_data ) ) {
						$value = $form_data[ $key ];
						if ( empty( $value ) ) {
							continue;
						}
						$data[] = array(
							'name'  => $name,
							'value' => $value,
						);
					}
				}
				if ( ! empty( $data ) ) {
					$data    = array( 'fields' => $data );
					$context = self::hubwoo_page_source( $hub_cookie );
					if ( ! empty( $context['context'] ) ) {
						$data = array_merge( $data, $context );
					}
				}
				$response = HubWooConnectionMananager::get_instance()->submit_form_data( $data, $portal_id, $form_id );
				if ( 200 != $response['status_code'] ) {
					$all_errors = json_decode( $response['body'] );
					if ( ! empty( $all_errors ) ) {
						$get_errors = $all_errors->errors;
					}
					if ( ! empty( $get_errors ) ) {
						$get_error_type = $get_errors[0]->errorType;
					}
					if ( ! empty( $get_error_type ) && 'INVALID_HUTK' === $get_error_type ) {
						$context_data = self::hubwoo_page_source();
						if ( ! empty( $context_data['context'] ) ) {
							$data = array_merge( $data, $context_data );
							HubWooConnectionMananager::get_instance()->submit_form_data( $data, $portal_id, $form_id );
						}
					}
				}
			}
		}
	}

	/**
	 * Fetching current page context.
	 *
	 * @since    1.0.4
	 * @param string $hub_cookie hubspot cookies.
	 * @return array $context.
	 */
	public static function hubwoo_page_source( $hub_cookie = '' ) {

		$referrer = wp_get_referer();
		$obj_id = 0;
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		if ( $referrer ) {
			$obj_id = url_to_postid( $referrer );
		}

		$current_url = get_permalink( $obj_id );
		$page_name = get_the_title( $obj_id );
		$context = array();

		if ( ! empty( $current_url ) ) {
			$context['pageUri'] = $current_url;
		}
		if ( ! empty( $page_name ) ) {
			$context['pageName'] = $page_name;
		}
		if ( ! empty( $hub_cookie ) ) {
			$context['hutk'] = $hub_cookie;
		}
		if ( ! empty( $ip ) ) {
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				$context['ipAddress'] = $ip;
			}
		}
		$context = array(
			'context' => $context,
		);

		return $context;
	}

	/**
	 * Fetching property value.
	 *
	 * @since    1.2.6
	 */
	public function hubwoo_check_property_value() {

		if ( isset( $_GET['hubwoo_tab'] ) && 'hubwoo-overview' == $_GET['hubwoo_tab'] && 'yes' != get_option( 'hubwoo_connection_issue', 'no' ) ) {

			$property_updated = get_option( 'hubwoo_newsletter_property_update' );
			$abandoned_property_updated = get_option( 'hubwoo_abandoned_property_update' );
			
			if ( empty( $property_updated ) ) {
				
				$flag = true;
				$object_type = 'contacts';
				$property_name = 'newsletter_subscription';
				$property_changed = 'no';

				if ( Hubwoo::is_access_token_expired() ) {

					$hapikey = HUBWOO_CLIENT_ID;
					$hseckey = HUBWOO_SECRET_ID;
					$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

					if ( ! $status ) {

						$flag = false;
					}
				}

				if ( $flag ) {
					$response = HubWooConnectionMananager::get_instance()->hubwoo_read_object_property( $object_type, $property_name );
					
					if ( 200 == $response['status_code'] ) {
						$results = json_decode($response['body']);
					
						if ( isset($results->options) ) {
							$options = $results->options;

							if ( 'no' == $options[0]->value || 'yes' == $options[0]->value ) {
								$property_changed = 'no';
							} else {
								$property_changed = 'yes';
							}
						}
					}
				}

				update_option( 'hubwoo_newsletter_property_update', $property_changed );
			}

			if ( empty( $abandoned_property_updated ) ) {
				
				$flag = true;
				$object_type = 'contacts';
				$property_name = 'current_abandoned_cart';
				$abandoned_property_changed = 'no';

				if ( Hubwoo::is_access_token_expired() ) {

					$hapikey = HUBWOO_CLIENT_ID;
					$hseckey = HUBWOO_SECRET_ID;
					$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

					if ( ! $status ) {

						$flag = false;
					}
				}

				if ( $flag ) {
					$response = HubWooConnectionMananager::get_instance()->hubwoo_read_object_property( $object_type, $property_name );
					
					if ( 200 == $response['status_code'] ) {
						
						$results = json_decode($response['body']);
						
						if ( isset($results->options) ) {
							$options = $results->options;

							if ( 'no' == $options[0]->value || 'yes' == $options[0]->value ) {
								$abandoned_property_changed = 'no';
							} else {
								$abandoned_property_changed = 'yes';
							}
						}
					}
				}

				update_option( 'hubwoo_abandoned_property_update', $abandoned_property_changed );
			}
		}
	}

}
