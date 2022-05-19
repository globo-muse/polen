<?php
namespace  Polen\Api\Module;

use Polen\Includes\Module\Polen_Order_Module;

interface Payments {

    /**
     * Implementar construtor para todas as classes terem a mesma assinatura
     * @param Polen_Order_Module $order_module // Order atual que será passada pelo o metodo de pagamento
     * @param array $data // Dados do request
     */
    public function __construct(Polen_Order_Module $order_module, array $data);

    /**
     * implementar criação de usuário
     */
    public function set_costumer();

    /**
     * Implementar criação de cartão de crédito
     */
    public function set_credit_card();

    /**
     * Usar ano para retornar formato correto para o metodo de pagamento
     * @param string $date FORMAT MM/YYYY
     */
    public function handle_card_expiration_date(string $date);

    /**
     * Request de pagamento
     */
    public function pay_request();

}