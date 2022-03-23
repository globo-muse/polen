<?php
/**
 * The class responsible for Error Handling.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.4
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * The class responsible for Error Handling.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubwooErrorHandling {

	/**
	 * The single instance of the class.
	 *
	 * @since   1.0.4
	 * @var HubwooErrorHandling  The single instance of the HubwooErrorHandling
	 */
	protected static $instance = null;

	/**
	 * Main HubwooErrorHandling Instance.
	 *
	 * Ensures only one instance of HubwooErrorHandling is loaded or can be loaded.
	 *
	 * @since 1.0.4
	 * @static
	 * @return HubwooErrorHandling - Main instance.
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Handling the error response from HubSpot.
	 *
	 * @since 1.0.4
	 * @param array  $response response from HubSpot.
	 * @param string $type type of error.
	 * @param array  $additional_args any additional args.
	 */
	public function hubwoo_handle_response( $response, $type, $additional_args = array() ) {

		switch ( $type ) {
			case HubwooConst::HUBWOOWORKFLOW:
				return $this->hubwoo_handle_workflow( $response, $additional_args );
			case HubwooConst::CHECKOUTFORM:
				return $this->hubwoo_handle_form( $response, $additional_args );
			default:
				return;
		}
	}

	/**
	 * Handling errors in Forms.
	 *
	 * @since 1.0.4
	 * @param array $response response from HubSpot.
	 * @param array $additional_args any additional args.
	 */
	public function hubwoo_handle_form( $response, $additional_args ) {

		switch ( $response['status_code'] ) {
			case 409:
				$res = HubWooConnectionMananager::get_instance()->hubwoo_get_all_forms();
				if ( 200 == $res['status_code'] ) {
					$res = json_decode( $res['body'], true );
					if ( ! empty( $res ) ) {
						foreach ( $res as $form_value ) {
							if ( HubwooConst::CHECKOUTFORMNAME == $form_value['name'] ) {
								update_option( 'hubwoo_checkout_form_created', 'yes' );
								update_option( 'hubwoo_checkout_form_id', $form_value['guid'] );
								break;
							}
						}
					}
				}
				break;
			default:
				return;
		}
	}

	/**
	 * Handling errors in Workflows.
	 *
	 * @since 1.0.4
	 * @param array $response response from HubSpot.
	 * @param array $additional_args any additional args.
	 */
	public function hubwoo_handle_workflow( $response, $additional_args ) {

		if ( array_key_exists( 'current_workflow', $additional_args ) && ! empty( $additional_args['current_workflow'] ) ) {

			$properties = array();

			$current_workflow = $additional_args['current_workflow'];

			switch ( $response['status_code'] ) {
				case 500:
					foreach ( $current_workflow['actions'] as $action ) {
						if ( array_key_exists( 'propertyName', $action ) ) {
							$properties[] = $action['propertyName'];
						}
					}
					if ( ! empty( $properties ) ) {
						$all_properties = HubWooContactProperties::get_instance()->_get( 'properties', '', true );
						foreach ( $properties as $property ) {

							$response = HubWooConnectionMananager::get_instance()->hubwoo_read_object_property( 'contact', $property );
							if ( 404 == $response['status_code'] ) {
								$this->hubwoo_prepare_single_property( $all_properties, $property );
							}
						}
						return HubWooConnectionMananager::get_instance()->create_workflow( $current_workflow );
					}
					break;
				default:
					return;
			}
		}
	}

	/**
	 * Read and create property in HubSpot.
	 *
	 * @since 1.0.4
	 * @param array  $all_properties all properties.
	 * @param string $property current property to be checked.
	 */
	public function hubwoo_prepare_single_property( $all_properties, $property ) {
		$property_details = array();
		foreach ( $all_properties as $group_name => $group_properties ) {
			foreach ( $group_properties as $group_property ) {
				if ( $property == $group_property['name'] ) {
					$property_details              = $group_property;
					$property_details['groupName'] = $group_name;
					HubWooConnectionMananager::get_instance()->create_property( $property_details );
				}
			}
		}
	}

}
