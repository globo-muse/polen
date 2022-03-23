<?php

add_filter('woocommerce_get_checkout_order_received_url','override_return_url', 10,2 );
function override_return_url($return_url, $order)
{
    $method = $order->get_payment_method_title();

    $order_items = $order->get_items();
    $first_item  = reset($order_items);
    $video_to    = $first_item->get_meta('video_to');

    $extension = [
        'method' => $method,
        'videoto' => $video_to,
    ];

    $url_extension = http_build_query($extension);

    return $return_url . '&' . $url_extension;
}