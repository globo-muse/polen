<?php
/**
 * All property callbacks.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Manage all property callbacks.
 *
 * Provide a list of functions to manage all the information
 * about contacts properties and there callback functions to
 * get value of that property.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubWooPropertyCallbacks {

	/**
	 * Contact id.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $_contact_id;

	/**
	 * WP user.
	 *
	 * @since 1.0.0
	 * @var WP_User
	 */
	protected $_user;

	/**
	 * Cache values.
	 *
	 * @var array.
	 */
	protected $_cache = array();

	/**
	 * Properties and there callbacks.
	 *
	 * @since 1.0.0
	 * @var Associated_array
	 */
	protected $_property_callbacks = array(

		'customer_group'                           => 'get_contact_group',
		'newsletter_subscription'                  => 'hubwoo_user_meta',
		'shopping_cart_customer_id'                => 'hubwoo_user_meta',
		'marketing_newsletter'                     => 'hubwoo_user_meta',

		'shipping_address_line_1'                  => 'get_user_meta',
		'shipping_address_line_2'                  => 'get_user_meta',
		'shipping_city'                            => 'get_user_meta',
		'shipping_state'                           => 'get_user_meta',
		'shipping_postal_code'                     => 'get_user_meta',
		'shipping_country'                         => 'get_user_meta',
		'billing_address_line_1'                   => 'get_user_meta',
		'billing_address_line_2'                   => 'get_user_meta',
		'billing_city'                             => 'get_user_meta',
		'billing_state'                            => 'get_user_meta',
		'billing_postal_code'                      => 'get_user_meta',
		'billing_country'                          => 'get_user_meta',

		'skus_bought'                              => 'hubwoo_user_meta',
		'last_skus_bought'                         => 'hubwoo_user_meta',

		'categories_bought'                        => 'hubwoo_user_meta',
		'last_categories_bought'                   => 'hubwoo_user_meta',

		'last_order_status'                        => 'hubwoo_user_meta',
		'last_order_fulfillment_status'            => 'hubwoo_user_meta',
		'last_order_tracking_number'               => 'hubwoo_user_meta',
		'last_order_tracking_url'                  => 'hubwoo_user_meta',
		'last_order_shipment_date'                 => 'hubwoo_user_meta',
		'last_order_order_number'                  => 'hubwoo_user_meta',
		'last_order_currency'                      => 'hubwoo_user_meta',
		'total_number_of_current_orders'           => 'hubwoo_user_meta',

		'total_value_of_orders'                    => 'hubwoo_user_meta',
		'average_order_value'                      => 'hubwoo_user_meta',
		'total_number_of_orders'                   => 'hubwoo_user_meta',
		'first_order_date'                         => 'hubwoo_user_meta',
		'first_order_value'                        => 'hubwoo_user_meta',
		'last_order_date'                          => 'hubwoo_user_meta',
		'last_order_value'                         => 'hubwoo_user_meta',
		'average_days_between_orders'              => 'hubwoo_user_meta',
		'account_creation_date'                    => 'hubwoo_user_meta',
		'monetary_rating'                          => 'hubwoo_user_meta',
		'order_frequency_rating'                   => 'hubwoo_user_meta',
		'order_recency_rating'                     => 'hubwoo_user_meta',

		'last_product_bought'                      => 'hubwoo_user_meta',
		'last_product_types_bought'                => 'hubwoo_user_meta',
		'last_products_bought'                     => 'hubwoo_user_meta',
		'last_products_bought_html'                => 'hubwoo_user_meta',
		'last_total_number_of_products_bought'     => 'hubwoo_user_meta',
		'product_types_bought'                     => 'hubwoo_user_meta',
		'last_products_bought_product_1_image_url' => 'hubwoo_user_meta',
		'last_products_bought_product_1_name'      => 'hubwoo_user_meta',
		'last_products_bought_product_1_price'     => 'hubwoo_user_meta',
		'last_products_bought_product_1_url'       => 'hubwoo_user_meta',
		'last_products_bought_product_2_image_url' => 'hubwoo_user_meta',
		'last_products_bought_product_2_name'      => 'hubwoo_user_meta',
		'last_products_bought_product_2_price'     => 'hubwoo_user_meta',
		'last_products_bought_product_2_url'       => 'hubwoo_user_meta',
		'last_products_bought_product_3_image_url' => 'hubwoo_user_meta',
		'last_products_bought_product_3_name'      => 'hubwoo_user_meta',
		'last_products_bought_product_3_price'     => 'hubwoo_user_meta',
		'last_products_bought_product_3_url'       => 'hubwoo_user_meta',
		'products_bought'                          => 'hubwoo_user_meta',
		'total_number_of_products_bought'          => 'hubwoo_user_meta',

		'last_subscription_order_number'           => 'hubwoo_user_subs_data',
		'last_subscription_parent_order_number'    => 'hubwoo_user_subs_data',
		'last_subscription_order_status'           => 'hubwoo_user_subs_data',
		'last_subscription_order_creation_date'    => 'hubwoo_user_subs_data',
		'last_subscription_order_paid_date'        => 'hubwoo_user_subs_data',
		'last_subscription_order_completed_date'   => 'hubwoo_user_subs_data',
		'last_subscription_trial_end_date'         => 'hubwoo_user_subs_data',
		'last_subscription_next_payment_date'      => 'hubwoo_user_subs_data',
		'last_subscription_billing_period'         => 'hubwoo_user_subs_data',
		'last_subscription_billing_interval'       => 'hubwoo_user_subs_data',
		'last_subscription_products'               => 'hubwoo_user_subs_data',
		'related_last_order_creation_date'         => 'hubwoo_user_subs_data',
		'related_last_order_paid_date'             => 'hubwoo_user_subs_data',
		'related_last_order_completed_date'        => 'hubwoo_user_subs_data',
	);

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param int $contact_id    contact id to get property values of.
	 */
	public function __construct( $contact_id ) {

		$this->_contact_id = $contact_id;

		$this->_user = get_user_by( 'id', $this->_contact_id );
	}

	/**
	 * Property value.
	 *
	 * @param  string $property_name    name of the contact property.
	 * @since 1.0.0
	 */
	public function _get_property_value( $property_name ) {

		$value = '';

		if ( ! empty( $property_name ) ) {

			$callback_function = $this->_get_property_callback( $property_name );

			if ( ! empty( $callback_function ) ) {

				$value = $this->$callback_function( $property_name );
			}
		}

		$value = apply_filters( 'hubwoo_contact_property_value', $value, $property_name, $this->_contact_id );

		return $value;
	}

	/**
	 * Filter the property callback to get value of.
	 *
	 * @param  strig $property_name   name of the property.
	 * @return string/false             callback function name or false.
	 */
	private function _get_property_callback( $property_name ) {

		if ( array_key_exists( $property_name, $this->_property_callbacks ) ) {

			$callback = $this->_property_callbacks[ $property_name ];
			return $callback;
		}

		return false;
	}

	/**
	 * Get contact user role.
	 *
	 * @return string    user role of the current contact.
	 * @since 1.0.0
	 */
	public function get_contact_group() {

		$user_roles = isset( $this->_user->roles ) ? $this->_user->roles : '';

		return HubwooGuestOrdersManager::hubwoo_format_array( $user_roles );
	}

	/**
	 * User email.
	 *
	 * @since 1.0.0
	 */
	public function _get_mail() {

		return $this->_user->data->user_email;
	}

	/**
	 * Get customer meta.
	 *
	 * @since 1.0.0
	 * @param string $property_name name of property.
	 */
	public function get_user_meta( $property_name ) {

		switch ( $property_name ) {

			case 'shipping_address_line_1':
				$key = 'shipping_address_1';
				break;

			case 'shipping_address_line_2':
				$key = 'shipping_address_2';
				break;

			case 'shipping_postal_code':
				$key = 'shipping_postcode';
				break;

			case 'billing_address_line_1':
				$key = 'billing_address_1';
				break;

			case 'billing_address_line_2':
				$key = 'billing_address_2';
				break;

			case 'billing_postal_code':
				$key = 'billing_postcode';
				break;

			default:
				$key = $property_name;
		}

		if ( 'billing_country' == $key ) {
			$value = get_user_meta( $this->_contact_id, $key, true );
			$value = Hubwoo::map_country_by_abbr( $value );
		} elseif ( 'billing_state' == $key ) {
			$value = get_user_meta( $this->_contact_id, $key, true );
			$value = Hubwoo::map_state_by_abbr( $value, get_user_meta( $this->_contact_id, 'billing_country', true ) );
		} elseif ( 'shipping_country' == $key ) {
			$value = get_user_meta( $this->_contact_id, $key, true );
			$value = Hubwoo::map_country_by_abbr( $value );
		} elseif ( 'shipping_state' == $key ) {
			$value = get_user_meta( $this->_contact_id, $key, true );
			$value = Hubwoo::map_state_by_abbr( $value, get_user_meta( $this->_contact_id, 'shipping_country', true ) );
		} else {
			$value = get_user_meta( $this->_contact_id, $key, true );
		}
		return $value;
	}

	/**
	 * User details with hubwoo_ prefix.
	 *
	 * @since 1.0.0
	 * @param string $key name of the user key.
	 */
	public function hubwoo_user_meta( $key ) {

		if ( array_key_exists( $key, $this->_cache ) ) {

			return $this->_cache[ $key ];
		}

		$order_statuses = get_option( 'hubwoo-selected-order-status', array() );

		if ( empty( $order_statuses ) ) {

			$order_statuses = array_keys( wc_get_order_statuses() );
		}

		$query = new WP_Query();

		$customer_orders = $query->query(
			array(
				'post_type'           => 'shop_order',
				'posts_per_page'      => -1,
				'post_status'         => $order_statuses,
				'orderby'             => 'date',
				'order'               => 'desc',
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'meta_query'          => array(
					array(
						'key'   => '_customer_user',
						'value' => $this->_contact_id,
					),
				),
			)
		);
		$last_order      = ! empty( $customer_orders ) && is_array( $customer_orders ) ? $customer_orders[0] : '';
		$customer        = new WP_User( $this->_contact_id );

		$order_frequency = 0;

		$account_creation = isset( $customer->data->user_registered ) ? $customer->data->user_registered : '';

		if ( ! empty( $account_creation ) ) {

			$account_creation = strtotime( $account_creation );
		}

		if ( ! empty( $account_creation ) && 0 < $account_creation ) {

			$this->_cache['account_creation_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( $account_creation );
		}

		$categories_bought         = array();
		$skus_bought               = array();
		$product_types_bought      = array();
		$last_3_products_ids       = array();
		$last_products_bought      = array();
		$last_order_date           = 0;
		$last_products_bought_html = array();
		$days_differences          = array();
		$last_type_bought          = '';
		$first_date                = 0;
		$last_date                 = 0;
		$average_days              = array();
		$products_bought           = array();
		$last_product_bought       = '';
		$last_order_for_html       = 0;
		$last_order_id             = 0;

		$order_tracking_number = get_post_meta( $last_order, '_wc_shipment_tracking_items', true );
		if ( ! empty( $order_tracking_number ) ) {

			$shipment_data = $order_tracking_number[0];

			if ( ! empty( $shipment_data['date_shipped'] ) ) {
				$this->_cache['last_order_shipment_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( $shipment_data['date_shipped'] );
			}
			if ( ! empty( $shipment_data['tracking_number'] ) ) {
				$this->_cache['last_order_tracking_number'] = $shipment_data['tracking_number'];
			}
			if ( ! empty( $shipment_data['custom_tracking_link'] ) ) {
				$this->_cache['last_order_tracking_url'] = $shipment_data['custom_tracking_link'];
			}
		}

		$this->_cache['shopping_cart_customer_id'] = $this->_contact_id;

		$this->_cache['total_number_of_current_orders'] = 0;

		$this->_cache['last_total_number_of_products_bought'] = 0;

		$this->_cache['total_number_of_orders'] = 0;

		$this->_cache['order_recency_rating'] = 1;

		$this->_cache['order_frequency_rating'] = 1;

		$this->_cache['monetary_rating'] = 1;

		$optin       = 'yes';
		$reg_optin   = get_user_meta( $this->_contact_id, 'hubwoo_registeration_marketing_optin', true );
		$check_optin = get_user_meta( $this->_contact_id, 'hubwoo_checkout_marketing_optin', true );

		$optin_sources = array();

		if ( empty( $reg_optin ) ) {
			$optin = $check_optin;
		}

		if ( empty( $optin ) ) {
			$optin = 'no';
		}

		$property_updated = get_option( 'hubwoo_newsletter_property_update' );

		if ( ! empty( $property_updated ) && 'yes' == $property_updated ) {
			if ( 'yes' == $optin ) {
				$optin = true;
			} else {
				$optin = false;
			}
		}

		if ( ! empty( $reg_optin ) && 'yes' == $reg_optin ) {
			$optin_sources[] = 'registration';
		}

		if ( ! empty( $check_optin ) && 'yes' == $check_optin ) {
			$optin_sources[] = 'checkout';
		}

		$this->_cache['newsletter_subscription'] = $optin;

		$this->_cache['marketing_newsletter'] = HubwooGuestOrdersManager::hubwoo_format_array( $optin_sources );

		$this->_cache['total_value_of_orders'] = 0;

		$this->_cache['total_number_of_products_bought'] = 0;

		// if customer have orders.
		if ( is_array( $customer_orders ) && count( $customer_orders ) ) {

			$this->_cache['total_number_of_orders'] = count( $customer_orders );

			$order_frequency = $this->_cache['total_number_of_orders'];

			$counter = 0;

			$products_count = 0;

			foreach ( $customer_orders as $order_id ) {

				if ( ! $order_id ) {

					continue;
				}

				// order date.
				$order_date = get_post_time( 'U', false, $order_id );

				$last_date = $order_date;

				if ( 0 == $first_date ) {

					$first_date = $last_date;
				}

				$average_days[] = $this->hubwoo_get_average_days( $first_date, $last_date );

				$first_date = $last_date;

				// get order.
				$order = new WC_Order( $order_id );

				// check for WP_Error object.
				if ( empty( $order ) || is_wp_error( $order ) ) {
					continue;
				}

				$order_status = $order->get_status();

				if ( 'failed' !== $order_status && 'cancelled' !== $order_status ) {

					$last_order_for_html++;

					$order_items = $order->get_items();

					// check if order has items.
					if ( is_array( $order_items ) && count( $order_items ) ) {

						// let's loop each order item to get the details.
						foreach ( $order_items as $item_id_1 => $wc_order_item_product ) {

							if ( ! empty( $wc_order_item_product ) && $wc_order_item_product instanceof WC_Order_Item ) {

								$item_id         = $wc_order_item_product->get_product_id();
								$parent_item_sku = get_post_meta( $item_id, '_sku', true );
								$item_var_id     = $wc_order_item_product->get_variation_id();

								if ( 'trash' == get_post_status( $item_id ) || false == get_post_status( $item_id ) ) {

									continue;
								}

								$product_cats_ids = wc_get_product_term_ids( $item_id, 'product_cat' );

								if ( is_array( $product_cats_ids ) && count( $product_cats_ids ) ) {

									foreach ( $product_cats_ids as $cat_id ) {

										$term                = get_term_by( 'id', $cat_id, 'product_cat' );
										$categories_bought[] = $term->slug;
									}
								}

								if ( $item_var_id ) {

									$item_id = $item_var_id;
								}

								$products_count += $wc_order_item_product->get_quantity();

								$item_sku = get_post_meta( $item_id, '_sku', true );

								if ( empty( $item_sku ) || '' == $item_sku ) {

									$item_sku = $parent_item_sku;
								}

								if ( empty( $item_sku ) || '' == $item_sku ) {

									$item_sku = $item_id;
								}

								$skus_bought[] = $item_sku;

								$post = get_post( $item_id );

								$product_uni_name = isset( $post->post_name ) ? $post->post_name : '';

								if ( ! empty( $product_uni_name ) ) {

									$products_bought[]      = $product_uni_name . '-' . $item_id;
									$last_product_bought    = $product_uni_name . '-' . $item_id;
									$last_products_bought[] = $product_uni_name . '-' . $item_id;
								}

								$product = $wc_order_item_product->get_product();

								if ( $product instanceof WC_Product ) {

									$product_type = $product->get_type();
								} else {

									$product_type = '';
								}

								if ( 'variation' == $product_type ) {

									$product_type = 'variable';
								}

								if ( 'subscription_variation' == $product_type ) {

									$product_type = 'variable-subscription';
								}

								if ( ! empty( $product_type ) ) {

									$product_types_bought[] = $product_type;
								}

								if ( count( $last_3_products_ids ) < 3 ) {

									$last_3_products_ids[] = $item_id;
								}

								if ( 1 == $last_order_for_html ) {

									$last_order_id = $order_id;
								}
							}
						}
					}
				}

				$order_total = $order->get_total();

				$this->_cache['total_value_of_orders'] += floatval( $order_total );

				if ( 'failed' !== $order_status && 'cancelled' !== $order_status && 'refunded' !== $order_status && 'completed' !== $order_status ) {

					$this->_cache['total_number_of_current_orders'] += 1;
				}

				// check for last order and finish all last order calculations.
				if ( ! $counter ) {

					$last_order_date = get_post_time( 'U', false, $order_id );

					if ( ! empty( $last_order_date ) ) {

						$this->_cache['last_order_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( $last_order_date );
					}

					$this->_cache['last_order_value'] = $order_total;

					$this->_cache['last_order_order_number'] = $order_id;

					$this->_cache['last_order_currency'] = get_post_meta( $order_id, '_order_currency', true );

					$this->_cache['last_order_fulfillment_status'] = 'wc-' . $order_status;

					$this->_cache['last_order_status'] = 'wc-' . $order->get_status();

					$this->_cache['last_product_bought'] = HubwooGuestOrdersManager::hubwoo_format_array( $last_product_bought );

					$this->_cache['last_products_bought'] = HubwooGuestOrdersManager::hubwoo_format_array( $last_products_bought );

					$this->_cache['last_skus_bought'] = HubwooGuestOrdersManager::hubwoo_format_array( $skus_bought );

					$this->_cache['last_categories_bought'] = HubwooGuestOrdersManager::hubwoo_format_array( $categories_bought );

					$this->_cache['last_product_types_bought'] = HubwooGuestOrdersManager::hubwoo_format_array( $product_types_bought );

					$this->_cache['last_total_number_of_products_bought'] = $products_count;

					$this->_cache['last_products_bought_html'] = HubWooContactProperties::get_instance()->hubwoo_last_order_html( $last_order_id );
				}

				// check for first order.
				if ( ( count( $customer_orders ) - 1 ) == $counter ) {

					// first order based calculation here..
					$first_order_date = get_post_time( 'U', false, $order_id );

					if ( ! empty( $first_order_date ) ) {

						$this->_cache['first_order_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( $first_order_date );
					}

					$this->_cache['first_order_value'] = $order_total;
				}

				$counter++;
			}

			// rest calculations here.
			$this->_cache['average_order_value'] = floatval( $this->_cache['total_value_of_orders'] / $this->_cache['total_number_of_orders'] );

			$this->_cache['average_days_between_orders'] = floatval( array_sum( $average_days ) / $this->_cache['total_number_of_orders'] );

			$this->_cache['skus_bought'] = HubwooGuestOrdersManager::hubwoo_format_array( $skus_bought );

			$this->_cache['categories_bought'] = HubwooGuestOrdersManager::hubwoo_format_array( $categories_bought );

			$this->_cache['product_types_bought'] = HubwooGuestOrdersManager::hubwoo_format_array( $product_types_bought );

			$this->_cache['products_bought'] = HubwooGuestOrdersManager::hubwoo_format_array( $products_bought );

			$this->_cache['total_number_of_products_bought'] = $products_count;

			if ( is_array( $last_3_products_ids ) && count( $last_3_products_ids ) ) {

				$counter = 1;

				foreach ( $last_3_products_ids as $last_3_product_id ) {

					$_product = wc_get_product( $last_3_product_id );

					if ( $_product instanceof WC_Product ) {

						$image_url = '';

						$this->_cache[ 'last_products_bought_product_' . $counter . '_name' ] = $_product->get_title();

						$attachment_src = wp_get_attachment_image_src( get_post_thumbnail_id( $last_3_product_id ), 'single-post-thumbnail' );

						if ( empty( $attachment_src[0] ) ) {

							$parent_id      = $_product->get_parent_id();
							$attachment_src = wp_get_attachment_image_src( get_post_thumbnail_id( $parent_id ), 'single-post-thumbnail' );
						}

						if ( ! empty( $attachment_src[0] ) ) {
							$image_url = $attachment_src[0];
						}

						$this->_cache[ 'last_products_bought_product_' . $counter . '_image_url' ] = $image_url;

						$this->_cache[ 'last_products_bought_product_' . $counter . '_price' ] = $_product->get_price();

						$this->_cache[ 'last_products_bought_product_' . $counter . '_url' ] = get_permalink( $last_3_product_id );

						$counter++;
					}
				}
			}

			$hubwoo_rfm_at_5 = get_option(
				'hubwoo_rfm_5',
				array(
					0 => 30,
					1 => 20,
					2 => 1000,
				)
			);

			$hubwoo_from_rfm_4 = get_option(
				'hubwoo_from_rfm_4',
				array(
					0 => 31,
					1 => 10,
					2 => 750,
				)
			);

			$hubwoo_to_rfm_4 = get_option(
				'hubwoo_to_rfm_4',
				array(
					0 => 90,
					1 => 20,
					2 => 1000,
				)
			);

			$hubwoo_from_rfm_3 = get_option(
				'hubwoo_from_rfm_3',
				array(
					0 => 91,
					1 => 5,
					2 => 500,
				)
			);

			$hubwoo_to_rfm_3 = get_option(
				'hubwoo_to_rfm_3',
				array(
					0 => 180,
					1 => 10,
					2 => 750,
				)
			);

			$hubwoo_from_rfm_2 = get_option(
				'hubwoo_from_rfm_2',
				array(
					0 => 181,
					1 => 2,
					2 => 250,
				)
			);

			$hubwoo_to_rfm_2 = get_option(
				'hubwoo_to_rfm_2',
				array(
					0 => 365,
					1 => 5,
					2 => 500,
				)
			);

			$hubwoo_rfm_at_1 = get_option(
				'hubwoo_rfm_1',
				array(
					0 => 365,
					1 => 2,
					2 => 250,
				)
			);

			$order_monetary = $this->_cache['total_value_of_orders'];

			$current_date    = gmdate( 'Y-m-d H:i:s', time() );
			$current_date    = new DateTime( $current_date );
			$last_order_date = gmdate( 'Y-m-d H:i:s', $last_order_date );
			$last_order_date = new DateTime( $last_order_date );
			$order_recency   = date_diff( $current_date, $last_order_date, true );

			$order_recency = $order_recency->days;

			if ( $order_recency <= $hubwoo_rfm_at_5[0] ) {

				$this->_cache['order_recency_rating'] = 5;
			} elseif ( $order_recency >= $hubwoo_from_rfm_4[0] && $order_recency <= $hubwoo_to_rfm_4[0] ) {

				$this->_cache['order_recency_rating'] = 4;
			} elseif ( $order_recency >= $hubwoo_from_rfm_3[0] && $order_recency <= $hubwoo_to_rfm_3[0] ) {

				$this->_cache['order_recency_rating'] = 3;
			} elseif ( $order_recency >= $hubwoo_from_rfm_2[0] && $order_recency <= $hubwoo_to_rfm_2[0] ) {

				$this->_cache['order_recency_rating'] = 2;
			} else {

				$this->_cache['order_recency_rating'] = 1;
			}

			if ( $order_frequency > $hubwoo_rfm_at_5[1] ) {

				$this->_cache['order_frequency_rating'] = 5;
			} elseif ( $order_frequency >= $hubwoo_from_rfm_4[1] && $order_frequency < $hubwoo_to_rfm_4[1] ) {

				$this->_cache['order_frequency_rating'] = 4;
			} elseif ( $order_frequency >= $hubwoo_from_rfm_3[1] && $order_frequency < $hubwoo_to_rfm_3[1] ) {

				$this->_cache['order_frequency_rating'] = 3;
			} elseif ( $order_frequency >= $hubwoo_from_rfm_2[1] && $order_frequency < $hubwoo_to_rfm_2[1] ) {

				$this->_cache['order_frequency_rating'] = 2;
			} else {

				$this->_cache['order_frequency_rating'] = 1;
			}

			if ( $order_monetary > $hubwoo_rfm_at_5[2] ) {

				$this->_cache['monetary_rating'] = 5;
			} elseif ( $order_monetary >= $hubwoo_from_rfm_4[2] && $order_monetary <= $hubwoo_to_rfm_4[2] ) {

				$this->_cache['monetary_rating'] = 4;
			} elseif ( $order_monetary >= $hubwoo_from_rfm_3[2] && $order_monetary < $hubwoo_to_rfm_3[2] ) {

				$this->_cache['monetary_rating'] = 3;
			} elseif ( $order_monetary >= $hubwoo_from_rfm_2[2] && $order_monetary < $hubwoo_to_rfm_2[2] ) {

				$this->_cache['monetary_rating'] = 2;
			} else {

				$this->_cache['monetary_rating'] = 1;
			}
		}

		if ( isset( $this->_cache[ $key ] ) ) {

			return $this->_cache[ $key ];
		}
	}

	/**
	 * Contact subscriptions data.
	 *
	 * @param  string $key for subscription properties.
	 * @since  1.0.0
	 */
	public function hubwoo_user_subs_data( $key ) {

		if ( Hubwoo::hubwoo_subs_active() && 'no' == get_option( 'hubwoo_subs_settings_enable', 'yes' ) ) {

			return;
		}

		if ( array_key_exists( $key, $this->_cache ) ) {

			return $this->_cache[ $key ];
		}

		$query = new WP_Query();

		$customer_orders = $query->query(
			array(
				'post_type'           => 'shop_subscription',
				'posts_per_page'      => 1,
				'post_status'         => 'any',
				'orderby'             => 'date',
				'order'               => 'desc',
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'meta_query'          => array(
					array(
						'key'   => '_customer_user',
						'value' => $this->_contact_id,
					),
				),
			)
		);

		// if customer have orders.
		if ( is_array( $customer_orders ) && count( $customer_orders ) ) {

			foreach ( $customer_orders as $counter => $order_id ) {

				// if order id not found let's check for another order.
				if ( ! $order_id ) {
					continue;
				}

				// get order.
				$order = wc_get_order( $order_id );

				// check for WP_Error object.
				if ( empty( $order ) || is_wp_error( $order ) ) {

					continue;
				}

				$subs_order = new WC_Subscription( $order_id );

				$order_items = $order->get_items();

				if ( is_array( $order_items ) && count( $order_items ) ) {

					$subs_products = array();

					// let's loop each order item to get the details.
					foreach ( $order_items as $item_id_1 => $wc_order_item_product ) {

						if ( ! empty( $wc_order_item_product ) && $wc_order_item_product instanceof WC_Order_Item ) {

							$item_id = $wc_order_item_product->get_product_id();

							if ( get_post_status( $item_id ) == 'trash' || get_post_status( $item_id ) == false ) {

								continue;
							}

							$post = get_post( $item_id );

							$product_uni_name = isset( $post->post_name ) ? $post->post_name : '';

							if ( ! empty( $product_uni_name ) ) {

								$subs_products[] = $product_uni_name . '-' . $item_id;
							}
						}
					}

					if ( count( $subs_products ) ) {

						$this->_cache['last_subscription_products'] = HubwooGuestOrdersManager::hubwoo_format_array( $subs_products );
					}

					if ( $subs_order->get_status() == 'pending-cancel' || $subs_order->get_status() == 'cancelled' ) {

						$this->_cache['last_subscription_products'] = ' ';
					}
				}

				$order_data = $order->get_data();

				if ( ! empty( $order_data['schedule_trial_end'] ) ) {

					$this->_cache['last_subscription_trial_end_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( $order_data['schedule_trial_end']->getTimestamp() );
				}

				if ( ! empty( $order_data['schedule_next_payment'] ) && $order_data['schedule_next_payment'] instanceof WC_DateTime ) {

					$this->_cache['last_subscription_next_payment_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( $order_data['schedule_next_payment']->getTimestamp() );
				}

				$this->_cache['last_subscription_order_status'] = 'wc-' . $subs_order->get_status();

				$this->_cache['last_subscription_order_number'] = $subs_order->get_id();

				$this->_cache['last_subscription_parent_order_number'] = $subs_order->get_parent_id();

				$date_created = ! empty( $subs_order->get_date( 'date_created' ) ) ? $subs_order->get_date( 'date_created' ) : '';

				if ( ! empty( $date_created ) ) {

					$this->_cache['last_subscription_order_creation_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( strtotime( $date_created ) );
				}

				$date_paid = ! empty( $subs_order->get_date( 'date_paid' ) ) ? $subs_order->get_date( 'date_paid' ) : '';

				if ( ! empty( $date_paid ) ) {

					$this->_cache['last_subscription_order_paid_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( strtotime( $date_paid ) );
				}

				$date_completed = ! empty( $subs_order->get_date( 'date_completed' ) ) ? $subs_order->get_date( 'date_completed' ) : '';

				if ( ! empty( $date_completed ) ) {

					$this->_cache['last_subscription_order_completed_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( strtotime( $date_completed ) );
				}

				$last_order_creation_date = ! empty( $subs_order->get_date( 'last_order_date_created' ) ) ? $subs_order->get_date( 'last_order_date_created' ) : '';

				if ( ! empty( $last_order_creation_date ) ) {

					$this->_cache['related_last_order_creation_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( strtotime( $last_order_creation_date ) );
				}

				$last_order_paid_date = ! empty( $subs_order->get_date( 'last_order_date_paid' ) ) ? $subs_order->get_date( 'last_order_date_paid' ) : '';

				if ( ! empty( $last_order_date_paid ) ) {

					$this->_cache['related_last_order_paid_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( strtotime( $last_order_paid_date ) );
				}

				$last_order_completion_date = ! empty( $subs_order->get_date( 'last_order_date_completed' ) ) ? $subs_order->get_date( 'last_order_date_completed' ) : '';

				if ( ! empty( $last_order_completion_date ) ) {

					$this->_cache['related_last_order_completed_date'] = HubwooGuestOrdersManager::hubwoo_set_utc_midnight( strtotime( $last_order_completion_date ) );
				}

				$this->_cache['last_subscription_billing_period'] = ! empty( $subs_order->get_billing_period() ) ? $subs_order->get_billing_period() : '';

				$this->_cache['last_subscription_billing_interval'] = ! empty( $subs_order->get_billing_interval() ) ? $subs_order->get_billing_interval() : '';
			}
		}

		if ( isset( $this->_cache[ $key ] ) ) {

			return $this->_cache[ $key ];
		}
	}

	/**
	 * Return days between two dates
	 *
	 * @param int $first_date first order date.
	 * @param int $last_date last order date.
	 * @since 1.0.0
	 */
	public function hubwoo_get_average_days( $first_date, $last_date ) {

		$now       = $first_date;
		$your_date = $last_date;
		$datediff  = $now - $your_date;
		return floor( $datediff / ( 60 * 60 * 24 ) );
	}
}
