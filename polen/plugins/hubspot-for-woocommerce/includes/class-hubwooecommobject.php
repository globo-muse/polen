<?php
/**
 * All object details.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Stores all object data that needs to be updated on hubspot.
 *
 * Provide a list of properties and associated data for objects
 * so that at the time of updating a object on hubspot we can
 * simply create an instance of this class and get everything
 * managed.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubwooEcommObject {

	/**
	 * Object in the form of acceptable by hubspot.
	 *
	 * @since 1.0.0
	 * @var json
	 */
	public $_object_type;

	/**
	 * WooCommerce Object ID
	 *
	 * @since 1.0.0
	 * @var json
	 */
	public $_object_id;

	/**
	 * Object Properties.
	 *
	 * @since 1.0.0
	 * @var Array
	 */
	private $_properties = array();

	/**
	 * Instance of HubwooEcommPropertyCallbacks class
	 *
	 * @since 1.0.0
	 * @var $_callback_instance
	 */
	private $_callback_instance = null;

	/**
	 * Load the modified object properties.
	 *
	 * Set all the modified object properties so that they will be
	 * ready in the form of directly acceptable by hubspot api.
	 *
	 * @param int    $id object id.
	 * @param string $object_type ecomm object type.
	 * @since 1.0.0
	 */
	public function __construct( $id, $object_type ) {

		// load the object id in the class property.
		$this->_object_id = $id;

		// load the object type in the class property.
		$this->_object_type = $object_type;

		// store the instance of property callback.
		$this->_callback_instance = new HubwooEcommPropertyCallbacks( $this->_object_id, $this->_object_type );

		// prepare the modified fields data and store it in the object.
		$this->prepare_modified_fields();
	}

	/**
	 * All properties for sync messages.
	 *
	 * @return array and key value pair array of properties.
	 * @since 1.0.0
	 */
	public function get_object_properties() {

		// let others decide if they have modified fields in there integration.
		$this->_properties = apply_filters( 'hubwoo_ecomm_' . $this->_object_type . '_modified_fields', $this->_properties, $this->_object_id );
		return $this->_properties;
	}

	/**
	 * All properties for creation
	 *
	 * @return array and key value pair array of properties.
	 * @since 1.0.0
	 */
	public function get_object_properties_for_creation() {

		$modified_fields = HubwooEcommProperties::get_instance()->hubwoo_ecomm_get_properties_for_object( $this->_object_type );

		$properties = array();

		if ( is_array( $modified_fields ) && count( $modified_fields ) ) {

			foreach ( $modified_fields as $single_field ) {

				$property_val = $this->_return_property_value( $single_field );

				if ( ! empty( $property_val ) ) {

					$properties[] = array(
						'name'  => $single_field,
						'value' => $property_val,
					);
				}
			}
		}

		return $properties;
	}

	/**
	 * Format modified fields of customer.
	 *
	 * Check for all the modified fields till the last update
	 * and prepare them in the hubspot api acceptable form.
	 *
	 * @since 1.0.0
	 */
	private function prepare_modified_fields() {

		// need to update all fields, so lets get all the properties that we are working with.
		$modified_fields = HubwooEcommProperties::get_instance()->hubwoo_ecomm_get_properties_for_object( $this->_object_type );

		if ( is_array( $modified_fields ) && count( $modified_fields ) ) {

			foreach ( $modified_fields as $single_field ) {

				$property = $this->_return_property_value( $single_field );

				if ( ! empty( $property ) ) {

					$this->_properties[ $single_field ] = $property;
				}
			}
		}
	}


	/**
	 * Prepare property in the form of key value accepted by hubspot.
	 *
	 * @param  array $property_name     array of the property details to validate the value.
	 * @return string   property value
	 */
	public function _return_property_value( $property_name ) {

		// if property name is not empty.
		if ( ! empty( $property_name ) ) {
			// get property value.
			$property_val = $this->_callback_instance->_get_object_property_value( $property_name, $this->_object_id );
			return $property_val;
		}
	}
}
