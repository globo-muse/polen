<?php
if( ! defined( 'ABSPATH' ) ) {
        header( 'location: /' );
}

/**
 * Cubo9: wp-content is now polen
 */
define( 'WP_CONTENT_DIR', ABSPATH . 'polen/' );
define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
define( 'PLUGINDIR', WP_PLUGIN_DIR );

if ( defined( 'WP_CLI' ) ) {
        $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
        $result = mysqli_query($conn, 'SELECT * FROM `wp_options` WHERE `option_name` = \'siteurl\' LIMIT 1;');
        $fetch = mysqli_fetch_row( $result );
        $url_data = parse_url( $fetch[2] );
        $_SERVER['HTTP_HOST'] = $url_data['host'];
        mysqli_close( $conn );
}

define( 'WP_CONTENT_URL', '//'. $_SERVER['HTTP_HOST'] . '/polen' );
define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );

/**
 * JWT
 */
define( 'JWT_AUTH_SECRET_KEY', '90424b7a87b2b4243a0312df61d3e5722b4e87c' );
define( 'JWT_AUTH_CORS_ENABLE', true );

/**
 * Caso o WP_DEBUG esteja ativo
 */
if(defined('WP_DEBUG')) {
        // Enable Debug logging to the /wp-content/debug.log file
        define( 'WP_DEBUG_LOG', true );
        // Disable display of errors and warnings
        define( 'WP_DEBUG_DISPLAY', false );
        @ini_set( 'display_errors', 0 );
        // Use dev versions of core JS and CSS files (only needed if you are modifying these core files)
        define( 'SCRIPT_DEBUG', false );
}
