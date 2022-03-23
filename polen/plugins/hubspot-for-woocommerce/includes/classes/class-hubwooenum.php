<?php
/**
 * Manage Enums.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes/classes
 */

/**
 * Manage Enums.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes/classes
 */
abstract class HubWooEnum {

	/**
	 * Register all of the Enums.
	 *
	 * @since    1.0.4
	 * @param any $value value for the enum.
	 * @throws IllegalArgumentException If the value is illegal.
	 */
	final public function __construct( $value ) {
		$c = new ReflectionClass( $this );
		if ( ! in_array( $value, $c->getConstants() ) ) {
			throw IllegalArgumentException();
		}
		$this->value = $value;
	}

	/**
	 * Conver the Enum value to String.
	 *
	 * @since    1.0.4
	 */
	final public function __toString() {
		return $this->value;
	}
}
