<?php
/**
 * Manage all object properties.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Manage all object properties.
 *
 * Provide a list of functions to manage all the information
 * about contacts properties and lists along with option to
 * change/update the mapping field on hubspot.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubwooEcommProperties {

	/**
	 * Hubspot objects
	 *
	 * @var $objects
	 * @since 1.0.0
	 */
	private $objects;

	/**
	 * Object Properties.
	 *
	 * @var $properties;
	 * @since 1.0.0
	 */
	private $properties;

	/**
	 * HubwooEcommProperties Instance.
	 *
	 * @var $_instance
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main HubwooEcommProperties Instance.
	 *
	 * Ensures only one instance of hubwoo-ecommProperties is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return hubwoo-ecommProperties - Main instance.
	 */
	public static function get_instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Define the object prooperties related functionality.
	 *
	 * Set the object groups and properties that we are going to use
	 * for creating/updating the object information for our tacking purpose
	 * and providing other developers to add there field and group for tracking
	 * too by simply using our hooks.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->objects    = $this->_set( 'objects' );
		$this->properties = $this->_set( 'properties' );
	}

	/**
	 * Get groups/properties.
	 *
	 * @param  string $option groups/properties.
	 * @return array Array of groups/properties information.
	 */
	public function _get( $option ) {

		if ( 'objects' === $option ) {

			return $this->objects;
		} elseif ( 'properties' === $option ) {

			return $this->properties;
		}
	}

	/**
	 * Get an array of required option.
	 *
	 * @param  String $option the identifier.
	 * @return Array        An array of values.
	 * @since 1.0.0
	 */
	private function _set( $option ) {

		$values = array();

		if ( 'objects' === $option ) {
			$values = array( 'CONTACT', 'PRODUCT', 'DEAL', 'LINE_ITEM' );
		} elseif ( 'properties' === $option ) {
			$values = $this->hubwoo_ecomm_get_object_properties();
		}

		return apply_filters( 'hubwoo_ecomm_' . $option, $values );
	}


	/**
	 * Get the required properties for an hubspot object for installation
	 *
	 * @return Array  object properties
	 * @since 1.0.0
	 */
	private function hubwoo_ecomm_get_object_properties() {

		$objects = $this->_get( 'objects' );

		$ecomm_object_properties = array();

		if ( is_array( $objects ) && count( $objects ) ) {
			foreach ( $objects as $single_object ) {
				$ecomm_object_properties[ $single_object ] = $this->hubwoo_ecomm_set_all_properties( $single_object );
			}
		}

		return apply_filters( 'hubwoo_ecomm_bridge_object_properties', $ecomm_object_properties );
	}

	/**
	 * Get the required properties for sync messages to hubspot.
	 *
	 * @since 1.0.0
	 * @param string $object type of object.
	 * @return Array  object properties
	 */
	public function hubwoo_ecomm_get_properties_for_object( $object ) {

		$object_properties = array();

		if ( ! empty( $object ) ) {
			$properties = $this->hubwoo_ecomm_set_all_properties( $object );
			if ( is_array( $properties ) && count( $properties ) ) {
				foreach ( $properties as $single_property ) {
					if ( ! empty( $single_property['externalPropertyName'] ) ) {
						$object_properties[] = $single_property['externalPropertyName'];
					}
				}
			}
		}

		return apply_filters( 'hubwoo_ecomm_bridge_' . $object . '_properties', $object_properties );
	}


	/**
	 * Prepares all the properties for hubspot objects.
	 *
	 * @since 1.0.0
	 * @param string $object_name object type.
	 * @return Array  object properties
	 */
	public function hubwoo_ecomm_set_all_properties( $object_name ) {

		$object_properties = array();

		if ( ! empty( $object_name ) ) {

			switch ( $object_name ) {

				case 'CONTACT':
					$object_properties[] = array(
						'externalPropertyName' => 'email',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'email',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'first_name',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'firstname',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'last_name',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'lastname',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'billing_company',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'company',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'billing_phone',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'phone',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'billing_mobile',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'mobilephone',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'billing_address_1',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'address',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'billing_city',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'city',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'billing_state',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'state',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'billing_country',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'country',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'billing_postcode',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'zip',
					);

					break;

				case 'PRODUCT':
					$object_properties[] = array(
						'externalPropertyName' => 'product_name',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'name',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'product_image_url',
						'dataType'             => 'AVATAR_IMAGE',
						'hubspotPropertyName'  => 'ip__ecomm_bridge__image_url',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'product_price',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'price',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'product_description',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'description',
					);

					break;

				case 'DEAL':
					$object_properties[] = array(
						'externalPropertyName' => 'dealstage',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'dealstage',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'dealname',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'dealname',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'closedate',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'closedate',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'order_date',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'createdate',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'order_amount',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'amount',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'order_discount_amount',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'ip__ecomm_bridge__discount_amount',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'order_id',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'ip__ecomm_bridge__order_number',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'order_shipment_ids',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'ip__ecomm_bridge__shipment_ids',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'order_tax_amount',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'ip__ecomm_bridge__tax_amount',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'customer_note',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'description',
					);

					break;

				case 'LINE_ITEM':
					$object_properties[] = array(
						'externalPropertyName' => 'discount_amount',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'discount',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'quantity',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'quantity',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'name',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'name',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'price',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'price',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'sku',
						'dataType'             => 'STRING',
						'hubspotPropertyName'  => 'description',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'amount',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'amount',
					);
					$object_properties[] = array(
						'externalPropertyName' => 'tax_amount',
						'dataType'             => 'NUMBER',
						'hubspotPropertyName'  => 'tax',
					);

					break;
			}
		}

		return $object_properties;
	}
}
