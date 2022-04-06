<?php

namespace Polen\Api\Module;

use Exception;

class Tuna_Credit_Card extends Gateway_Tuna
{
    /**
     * Montar array para card_info no body da requisição com a API
     *
     * @param $current_user
     * @param array $data
     * @return array
     */
    private function card_info_body($current_user, array $data): array
    {
        $session_id = parent::get_session_id($current_user['user_object']->data);
        $token = parent::generate_token_card($session_id, $data);
        $tuna_expiration_date = $this->separate_month_year($data['tuna_expiration_date']);

        $value_document = preg_replace( '/[^0-9]/', '', $data['cnpj']);
        $type_document = parent::check_cpf_cnpj($value_document);

        return [
            "Token" => $token,  
            "TokenProvider" => 'Tuna',
            "CardHolderName" => sanitize_text_field($data["tuna_card_holder_name"]),
            "BrandName" => sanitize_text_field($data["tuna_card_brand"]),
            "ExpirationMonth" => (int) $tuna_expiration_date[0],
            "ExpirationYear" => (int) $tuna_expiration_date[1],
            "TokenSingleUse" => 1,
            "SaveCard" => false,
            "BillingInfo" => [
                "Document" => $type_document,
                "DocumentType" => $value_document,
                "Address" => [
                    "Street" => '',
                    "Number" => '',
                    "Complement" => '',
                    "Neighborhood" => '',
                    "City" => '',
                    "State" => '',
                    "Country" => '',
                    "PostalCode" => '',
                    "Phone" => '',
                ]
            ]
        ];
    }

    /**
     * Fazer requisição de pagamento com cartão de crédito
     */
    public function payment($order_id, $current_user, $data): array
    {
        $card_info = $this->card_info_body($current_user, $data);

        $installments = 1;
        if (isset($data['installments'])) {
            $installments = sanitize_text_field(($data['installments']));
        }

        $body = parent::body_for_request($order_id, $current_user, $card_info, $installments);
        $response_api_tuna = parent::request($body);

        $new_status = $this->get_status_response($response_api_tuna->status);
        if('failed' === $new_status || 'cancelled' === $new_status) {
            throw new Exception('Erro no pagamento, tente novamente', 422);
        }

        $response_message = parent::get_response_message($new_status);

        $order = wc_get_order($order_id);
        $order->update_status($new_status);

        $response_payment = [
            'message' => $response_message['message'],
            'method_payment' => 'Cartão de crédito',
            'order_status' => $response_message['status_code'],
            // 'order_code' => $order->get_order_key()
        ];

        return $response_payment;
    }

    /**
     * Quebrar data em formato de stirng para array, separando Mes e Ano
     *
     * @param string $date
     * @return array
     */
    private function separate_month_year(string $date): array
    {
        return explode('/', $date);
    }

}