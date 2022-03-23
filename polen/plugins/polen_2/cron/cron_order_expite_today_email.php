<?php
include_once dirname( __FILE__ ) . '/init.php';

use Automattic\WooCommerce\Admin\API\Reports\TimeInterval;
use Polen\Includes\Emails\Polen_WC_Order_Expire_Today;
use Polen\Includes\Polen_Order;

$current_date = new WC_DateTime();
$plus_one_day = new DateInterval('P1D');
$tomorrow = $current_date->add( $plus_one_day );
$tomorrow_init = WC_DateTime::createFromFormat( 'Y/m/d H:i:s',  $tomorrow->format('Y/m/d') . ' 00:00:00' );
$tomorrow_final = WC_DateTime::createFromFormat( 'Y/m/d H:i:s', $tomorrow->format('Y/m/d') . ' 23:59:59' );

echo "Vence amanha: ";
echo $tomorrow->format('d/m/Y');
echo "\n";
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
            'value' => $tomorrow_init->getTimestamp(),
            'type' => 'NUMERIC',
            'compare' => '>=',
        ],
        [
            'key' => Polen_Order::META_KEY_DEADLINE,
            'value' => $tomorrow_final->getTimestamp(),
            'type' => 'NUMERIC',
            'compare' => '<=',
        ],
    ]
];

$wp_pq = new \WP_Query( $args );
$orders_ids = $wp_pq->get_posts();

\WC_Emails::instance();
$pe = new Polen_WC_Order_Expire_Today();

if( !empty( $orders_ids ) ) {
    foreach( $orders_ids as $order_id ) {
        echo "Enviando email para Order_ID: {$order_id}";
        $pe->trigger( $order_id );
        echo "\n";
    }
}
