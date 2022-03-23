<?php
namespace Polen\Includes;

use Exception;

class Polen_Zapier
{

    /**
     * Envia um Request para o Zapier
     * @param string $url
     * @param array @body
     * @param int $timeout
     * @return bool
     * @throws Exception
     */
    public function send(string $url, array $body, $timeout = 45)
    {
        $response = wp_remote_post($url, array(
                'method' => 'POST',
                'timeout' => $timeout,
                'headers' => array(),
                'body' => $body,
            )
        );

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message(), 503);
        }
        return true;
    }
}