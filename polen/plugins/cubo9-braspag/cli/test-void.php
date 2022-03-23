<?php

// Se a execução não for pelo CLI gera Exception
if( strpos( php_sapi_name(), 'cli' ) === false ) {
    echo 'Silence is Golden';
    die;
}

function findWordpressBasePath() {
	$dir = dirname( __FILE__ );
	do {
		if( file_exists( $dir . '/wp-config.php' ) ) {
			return $dir;
		}
	} while( $dir = realpath( "$dir/.." ) );
	return null;
}

define( 'BASE_PATH', findWordpressBasePath() . "/" );
define( 'WP_USE_THEMES', false ) ;

global $argv, $wpdb, $Polen_Plugin_Settings, $WC_Cubo9_BraspagReduxSettings;

$order_id = $argv[3];

$order = wc_get_order( $order_id );
$Cubo9_Braspag = new Cubo9_Braspag( $order, false );
$return = $Cubo9_Braspag->void();

if( $return['ProviderReturnMessage'] == 'Operation Successful' ) {
    if( $order->get_status() != 'talent-rejected' && $body->ProviderReturnMessage == 'Operation Successful' ) {
        $order->update_status( 'talent-rejected', 'order_note' );
    }
}
