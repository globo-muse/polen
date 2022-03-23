<?php
/**
 * Manage all contact properties.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Manage all contact properties.
 *
 * Provide a list of functions to manage all the information
 * about contacts properties and lists along with option to
 * change/update the mapping field on hubspot.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubWooContactProperties {

	/**
	 * Contact Property Groups.
	 *
	 * @since 1.0.0
	 * @var array name of contact property groups.
	 */
	private $groups;

	/**
	 * Contact Properties.
	 *
	 * @since 1.0.0
	 * @var array name of properties for contact.
	 */
	private $properties;

	/**
	 * Contact Lists.
	 *
	 * @since 1.0.0
	 * @var array name of the lists.
	 */
	private $lists;

	/**
	 * Workflows.
	 *
	 * @since 1.0.0
	 * @var array name of the workflows.
	 */
	private $workflows;

	/**
	 * HubWooContactProperties Instance.
	 *
	 * @since 1.0.0
	 * @var HubWooContactProperties
	 */
	protected static $_instance = null;

	/**
	 * Main HubWooContactProperties Instance.
	 *
	 * Ensures only one instance of HubWooContactProperties is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return HubWooContactProperties - Main instance.
	 */
	public static function get_instance() {

		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Define the contact prooperties related functionality.
	 *
	 * Set the contact groups and properties that we are going to use
	 * for creating/updating the contact information for our tacking purpose
	 * and providing other developers to add there field and group for tracking
	 * too by simply using our hooks.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->groups     = $this->_set( 'groups' );
		$this->properties = $this->_set( 'properties' );
		$this->lists      = $this->_set( 'lists' );
		$this->workflows  = $this->_set( 'workflows' );
	}

	/**
	 * Get groups/properties.
	 *
	 * @param string $option groups/properties.
	 * @param string $group_name name of group to get properties.
	 * @param bool   $all true/false.
	 * @return array array of groups/properties/lists/workflows information.
	 */
	public function _get( $option, $group_name = '', $all = false ) {

		if ( 'groups' === $option ) {

			return $this->groups;
		} elseif ( 'properties' === $option ) {

			if ( $all ) {
				$properties = $this->get_all_active_groups_properties( true );
				return $properties;
			}

			if ( ! empty( $group_name ) && isset( $this->properties[ $group_name ] ) && ! $all ) {
				return $this->properties[ $group_name ];
			} else {
				return $this->_get_group_properties( $group_name );
			}
		} elseif ( 'lists' === $option ) {
			return $this->lists;
		} elseif ( 'workflows' === $option ) {
			return $this->workflows;
		}
	}

	/**
	 * Get an array of required option.
	 *
	 * @param  String $option         the identifier.
	 * @return Array        An array of values.
	 * @since 1.0.0
	 */
	private function _set( $option ) {

		$values = array();

		if ( 'groups' === $option ) {

			// order details.
			$values[] = array(
				'name'        => 'order',
				'displayName' => __( 'Order Information', 'hubspot-for-woocommerce' ),
			);

			// products bought details.
			$values[] = array(
				'name'        => 'last_products_bought',
				'displayName' => __( 'Products Bought', 'hubspot-for-woocommerce' ),
			);

			// shopping cart details.
			$values[] = array(
				'name'        => 'shopping_cart_fields',
				'displayName' => __( 'Shopping Cart Information', 'hubspot-for-woocommerce' ),
			);

			// customer details.
			$values[] = array(
				'name'        => 'customer_group',
				'displayName' => __( 'Customer Group', 'hubspot-for-woocommerce' ),
			);

			// categories bought details.
			$values[] = array(
				'name'        => 'categories_bought',
				'displayName' => __( 'Categories Bought', 'hubspot-for-woocommerce' ),
			);
			// RFM details.
			$values[] = array(
				'name'        => 'rfm_fields',
				'displayName' => __( 'RFM Information', 'hubspot-for-woocommerce' ),
			);
			// skus bought details.
			$values[] = array(
				'name'        => 'skus_bought',
				'displayName' => __( 'SKUs Bought', 'hubspot-for-woocommerce' ),
			);
			// roi tracking.
			$values[] = array(
				'name'        => 'roi_tracking',
				'displayName' => __( 'ROI Tracking', 'hubspot-for-woocommerce' ),
			);
			// Abandeond Cart.
			$values[] = array(
				'name'        => 'abandoned_cart',
				'displayName' => __( 'Abandoned Cart Details', 'hubspot-for-woocommerce' ),
			);

			// filter for new groups.
			$values = apply_filters( 'hubwoo_sync_groups', $values );
		} elseif ( 'properties' === $option ) {

			// let's check for all active tracking groups and get there associated properties.
			$values = $this->get_all_active_groups_properties();
		} elseif ( 'lists' === $option ) {

			$values = $this->get_all_active_lists();
		} elseif ( 'workflows' === $option ) {

			$values = $this->get_all_workflows();
		}

		// add your values to the either groups or properties.
		return apply_filters( 'hubwoo_contact_' . $option, $values );
	}

	/**
	 * Add subscription groups.
	 *
	 * @param array $values predefined groups.
	 * @return Array Properties array with there associated group.
	 * @since 1.0.0
	 */
	public static function _get_subs_groups( $values = array() ) {

		$values[] = array(
			'name'        => 'subscriptions_details',
			'displayName' => __( 'Subscriptions Details', 'hubspot-for-woocommerce' ),
		);
		return apply_filters( 'hubwoo_subs_groups', $values );
	}

	/**
	 * Check for the active groups and get there properties.
	 *
	 * @param bool $all to get all propertues or not.
	 * @return Array Properties array with there associated group.
	 * @since 1.0.0
	 */
	private function get_all_active_groups_properties( $all = false ) {

		$active_groups_properties = array();

		$active_groups = $all ? $this->_get( 'groups' ) : $this->get_active_groups();

		if ( is_array( $active_groups ) && count( $active_groups ) ) {

			foreach ( $active_groups as $active_group ) {

				if ( ! empty( $active_group ) ) {

					if ( $all ) {
						$active_groups_properties[ $active_group['name'] ] = $this->_get_group_properties( $active_group['name'] );
					} else {
						$active_groups_properties[ $active_group ] = $this->_get_group_properties( $active_group );
					}
				}
			}
		}

		return apply_filters( 'hubwoo_active_groups_properties', $active_groups_properties );
	}

	/**
	 * Filter extra properties to avaoid error on hubspot.
	 *
	 * @return only created properties
	 * @since 1.0.0
	 */
	public function hubwoo_get_filtered_properties() {

		$filtered_properties = array();

		$all_filtered_properties = array();

		$active_groups = $this->get_active_groups();

		if ( is_array( $active_groups ) && count( $active_groups ) ) {

			foreach ( $active_groups as $active_group ) {

				if ( ! empty( $active_group ) && ! is_array( $active_group ) ) {

					$active_groups_properties[ $active_group ] = $this->_get_group_properties( $active_group );
				}
			}
		}

		if ( ! empty( $active_groups_properties ) ) {

			$group_name = '';

			$created_properties = array_map(
				function( $property ) {
					return str_replace( "'", '', $property );
				},
				get_option( 'hubwoo-properties-created', array() )
			);

			foreach ( $active_groups_properties as $group_name_key => $single_group_property ) {

				$group_name = $group_name_key;

				$filtered_properties = array();

				foreach ( $single_group_property as $single_property ) {

					if ( isset( $single_property['name'] ) && in_array( $single_property['name'], $created_properties ) ) {

						$filtered_properties[] = $single_property;
					}
				}

				$all_filtered_properties[ $group_name ] = $filtered_properties;
			}
		}

		return apply_filters( 'hubwoo_active_groups_properties', $all_filtered_properties );
	}


	/**
	 * Filter for active groups only.
	 *
	 * @return Array active group names.
	 * @since 1.0.0
	 */
	private function get_active_groups() {

		$active_groups = array();

		$all_groups = $this->_get( 'groups' );
		if ( is_array( $all_groups ) && count( $all_groups ) ) {

			foreach ( $all_groups as $group_details ) {

				$group_name = isset( $group_details['name'] ) ? $group_details['name'] : '';

				if ( ! empty( $group_name ) ) {

					$created_groups = get_option( 'hubwoo-groups-created', array() );

					$is_active = false;

					if ( in_array( $group_name, $created_groups ) ) {

						$is_active = true;
					}

					if ( $is_active ) {

						$active_groups[] = $group_name;
					}
				}
			}
		}
		return apply_filters( 'hubwoo_active_groups', $active_groups );
	}


	/**
	 * Get all the groups properties.
	 *
	 * @param   string $group_name     name of the existed valid hubspot contact properties group.
	 * @return  Array      Properties array.
	 * @since 1.0.0
	 */
	private function _get_group_properties( $group_name ) {

		$group_properties = array();

		if ( ! empty( $group_name ) ) {

			if ( 'customer_group' === $group_name ) {

				$group_properties[] = array(
					'name'      => 'customer_group',
					'label'     => __( 'Customer Group/ User role', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'newsletter_subscription',
					'label'     => __( 'Accepts Marketing', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => true,
					'options'   => $this->get_user_marketing_action(),
				);

				$group_properties[] = array(
					'name'      => 'marketing_newsletter',
					'label'     => __( 'Marketing Newsletter', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'checkbox',
					'formField' => true,
					'options'   => $this->get_user_marketing_sources(),
				);

				$group_properties[] = array(
					'name'      => 'shopping_cart_customer_id',
					'label'     => __( 'Shopping Cart ID', 'hubspot-for-woocommerce' ),
					'type'      => 'number',
					'fieldType' => 'number',
					'formField' => false,
				);
			} elseif ( 'shopping_cart_fields' === $group_name ) {

				$group_properties[] = array(
					'name'      => 'shipping_address_line_1',
					'label'     => __( 'Shipping Address Line 1', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'shipping_address_line_2',
					'label'     => __( 'Shipping Address Line 2', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'shipping_city',
					'label'     => __( 'Shipping City', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'shipping_state',
					'label'     => __( 'Shipping State', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'shipping_postal_code',
					'label'     => __( 'Shipping Postal Code', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'shipping_country',
					'label'     => __( 'Shipping Country', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'billing_address_line_1',
					'label'     => __( 'Billing Address Line 1', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'billing_address_line_2',
					'label'     => __( 'Billing Address Line 2', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'billing_city',
					'label'     => __( 'Billing City', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'billing_state',
					'label'     => __( 'Billing State', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'billing_postal_code',
					'label'     => __( 'Billing Postal Code', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);

				$group_properties[] = array(
					'name'      => 'billing_country',
					'label'     => __( 'Billing Country', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => true,
				);
			} elseif ( 'last_products_bought' === $group_name ) {

				$group_properties[] = array(
					'name'      => 'last_product_bought',
					'label'     => __( 'Last Product Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_product_types_bought',
					'label'     => __( 'Last Product Types Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought',
					'label'     => __( 'Last Products Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_html',
					'label'     => __( 'Last Products Bought HTML', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_total_number_of_products_bought',
					'label'     => __( 'Last Total Number Of Products Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'number',
					'fieldType' => 'number',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'product_types_bought',
					'label'     => __( 'Product Types Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'products_bought',
					'label'     => __( 'Products Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'total_number_of_products_bought',
					'label'     => __( 'Total Number Of Products Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'number',
					'fieldType' => 'number',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_product_1_image_url',
					'label'     => __( 'Last Products Bought Product 1 Image URL', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_product_1_name',
					'label'     => __( 'Last Products Bought Product 1 Name', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'               => 'last_products_bought_product_1_price',
					'label'              => __( 'Last Products Bought Product 1 Price', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'showCurrencySymbol' => true,
					'formField'          => false,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_product_1_url',
					'label'     => __( 'Last Products Bought Product 1 Url', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_product_2_image_url',
					'label'     => __( 'Last Products Bought Product 2 Image URL', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_product_2_name',
					'label'     => __( 'Last Products Bought Product 2 Name', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'               => 'last_products_bought_product_2_price',
					'label'              => __( 'Last Products Bought Product 2 Price', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_product_2_url',
					'label'     => __( 'Last Products Bought Product 2 Url', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_product_3_image_url',
					'label'     => __( 'Last Products Bought Product 3 Image URL', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_product_3_name',
					'label'     => __( 'Last Products Bought Product 3 Name', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'               => 'last_products_bought_product_3_price',
					'label'              => __( 'Last Products Bought Product 3 Price', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'last_products_bought_product_3_url',
					'label'     => __( 'Last Products Bought Product 3 Url', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);
			} elseif ( 'order' === $group_name ) {

				$group_properties[] = array(
					'name'      => 'last_order_status',
					'label'     => __( 'Last Order Status', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => false,
					'options'   => $this->get_order_statuses(),
				);

				$group_properties[] = array(
					'name'      => 'last_order_fulfillment_status',
					'label'     => __( 'Last Order Fulfillment Status', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => false,
					'options'   => $this->get_order_statuses(),
				);

				$group_properties[] = array(
					'name'      => 'last_order_tracking_number',
					'label'     => __( 'Last Order Tracking Number', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_order_tracking_url',
					'label'     => __( 'Last Order Tracking URL', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_order_shipment_date',
					'label'     => __( 'Last Order Shipment Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_order_order_number',
					'label'     => __( 'Last Order Number', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_order_currency',
					'label'     => __( 'Last Order Currency', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'total_number_of_current_orders',
					'label'     => __( 'Total Number of Current Orders', 'hubspot-for-woocommerce' ),
					'type'      => 'number',
					'fieldType' => 'number',
					'formField' => false,
				);
			} elseif ( 'rfm_fields' === $group_name ) {

				$group_properties[] = array(
					'name'               => 'total_value_of_orders',
					'label'              => __( 'Total Value of Orders', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'               => 'average_order_value',
					'label'              => __( 'Average Order Value', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'total_number_of_orders',
					'label'     => __( 'Total Number of Orders', 'hubspot-for-woocommerce' ),
					'type'      => 'number',
					'fieldType' => 'number',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'               => 'first_order_value',
					'label'              => __( 'First Order Value', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'first_order_date',
					'label'     => __( 'First Order Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'               => 'last_order_value',
					'label'              => __( 'Last Order Value', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'last_order_date',
					'label'     => __( 'Last Order Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'average_days_between_orders',
					'label'     => __( 'Average Days Between Orders', 'hubspot-for-woocommerce' ),
					'type'      => 'number',
					'fieldType' => 'number',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'account_creation_date',
					'label'     => __( 'Account Creation Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'monetary_rating',
					'label'     => __( 'Monetary Rating', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => false,
					'options'   => $this->get_rfm_rating(),
				);

				$group_properties[] = array(
					'name'      => 'order_frequency_rating',
					'label'     => __( 'Order Frequency Rating', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => false,
					'options'   => $this->get_rfm_rating(),
				);

				$group_properties[] = array(
					'name'      => 'order_recency_rating',
					'label'     => __( 'Order Recency Rating', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => false,
					'options'   => $this->get_rfm_rating(),
				);
			} elseif ( 'categories_bought' === $group_name ) {

				$group_properties[] = array(
					'name'      => 'last_categories_bought',
					'label'     => __( 'Last Categories Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'categories_bought',
					'label'     => __( 'Categories Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);
			} elseif ( 'skus_bought' === $group_name ) {

				$group_properties[] = array(
					'name'      => 'last_skus_bought',
					'label'     => __( 'Last SKUs Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'skus_bought',
					'label'     => __( 'SKUs Bought', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);
			} elseif ( 'subscriptions_details' == $group_name ) {

				$group_properties[] = array(
					'name'      => 'last_subscription_order_number',
					'label'     => __( 'Last Subscription Order Number', 'hubspot-for-woocommerce' ),
					'type'      => 'number',
					'fieldType' => 'number',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_parent_order_number',
					'label'     => __( 'Last Subscription Parent Order Number', 'hubspot-for-woocommerce' ),
					'type'      => 'number',
					'fieldType' => 'number',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_order_status',
					'label'     => __( 'Last Subscription Order Status', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => false,
					'options'   => $this->get_subscription_status_options(),
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_order_creation_date',
					'label'     => __( 'Last Subscription Order Creation Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_order_paid_date',
					'label'     => __( 'Last Subscription Order Paid Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_order_completed_date',
					'label'     => __( 'Last Subscription Order Completed Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'related_last_order_creation_date',
					'label'     => __( 'Related Last Order Creation Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'related_last_order_paid_date',
					'label'     => __( 'Related Last Order Paid Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'related_last_order_completed_date',
					'label'     => __( 'Related Last Order Completed Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_trial_end_date',
					'label'     => __( 'Last Subscription Trial End Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_next_payment_date',
					'label'     => __( 'Last Subscription Next Payment Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_billing_period',
					'label'     => __( 'Last Subscription Billing Period', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => false,
					'options'   => $this->get_subscriptions_billing_period(),
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_billing_interval',
					'label'     => __( 'Last Subscription Billing Interval', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => false,
					'options'   => $this->get_subscriptions_billing_interval(),
				);

				$group_properties[] = array(
					'name'      => 'last_subscription_products',
					'label'     => __( 'Last Subscription Products', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formField' => false,
				);
			} elseif ( 'roi_tracking' === $group_name ) {

				$group_properties[] = array(
					'name'      => 'customer_new_order',
					'label'     => __( 'Customer New Order', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'options'   => $this->hubwoo_new_order_status(),
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_recovery_workflow_conversion',
					'label'     => __( 'Abandoned Cart Recovery Workflow Conversion', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'booleancheckbox',
					'formField' => false,
					'options'   => $this->hubwoo_campaign_conversion_options(),
				);

				$group_properties[] = array(
					'name'               => 'abandoned_cart_recovery_workflow_conversion_amount',
					'label'              => __( 'Abandoned Cart Recovery Workflow Conversion Amount', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_recovery_workflow_conversion_date',
					'label'     => __( 'Abandoned Cart Recovery Workflow Conversion Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_recovery_workflow_start_date',
					'label'     => __( 'Abandoned Cart Recovery Workflow Start Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'current_roi_campaign',
					'label'     => __( 'Current ROI Campaign', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formField' => false,
					'options'   => $this->get_all_campaign_names(),
				);

				$group_properties[] = array(
					'name'      => 'customer_reengagement_workflow_conversion',
					'label'     => __( 'Customer Reengagement Workflow Conversion', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'booleancheckbox',
					'formField' => false,
					'options'   => $this->hubwoo_campaign_conversion_options(),
				);

				$group_properties[] = array(
					'name'               => 'customer_reengagement_workflow_conversion_amount',
					'label'              => __( 'Customer Reengagement Workflow Conversion Amount', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'customer_reengagement_workflow_conversion_date',
					'label'     => __( 'Customer Reengagement Workflow Conversion Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'customer_reengagement_workflow_start_date',
					'label'     => __( 'Customer Reengagement Workflow Start Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'customer_rewards_workflow_conversion',
					'label'     => __( 'Customer Rewards Workflow Conversion', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'booleancheckbox',
					'formField' => false,
					'options'   => $this->hubwoo_campaign_conversion_options(),
				);

				$group_properties[] = array(
					'name'               => 'customer_rewards_workflow_conversion_amount',
					'label'              => __( 'Customer Rewards Workflow Conversion Amount', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'customer_rewards_workflow_conversion_date',
					'label'     => __( 'Customer Rewards Workflow Conversion Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'customer_rewards_workflow_start_date',
					'label'     => __( 'Customer Rewards Workflow Start Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'mql_capture_nurture_conversion_conversion',
					'label'     => __( 'MQL Capture, Nurture & Conversion Conversion', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'booleancheckbox',
					'formField' => false,
					'options'   => $this->hubwoo_campaign_conversion_options(),
				);

				$group_properties[] = array(
					'name'               => 'mql_capture_nurture_conversion_conversion_amount',
					'label'              => __( 'MQL Capture, Nurture & Conversion Conversion Amount', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'mql_capture_nurture_conversion_conversion_date',
					'label'     => __( 'MQL Capture, Nurture & Conversion Conversion Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'mql_capture_nurture_conversion_start_date',
					'label'     => __( 'MQL Capture, Nurture & Conversion Start date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'new_customer_workflow_conversion',
					'label'     => __( 'New Customer Workflow Conversion', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'booleancheckbox',
					'formField' => false,
					'options'   => $this->hubwoo_campaign_conversion_options(),
				);

				$group_properties[] = array(
					'name'               => 'new_customer_workflow_conversion_amount',
					'label'              => __( 'New Customer Workflow Conversion Amount', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'new_customer_workflow_conversion_date',
					'label'     => __( 'New Customer Workflow Conversion Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'new_customer_workflow_start_date',
					'label'     => __( 'New Customer Workflow Start Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'second_purchase_workflow_conversion',
					'label'     => __( 'Second Purchase Workflow Conversion', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'booleancheckbox',
					'formField' => false,
					'options'   => $this->hubwoo_campaign_conversion_options(),
				);

				$group_properties[] = array(
					'name'               => 'second_purchase_workflow_conversion_amount',
					'label'              => __( 'Second Purchase Workflow Conversion Amount', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'second_purchase_workflow_conversion_date',
					'label'     => __( 'Second Purchase Workflow Conversion Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'second_purchase_workflow_start_date',
					'label'     => __( 'Second Purchase Workflow Start Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'third_purchase_workflow_conversion',
					'label'     => __( 'Third Purchase Workflow Conversion', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'booleancheckbox',
					'formField' => false,
					'options'   => $this->hubwoo_campaign_conversion_options(),
				);

				$group_properties[] = array(
					'name'               => 'third_purchase_workflow_conversion_amount',
					'label'              => __( 'Third Purchase Workflow Conversion Amount', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'formField'          => false,
					'showCurrencySymbol' => true,
				);

				$group_properties[] = array(
					'name'      => 'third_purchase_workflow_conversion_date',
					'label'     => __( 'Third Purchase Workflow Conversion Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);

				$group_properties[] = array(
					'name'      => 'third_purchase_workflow_start_date',
					'label'     => __( 'Third Purchase Workflow Start Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formField' => false,
				);
			} elseif ( 'abandoned_cart' === $group_name ) {

				$group_properties[] = array(
					'name'      => 'current_abandoned_cart',
					'label'     => __( 'Current Abandoned Cart', 'hubspot-for-woocommerce' ),
					'type'      => 'enumeration',
					'fieldType' => 'select',
					'formfield' => false,
					'options'   => Hubwoo_Admin::get_abandoned_cart_status(),
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_date',
					'label'     => __( 'Abandoned Cart Date', 'hubspot-for-woocommerce' ),
					'type'      => 'date',
					'fieldType' => 'date',
					'formfield' => false,
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_counter',
					'label'     => __( 'Abandoned Cart Counter', 'hubspot-for-woocommerce' ),
					'type'      => 'number',
					'fieldType' => 'number',
					'formfield' => false,
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_url',
					'label'     => __( 'Abandoned Cart URL', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'text',
					'formfield' => false,
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_products_skus',
					'label'     => __( 'Abandoned Cart Products SKUs', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formfield' => false,
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_products_categories',
					'label'     => __( 'Abandoned Cart Products Categories', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formfield' => false,
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_products',
					'label'     => __( 'Abandoned Cart Products', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formfield' => false,
				);

				$group_properties[] = array(
					'name'      => 'abandoned_cart_products_html',
					'label'     => __( 'Abandoned Cart Products HTML', 'hubspot-for-woocommerce' ),
					'type'      => 'string',
					'fieldType' => 'textarea',
					'formfield' => false,
				);

				$group_properties[] = array(
					'name'               => 'abandoned_cart_tax_value',
					'label'              => __( 'Abandoned Cart Tax Value', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'showCurrencySymbol' => true,
					'formfield'          => false,
				);

				$group_properties[] = array(
					'name'               => 'abandoned_cart_subtotal',
					'label'              => __( 'Abandoned Cart Subtotal', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'showCurrencySymbol' => true,
					'formfield'          => false,
				);

				$group_properties[] = array(
					'name'               => 'abandoned_cart_total_value',
					'label'              => __( 'Abandoned Cart Total Value', 'hubspot-for-woocommerce' ),
					'type'               => 'number',
					'fieldType'          => 'number',
					'showCurrencySymbol' => true,
					'formfield'          => false,
				);
			}
		}

		return apply_filters( 'hubwoo_group_properties', $group_properties, $group_name );
	}

	/**
	 * Get all active lists for hubspot.
	 *
	 * @since 1.0.0
	 */
	private function get_all_active_lists() {

		$lists = array();

		$optin = 'yes';
		$abandoned_status = 'yes';
		$property_updated = get_option( 'hubwoo_newsletter_property_update' );
		$abandoned_property_updated = get_option( 'hubwoo_abandoned_property_update' );

		if ( ! empty( $property_updated ) && 'yes' == $property_updated ) {
			if ( 'yes' == $optin ) {
				$optin = true;
			}
		}

		if ( ! empty( $abandoned_property_updated ) && 'yes' == $abandoned_property_updated ) {
			$abandoned_status = true;
		}

		$lists[] = array(

			'name'    => __( 'Customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 'customer',
						'property' => 'lifecyclestage',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Leads', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 'lead',
						'property' => 'lifecyclestage',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Abandoned Cart', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => $abandoned_status,
						'property' => 'current_abandoned_cart',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Best Customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 5,
						'property' => 'monetary_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'EQ',
						'value'    => 5,
						'property' => 'order_frequency_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'EQ',
						'value'    => 5,
						'property' => 'order_recency_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Big Spenders', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 5,
						'property' => 'monetary_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Loyal Customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 5,
						'property' => 'order_frequency_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'EQ',
						'value'    => 5,
						'property' => 'order_recency_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Churning Customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 5,
						'property' => 'monetary_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'EQ',
						'value'    => 5,
						'property' => 'order_frequency_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'EQ',
						'value'    => 1,
						'property' => 'order_recency_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Low Value Lost Customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 1,
						'property' => 'monetary_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'EQ',
						'value'    => 1,
						'property' => 'order_frequency_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'EQ',
						'value'    => 1,
						'property' => 'order_recency_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'New Customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 1,
						'property' => 'order_frequency_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'EQ',
						'value'    => 1,
						'property' => 'order_recency_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Customers needing attention', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 3,
						'property' => 'monetary_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'EQ',
						'value'    => 3,
						'property' => 'order_frequency_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'SET_ANY',
						'value'    => implode( ';', array( 1, 2 ) ),
						'property' => 'order_recency_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'About to Sleep', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'SET_ANY',
						'value'    => implode( ';', array( 1, 2 ) ),
						'property' => 'monetary_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'SET_ANY',
						'value'    => implode( ';', array( 1, 2 ) ),
						'property' => 'order_frequency_rating',
						'type'     => 'enumeration',
					),
					array(
						'operator' => 'SET_ANY',
						'value'    => implode( ';', array( 1, 2 ) ),
						'property' => 'order_recency_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Mid Spenders', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 3,
						'property' => 'monetary_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Low Spenders', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 1,
						'property' => 'monetary_rating',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Newsletter Subscriber', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => $optin,
						'property' => 'newsletter_subscription',
						'type'     => 'enumeration',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'One time purchase customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 1,
						'property' => 'total_number_of_orders',
						'type'     => 'number',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Two time purchase customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 2,
						'property' => 'total_number_of_orders',
						'type'     => 'number',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Three time purchase customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 3,
						'property' => 'total_number_of_orders',
						'type'     => 'number',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Bought four or more times', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 4,
						'property' => 'total_number_of_orders',
						'type'     => 'number',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'Marketing Qualified Leads', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator' => 'EQ',
						'value'    => 'marketingqualifiedlead',
						'property' => 'lifecyclestage',
						'type'     => 'enumeration',
					),
				),
			),
		);
		$lists[] = array(

			'name'    => __( 'Engaged Customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'operator'           => 'WITHIN_TIME',
						'withinLastTime'     => 60,
						'withinLastTimeUnit' => 'DAYS',
						'withinLastDays'     => 60,
						'withinTimeMode'     => 'PAST',
						'property'           => 'last_order_date',
						'type'               => 'date',
					),
				),
			),
		);

		$lists[] = array(

			'name'    => __( 'DisEngaged Customers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'withinLastTime'          => 60,
						'withinLastTimeUnit'      => 'DAYS',
						'reverseWithinTimeWindow' => true,
						'withinLastDays'          => 60,
						'withinTimeMode'          => 'PAST',
						'type'                    => 'date',
						'operator'                => 'WITHIN_TIME',
						'property'                => 'last_order_date',
					),
					array(
						'withinLastTime'     => 180,
						'withinLastTimeUnit' => 'DAYS',
						'withinLastDays'     => 180,
						'withinTimeMode'     => 'PAST',
						'type'               => 'date',
						'operator'           => 'WITHIN_TIME',
						'property'           => 'last_order_date',
					),
				),
			),
		);

		$lists[] = array(
			'name'    => __( 'Repeat Buyers', 'hubspot-for-woocommerce' ),
			'dynamic' => true,
			'filters' => array(
				array(
					array(
						'type'     => 'number',
						'operator' => 'GTE',
						'property' => 'total_number_of_orders',
						'value'    => 5,
					),
					array(
						'type'     => 'number',
						'operator' => 'LTE',
						'property' => 'average_days_between_orders',
						'value'    => 30,
					),
				),
			),
		);

		return apply_filters( 'hubwoo_lists', $lists );
	}

	/**
	 * Get all workflows.
	 *
	 * @since 1.0.0
	 */
	private function get_all_workflows() {

		$workflows = array();

		$abandoned_status = 'yes';
		$abandoned_property_updated = get_option( 'hubwoo_abandoned_property_update' );

		if ( ! empty( $abandoned_property_updated ) && 'yes' == $abandoned_property_updated ) {
			$abandoned_status = true;
		}

		$workflows[] = array(
			'type'         => 'DRIP_DELAY',
			'name'         => 'WooCommerce: MQL to Customer lifecycle stage Conversion',
			'enabled'      => true,
			'actions'      => array(
				array(
					'type'         => 'DATE_STAMP_PROPERTY',
					'propertyName' => 'mql_capture_nurture_conversion_start_date',
					'name'         => 'MQL Capture, Nurture & Conversion Start Date',
				),
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 'MQL Nurture & Conversion',
					'propertyName' => 'current_roi_campaign',
					'name'         => 'Current ROI Campaign',
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => 172800000,
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => 432000000,
				),
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 'None',
					'propertyName' => 'current_roi_campaign',
					'name'         => 'Current ROI Campaign',
				),
			),
			'goalCriteria' => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'type'           => 'enumeration',
						'property'       => 'lifecyclestage',
						'value'          => 'customer',
						'operator'       => 'SET_ANY',
					),
				),
			),
		);

		$workflows[] = array(
			'type'              => 'DRIP_DELAY',
			'name'              => 'WooCommerce: Welcome New Customer & Get a 2nd Order',
			'enabled'           => true,
			'actions'           => array(
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 'New Customer Welcome & Get a 2nd Order',
					'propertyName' => 'current_roi_campaign',
					'name'         => 'Current ROI Campaign',
				),
				array(
					'type'         => 'DATE_STAMP_PROPERTY',
					'propertyName' => 'second_purchase_workflow_start_date',
					'name'         => 'Second Purchase Workflow Start Date',
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => 172800000,
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => 172800000,
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => 604800000,
				),
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 'None',
					'propertyName' => 'current_roi_campaign',
					'name'         => 'Current ROI Campaign',
				),
			),
			'goalCriteria'      => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'type'           => 'number',
						'property'       => 'total_number_of_orders',
						'value'          => 1,
						'operator'       => 'GT',
					),
				),
			),
			'onlyExecOnBizDays' => true,
		);

		$workflows[] = array(
			'type'              => 'DRIP_DELAY',
			'name'              => 'WooCommerce: 2nd Order Thank You & Get a 3rd Order',
			'enabled'           => true,
			'actions'           => array(
				array(
					'type'        => 'DELAY',
					'delayMillis' => 172800000,
				),
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => '2nd Order Thank You & Get a 3rd Order',
					'propertyName' => 'current_roi_campaign',
					'name'         => 'Current ROI Campaign',
				),
				array(
					'type'         => 'DATE_STAMP_PROPERTY',
					'propertyName' => 'third_purchase_workflow_start_date',
					'name'         => 'Third Purchase Workflow Start Date',
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => 172800000,
				),

				array(
					'type'        => 'DELAY',
					'delayMillis' => 172800000,
				),

				array(
					'type'        => 'DELAY',
					'delayMillis' => 432000000,
				),
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 'None',
					'propertyName' => 'current_roi_campaign',
					'name'         => 'Current ROI Campaign',
				),
			),
			'goalCriteria'      => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'type'           => 'number',
						'property'       => 'total_number_of_orders',
						'value'          => 2,
						'operator'       => 'GT',
					),
				),
			),
			'onlyExecOnBizDays' => true,
		);

		$workflows[] = array(
			'type'              => 'DRIP_DELAY',
			'name'              => 'WooCommerce: 3rd Order Thank You',
			'enabled'           => true,
			'actions'           => array(
				array(
					'type'        => 'DELAY',
					'delayMillis' => 172800000,
				),
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => '3rd Order Thank You',
					'propertyName' => 'current_roi_campaign',
					'name'         => 'Current ROI Campaign',
				),

				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 'None',
					'propertyName' => 'current_roi_campaign',
					'name'         => 'Current ROI Campaign',
				),
			),
			'onlyExecOnBizDays' => true,
		);

		$workflows[] = array(
			'type'                   => 'DRIP_DELAY',
			'name'                   => 'WooCommerce: ROI Calculation',
			'enabled'                => true,
			'enrollOnCriteriaUpdate' => true,
			'actions'                => array(
				array(
					'type'          => 'BRANCH',
					'filters'       => array(
						array(
							array(
								'withinTimeMode' => 'PAST',
								'type'           => 'enumeration',
								'property'       => 'current_roi_campaign',
								'value'          => 'MQL Nurture & Conversion',
								'operator'       => 'SET_ANY',
							),
						),
					),
					'acceptActions' => array(
						array(
							'type'         => 'SET_CONTACT_PROPERTY',
							'newValue'     => 'true',
							'propertyName' => 'mql_capture_nurture_conversion_conversion',
							'name'         => 'Set Property',
						),
						array(
							'type'           => 'COPY_PROPERTY',
							'sourceProperty' => 'last_order_value',
							'targetProperty' => 'mql_capture_nurture_conversion_conversion_amount',
							'targetModel'    => 'CONTACT',
							'name'           => 'Copy property',
						),
						array(
							'type'         => 'DATE_STAMP_PROPERTY',
							'propertyName' => 'mql_capture_nurture_conversion_conversion_date',
							'model'        => 'CONTACT',
							'name'         => 'MQL Capture, Nurture & Conversion Conversion Date',
						),
						array(
							'type'         => 'SET_CONTACT_PROPERTY',
							'newValue'     => 'None',
							'propertyName' => 'current_roi_campaign',
							'name'         => 'Set Property',
						),
					),
					'rejectActions' => array(
						array(
							'type'          => 'BRANCH',
							'filters'       => array(
								array(
									array(
										'withinTimeMode' => 'PAST',
										'type'           => 'enumeration',
										'property'       => 'current_roi_campaign',
										'value'          => 'New Customer Welcome & Get a 2nd Order',
										'operator'       => 'SET_ANY',
									),
								),
							),
							'acceptActions' => array(
								array(
									'type'         => 'SET_CONTACT_PROPERTY',
									'newValue'     => 'true',
									'propertyName' => 'new_customer_workflow_conversion',
									'name'         => 'Set Property',
								),
								array(
									'type'           => 'COPY_PROPERTY',
									'sourceProperty' => 'last_order_value',
									'targetProperty' => 'new_customer_workflow_conversion_amount',
									'targetModel'    => 'CONTACT',
									'name'           => 'Copy property',
								),
								array(
									'type'         => 'DATE_STAMP_PROPERTY',
									'propertyName' => 'new_customer_workflow_conversion_date',
									'model'        => 'CONTACT',
									'name'         => 'New Customer Workflow Conversion Date',
								),
								array(
									'type'         => 'SET_CONTACT_PROPERTY',
									'newValue'     => 'None',
									'propertyName' => 'current_roi_campaign',
									'name'         => 'Set Property',
								),
							),
							'rejectActions' => array(
								array(
									'type'          => 'BRANCH',
									'filters'       => array(
										array(
											array(
												'withinTimeMode' => 'PAST',
												'type'     => 'enumeration',
												'property' => 'current_roi_campaign',
												'value'    => '2nd Order Thank You & Get a 3rd Order',
												'operator' => 'SET_ANY',
											),
										),
									),
									'acceptActions' => array(
										array(
											'type'         => 'SET_CONTACT_PROPERTY',
											'newValue'     => 'true',
											'propertyName' => 'second_purchase_workflow_conversion',
											'name'         => 'Set Property',
										),
										array(
											'type'        => 'COPY_PROPERTY',
											'sourceProperty' => 'last_order_value',
											'targetProperty' => 'second_purchase_workflow_conversion_amount',
											'targetModel' => 'CONTACT',
											'name'        => 'Copy property',
										),
										array(
											'type'         => 'DATE_STAMP_PROPERTY',
											'propertyName' => 'second_purchase_workflow_conversion_date',
											'model'        => 'CONTACT',
											'name'         => 'Second Purchase Workflow Conversion Date',
										),
										array(
											'type'         => 'SET_CONTACT_PROPERTY',
											'newValue'     => 'None',
											'propertyName' => 'current_roi_campaign',
											'name'         => 'Set Property',
										),
									),
									'rejectActions' => array(
										array(
											'type'    => 'BRANCH',
											'filters' => array(
												array(
													array(
														'withinTimeMode' => 'PAST',
														'type' => 'enumeration',
														'property' => 'current_roi_campaign',
														'value' => '3rd Order Thank You',
														'operator' => 'SET_ANY',
													),
												),
											),
											'acceptActions' => array(
												array(
													'type' => 'SET_CONTACT_PROPERTY',
													'newValue' => 'true',
													'propertyName' => 'third_purchase_workflow_conversion',
													'name' => 'Set Property',
												),
												array(
													'type' => 'COPY_PROPERTY',
													'sourceProperty' => 'last_order_value',
													'targetProperty' => 'third_purchase_workflow_conversion_amount',
													'targetModel' => 'CONTACT',
													'name' => 'COPY_PROPERTY',
												),
												array(
													'type' => 'DATE_STAMP_PROPERTY',
													'propertyName' => 'third_purchase_workflow_conversion_date',
													'model' => 'CONTACT',
													'name' => 'Third Purchase Workflow Conversion Date',
												),
												array(
													'type' => 'SET_CONTACT_PROPERTY',
													'newValue' => 'None',
													'propertyName' => 'current_roi_campaign',
													'name' => 'Set Property',
												),
											),
											'rejectActions' => array(
												array(
													'type' => 'BRANCH',
													'filters' => array(
														array(
															array(
																'withinTimeMode' => 'PAST',
																'type' => 'enumeration',
																'property' => 'current_roi_campaign',
																'value' => 'Customer Reengagement',
																'operator' => 'SET_ANY',
															),
														),
													),
													'acceptActions' => array(
														array(
															'type' => 'SET_CONTACT_PROPERTY',
															'newValue' => 'true',
															'propertyName' => 'customer_reengagement_workflow_conversion',
															'name' => 'Set Property',
														),
														array(
															'type' => 'COPY_PROPERTY',
															'sourceProperty' => 'last_order_value',
															'targetProperty' => 'customer_reengagement_workflow_conversion_amount',
															'targetModel' => 'CONTACT',
															'name' => 'Copy property',
														),
														array(
															'type' => 'DATE_STAMP_PROPERTY',
															'propertyName' => 'customer_reengagement_workflow_conversion_date',
															'name' => 'Customer Reengagement Workflow Conversion Date',
														),
														array(
															'type' => 'SET_CONTACT_PROPERTY',
															'newValue' => 'None',
															'propertyName' => 'current_roi_campaign',
															'name' => 'Set Property',
														),
													),
													'rejectActions' => array(
														array(
															'type' => 'BRANCH',
															'filters' => array(
																array(
																	array(
																		'withinTimeMode' => 'PAST',
																		'type' => 'enumeration',
																		'property' => 'current_roi_campaign',
																		'value' => 'Customer Rewards',
																		'operator' => 'SET_ANY',
																	),
																),
															),
															'acceptActions' => array(
																array(
																	'type' => 'SET_CONTACT_PROPERTY',
																	'newValue' => 'true',
																	'propertyName' => 'customer_rewards_workflow_conversion',
																	'name' => 'Set Property',
																),
																array(
																	'type' => 'COPY_PROPERTY',
																	'sourceProperty' => 'last_order_value',
																	'targetProperty' => 'customer_rewards_workflow_conversion_amount',
																	'targetModel' => 'CONTACT',
																	'name' => 'Copy property',
																),
																array(
																	'type' => 'DATE_STAMP_PROPERTY',
																	'propertyName' => 'customer_rewards_workflow_conversion_date',
																	'model' => 'CONTACT',
																	'name' => 'Customer Rewards Workflow Conversion Date',
																),
																array(
																	'type' => 'SET_CONTACT_PROPERTY',
																	'newValue' => 'None',
																	'propertyName' => 'current_roi_campaign',
																	'name' => 'Set Property',
																),
															),
															'rejectActions' => array(
																array(
																	'type' => 'BRANCH',
																	'filters' => array(
																		array(
																			array(
																				'withinTimeMode' => 'PAST',
																				'type' => 'enumeration',
																				'property' => 'current_roi_campaign',
																				'value' => 'Abandoned Cart Recovery',
																				'operator' => 'SET_ANY',
																			),
																		),
																	),
																	'acceptActions' => array(
																		array(
																			'type' => 'SET_CONTACT_PROPERTY',
																			'newValue' => 'true',
																			'propertyName' => 'abandoned_cart_recovery_workflow_conversion',
																			'name' => 'Set Property',
																		),
																		array(
																			'type' => 'COPY_PROPERTY',
																			'sourceProperty' => 'last_order_value',
																			'targetProperty' => 'abandoned_cart_recovery_workflow_conversion_amount',
																			'targetModel' => 'CONTACT',
																			'name' => 'Copy Property',
																		),
																		array(
																			'type' => 'DATE_STAMP_PROPERTY',
																			'propertyName' => 'abandoned_cart_recovery_workflow_conversion_date',
																			'model' => 'CONTACT',
																			'name' => 'Abandoned Cart Recovery Workflow Conversion Date',
																		),
																		array(
																			'type' => 'SET_CONTACT_PROPERTY',
																			'newValue' => 'None',
																			'propertyName' => 'current_roi_campaign',
																			'name' => 'Set Property',
																		),
																	),
																	'rejectActions' => array(
																		array(
																			'type'          => 'SET_CONTACT_PROPERTY',
																			'newValue'      => 'None',
																			'propertyName'  => 'current_roi_campaign',
																			'name'          => 'Set Property',
																		),
																	),
																),
															),
														),
													),
												),
											),
										),
									),
								),
							),
						),
					),
				),
			),
		);
		$workflows[] = array(
			'type'    => 'DRIP_DELAY',
			'name'    => 'WooCommerce: After order Workflow',
			'enabled' => true,
			'actions' => array(
				array(
					'type'          => 'BRANCH',
					'filters'       => array(
						array(
							array(
								'withinTimeMode' => 'PAST',
								'type'           => 'number',
								'property'       => 'total_number_of_orders',
								'value'          => 3,
								'operator'       => 'EQ',
							),
						),
					),
					'acceptActions' => array(
						array(
							'type'       => 'WORKFLOW_ENROLLMENT',
							'name'       => 'WooCommerce: 3rd Order Thank You',
							'workflowId' => get_option( 'WooCommerce: 3rd Order Thank You', '' ),
						),
						array(
							'type'         => 'SET_CONTACT_PROPERTY',
							'newValue'     => '',
							'propertyName' => 'customer_new_order',
							'name'         => 'Customer New Order',
						),
					),
					'rejectActions' => array(
						array(
							'type'          => 'BRANCH',
							'filters'       => array(
								array(
									array(
										'withinTimeMode' => 'PAST',
										'type'           => 'number',
										'property'       => 'total_number_of_orders',
										'value'          => 2,
										'operator'       => 'EQ',
									),
								),
							),
							'acceptActions' => array(
								array(
									'type'       => 'WORKFLOW_ENROLLMENT',
									'name'       => 'WooCommerce: 2nd Order Thank You & Get a 3rd Order',
									'workflowId' => get_option( 'WooCommerce: 2nd Order Thank You & Get a 3rd Order', '' ),
								),
								array(
									'type'         => 'SET_CONTACT_PROPERTY',
									'newValue'     => '',
									'propertyName' => 'customer_new_order',
									'name'         => 'Customer New Order',
								),
							),
							'rejectActions' => array(
								array(
									'type'          => 'BRANCH',
									'filters'       => array(
										array(
											array(
												'withinTimeMode' => 'PAST',
												'type'     => 'number',
												'property' => 'total_number_of_orders',
												'value'    => 1,
												'operator' => 'EQ',
											),
										),
									),
									'acceptActions' => array(
										array(
											'type'       => 'WORKFLOW_ENROLLMENT',
											'name'       => 'WooCommerce: Welcome New Customer & Get a 2nd Order',
											'workflowId' => get_option( 'WooCommerce: Welcome New Customer & Get a 2nd Order', '' ),
										),
										array(
											'type'         => 'SET_CONTACT_PROPERTY',
											'newValue'     => '',
											'propertyName' => 'customer_new_order',
											'name'         => 'Customer New Order',
										),
									),
									'rejectActions' => array(
										array(
											'type'         => 'SET_CONTACT_PROPERTY',
											'newValue'     => '',
											'propertyName' => 'customer_new_order',
											'name'         => 'Customer New Order',
										),
									),
								),
							),
						),
					),
				),
			),
		);

		$workflows[] = array(
			'type'                   => 'DRIP_DELAY',
			'name'                   => 'WooCommerce: Order Workflow',
			'enabled'                => true,
			'actions'                => array(
				array(
					'type'        => 'DELAY',
					'delayMillis' => 300000,
				),
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => '',
					'propertyName' => 'lifecyclestage',
					'name'         => 'Lifecycle stage',
				),
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 'customer',
					'propertyName' => 'lifecyclestage',
					'name'         => 'Lifecycle stage',
				),
				array(
					'type'       => 'WORKFLOW_ENROLLMENT',
					'workflowId' => get_option( 'WooCommerce: ROI Calculation', '' ),
					'name'       => 'WooCommerce: ROI Calculation',
				),
				array(
					'type'       => 'WORKFLOW_ENROLLMENT',
					'workflowId' => get_option( 'WooCommerce: After order Workflow', '' ),
					'name'       => 'WooCommerce: After order Workflow',
				),
			),
			'enrollOnCriteriaUpdate' => true,
			'segmentCriteria'        => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'filterFamily'   => 'PropertyValue',
						'type'           => 'enumeration',
						'property'       => 'customer_new_order',
						'value'          => 'yes',
						'operator'       => 'SET_ANY',
					),
				),
			),
		);

		if ( 'yes' == get_option( 'hubwoo_abncart_enable_addon', 'yes' ) ) {

			$workflows[] = array(
				'type'                    => 'DRIP_DELAY',
				'name'                    => 'WooCommerce: Abandoned Cart Recovery',
				'enabled'                 => true,
				'actions'                 => array(
					array(
						'type'         => 'SET_CONTACT_PROPERTY',
						'propertyName' => 'current_roi_campaign',
						'newValue'     => 'Abandoned Cart Recovery',
						'name'         => 'Current ROI Campaign',
					),
					array(
						'type'         => 'DATE_STAMP_PROPERTY',
						'propertyName' => 'abandoned_cart_recovery_workflow_start_date',
						'name'         => 'Abandoned Cart Recovery Workflow Start Date',
					),

					array(
						'type'        => 'DELAY',
						'delayMillis' => 345600000,
					),

					array(
						'type'        => 'DELAY',
						'delayMillis' => 1209600000,
					),
					array(
						'type'         => 'SET_CONTACT_PROPERTY',
						'propertyName' => 'current_roi_campaign',
						'newValue'     => 'None',
						'name'         => 'Current ROI Campaign',
					),
				),
				'onlyExecOnBizDays'       => true,
				'enrollOnCriteriaUpdate'  => true,
				'segmentCriteria'         => array(
					array(
						array(
							'withinTimeMode' => 'PAST',
							'filterFamily'   => 'PropertyValue',
							'type'           => 'enumeration',
							'property'       => 'current_abandoned_cart',
							'value'          => $abandoned_status,
							'operator'       => 'SET_ANY',
						),
					),
				),
				'goalCriteria'            => array(
					array(
						array(
							'withinTimeMode' => 'PAST',
							'filterFamily'   => 'Workflow',
							'workflowId'     => get_option( 'WooCommerce: Order Workflow', '' ),
							'operator'       => 'ACTIVE_IN_WORKFLOW',
						),
					),
				),
				'reEnrollmentTriggerSets' => array(
					array(
						array(
							'type' => 'CONTACT_PROPERTY_NAME',
							'id'   => 'current_abandoned_cart',
						),
						array(
							'type' => 'CONTACT_PROPERTY_VALUE',
							'id'   => $abandoned_status,
						),
					),
				),
			);

		}

		$workflows[] = array(

			'type'         => 'DRIP_DELAY',
			'name'         => 'WooCommerce: set Order Recency 1 Ratings',
			'enabled'      => true,
			'actions'      => array(
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 1,
					'propertyName' => 'order_recency_rating',
					'name'         => 'Order Recency Rating',
				),
			),
			'goalCriteria' => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'filterFamily'   => 'Workflow',
						'workflowId'     => get_option( 'WooCommerce: Order Workflow', '' ),
						'operator'       => 'ACTIVE_IN_WORKFLOW',
					),
				),
			),
		);

		$workflows[] = array(

			'type'         => 'DRIP_DELAY',
			'name'         => 'WooCommerce: set Order Recency 2 Ratings',
			'enabled'      => true,
			'actions'      => array(
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 2,
					'propertyName' => 'order_recency_rating',
					'name'         => 'Order Recency Rating',
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => '31104000000',
				),
				array(
					'type'       => 'WORKFLOW_ENROLLMENT',
					'name'       => 'WooCommerce: set Order Recency 1 Ratings',
					'workflowId' => get_option( 'WooCommerce: set Order Recency 1 Ratings', '' ),
				),
			),
			'goalCriteria' => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'filterFamily'   => 'Workflow',
						'workflowId'     => get_option( 'WooCommerce: Order Workflow', '' ),
						'operator'       => 'ACTIVE_IN_WORKFLOW',
					),
				),
			),
		);

		$workflows[] = array(
			'type'         => 'DRIP_DELAY',
			'name'         => 'WooCommerce: set Order Recency 3 Ratings',
			'enabled'      => true,
			'actions'      => array(
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 3,
					'propertyName' => 'order_recency_rating',
					'name'         => 'Order Recency Rating',
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => '15552000000',
				),
				array(
					'type'       => 'WORKFLOW_ENROLLMENT',
					'name'       => 'WooCommerce: set Order Recency 2 Ratings',
					'workflowId' => get_option( 'WooCommerce: set Order Recency 2 Ratings', '' ),
				),
			),
			'goalCriteria' => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'filterFamily'   => 'Workflow',
						'workflowId'     => get_option( 'WooCommerce: Order Workflow', '' ),
						'operator'       => 'ACTIVE_IN_WORKFLOW',
					),
				),
			),
		);

		$workflows[] = array(
			'type'         => 'DRIP_DELAY',
			'name'         => 'WooCommerce: set Order Recency 4 Ratings',
			'enabled'      => true,
			'actions'      => array(
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 4,
					'propertyName' => 'order_recency_rating',
					'name'         => 'Order Recency Rating',
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => '7776000000',
				),
				array(
					'type'       => 'WORKFLOW_ENROLLMENT',
					'name'       => 'WooCommerce: set Order Recency 3 Ratings',
					'workflowId' => get_option( 'WooCommerce: set Order Recency 3 Ratings', '' ),
				),
			),
			'goalCriteria' => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'filterFamily'   => 'Workflow',
						'workflowId'     => get_option( 'WooCommerce: Order Workflow', '' ),
						'operator'       => 'ACTIVE_IN_WORKFLOW',
					),
				),
			),
		);

		$workflows[] = array(
			'type'         => 'DRIP_DELAY',
			'name'         => 'WooCommerce: set Order Recency 5 Ratings',
			'enabled'      => true,
			'actions'      => array(
				array(
					'type'         => 'SET_CONTACT_PROPERTY',
					'newValue'     => 5,
					'propertyName' => 'order_recency_rating',
					'name'         => 'Order Recency Rating',
				),
				array(
					'type'        => 'DELAY',
					'delayMillis' => '2592000000',
				),
				array(
					'type'       => 'WORKFLOW_ENROLLMENT',
					'name'       => 'WooCommerce: set Order Recency 4 Ratings',
					'workflowId' => get_option( 'WooCommerce: set Order Recency 4 Ratings', '' ),
				),
			),
			'goalCriteria' => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'filterFamily'   => 'Workflow',
						'workflowId'     => get_option( 'WooCommerce: Order Workflow', '' ),
						'operator'       => 'ACTIVE_IN_WORKFLOW',
					),
				),
			),
		);

		$workflows[] = array(

			'type'    => 'DRIP_DELAY',
			'name'    => 'WooCommerce: Update Historical Order Recency Rating',
			'enabled' => true,
			'actions' => array(
				array(
					'type'          => 'BRANCH',
					'filters'       => array(
						array(
							array(
								'withinLastTime'     => 31,
								'withinLastTimeUnit' => 'DAYS',
								'withinLastDays'     => 31,
								'withinTimeMode'     => 'PAST',
								'type'               => 'date',
								'property'           => 'last_order_date',
								'operator'           => 'WITHIN_TIME',
							),
						),
					),
					'acceptActions' => array(
						array(
							'type'       => 'WORKFLOW_ENROLLMENT',
							'name'       => 'WooCommerce: set Order Recency 5 Ratings',
							'workflowId' => get_option( 'WooCommerce: set Order Recency 5 Ratings', '' ),
						),
					),
					'rejectActions' => array(
						array(
							'type'          => 'BRANCH',
							'filters'       => array(
								array(
									array(
										'withinLastTime' => 30,
										'withinLastTimeUnit' => 'DAYS',
										'reverseWithinTimeWindow' => true,
										'withinLastDays' => 30,
										'withinTimeMode' => 'PAST',
										'type'           => 'date',
										'property'       => 'last_order_date',
										'operator'       => 'WITHIN_TIME',
									),
									array(
										'withinLastTime' => 91,
										'withinLastTimeUnit' => 'DAYS',
										'withinLastDays' => 91,
										'withinTimeMode' => 'PAST',
										'type'           => 'date',
										'property'       => 'last_order_date',
										'operator'       => 'WITHIN_TIME',
									),
								),
							),
							'acceptActions' => array(
								array(
									'type'       => 'WORKFLOW_ENROLLMENT',
									'name'       => 'WooCommerce: set Order Recency 4 Ratings',
									'workflowId' => get_option( 'WooCommerce: set Order Recency 4 Ratings', '' ),
								),
							),
							'rejectActions' => array(
								array(
									'type'          => 'BRANCH',
									'filters'       => array(
										array(
											array(
												'withinLastTime' => 90,
												'withinLastTimeUnit' => 'DAYS',
												'reverseWithinTimeWindow' => true,
												'withinLastDays' => 90,
												'withinTimeMode' => 'PAST',
												'type'     => 'date',
												'property' => 'last_order_date',
												'operator' => 'WITHIN_TIME',
											),
											array(
												'withinLastTime' => 181,
												'withinLastTimeUnit' => 'DAYS',
												'withinLastDays' => 181,
												'withinTimeMode' => 'PAST',
												'type'     => 'date',
												'property' => 'last_order_date',
												'operator' => 'WITHIN_TIME',
											),
										),
									),
									'acceptActions' => array(
										array(
											'type'       => 'WORKFLOW_ENROLLMENT',
											'name'       => 'WooCommerce: set Order Recency 3 Ratings',
											'workflowId' => get_option( 'WooCommerce: set Order Recency 3 Ratings', '' ),
										),
									),
									'rejectActions' => array(
										array(
											'type'    => 'BRANCH',
											'filters' => array(
												array(
													array(
														'withinLastTime'            => 180,
														'withinLastTimeUnit'        => 'DAYS',
														'reverseWithinTimeWindow'   => true,
														'withinLastDays'            => 180,
														'withinTimeMode'            => 'PAST',
														'type'                      => 'date',
														'property'                  => 'last_order_date',
														'operator'                  => 'WITHIN_TIME',
													),
													array(
														'withinLastTime'            => 365,
														'withinLastTimeUnit'        => 'DAYS',
														'withinLastDays'            => 365,
														'withinTimeMode'            => 'PAST',
														'type'                      => 'date',
														'property'                  => 'last_order_date',
														'operator'                  => 'WITHIN_TIME',
													),
												),
											),
											'acceptActions' => array(
												array(
													'type' => 'WORKFLOW_ENROLLMENT',
													'name' => 'WooCommerce: set Order Recency 2 Ratings',
													'workflowId' => get_option( 'WooCommerce: set Order Recency 2 Ratings', '' ),
												),
											),
											'rejectActions' => array(
												array(
													'type' => 'WORKFLOW_ENROLLMENT',
													'name' => 'WooCommerce: set Order Recency 1 Ratings',
													'workflowId' => get_option( 'WooCommerce: set Order Recency 1 Ratings', '' ),
												),
											),
										),
									),
								),
							),
						),
					),
				),
			),
		);

		$workflows[] = array(
			'type'                    => 'DRIP_DELAY',
			'name'                    => 'WooCommerce: Enroll Customers for Recency Settings',
			'enabled'                 => true,
			'actions'                 => array(
				array(
					'type'       => 'WORKFLOW_ENROLLMENT',
					'workflowId' => get_option( 'WooCommerce: Update Historical Order Recency Rating', '' ),
					'name'       => 'WooCommerce: Update Historical Order Recency Rating',
				),
			),
			'enrollOnCriteriaUpdate'  => true,
			'segmentCriteria'         => array(
				array(
					array(
						'withinTimeMode' => 'PAST',
						'filterFamily'   => 'PropertyValue',
						'type'           => 'enumeration',
						'property'       => 'lifecyclestage',
						'value'          => 'customer',
						'operator'       => 'SET_ANY',
					),
				),
			),
			'reEnrollmentTriggerSets' => array(
				array(
					array(
						'type' => 'CONTACT_PROPERTY_NAME',
						'id'   => 'lifecyclestage',
					),
					array(
						'type' => 'CONTACT_PROPERTY_VALUE',
						'id'   => 'customer',
					),
				),
			),
		);

		return apply_filters( 'hubwoo_workflows', $workflows );
	}

	/**
	 * Customer new order.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_new_order_status() {

		$values = array();

		$values[] = array(
			'label' => __( 'Yes', 'hubspot-for-woocommerce' ),
			'value' => 'yes',
		);
		$values[] = array(
			'label' => __( 'No', 'hubspot-for-woocommerce' ),
			'value' => 'no',
		);

		return $values;
	}

	/**
	 * Get optin sources.
	 *
	 * @since 1.0.0
	 */
	public function get_user_marketing_sources() {

		$sources   = array();
		$sources[] = array(
			'label' => __( 'Checkout', 'hubspot-for-woocommerce' ),
			'value' => 'checkout',
		);
		$sources[] = array(
			'label' => __( 'Registration', 'hubspot-for-woocommerce' ),
			'value' => 'registration',
		);
		$sources[] = array(
			'label' => __( 'Others', 'hubspot-for-woocommerce' ),
			'value' => 'other',
		);
		$sources   = apply_filters( 'hubwoo_user_marketing_sources', $sources );
		return $sources;
	}

	/**
	 * Get all campaigns names for hubspot.
	 *
	 * @since 1.0.0
	 */
	public function get_all_campaign_names() {

		$all_names = array();

		$all_names[] = array(
			'label' => 'MQL Nurture & Conversion',
			'value' => 'MQL Nurture & Conversion',
		);

		$all_names[] = array(
			'label' => 'New Customer Welcome & Get a 2nd Order',
			'value' => 'New Customer Welcome & Get a 2nd Order',
		);

		$all_names[] = array(
			'label' => '2nd Order Thank You & Get a 3rd Order',
			'value' => '2nd Order Thank You & Get a 3rd Order',
		);

		$all_names[] = array(
			'label' => '3rd Order Thank You',
			'value' => '3rd Order Thank You',
		);

		$all_names[] = array(
			'label' => 'Customer Reengagement',
			'value' => 'Customer Reengagement',
		);

		$all_names[] = array(
			'label' => 'Customer Rewards',
			'value' => 'Customer Rewards',
		);

		$all_names[] = array(
			'label' => 'Abandoned Cart Recovery',
			'value' => 'Abandoned Cart Recovery',
		);

		$all_names[] = array(
			'label' => 'None',
			'value' => 'None',
		);

		return $all_names;
	}

	/**
	 * Conversion options for campaigns on hubspot.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_campaign_conversion_options() {

		$values = array();

		$values[] = array(
			'label' => __( 'Yes', 'hubspot-for-woocommerce' ),
			'value' => 'true',
		);
		$values[] = array(
			'label' => __( 'No', 'hubspot-for-woocommerce' ),
			'value' => 'false',
		);

		return $values;
	}

	/**
	 * Get subscriptions billing period for hubspot.
	 *
	 * @since 1.0.0
	 */
	public static function get_subscriptions_billing_period() {

		$values = array();

		$values[] = array(
			'label' => __( 'Day', 'hubspot-for-woocommerce' ),
			'value' => 'day',
		);
		$values[] = array(
			'label' => __( 'Week', 'hubspot-for-woocommerce' ),
			'value' => 'week',
		);
		$values[] = array(
			'label' => __( 'Month', 'hubspot-for-woocommerce' ),
			'value' => 'month',
		);
		$values[] = array(
			'label' => __( 'Year', 'hubspot-for-woocommerce' ),
			'value' => 'year',
		);

		$values = apply_filters( 'hubwoo_subscriptions_period', $values );

		return $values;
	}

	/**
	 * Get subscriptions billing interval for hubspot.
	 *
	 * @since 1.0.0
	 */
	public static function get_subscriptions_billing_interval() {

		$values = array();

		$values[] = array(
			'label' => __( 'Every', 'hubspot-for-woocommerce' ),
			'value' => 1,
		);
		$values[] = array(
			'label' => __( 'Every Second', 'hubspot-for-woocommerce' ),
			'value' => 2,
		);
		$values[] = array(
			'label' => __( 'Every Third', 'hubspot-for-woocommerce' ),
			'value' => 3,
		);
		$values[] = array(
			'label' => __( 'Every Fourth', 'hubspot-for-woocommerce' ),
			'value' => 4,
		);
		$values[] = array(
			'label' => __( 'Every Fifth', 'hubspot-for-woocommerce' ),
			'value' => 5,
		);
		$values[] = array(
			'label' => __( 'Every Sixth', 'hubspot-for-woocommerce' ),
			'value' => 6,
		);

		$values = apply_filters( 'hubwoo_subscriptions_interval', $values );

		return $values;
	}

	/**
	 * Get all available woocommerce order statuses.
	 *
	 * @return JSON Order statuses in the form of enumaration options.
	 * @since 1.0.0
	 */
	public static function get_order_statuses() {

		$all_wc_statuses = array();

		// get all statuses.
		$all_status = wc_get_order_statuses();

		// if status available.
		if ( is_array( $all_status ) && count( $all_status ) ) {

			foreach ( $all_status as $status_id => $status_label ) {

				$all_wc_statuses[] = array(
					'label' => $status_label,
					'value' => $status_id,
				);
			}
		}
		$all_wc_statuses = apply_filters( 'hubwoo_order_status_options', $all_wc_statuses );

		return $all_wc_statuses;
	}

	/**
	 * Get all available woocommerce order statuses for subscriptions.
	 *
	 * @return JSON Order statuses in the form of enumaration options.
	 * @since 1.0.0
	 */
	public static function get_subscription_status_options() {

		$all_wc_subs_status = array();

		// get all statuses.
		$all_status = wcs_get_subscription_statuses();

		// if status available.
		if ( is_array( $all_status ) && count( $all_status ) ) {

			foreach ( $all_status as $status_id => $status_label ) {

				$all_wc_subs_status[] = array(
					'label' => $status_label,
					'value' => $status_id,
				);
			}
		}

		$all_wc_subs_status = apply_filters( 'hubwoo_order_status_options', $all_wc_subs_status );

		return $all_wc_subs_status;
	}

	/**
	 * Get ratings for RFM analysis.
	 *
	 * @return ratings for RFM analysis.
	 * @since 1.0.0
	 */
	public function get_rfm_rating() {

		$rating = array();

		$rating[] = array(
			'label' => __( '5', 'hubspot-for-woocommerce' ),
			'value' => 5,
		);
		$rating[] = array(
			'label' => __( '4', 'hubspot-for-woocommerce' ),
			'value' => 4,
		);
		$rating[] = array(
			'label' => __( '3', 'hubspot-for-woocommerce' ),
			'value' => 3,
		);
		$rating[] = array(
			'label' => __( '2', 'hubspot-for-woocommerce' ),
			'value' => 2,
		);
		$rating[] = array(
			'label' => __( '1', 'hubspot-for-woocommerce' ),
			'value' => 1,
		);

		$rating = apply_filters( 'hubwoo_rfm_ratings', $rating );

		return $rating;
	}

	/**
	 * Get user actions for marketing.
	 *
	 * @return array  marketing actions for users.
	 * @since 1.0.0
	 */
	public function get_user_marketing_action() {

		$user_actions = array();

		$user_actions[] = array(
			'label' => __( 'Yes', 'hubspot-for-woocommerce' ),
			'value' => 'yes',
		);
		$user_actions[] = array(
			'label' => __( 'No', 'hubspot-for-woocommerce' ),
			'value' => 'no',
		);

		$user_actions = apply_filters( 'hubwoo_user_marketing_actions', $user_actions );

		return $user_actions;
	}

	/**
	 * Last order products html for hubspot.
	 *
	 * @since 1.0.0
	 * @param int $last_order_id last order if to create html.
	 */
	public function hubwoo_last_order_html( $last_order_id = '' ) {

		$products_html = '';

		if ( ! empty( $last_order_id ) ) {

			$order = new WC_Order( $last_order_id );

			$key = 0;

			$last_order_products = array();

			if ( ! empty( $order ) || ! is_wp_error( $order ) ) {

				$order_items = $order->get_items();

				if ( is_array( $order_items ) && count( $order_items ) ) {

					foreach ( $order_items as $item_id_1 => $wc_order_item_product ) {

						if ( ! empty( $wc_order_item_product ) && $wc_order_item_product instanceof WC_Order_Item ) {

							$item_id = $wc_order_item_product->get_variation_id();

							if ( empty( $item_id ) ) {
								$item_id = $wc_order_item_product->get_product_id();
							}

							$product = wc_get_product( $item_id );

							if ( get_post_status( $item_id ) == 'trash' || get_post_status( $item_id ) == false ) {

								continue;
							}

							$attachment_src = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'single-post-thumbnail' );

							$last_order_products[ $key ]['image'] = isset( $attachment_src[0] ) ? $attachment_src[0] : '';
							$last_order_products[ $key ]['name']  = get_the_title( $item_id );
							$last_order_products[ $key ]['url']   = get_permalink( $item_id );
							$last_order_products[ $key ]['price'] = $product->get_price();
							$last_order_products[ $key ]['qty']   = $wc_order_item_product->get_quantity();
							$last_order_products[ $key ]['disc']  = $wc_order_item_product->get_total();
							
							$key++;
						}
					}
				}
			}

			if ( count( $last_order_products ) ) {

				$products_html = '<div><hr></div><!--[if mso]><center><table width="100%" style="width:600px;"><![endif]--><table style="font-size: 14px; font-family: Arial, sans-serif; line-height: 20px; text-align: left; table-layout: fixed;" width="100%"><thead><tr><th style="text-align: center;word-wrap: unset;">' . __( 'Image', 'hubspot-for-woocommerce' ) . '</th><th style="text-align: center;word-wrap: unset;">' . __( 'Item', 'hubspot-for-woocommerce' ) . '</th><th style="text-align: center;word-wrap: unset;">' . __( 'Qty', 'hubspot-for-woocommerce' ) . '</th><th style="text-align: center;word-wrap: unset;">' . __( 'Price', 'huwboo' ) . '</th><th style="text-align: center;word-wrap: unset;">' . __( 'Discount', 'hubspot-for-woocommerce' ) . '</th><th style="text-align: center;word-wrap: unset;">' . __( 'Total', 'hubspot-for-woocommerce' ) . '</th></tr></thead><tbody>';

				foreach ( $last_order_products as $single_product ) {

					$total = $single_product['disc'];
					$disc  = ( $single_product['price'] * $single_product['qty'] ) - $total;
					$products_html .= '<tr><td style="max-width: 20%;width: 100%; text-align: center;"><img height="50" width="50" src="' . $single_product['image'] . '"></td><td style="max-width: 50%;width: 100%; text-align: center; font-weight: normal;font-size: 12px;word-wrap: unset;"><a style="display: inline-block;" target="_blank" href="' . $single_product['url'] . '"><strong>' . $single_product['name'] . '</strong></a></td><td style="max-width: 10%;width: 100%;text-align: center;">' . $single_product['qty'] . '</td><td style="max-width: 10%;width: 100%;text-align: center; font-size: 10px;">' . wc_price( $single_product['price'], array( 'currency' => get_post_meta( $last_order_id, '_order_currency', true ) ) ) . '</td><td style="max-width: 10%;width: 100%;text-align: center; font-size: 10px;">' . wc_price( $disc, array( 'currency' => get_post_meta( $last_order_id, '_order_currency', true ) ) ) . '</td><td style="max-width: 10%;width: 100%;text-align: center; font-size: 10px;">' . wc_price( $total, array( 'currency' => get_post_meta( $last_order_id, '_order_currency', true ) ) ) . '</td></tr>';
				}

				$products_html .= '</tbody></table><!--[if mso]></table></center><![endif]--><div><hr></div>';
			}
		}

		return $products_html;
	}
}
