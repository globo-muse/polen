<?php
namespace Polen\Includes\Module\Products;

use Exception;
use Polen\Includes\Module\Polen_Order_Module;

class Polen_B2B_Orders extends Polen_Order_Module
{
    private \WC_Order $order;

    /**
     * @throws Exception
     */
    public function __construct($order_id, $order_key)
    {
        $this->order = $this->get_order($order_id, $order_key);
        parent::__construct($this->get_order($order_id, $order_key));
    }

    /**
     * Salvar no atributo order
     *
     * @param int $order_id
     * @param string $order_key
     * @return \WC_Order
     * @throws Exception
     */
    public function get_order(int $order_id, string $order_key): \WC_Order
    {
        $order = wc_get_order($order_id);
        if (empty($order)) {
            throw new Exception('Não existe pedido com esse ID', 403);
        }

        if ($order->get_status() == 'completed') {
            throw new Exception('Esse pedido já foi pago!', 406);
        }

//        if ($order->get_order_key() !== $order_key) {
//            throw new Exception('Chave do pedido é diferente da chave informada', 403);
//        }

        return $order;
    }

    /**
     * Atualizar metas da order
     *
     * @param array $data
     */
    public function update_order(array $data): void
    {
        $metas = $this->meta_to_order();
        $value_to_meta = array_intersect_key($data, $metas);

        foreach ($value_to_meta as $key => $value) {
            update_post_meta($this->order->get_id(), "_billing_{$key}", $value);
        }
    }

    /**
     * Retornar informações básicas do pedido para tela de checkout 1
     *
     * @return array
     */
    public function get_order_info_step_one(): array
    {
        $cnpj = parent::get_billing_cnpj();
        $corporate_name = parent::get_corporate_name();
        $company_name = parent::get_company_name();
        $instructions_to_video = parent::get_instructions_to_video();
        $licence_in_days = parent::get_licence_in_days();
        $price = parent::get_total();
        $category_video = parent::get_video_category();

        return compact('cnpj', 'corporate_name', 'company_name', 'instructions_to_video', 'licence_in_days', 'category_video', 'price');
    }

    /**
     * Definir metas que devem ser atualizados na order
     *
     * @return string[]
     */
    private function meta_to_order(): array
    {
        return [
            'company' => 'Nome empresa',
            'address_1' => 'Endereço',
            'address_2' => 'Complemento',
            'city' => 'Cidade',
            'neighborhood' => 'Bairro',
            'postcode' => 'CEP',
            'country' => 'País',
            'state' => 'Estado',
            'email' => 'Email',
            'phone' => 'Celular',
            'cnpj' => 'CNPJ',
            'corporate_name' => 'Razão Social',
        ];
    }
}
