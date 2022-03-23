<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://polen.me
 * @since      1.0.0
 *
 * @package    Promotional_Event
 * @subpackage Promotional_Event/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Promotional_Event
 * @subpackage Promotional_Event/includes
 * @author     Polen.me <glaydson.queiroz@polen.me>
 */
class Promotional_Event_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
        register_deactivation_hook( __FILE__, 'promotional_event' );
        self::drop_table_promotinal_event();
	}

    public static function drop_table_promotinal_event()
    {

    }

}
