<?php
/**
 * All property callbacks for guest orders.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Manage all property callbacks for guest orders.
 *
 * Provide a list of functions to manage all the information
 * about guest contacts properties and there callback functions to
 * get value of that property.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubwooGuestOrdersManager {

	/**
	 * Constructor.
	 *
	 * @param int $order_id    contact id to get property values of.
	 */
	public function __construct( $order_id ) {

		$this->_order_id = $order_id;
	}

	/**
	 * Get customer shopping cart details from order meta.
	 *
	 * @return string    order meta detail.
	 * @param int    $order_id id of the order.
	 * @param string $property_name name of the property.
	 * @since 1.0.0
	 */
	public static function get_order_meta( $order_id, $property_name ) {

		return get_post_meta( $order_id, $property_name, true );
	}

	/**
	 * Guest customer details.
	 *
	 * @param int    $order_id id of the order.
	 * @param string $email email of the user.
	 * @return array        full of HubSpot contact properties and values
	 * @since 1.0.0
	 */
	public static function get_order_related_properties( $order_id, $email ) {

		$guest_user_properties = array();

		$billing_country  = self::get_order_meta( $order_id, '_billing_country' );
		$billing_state    = self::get_order_meta( $order_id, '_billing_state' );
		$shipping_state   = self::get_order_meta( $order_id, '_shipping_state' );
		$shipping_country = self::get_order_meta( $order_id, '_shipping_country' );

		$guest_user_properties[] = array(
			'property' => 'shipping_address_line_1',
			'value'    => self::get_order_meta( $order_id, '_shipping_address_1' ),
		);

		$guest_user_properties[] = array(
			'property' => 'shipping_address_line_2',
			'value'    => self::get_order_meta( $order_id, '_shipping_address_2' ),
		);

		$guest_user_properties[] = array(
			'property' => 'shipping_city',
			'value'    => self::get_order_meta( $order_id, '_shipping_city' ),
		);

		$guest_user_properties[] = array(
			'property' => 'shipping_country',
			'value'    => Hubwoo::map_country_by_abbr( $shipping_country ),
		);

		$guest_user_properties[] = array(
			'property' => 'shipping_state',
			'value'    => Hubwoo::map_state_by_abbr( $shipping_state, $shipping_country ),
		);

		$guest_user_properties[] = array(
			'property' => 'shipping_postal_code',
			'value'    => self::get_order_meta( $order_id, '_shipping_postcode' ),
		);

		$guest_user_properties[] = array(
			'property' => 'billing_address_line_1',
			'value'    => self::get_order_meta( $order_id, '_billing_address_1' ),
		);

		$guest_user_properties[] = array(
			'property' => 'billing_address_line_2',
			'value'    => self::get_order_meta( $order_id, '_billing_address_2' ),
		);

		$guest_user_properties[] = array(
			'property' => 'billing_city',
			'value'    => self::get_order_meta( $order_id, '_billing_city' ),
		);

		$guest_user_properties[] = array(
			'property' => 'billing_country',
			'value'    => Hubwoo::map_country_by_abbr( $billing_country ),
		);

		$guest_user_properties[] = array(
			'property' => 'billing_state',
			'value'    => Hubwoo::map_state_by_abbr( $billing_state, $billing_country ),
		);

		$guest_user_properties[] = array(
			'property' => 'billing_postal_code',
			'value'    => self::get_order_meta( $order_id, '_billing_postcode' ),
		);

		$categories_bought              = array();
		$skus_bought                    = array();
		$product_types_bought           = array();
		$last_3_products_ids            = array();
		$last_products_bought           = array();
		$last_order_date                = 0;
		$last_products_bought_html      = array();
		$days_differences               = array();
		$last_type_bought               = '';
		$first_date                     = 0;
		$last_date                      = 0;
		$average_days                   = array();
		$products_bought                = array();
		$last_product_bought            = '';
		$total_value_of_orders          = 0;
		$total_number_of_orders         = 0;
		$total_number_of_current_orders = 0;
		$last_order_for_html            = 0;
		$last_order_id                  = 0;

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
						'key'   => '_billing_email',
						'value' => $email,
					),
				),
			)
		);

		$guest_user_properties[] = array(
			'property' => 'customer_group',
			'value'    => 'guest',
		);

		$last_order            = ! empty( $customer_orders ) && is_array( $customer_orders ) ? $customer_orders[0] : '';
		$order_tracking_number = get_post_meta( $last_order, '_wc_shipment_tracking_items', true );
		if ( ! empty( $order_tracking_number ) ) {

			$shipment_data = $order_tracking_number[0];

			if ( ! empty( $shipment_data['date_shipped'] ) ) {
				$guest_user_properties[] = array(
					'property' => 'last_order_shipment_date',
					'value'    => self::hubwoo_set_utc_midnight( $shipment_data['date_shipped'] ),
				);
			}
			if ( ! empty( $shipment_data['tracking_number'] ) ) {
				$guest_user_properties[] = array(
					'property' => 'last_order_tracking_number',
					'value'    => $shipment_data['tracking_number'],
				);
			}
			if ( ! empty( $shipment_data['custom_tracking_link'] ) ) {
				$guest_user_properties[] = array(
					'property' => 'last_order_tracking_url',
					'value'    => $shipment_data['custom_tracking_link'],
				);
			}
		}

		$optin         = get_post_meta( $order_id, 'hubwoo_checkout_marketing_optin', true );
		$optin_sources = array();
		if ( ! empty( $optin ) && 'yes' == $optin ) {
			$optin_sources[] = 'checkout';
		} else {
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

		$guest_user_properties[] = array(
			'property' => 'newsletter_subscription',
			'value'    => $optin,
		);
		$guest_user_properties[] = array(
			'property' => 'marketing_newsletter',
			'value'    => self::hubwoo_format_array( $optin_sources ),
		);

		// if customer have orders.
		if ( is_array( $customer_orders ) && count( $customer_orders ) ) {

			// total number of customer orders.
			$total_number_of_orders = count( $customer_orders );

			$guest_user_properties[] = array(
				'property' => 'total_number_of_orders',
				'value'    => count( $customer_orders ),
			);

			$order_frequency = count( $customer_orders );

			$counter = 0;

			$products_count = 0;

			foreach ( $customer_orders as $order_id ) {

				// if order id not found let's check for another order.
				if ( ! $order_id ) {
					continue;
				}

				// order date.
				$order_date = get_post_time( 'U', false, $order_id );

				$last_date = $order_date;

				if ( 0 == $first_date ) {
					$first_date = $last_date;
				}

				$average_days[] = self::hubwoo_get_average_days( $first_date, $last_date );

				$first_date = $last_date;

				// get order.
				$order = new WC_Order( $order_id );

				// check for WP_Error object.
				if ( empty( $order ) || is_wp_error( $order ) ) {
					continue;
				}

				$order_status = $order->get_status();

				if ( 'failed' !== $order_status && 'cancelled' !== $order_status ) {

					$last_order_for_html ++;

					$order_items = $order->get_items();

					// check if order has items.
					if ( is_array( $order_items ) && count( $order_items ) ) {

						// let's loop each order item to get the details.
						foreach ( $order_items as $item_id_1 => $wc_order_item_product ) {

							if ( ! empty( $wc_order_item_product ) && $wc_order_item_product instanceof WC_Order_Item ) {

								$item_id         = $wc_order_item_product->get_product_id();
								$parent_item_sku = get_post_meta( $item_id, '_sku', true );
								$item_var_id     = $wc_order_item_product->get_variation_id();

								if ( get_post_status( $item_id ) == 'trash' || get_post_status( $item_id ) == false ) {

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

				$total_value_of_orders += floatval( $order_total );

				if ( 'failed' !== $order_status && 'cancelled' !== $order_status && 'refunded' !== $order_status && 'completed' !== $order_status ) {

					$total_number_of_current_orders ++;
				}

				// check for last order and finish all last order calculations.
				if ( ! $counter ) {

					// last order date.
					$guest_user_properties[] = array(
						'property' => 'last_order_date',
						'value'    => self::hubwoo_set_utc_midnight( get_post_time( 'U', false, $order_id ) ),
					);

					$last_order_date = get_post_time( 'U', false, $order_id );

					$guest_user_properties[] = array(
						'property' => 'last_order_value',
						'value'    => $order_total,
					);

					$guest_user_properties[] = array(
						'property' => 'last_order_order_number',
						'value'    => $order_id,
					);

					$guest_user_properties[] = array(
						'property' => 'last_order_fulfillment_status',
						'value'    => 'wc-' . $order->get_status(),
					);

					$guest_user_properties[] = array(
						'property' => 'last_order_status',
						'value'    => 'wc-' . $order->get_status(),
					);

					$guest_user_properties[] = array(
						'property' => 'last_order_currency',
						'value'    => get_post_meta( $order_id, '_order_currency', true ),
					);

					$guest_user_properties[] = array(
						'property' => 'last_product_bought',
						'value'    => self::hubwoo_format_array( $last_product_bought ),
					);

					$guest_user_properties[] = array(
						'property' => 'last_products_bought',
						'value'    => self::hubwoo_format_array( $last_products_bought ),
					);

					$guest_user_properties[] = array(
						'property' => 'last_skus_bought',
						'value'    => self::hubwoo_format_array( $skus_bought ),
					);

					$guest_user_properties[] = array(
						'property' => 'last_categories_bought',
						'value'    => self::hubwoo_format_array( $categories_bought ),
					);

					$guest_user_properties[] = array(
						'property' => 'last_product_types_bought',
						'value'    => self::hubwoo_format_array( $product_types_bought ),
					);

					$guest_user_properties[] = array(
						'property' => 'last_total_number_of_products_bought',
						'value'    => $products_count,
					);

					$guest_user_properties[] = array(
						'property' => 'last_products_bought_html',
						'value'    => HubWooContactProperties::get_instance()->hubwoo_last_order_html( $last_order_id ),
					);

				}

				// check for first order.
				if ( ( count( $customer_orders ) - 1 ) == $counter ) {

					// first order based calculation here..
					$guest_user_properties[] = array(
						'property' => 'first_order_date',
						'value'    => self::hubwoo_set_utc_midnight( get_post_time( 'U', false, $order_id ) ),
					);
					$guest_user_properties[] = array(
						'property' => 'first_order_value',
						'value'    => $order_total,
					);
				}

				$counter++;
			}

			// rest calculations here.
			$guest_user_properties[] = array(
				'property' => 'average_order_value',
				'value'    => floatval( $total_value_of_orders / $total_number_of_orders ),
			);

			$guest_user_properties[] = array(
				'property' => 'total_number_of_current_orders',
				'value'    => $total_number_of_current_orders,
			);

			$guest_user_properties[] = array(
				'property' => 'total_value_of_orders',
				'value'    => $total_value_of_orders,
			);

			$guest_user_properties[] = array(
				'property' => 'average_days_between_orders',
				'value'    => floatval( array_sum( $average_days ) / $total_number_of_orders ),
			);

			$guest_user_properties[] = array(
				'property' => 'skus_bought',
				'value'    => self::hubwoo_format_array( $skus_bought ),
			);

			$guest_user_properties[] = array(
				'property' => 'categories_bought',
				'value'    => self::hubwoo_format_array( $categories_bought ),
			);

			$guest_user_properties[] = array(
				'property' => 'product_types_bought',
				'value'    => self::hubwoo_format_array( $product_types_bought ),
			);

			$guest_user_properties[] = array(
				'property' => 'products_bought',
				'value'    => self::hubwoo_format_array( $products_bought ),
			);

			$guest_user_properties[] = array(
				'property' => 'total_number_of_products_bought',
				'value'    => $products_count,
			);

			if ( is_array( $last_3_products_ids ) && count( $last_3_products_ids ) ) {

				$counter = 1;

				foreach ( $last_3_products_ids as $last_3_product_id ) {

					$_product = wc_get_product( $last_3_product_id );

					if ( $_product instanceof WC_Product ) {

						$image_url = '';

						$guest_user_properties[] = array(
							'property' => 'last_products_bought_product_' . $counter . '_name',
							'value'    => $_product->get_title(),
						);

						$attachment_src = wp_get_attachment_image_src( get_post_thumbnail_id( $last_3_product_id ), 'single-post-thumbnail' );

						if ( empty( $attachment_src[0] ) ) {

							$parent_id      = $_product->get_parent_id();
							$attachment_src = wp_get_attachment_image_src( get_post_thumbnail_id( $parent_id ), 'single-post-thumbnail' );
						}

						if ( ! empty( $attachment_src[0] ) ) {
							$image_url = $attachment_src[0];
						}

						$guest_user_properties[] = array(
							'property' => 'last_products_bought_product_' . $counter . '_image_url',
							'value'    => $image_url,
						);

						$guest_user_properties[] = array(
							'property' => 'last_products_bought_product_' . $counter . '_price',
							'value'    => $_product->get_price(),
						);

						$guest_user_properties[] = array(
							'property' => 'last_products_bought_product_' . $counter . '_url',
							'value'    => get_permalink( $last_3_product_id ),
						);

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

			$order_monetary = $total_value_of_orders;

			$current_date    = gmdate( 'Y-m-d H:i:s', time() );
			$current_date    = new DateTime( $current_date );
			$last_order_date = gmdate( 'Y-m-d H:i:s', $last_order_date );
			$last_order_date = new DateTime( $last_order_date );
			$order_recency   = date_diff( $current_date, $last_order_date, true );

			$order_recency          = $order_recency->days;
			$monetary_rating        = 1;
			$order_recency_rating   = 1;
			$order_frequency_rating = 1;

			if ( $order_recency <= $hubwoo_rfm_at_5[0] ) {
				$order_recency_rating = 5;
			} elseif ( $order_recency >= $hubwoo_from_rfm_4[0] && $order_recency <= $hubwoo_to_rfm_4[0] ) {
				$order_recency_rating = 4;
			} elseif ( $order_recency >= $hubwoo_from_rfm_3[0] && $order_recency <= $hubwoo_to_rfm_3[0] ) {
				$order_recency_rating = 3;
			} elseif ( $order_recency >= $hubwoo_from_rfm_2[0] && $order_recency <= $hubwoo_to_rfm_2[0] ) {
				$order_recency_rating = 2;
			} else {
				$order_recency_rating = 1;
			}

			$guest_user_properties[] = array(
				'property' => 'order_recency_rating',
				'value'    => $order_recency_rating,
			);

			if ( $order_frequency > $hubwoo_rfm_at_5[1] ) {
				$order_frequency_rating = 5;
			} elseif ( $order_frequency >= $hubwoo_from_rfm_4[1] && $order_frequency < $hubwoo_to_rfm_4[1] ) {
				$order_frequency_rating = 4;
			} elseif ( $order_frequency >= $hubwoo_from_rfm_3[1] && $order_frequency < $hubwoo_to_rfm_3[1] ) {
				$order_frequency_rating = 3;
			} elseif ( $order_frequency >= $hubwoo_from_rfm_2[1] && $order_frequency < $hubwoo_to_rfm_2[1] ) {
				$order_frequency_rating = 2;
			} else {
				$order_frequency_rating = 1;
			}

			$guest_user_properties[] = array(
				'property' => 'order_frequency_rating',
				'value'    => $order_frequency_rating,
			);

			if ( $order_monetary > $hubwoo_rfm_at_5[2] ) {
				$monetary_rating = 5;
			} elseif ( $order_monetary >= $hubwoo_from_rfm_4[2] && $order_monetary < $hubwoo_to_rfm_4[2] ) {
				$monetary_rating = 4;
			} elseif ( $order_monetary >= $hubwoo_from_rfm_3[2] && $order_monetary < $hubwoo_to_rfm_3[2] ) {
				$monetary_rating = 3;
			} elseif ( $order_monetary >= $hubwoo_from_rfm_2[2] && $order_monetary < $hubwoo_to_rfm_2[2] ) {
				$monetary_rating = 2;
			} else {
				$monetary_rating = 1;
			}

			$guest_user_properties[] = array(
				'property' => 'monetary_rating',
				'value'    => $monetary_rating,
			);
		} else {

			$guest_user_properties[] = array(
				'property' => 'total_number_of_orders',
				'value'    => $total_number_of_orders,
			);
			$guest_user_properties[] = array(
				'property' => 'total_number_of_products_bought',
				'value'    => 0,
			);
			$guest_user_properties[] = array(
				'property' => 'total_number_of_current_orders',
				'value'    => $total_number_of_current_orders,
			);
			$guest_user_properties[] = array(
				'property' => 'total_value_of_orders',
				'value'    => $total_value_of_orders,
			);
			$guest_user_properties[] = array(
				'property' => 'last_total_number_of_products_bought',
				'value'    => 0,
			);

			$guest_user_properties[] = array(
				'property' => 'monetary_rating',
				'value'    => 1,
			);
			$guest_user_properties[] = array(
				'property' => 'order_frequency_rating',
				'value'    => 1,
			);
			$guest_user_properties[] = array(
				'property' => 'order_recency_rating',
				'value'    => 1,
			);
		}

		return $guest_user_properties;
	}

	/**
	 * Format an array in hubspot accepted enumeration value.
	 *
	 * @param  array $properties  Array of values.
	 * @return string       formatted string.
	 * @since 1.0.0
	 */
	public static function hubwoo_format_array( $properties ) {

		if ( is_array( $properties ) ) {

			$properties = array_unique( $properties );

			$properties = implode( ';', $properties );
		}

		return $properties;
	}

	/**
	 * Convert unix timestamp to hubwoo formatted midnight time.
	 *
	 * @param int  $unix_timestamp timestamp for date.
	 * @param bool $for_deals true/false.
	 * @return Unix midnight timestamp.
	 * @since  1.0.0
	 */
	public static function hubwoo_set_utc_midnight( $unix_timestamp, $for_deals = false ) {
		$string = gmdate( 'Y-m-d H:i:s', $unix_timestamp );
		$date   = new DateTime( $string );

		if ( $for_deals ) {

			$wptimezone = get_option( 'timezone_string', '' );
			if ( empty( $wptimezone ) ) {
				$wptimezone = 'UTC';
			}
			$timezone = new DateTimeZone( $wptimezone );
			//phpcs:disable
			$date->setTimezone( $timezone );
			//phpcs:enable
		} else {
			$date->modify( 'midnight' );
		}
		return $date->getTimestamp() * 1000;
	}

	/**
	 * Return days between two dates.
	 *
	 * @param int $first_date first order date.
	 * @param int $last_date last order date.
	 * @return int $datediff date differences.
	 * @since 1.0.0
	 */
	public static function hubwoo_get_average_days( $first_date, $last_date ) {

		$now       = $first_date;
		$your_date = $last_date;
		$datediff  = $now - $your_date;
		return floor( $datediff / ( 60 * 60 * 24 ) );
	}
}

