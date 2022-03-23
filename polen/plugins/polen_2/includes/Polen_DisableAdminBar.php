<?php

namespace Polen\Includes;

class Polen_DisableAdminBar {

    function __construct() {
        $Polen_Plugin_Settings = get_option('Polen_Plugin_Settings');
        if (
                isset($Polen_Plugin_Settings['admin_bar']) 
                && strval($Polen_Plugin_Settings['admin_bar']) == strval('1')
            ) {
            add_filter('show_admin_bar', '__return_false');
        }
    }

}
