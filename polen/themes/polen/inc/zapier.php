<?php

function send_zapier_by_change_status($order)
{
    try{
        $email = $order->get_billing_email();
        $status = $order->get_status();

        $url = 'https://hooks.zapier.com/hooks/catch/10583855/brolnda';

        $response = wp_remote_post($url, array(
                'method' => 'POST',
                'timeout' => 45,
                'headers' => array(),
                'body' => array(
                    'email' => $email,
                    'status' => $status,
                ),
            )
        );

        if (is_wp_error($response)) {
            wp_send_json_error( 'Sistema indisponível. Por favor entre em contato com o suporte', 503 );
            wp_die();
        }

    } catch (\Exception $e) {
        wp_send_json_error($e->getMessage(), 422);
        wp_die();
    }
}