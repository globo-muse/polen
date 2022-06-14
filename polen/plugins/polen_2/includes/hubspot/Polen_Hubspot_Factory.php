<?php

namespace Polen\Includes\Hubspot;

defined('ABSPATH') || die('Silence is Golden');

use HubSpot\Factory;

class Polen_Hubspot_Factory
{
    static public function create_client_with_redux()
    {
        global $Polen_Plugin_Settings;
        $client = Factory::createWithApiKey($Polen_Plugin_Settings['hubspot_api_key']);
        return $client;
    }
}
