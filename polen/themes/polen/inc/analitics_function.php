<?php
if ( ! defined( 'ABSPATH' ) ) {
    echo 'Silence is golden';
	exit;
}


/**
 * Funcao que criar uma chamada JS quando um compra é dada como sucesso
 *
 * @param WC_Order
 */
function polen_create_ga_order( $order )
{
    $items = $order->get_items();
    $item = array_pop( $items );
    $product = $item->get_product();
    $category_ids = $product->get_category_ids();
    $category_id = array_pop( $category_ids );
    $category_name = get_term_by( 'id', $category_id, 'product_cat' );

    $output = <<<EOL
    <script>
    polenGtag.sendEvent(polenGtag.type.purchase, {
    "transaction_id": "{$order->get_id()}",
    "affiliation": "Polen.me",
    "value": {$order->get_total()},
    "currency": "BRL",
    "tax": 0.0,
    "shipping": 0,
    "items": [
        {
        "id": "{$item->get_product_id()}",
        "name": "{$item->get_name()}",
        "category": "{$category_name->name}",
        "list_position": 1,
        "quantity": {$item->get_quantity()},
        "price": "{$item->get_subtotal()}"
        }
    ]
    });
    </script>
    EOL;
    return $output;
}
