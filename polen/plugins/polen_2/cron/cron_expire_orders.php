<?php

// Se a execução não for pelo CLI gera Exception
if( strpos( php_sapi_name(), 'cli' ) === false ) {
    echo 'Silence is Golden';
    die;
}

use Polen\Includes\Polen_Order;
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

wp_set_current_user( 1 );
global $wpdb, $Polen_Plugin_Settings, $WC_Cubo9_BraspagReduxSettings;
$current_date = new WC_DateTime();
$args = [
    'fields' => 'ids',
    'order_by' => 'id',
    'order' => 'DESC',
    'post_type' => wc_get_order_types(),
    'nopaging' => true,
    'post_status' => [ 'wc-payment-approved', 'wc-talent-accepted' ],
    'meta_query' => [
        [
            'key' => Polen_Order::META_KEY_DEADLINE,
            'value' => $current_date->getTimestamp(),
            'type' => 'NUMERIC',
            'compare' => '<',
        ]
    ]
];
$wpq = new \WP_Query( $args );
$res = $wpq->get_posts();
if( $res && ! is_null( $res ) && ! is_wp_error( $res ) && is_array( $res ) && count( $res ) > 0 ) {
    foreach( $res as $order_id ) {
        $order = wc_get_order( $order_id );
        echo '#' . $order_id . ': Marcado como expirado extorno manual.' . "\n"; 
        $order->update_status( 'order-expired', 'order_note' );
    }
} else {
    echo "Nenhum pedido a ser expirado.\n";
}

echo "\n";