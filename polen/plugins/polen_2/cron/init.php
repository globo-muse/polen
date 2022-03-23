<?php

//Se a execução não for pelo CLI gera Exception
if( strpos(php_sapi_name(), 'cli' ) === false ) {
    echo 'Silence is Goldem';
    die;
}

function findWordpressBasePath() {
	$dir = dirname(__FILE__);
	do {
		if( file_exists($dir."/wp-config.php") ) {
			return $dir;
		}
	} while( $dir = realpath("$dir/..") );
	return null;
}

define( 'BASE_PATH', findWordpressBasePath() . "/" );
// define( 'WP_USE_THEMES', false) ;

global $wp, $wpdb, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header, $Polen_Plugin_Settings;

include_once BASE_PATH . 'polen/plugins/polen_2/autoload.php';
include_once BASE_PATH . 'polen/plugins/polen_2/vendor/autoload.php';
