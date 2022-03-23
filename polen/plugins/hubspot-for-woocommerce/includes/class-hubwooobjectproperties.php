<?php
/**
 * All api GET/POST functionalities.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Handles all hubspot api reqests/response related functionalities of the plugin.
 *
 * Provide a list of functions to manage all the requests
 * that needs in our integration to get/fetch data
 * from/to hubspot.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubwooObjectProperties {

	/**
	 * The single instance of the class.
	 *
	 * @since   1.0.0
	 * @var HubwooObjectProperties  The single instance of the HubwooObjectProperties
	 */
	protected static $instance = null;
	/**
	 * Main HubwooObjectProperties Instance.
	 *
	 * Ensures only one instance of HubwooObjectProperties is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return HubwooObjectProperties - Main instance.
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
	 * Create/update contact and associate with a deal.
	 *
	 * @since 1.0.0
	 * @param int $user_id - User Id of the contact.
	 * @static
	 * @return  void.
	 */
	public static function hubwoo_ecomm_contacts_with_id( $user_id ) {

		$object_type           = 'CONTACT';
		$updates               = array();
		$user_info             = json_decode( json_encode( get_userdata( $user_id ) ), true );
		$user_email            = $user_info['data']['user_email'];
		$hubwoo_ecomm_customer = new HubwooEcommObject( $user_id, $object_type );
		$contact_properties    = $hubwoo_ecomm_customer->get_object_properties();
		$contact_properties    = apply_filters( 'hubwoo_map_ecomm_' . $object_type . '_properties', $contact_properties, $user_id );
		$updates[]             = array(
			'action'           => 'UPSERT',
			'changedAt'        => strtotime( gmdate( 'Y-m-d H:i:s ', time() ) ) . '000',
			'externalObjectId' => $user_email,
			'properties'       => $contact_properties,
		);

		if ( count( $updates ) ) {

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

				HubWooConnectionMananager::get_instance()->ecomm_sync_messages( $updates, $object_type );
				unset( $updates );
			}
		}
	}

	/**
	 * Create/update a guest user and associate with a deal.
	 *
	 * @since 1.0.0
	 * @param int $order_id - order id of the contact.
	 * @static
	 * @return  void.
	 */
	public static function hubwoo_ecomm_guest_user( $order_id ) {

		$guest_email = get_post_meta( $order_id, '_billing_email', true );

		$guest_updates = array();

		if ( ! empty( $guest_email ) ) {

			$object_type                          = 'CONTACT';
			$guest_user_info                      = array();
			$guest_user_info['email']             = $guest_email;
			$guest_user_info['first_name']        = get_post_meta( $order_id, '_billing_first_name', true );
			$guest_user_info['last_name']         = get_post_meta( $order_id, '_billing_last_name', true );
			$guest_user_info['billing_phone']     = get_post_meta( $order_id, '_billing_phone', true );
			$guest_user_info['billing_address_1'] = get_post_meta( $order_id, '_billing_address_1', true );
			$guest_user_info['billing_address_2'] = get_post_meta( $order_id, '_billing_address_2', true );
			$guest_user_info['billing_city']      = get_post_meta( $order_id, '_billing_city', true );
			$guest_user_info['billing_state']     = get_post_meta( $order_id, '_billing_state', true );
			$guest_user_info['billing_country']   = get_post_meta( $order_id, '_billing_country', true );
			$guest_user_info['billing_postcode']  = get_post_meta( $order_id, '_billing_postcode', true );
			$guest_user_info['contact_stage']     = 'customer';
			$guest_contact_properties             = apply_filters( 'hubwoo_map_ecomm_guest_' . $object_type . '_properties', $guest_user_info, $order_id );
			$guest_updates[]                      = array(
				'action'           => 'UPSERT',
				'changedAt'        => strtotime( gmdate( 'Y-m-d H:i:s ', time() ) ) . '000',
				'externalObjectId' => $guest_email,
				'properties'       => $guest_contact_properties,
			);
		}
		if ( count( $guest_updates ) ) {

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

				HubWooConnectionMananager::get_instance()->ecomm_sync_messages( $guest_updates, $object_type );
			}
		}
	}

	/**
	 * Create/update an ecommerce deal.
	 *
	 * @since 1.0.0
	 * @param int $order_id - order id.
	 * @param int $source - register or guest.
	 * @param int $customer_id - user id.
	 * @static
	 * @return  array sync response from HubSpot.
	 */
	public static function hubwoo_ecomm_sync_deal( $order_id, $source, $customer_id ) {
		$object_type       = 'DEAL';
		$deal_updates      = array();
		$hubwoo_ecomm_deal = new HubwooEcommObject( $order_id, $object_type );
		$deal_properties   = $hubwoo_ecomm_deal->get_object_properties();
		$deal_properties   = apply_filters( 'hubwoo_map_ecomm_' . $object_type . '_properties', $deal_properties, $order_id );
		$response          = '';

		if ( 'user' == $source ) {
			$user_info  = json_decode( wp_json_encode( get_userdata( $customer_id ) ), true );
			$user_email = $user_info['data']['user_email'];
			$contact    = $user_email;
			if ( empty( $contact ) ) {
				$contact = $customer_id;
			}
		} else {
			$contact = get_post_meta( $order_id, '_billing_email', true );
		}

		$deal_updates[] = array(
			'action'           => 'UPSERT',
			'changedAt'        => strtotime( gmdate( 'Y-m-d H:i:s ', time() ) ) . '000',
			'externalObjectId' => $order_id,
			'properties'       => $deal_properties,
			'associations'     => array( 'CONTACT' => array( $contact ) ),
		);

		if ( count( $deal_updates ) ) {

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

				$response = HubWooConnectionMananager::get_instance()->ecomm_sync_messages( $deal_updates, $object_type );

				if ( 204 == $response['status_code'] ) {
					$hubwoo_ecomm_deal_id = get_post_meta( $order_id, 'hubwoo_ecomm_deal_id', true );
					if ( ! empty( $hubwoo_ecomm_deal_id ) ) {
						do_action( 'hubwoo_ecomm_deal_created', $order_id );
					}
				}

				$update_deal_id = get_option( 'mwb_update_deal_ids' );

				if ( empty( $update_deal_id ) ) {
					$update_deal_id = array();
				}

				$update_deal_id[] = $order_id;

				$update_deal_id = array_unique( $update_deal_id );

				update_option( 'mwb_update_deal_ids', $update_deal_id );

				if ( ! as_next_scheduled_action( 'hubwoo_deal_update_schedule' ) ) {

					as_schedule_recurring_action( time(), 300, 'hubwoo_deal_update_schedule' );
				}

				$response = self::hubwoo_ecomm_sync_line_items( $order_id );

				return $response;
			}
		}
	}


	/**
	 * Create and Associate Line Items for an order.
	 *
	 * @since 1.0.0
	 * @param int $order_id - order id.
	 * @static
	 * @return  array sync response from HubSpot.
	 */
	public static function hubwoo_ecomm_sync_line_items( $order_id ) {

		if ( ! empty( $order_id ) ) {
			$object_type       = 'LINE_ITEM';
			$order             = wc_get_order( $order_id );
			$line_updates      = array();
			$order_items       = $order->get_items();
			$object_ids        = array();
			$count             = 0;
			$response          = array( 'status_code' => 206 );
			$no_products_found = false;

			if ( is_array( $order_items ) && count( $order_items ) ) {

				foreach ( $order_items as $item_key => $single_item ) :

					$product_id = $single_item->get_variation_id();
					if ( 0 === $product_id ) {
						$product_id = $single_item->get_product_id();
						if ( 0 === $product_id ) {
							$no_products_found = true;
						}
					}
					if ( get_post_status( $product_id ) == 'trash' || get_post_status( $product_id ) == false ) {
						continue;
					}
					$item_sku = get_post_meta( $product_id, '_sku', true );
					if ( empty( $item_sku ) ) {
						$item_sku = $product_id;
					}
					$quantity        = $single_item->get_quantity();
					$item_total      = ! empty( $single_item->get_total() ) ? $single_item->get_total() : 0;
					$item_sub_total  = ! empty( $single_item->get_subtotal() ) ? $single_item->get_subtotal() : 0;
					$product         = $single_item->get_product();
					$name            = self::hubwoo_ecomm_product_name( $product );
					$discount_amount = abs( $item_total - $item_sub_total );
					$discount_amount = $discount_amount / $quantity;
					$item_sub_total  = $item_sub_total / $quantity;
					$object_ids[]    = $item_key;

					$line_updates[] = array(
						'externalObjectId' => $item_key,
						'action'           => 'UPSERT',
						'changedAt'        => strtotime( gmdate( 'Y-m-d H:i:s ', time() ) ) . '000',
						'properties'       => array(
							'quantity'        => $quantity,
							'price'           => $item_sub_total,
							'amount'          => $item_total,
							'name'            => $name,
							'discount_amount' => $discount_amount,
							'sku'             => $item_sku,
							'tax_amount'      => $single_item->get_total_tax(),
						),
						'associations'     => array(
							'DEAL'    => array( $order_id ),
							'PRODUCT' => array( $product_id ),
						),
					);
				endforeach;
			}

			if ( count( $line_updates ) ) {
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
					$response = HubWooConnectionMananager::get_instance()->ecomm_sync_messages( $line_updates, $object_type );
				}
			}

			if ( 204 == $response['status_code'] || 206 == $response['status_code'] || empty( $object_ids ) ) {

				update_post_meta( $order_id, 'hubwoo_ecomm_deal_created', 'yes' );

				if ( 1 == get_option( 'hubwoo_deals_sync_running', 0 ) ) {

					$current_count = get_option( 'hubwoo_deals_current_sync_count', 0 );
					update_option( 'hubwoo_deals_current_sync_count', ++$current_count );
				}
			}

			return $response;
		}
	}


	/**
	 * Start syncing an ecommerce deal
	 *
	 * @since 1.0.0
	 * @param int $order_id - order id.
	 * @return  array sync response from HubSpot.
	 */
	public function hubwoo_ecomm_deals_sync( $order_id ) {

		if ( ! empty( $order_id ) ) {
			$hubwoo_ecomm_order = wc_get_order( $order_id );
			if ( $hubwoo_ecomm_order instanceof WC_Order ) {
				$customer_id = $hubwoo_ecomm_order->get_customer_id();

				if ( ! empty( $customer_id ) ) {
					$source = 'user';
					self::hubwoo_ecomm_contacts_with_id( $customer_id );
				} else {
					$source = 'guest';
					self::hubwoo_ecomm_guest_user( $order_id );
				}
				$response = self::hubwoo_ecomm_sync_deal( $order_id, $source, $customer_id );
				update_option( 'hubwoo_last_sync_date', time() );
				return $response;
			}
		}
	}
	/**
	 * Create a formatted name of the product.
	 *
	 * @since 1.0.0
	 * @param int $product product object.
	 * @return string formatted name of the product.
	 */
	public static function hubwoo_ecomm_product_name( $product ) {

		if ( $product->get_sku() ) {
			$identifier = $product->get_sku();
		} else {
			$identifier = '#' . $product->get_id();
		}
		return sprintf( '%2$s (%1$s)', $identifier, $product->get_name() );
	}


	/**
	 * Return formatted time for HubSpot
	 *
	 * @param  int $unix_timestamp current timestamp.
	 * @return string formatted time.
	 * @since 1.0.0
	 */
	public static function hubwoo_set_utc_midnight( $unix_timestamp ) {

		$string       = gmdate( 'Y-m-d H:i:s', $unix_timestamp );
		$date         = new DateTime( $string );
		$wp_time_zone = get_option( 'timezone_string', '' );
		if ( empty( $wp_time_zone ) ) {
			$wp_time_zone = 'UTC';
		}
		$time_zone = new DateTimeZone( $wp_time_zone );
		$date->setTimezone( $time_zone );
		return $date->getTimestamp() * 1000; // in miliseconds.
	}
}
