<?php
/**
 * All customer details.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Stores all customer data that needs to be updated on hubspot.
 *
 * Provide a list of properties and associated data for customer
 * so that at the time of updating a customer on hubspot we can
 * simply create an instance of this class and get everything
 * managed.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubWooCustomer {

	/**
	 * Contact in the form of acceptable by hubspot.
	 *
	 * @since 1.0.0
	 * @var json
	 */
	public $contact;

	/**
	 * WooCommerce Customer ID.
	 *
	 * @since 1.0.0
	 * @var json
	 */
	public $_contact_id;

	/**
	 * Contact Properties.
	 *
	 * @since 1.0.0
	 * @var Array
	 */
	private $_properties = array();

	/**
	 * Instance of HubWooPropertyCallbacks class.
	 *
	 * @since 1.0.0
	 * @var HubWooPropertyCallbacks
	 */
	private $_callback_instance = null;

	/**
	 * Load the modified customer properties.
	 *
	 * Set all the modified customer properties so that they will be
	 * ready in the form of directly acceptable by hubspot api.
	 *
	 * @since    1.0.0
	 * @param int $contact_id ID of the user.
	 */
	public function __construct( $contact_id ) {

		// load the contact id in the class property.
		$this->_contact_id = $contact_id;

		// store the instance of property callback.
		$this->_callback_instance = new HubWooPropertyCallbacks( $this->_contact_id );

		// prepare the modified fields data and store it in the contact.
		$this->prepare_modified_fields();
	}

	/**
	 * Get user email.
	 *
	 * @since 1.0.0
	 */
	public function get_email() {

		return $this->_callback_instance->_get_mail();
	}

	/**
	 * Get all properties with values.
	 *
	 * @return array    and key value pair array of properties.
	 * @since 1.0.0
	 */
	public function get_contact_properties() {

		// let others decide if they have modified fields in there integration.
		$this->_properties = apply_filters( 'hubwoo_contact_modified_fields', $this->_properties, $this->_contact_id );
		return $this->_properties;
	}

	/**
	 * Get all properties with values.
	 *
	 * @param array $properties array of contact properties.
	 * @return array $properties array of properties with user value.
	 * @since 1.0.0
	 */
	public function get_user_data_properties( $properties ) {

		$fname = get_user_meta( $this->_contact_id, 'first_name', true );
		if ( ! empty( $fname ) ) {
			$properties[] = array(
				'property' => 'firstname',
				'value'    => $fname,
			);
		}

		$lname = get_user_meta( $this->_contact_id, 'last_name', true );
		if ( ! empty( $lname ) ) {
			$properties[] = array(
				'property' => 'lastname',
				'value'    => $lname,
			);
		}

		$cname = get_user_meta( $this->_contact_id, 'billing_company', true );
		if ( ! empty( $cname ) ) {
			$properties[] = array(
				'property' => 'company',
				'value'    => $cname,
			);
		}

		$phone = get_user_meta( $this->_contact_id, 'billing_phone', true );
		if ( ! empty( $phone ) ) {
			$properties[] = array(
				'property' => 'mobilephone',
				'value'    => $phone,
			);
			$properties[] = array(
				'property' => 'phone',
				'value'    => $phone,
			);
		}

		$city = get_user_meta( $this->_contact_id, 'billing_city', true );
		if ( ! empty( $city ) ) {
			$properties[] = array(
				'property' => 'city',
				'value'    => $city,
			);
		}

		$state = get_user_meta( $this->_contact_id, 'billing_state', true );
		if ( ! empty( $state ) ) {
			$properties[] = array(
				'property' => 'state',
				'value'    => $state,
			);
		}

		$country = get_user_meta( $this->_contact_id, 'billing_country', true );
		if ( ! empty( $country ) ) {
			$properties[] = array(
				'property' => 'country',
				'value'    => Hubwoo::map_country_by_abbr( $country ),
			);
		}

		$address1 = get_user_meta( $this->_contact_id, 'billing_address_1', true );
		$address2 = get_user_meta( $this->_contact_id, 'billing_address_2', true );

		if ( ! empty( $address1 ) || ! empty( $address2 ) ) {
			$address      = $address1 . ' ' . $address2;
			$properties[] = array(
				'property' => 'address',
				'value'    => $address,
			);
		}

		$postcode = get_user_meta( $this->_contact_id, 'billing_postcode', true );
		if ( ! empty( $postcode ) ) {
			$properties[] = array(
				'property' => 'zip',
				'value'    => $postcode,
			);
		}

		$prop_index = array_search( 'customer_new_order', array_column( $properties, 'property' ) );

		$customer_new_order_flag = 'no';

		if ( ! Hubwoo_Admin::hubwoo_check_for_cart( $properties ) ) {
			if ( Hubwoo_Admin::hubwoo_check_for_properties( 'order_recency_rating', 5, $properties ) ) {
				if ( Hubwoo_Admin::hubwoo_check_for_properties( 'last_order_status', get_option( 'hubwoo_no_status', 'wc-completed' ), $properties ) ) {
					$customer_new_order_flag = 'yes';
				}
			}
		}

		if ( $prop_index ) {
			$properties[ $prop_index ]['value'] = $customer_new_order_flag;
		} else {
			$properties[] = array(
				'property' => 'customer_new_order',
				'value'    => $customer_new_order_flag,
			);
		}

		$properties = apply_filters( 'hubwoo_unset_workflow_properties', $properties );
		$properties = apply_filters( 'hubwoo_map_new_properties', $properties, $this->_contact_id );

		if ( Hubwoo_Admin::hubwoo_check_for_cart( $properties ) ) {
			update_user_meta( $this->_contact_id, 'hubwoo_pro_user_cart_sent', 'yes' );
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

		$hubwoo_vid = $this->get_hubwoo_vid();

		if ( ! empty( $hubwoo_vid ) && $hubwoo_vid > 0 ) {

			$modified_fields = $this->get_contact_modified_fields();
		} else {

			$modified_fields = HubWooContactProperties::get_instance()->hubwoo_get_filtered_properties();
		}

		if ( is_array( $modified_fields ) && count( $modified_fields ) ) {

			foreach ( $modified_fields as $group_fields ) {

				if ( is_array( $group_fields ) ) {

					foreach ( $group_fields as $field ) {
						$property = $this->_prepare_property( $field );

						if ( is_array( $property ) && ! empty( $property['value'] ) ) {

							$this->_properties[] = array(
								'property' => $property['property'],
								'value'    => $property['value'],
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Check if the contact is not uploaded to hubspot.
	 *
	 * @return Int/null   hubspot vid if pre-uploaded either null.
	 * @since 1.0.0
	 */
	private function get_hubwoo_vid() {

		return get_user_meta( $this->_contact_id, 'hubwoo_vid', true );
	}

	/**
	 * Get modified fields since last update of the contact.
	 *
	 * @return     Array     Array of fields modified.
	 */
	public function get_contact_modified_fields() {

		$modified_fields = get_user_meta( $this->_contact_id, 'hubwoo_modified_fields', true );

		if ( ! is_array( $modified_fields ) ) {

			$modified_fields = array();
		}

		return $modified_fields;
	}

	/**
	 * Prepare property in the form of key value accepted by hubspot.
	 *
	 * @param  array $property     array of the property details to validate the value.
	 * @return array               formatted key value pair.
	 */
	public function _prepare_property( $property ) {

		$property_name = isset( $property['name'] ) ? $property['name'] : '';

		if ( ! empty( $property_name ) ) {

			$property_val = $this->_callback_instance->_get_property_value( $property_name, $this->_contact_id );

			$property = array(
				'property' => $property_name,
				'value'    => $property_val,
			);

			return $property;
		}
	}
}
