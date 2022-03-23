<?php
/**
 * Handling data for sync.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Handling Data for Syncing Prodcess
 *
 * All the Data required for handling Users and
 * Order Data required by the plugin.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubwooDataSync {

	/**
	 * Retreive all of the
	 * required user Data and Count
	 *
	 * @param int    $count count of users to be get.
	 * @param string $response_type sync type.
	 * @param int    $limit offset for users.
	 */
	public function hubwoo_get_all_unique_user( $count = false, $response_type = 'customer', $limit = 20 ) {

		$limit        = $count ? -1 : $limit;
		$unique_users = 0;
		$args         = array();
		$date_range   = false;
		if ( 'yes' == get_option( 'hubwoo_customers_manual_sync', 'no' ) ) {
			$date_range = true;
			$from_date  = get_option( 'hubwoo_users_from_date', gmdate( 'd-m-Y' ) );
			$upto_date  = get_option( 'hubwoo_users_upto_date', gmdate( 'd-m-Y' ) );
		}

		// checking for guest users in the array.
		$roles = get_option( 'hubwoo_customers_role_settings', array() );

		if ( empty( $roles ) ) {
			global $hubwoo;
			$roles = array_keys( $hubwoo->hubwoo_get_user_roles() );
			$key   = array_search( 'guest_user', $roles );
			if ( false !== $key ) {
				unset( $roles[ $key ] );
			}
		}

		if ( in_array( 'guest_user', $roles ) ) {

			$key = array_search( 'guest_user', $roles );

			$order_statuses = get_option( 'hubwoo-selected-order-status', array() );

			if ( empty( $order_statuses ) || ( ! is_array( $order_statuses ) && count( $order_statuses ) < 1 ) ) {
				$order_statuses = array_keys( wc_get_order_statuses() );
			}

			if ( false !== $key ) {
				unset( $roles[ $key ] );
			}

			$order_args = array(
				'return'                 => 'ids',
				'limit'                  => $limit,
				'type'                   => wc_get_order_types(),
				'status'                 => $order_statuses,
				'customer'               => 0,
				'hubwoo_pro_guest_order' => 'synced',
			);

			if ( $date_range ) {
				$order_args['date_modified'] = gmdate( 'Y-m-d', strtotime( $from_date ) ) . '...' . gmdate( 'Y-m-d', strtotime( $upto_date . ' +1 day' ) );
			}

			$guest_orders = wc_get_orders( $order_args );

			$guest_emails = array_unique( self::get_guest_sync_data( $guest_orders, true ) );

			if ( 'guestOrder' == $response_type ) {
				return $guest_orders;
			}

			if ( is_array( $guest_orders ) ) {
				$unique_users += count( $guest_emails );
			}
		} else {

			if ( 'guestOrder' == $response_type ) {
				return false;
			}
		}

		if ( $date_range ) {

			$args['date_query'] = array(
				array(
					'after'     => gmdate( 'd-m-Y', strtotime( $from_date ) ),
					'before'    => gmdate( 'd-m-Y', strtotime( $upto_date . ' +1 day' ) ),
					'inclusive' => true,
				),
			);
		}

		// creating args for registered users.
		$args['meta_query'] = array(

			'relation' => 'OR',
			array(
				'key'     => 'hubwoo_pro_user_data_change',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'hubwoo_pro_user_data_change',
				'value'   => 'synced',
				'compare' => '!=',
			),
		);

		$args['role__in'] = $roles;

		$args['number'] = $limit;

		$args['fields'] = 'ID';

		$registered_users = get_users( $args );

		if ( $count ) {
			$unique_users += count( $registered_users );
			return $unique_users;
		}

		return $registered_users;
	}

	/**
	 * Retrieve User Data
	 * for Guest Users
	 *
	 * @param array $hubwoo_orders array of order to be synced.
	 * @param bool  $only_email true/false.
	 */
	public static function get_guest_sync_data( $hubwoo_orders, $only_email = false ) {

		global $hubwoo;

		$guest_user_emails     = array();
		$guest_contacts        = array();
		$guest_user_properties = array();

		if ( ! empty( $hubwoo_orders ) && count( $hubwoo_orders ) ) {

			foreach ( $hubwoo_orders as $order_id ) {

				$hubwoo_guest_order = wc_get_order( $order_id );

				if ( $hubwoo_guest_order instanceof WC_Order ) {

					$guest_email = get_post_meta( $order_id, '_billing_email', true );

					if ( ! empty( $guest_email ) && $only_email ) {
						$guest_user_emails[] = $guest_email;
						continue;
					}

					if ( empty( $guest_email ) ) {

						delete_post_meta( $order_id, 'hubwoo_pro_guest_order' );
						continue;
					}

					$guest_order_callback = new HubwooGuestOrdersManager( $order_id );

					$guest_user_properties = $guest_order_callback->get_order_related_properties( $order_id, $guest_email );

					$guest_user_properties = $hubwoo->hubwoo_filter_contact_properties( $guest_user_properties );

					$fname = get_post_meta( $order_id, '_billing_first_name', true );
					if ( ! empty( $fname ) ) {
						$guest_user_properties[] = array(
							'property' => 'firstname',
							'value'    => $fname,
						);
					}

					$lname = get_post_meta( $order_id, '_billing_last_name', true );
					if ( ! empty( $lname ) ) {
						$guest_user_properties[] = array(
							'property' => 'lastname',
							'value'    => $lname,
						);
					}

					$cname = get_post_meta( $order_id, '_billing_company', true );
					if ( ! empty( $cname ) ) {
						$guest_user_properties[] = array(
							'property' => 'company',
							'value'    => $cname,
						);
					}

					$city = get_post_meta( $order_id, '_billing_city', true );
					if ( ! empty( $city ) ) {
						$guest_user_properties[] = array(
							'property' => 'city',
							'value'    => $city,
						);
					}

					$state = get_post_meta( $order_id, '_billing_state', true );
					if ( ! empty( $state ) ) {
						$guest_user_properties[] = array(
							'property' => 'state',
							'value'    => $state,
						);
					}

					$country = get_post_meta( $order_id, '_billing_country', true );
					if ( ! empty( $country ) ) {
						$guest_user_properties[] = array(
							'property' => 'country',
							'value'    => Hubwoo::map_country_by_abbr( $country ),
						);
					}

					$address1 = get_post_meta( $order_id, '_billing_address_1', true );
					$address2 = get_post_meta( $order_id, '_billing_address_2', true );
					if ( ! empty( $address1 ) || ! empty( $address2 ) ) {
						$address                 = $address1 . ' ' . $address2;
						$guest_user_properties[] = array(
							'property' => 'address',
							'value'    => $address,
						);
					}

					$zip = get_post_meta( $order_id, '_billing_postcode', true );
					if ( ! empty( $zip ) ) {
						$guest_user_properties[] = array(
							'property' => 'zip',
							'value'    => $zip,
						);
					}

					$guest_phone = get_post_meta( $order_id, '_billing_phone', true );

					if ( ! empty( $guest_phone ) ) {
						$guest_user_properties[] = array(
							'property' => 'mobilephone',
							'value'    => $guest_phone,
						);
						$guest_user_properties[] = array(
							'property' => 'phone',
							'value'    => $guest_phone,
						);
					}

					$customer_new_order_flag = 'no';
					$prop_index              = array_search( 'customer_new_order', array_column( $guest_user_properties, 'property' ) );

					if ( Hubwoo_Admin::hubwoo_check_for_properties( 'order_recency_rating', 5, $guest_user_properties ) ) {

						if ( Hubwoo_Admin::hubwoo_check_for_properties( 'last_order_status', get_option( 'hubwoo_no_status', 'wc-completed' ), $guest_user_properties ) ) {

							$customer_new_order_flag = 'yes';
						}
					}

					if ( $prop_index ) {
						$guest_user_properties[ $prop_index ]['value'] = $customer_new_order_flag;
					} else {
						$guest_user_properties[] = array(
							'property' => 'customer_new_order',
							'value'    => $customer_new_order_flag,
						);
					}

					$guest_user_properties_data = array(
						'email'      => $guest_email,
						'properties' => $guest_user_properties,
					);

					$guest_contacts[] = $guest_user_properties_data;

					delete_post_meta( $order_id, 'hubwoo_pro_guest_order' );
				}
			}
		}
		$response = $only_email ? $guest_user_emails : $guest_contacts;

		return $response;
	}

	/**
	 * Scheduling Background Tasks
	 */
	public function schedule_background_task() {

		delete_option( 'hubwoo_ocs_data_synced' );
		delete_option( 'hubwoo_ocs_contacts_synced' );
		update_option( 'hubwoo_background_process_running', true );

		if ( ! as_next_scheduled_action( 'hubwoo_contacts_sync_background' ) ) {
			as_schedule_recurring_action( time(), 300, 'hubwoo_contacts_sync_background' );
		}
	}

	/**
	 * Starts Scheduling once
	 * the data has been retrieved
	 */
	public function hubwoo_start_schedule() {

		$hubwoo_unique_users = $this->hubwoo_get_all_unique_user( true );

		if ( $hubwoo_unique_users ) {
			$this->schedule_background_task();
		}
	}

	/**
	 * Retrieving Data For Registered
	 * Users
	 *
	 * @param array $hubwoo_unique_users array of users to sync.
	 * @return User Data
	 */
	public static function get_sync_data( $hubwoo_unique_users ) {

		// basic data function for user ids.
		$contacts = array();
		$role__in = get_option( 'hubwoo-selected-user-roles', array() );
		$user_role_in = get_option( 'hubwoo_customers_role_settings', array() );

		if ( ! empty( $hubwoo_unique_users ) && count( $hubwoo_unique_users ) ) {

			foreach ( $hubwoo_unique_users as $key => $id ) {

				$hubwoo_customer = new HubWooCustomer( $id );

				$email     = $hubwoo_customer->get_email();
				$user_data = get_user_by( 'email', $email );
				$role      = $user_data->roles[0];

				if ( in_array( $role, $role__in ) || in_array( $role, $user_role_in ) ) {

					if ( empty( $email ) ) {
						delete_user_meta( $id, 'hubwoo_pro_user_data_change' );
						continue;
					}
					$properties      = $hubwoo_customer->get_contact_properties();
					$user_properties = $hubwoo_customer->get_user_data_properties( $properties );
					$properties_data = array(
						'email'      => $email,
						'properties' => $user_properties,
					);

					$contacts[] = $properties_data;
				}
			}
		}
		return $contacts;
	}
}

