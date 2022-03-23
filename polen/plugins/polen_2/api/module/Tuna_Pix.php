<?php

namespace Polen\Api\Module;

use Exception;

class Tuna_Pix extends Gateway_Tuna
{
    /**
     * Fazer requisição de pagamento com cartão de crédito
     */
    public function payment($order_id, $current_user, $data): array
    {
        $body = parent::body_for_request($order_id, $current_user);
        $response_api_tuna = parent::request($body);
        $new_status = $this->get_status_response($response_api_tuna->status);
        if('failed' === $new_status || 'cancelled' === $new_status) {
            throw new Exception('Erro no pagamento, tente novamente', 422);
        }

        $response_message = parent::get_response_message($new_status);

        return [
            'message' => $response_message['message'],
            'method_payment' => 'Pix',
            'order_status' => $response_message['status_code'],
            'pix_code' => $response_api_tuna->methods[0]->pixInfo->qrContent,
            'pix_qrcode' => $response_api_tuna->methods[0]->pixInfo->qrImage,
        ];
    }

    /**
     * Verificar status do pedido
     *
     * @param $order_id
     * @return string
     * @throws Exception
     */
    public function get_status($order_id): string
    {
        $tuna = new Gateway_Tuna();

        $order = wc_get_order($order_id);
        $status_woocommerce = $order->get_status();
        $status_tuna = $tuna->get_tuna_status($order_id);

        if ($status_woocommerce != $status_tuna) {
            $order->update_status($status_tuna);
        }

        return $status_tuna;
    }

}
