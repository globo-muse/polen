<?php

namespace Polen\Includes\Hubspot;

defined('ABSPATH') || die('Silence is Golden');

use HubSpot\Client\Crm\Deals\Model\SimplePublicObjectWithAssociations;
use HubSpot\Client\Crm\Tickets\Model\{AssociatedId, SimplePublicObjectInput};
use \HubSpot\Discovery\Discovery;
use Polen\Includes\Module\Polen_Order_Module;

class Polen_Hubspot
{

    const PAYMENT_TYPE_PIX      = 'PIX';
    const PAYMENT_TYPE_CC       = 'Cartão de Crédito';
    const PAYMENT_TYPE_TRANSFER = 'Transferência';
    const PAYMENT_TYPE_OTHERS   = 'Outro';


    public Discovery $client;

    public function __construct(Discovery $client)
    {
        $this->client = $client;
    }


    /**
     * Pegar os dados de um Deal no Hubspot pelo ID
     * 
     * @param int
     * @return HubSpot\Client\Crm\Deals\Model\SimplePublicObjectWithAssociations
     */
    public function get_deal_by_deal_id($deal_id)
    {
        $queryProperties = ["dealname","dealstage","createdate","closedate","amount","budget","pipeline","cache_do_talento","chave_pix"];
        $associations    = ["tickets"];
        $apiResponse = $this->client
            ->crm()
            ->deals()
            ->basicApi()
            ->getById($deal_id, $queryProperties, null, $associations);
        return $apiResponse;
    }


    /**
     * Pega o Ticket associado ao Deal
     * 
     * @param HubSpot\Client\Crm\Deals\Model\SimplePublicObjectWithAssociations
     */
    public function get_ticket_in_deal(SimplePublicObjectWithAssociations $respose)
    {
        $ticket = $respose->getAssociations()['tickets']['results'][0] ?? null;
        if(empty($ticket) || 'deal_to_ticket' != $ticket->getType()) {
            return null;
        }
        return $ticket;
    }


    /**
     * Pega os dados de um Ticket no hubspot
     * 
     * @param HubSpot\Client\Crm\Tickets\Model\AssociatedId
     * @return HubSpot\Client\Crm\Tickets\Model\SimplePublicObjectWithAssociations
     */
    public function get_ticket_data(AssociatedId $ticket_deal)
    {
        $queryProperties = ["subject","hs_pipeline_stage","hs_pipeline",
            "hs_object_id","hs_lastmodifieddate","content","hs_ticket_category",
            "hs_ticket_priority","pagamento_recebido","pagamento_do_talento",
            "meio_de_pagamento_do_talento","meio_de_pagamento_da_empresa",
            "link_de_pagamento","data_de_pagamento_do_talento"];
        $apiResponse = $this->client->crm()->tickets()->basicApi()->getById($ticket_deal->getId(), $queryProperties);
        return $apiResponse;
    }


    /**
     * Faz update no Ticket do Hubspot
     * 
     * @param int
     * @param array [chave:valor] das propriedades
     */
    public function update_ticket($ticket_id, $data)
    {
        $ticket_object_input = new SimplePublicObjectInput(['properties' => $data]);
        return $this->client->crm()->tickets()->basicApi()->update($ticket_id, $ticket_object_input);
    }

    
    /**
     * Pega o ticket_id no hubspot associado ao Deal passando uma Polen_Order_Module
     * 
     * @param Polen_Order_Module
     * @return int
     */
    public function get_hubspot_ticket_id_by_order_module(Polen_Order_Module $order)
    {
        $hubspot_deal = $this->get_deal_by_deal_id($order->hubspot_deal_id());
        $hubspot_ticket_associate = $this->get_ticket_in_deal($hubspot_deal);
        return !empty($hubspot_ticket_associate) ? $hubspot_ticket_associate->getId() : null;
    }

    
    /**
     * Faz update do pagamento_recebido e meio_de_pagamento_da_empresa no Ticket do Hubspot
     * passando um $ticket_id
     * 
     * @param int
     * @param bool
     * @param string PIX || Cartão de Crédito || Transferência || Outro
     */
    public function update_ticket_payment($ticket_id, bool $payment_recived, string $payment_type)
    {
        $update_data = ['pagamento_recebido' => $payment_recived, 'meio_de_pagamento_da_empresa' => $payment_type];
        return $this->update_ticket($ticket_id, $update_data);
    }


    /**
     * Faz update do pagamento_recebido e meio_de_pagamento_da_empresa no Ticket do Hubspot
     * passando um Order_Module
     * 
     * @param Polen_Order_Module
     * @param bool
     * @param string PIX || Cartão de Crédito || Transferência || Outro
     */
    public function update_ticket_payment_by_order_module(Polen_Order_Module $order, bool $payment_recived, string $payment_type)
    {
        $ticket_id = $this->get_hubspot_ticket_id_by_order_module($order);
        if(empty($ticket_id)) return null;
        return $this->update_ticket_payment($ticket_id, $payment_recived, $payment_type);
    }
}
