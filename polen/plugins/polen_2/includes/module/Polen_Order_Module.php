<?php
namespace Polen\Includes\Module;

use Polen\Admin\Polen_Admin_Event_Promotional_Event_Fields;
use Polen\Api\Api_Checkout;
use Polen\Includes\Cart\Polen_Cart_Item;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Polen_Order;
use WC_DateTime;
use WC_Order_Query;

class Polen_Order_Module
{
    const VIDEO_TO_TO_MYSELF = 'to_myself';
    const VIDEO_TO_OTHER_ONE = 'other_one';

    public $object;
    public $cart_item;

    public function __construct($order)
    {
        if(empty($order)) {
            // throw new Exception('Nao é uma order válida', 500);
            return null;
        }

        $this->object = $order;
        $this->cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $this->object );
    }

    /**
     * 
     */
    public function get_is_campaign()
    {
        $order = $this->object;
        $campaign_slug = $order->get_meta( Api_Checkout::ORDER_METAKEY, true );

        if( empty( $campaign_slug ) ) {
            return false;
        }
        return true;
    }

    /**
     * Retornar produto da order
     *
     */
    public function get_product_from_order()
    {
        return $this->cart_item->get_product();
    }

    public function get_talent_name()
    {
        $product = $this->get_product_from_order();
        if(!empty($product)) {
            return $product->get_title();
        }
        return '-';
    }


    /**
     * 
     */
    public function get_campaign_slug()
    {
        $order = $this->object;
        $campaign_slug = $order->get_meta( Api_Checkout::ORDER_METAKEY, true );

        if( empty( $campaign_slug ) ) {
            return '';
        }
        return $campaign_slug;
    }


    /**
     * 
     */
    public function get_video_to()
    {
        return $this->cart_item->get_video_to();
    }


    /**
     * 
     */
    public function get_video_to_is_to_my_self()
    {
        return $this->cart_item->get_video_to() === Polen_Cart_Item::VIDEO_FOR_TO_MY_SELF ? true : false;
    }


    /**
     * 
     */
    public function get_name_to_video()
    {
        if( self::VIDEO_TO_OTHER_ONE === $this->get_video_to() || $this->get_is_campaign() ) {
            return $this->cart_item->get_name_to_video();
        }
        return $this->cart_item->get_offered_by();
    }


    /**
     * 
     */
    public function get_offered_by()
    {
        return $this->cart_item->get_offered_by();
    }


    /**
     * 
     */
    public function get_instructions_to_video()
    {
        return $this->cart_item->get_instructions_to_video();
    }

    /**
     * Retorna um inteiro com o timestamp da deadline
     */
    public function get_deadline()
    {
        return $this->object->get_meta( Polen_Order::META_KEY_DEADLINE, true );
    }


    /**
     * Retorna a deadline formatada
     */
    public function get_deadline_formatted()
    {
        $deadline = $this->get_deadline();
        $currentDate = new WC_DateTime();
        $deadlineDate = WC_DateTime::createFromFormat('U', $deadline);
        $interval = $deadlineDate->diff($currentDate);
        if(!$interval) {
            return '';
        }
        return $interval->format('%d');
    }


    public function get_video_category()
    {
        return $this->cart_item->get_video_category();
    }

    /**
     * Pega a parte que o talento fica (0 ~ 1)
     */
    public function get_talent_fee()
    {
        return $this->cart_item->get_talent_fee();
    }


    /**
     * Order B2B
     */
    public function get_licence_in_days()
    {
        return $this->cart_item->get_licence_in_days();
    }


    /**
     * Order B2B
     */
    public function get_company_name()
    {
        return $this->cart_item->get_company_name();
    }





    /**
     * 
     * Retorna Orders_ids por alguma campanha e por um status especifico
     */
    public static function get_orders_ids_by_campaign_and_status( string $campaign_name, string $order_status )
    {
        $orders_query = new WC_Order_Query([
            'return' => 'ids',
            'limit' => 10,
            'paginate' => true,
            'status' => [ $order_status ],
            'meta_key' => Polen_Admin_Event_Promotional_Event_Fields::FIELD_NAME_SLUG_CAMPAIGN,
            'meta_value' => $campaign_name,
            'orderby' => 'rand'
        ]);
        
        $result = $orders_query->get_orders();
        return $result->orders;
    }


    /**
     * Retorna a Oriem do Pedido se foi Polen ou se for
     * 
     * @return string
     */
    public function get_origin_to_list_orders_talent()
    {
        if( $this->get_is_campaign() ) {
            return $this->get_campaign_slug();
        }

        return 'Polen';
    }


    /**
     * Cria uma linha na lista de Orders do Talento com a origem de onde veio o pedido,
     * se da Polen ou de algum WhiteLabel
     * 
     * @return HTML
     */
    public function get_html_origin_to_list_orders_talent()
    {
        return <<<HTML
            <div class="col-6 col-md-6">
                <p class="p">Origem do Pedido</p>
                <p class="value small">{$this->get_origin_to_list_orders_talent()}</p>
            </div>
        HTML;
    }

    /**
     * Retorna o nome cadastrado de cobrança
     * @return string
     */
    public function get_billing_name()
    {
        return $this->get_billing_first_name() . ' ' . $this->get_billing_last_name();
    }


    /**
     * Retorna o endereço completo cadastrado na cobrança
     * @return string
     */
    public function get_billing_address_full()
    {
        return  '' . 
                $this->get_billing_address_1() . ' ' .
                $this->get_billing_address_2() . ', ' .
                $this->get_billing_city() . ' - ' .
                $this->get_billing_state() . ' ' .
                $this->get_billing_postcode();
    }



    /**
     * Verifica se é a FirstOrder
     * @return bool
     */
    public function get_is_first_order()
    {
        $cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order($this->object);
        $is_first_order = $cart_item->get_first_order();
        return $is_first_order == '1' ? true : false;
    }


    /**
     * Comportamento Padrão do WC_Order
     */
    public function get_id()
    {
        return $this->object->get_id();
    }

    public function get_formatted_order_total()
    {
        return $this->object->get_formatted_order_total();
    }

    public function get_status()
    {
        return $this->object->get_status();
    }

    public function get_total_for_talent()
    {
        return $this->object->get_total() * 0.75;
    }


    public function get_total()
    {
        return $this->object->get_total();
    }

    public function get_subtotal()
    {
        return $this->object->get_subtotal();
    }

    public function get_items()
    {
        return $this->object->get_items();
    }

    public function get_view_order_url()
    {
        return $this->object->get_view_order_url();
    }

    public function get_billing_cnpj_cpf()
    {
        return $this->object->get_meta('_billing_cnpj_cpf');
    }

    public function get_corporate_name()
    {
        return $this->object->get_meta('_billing_corporate_name');
    }

    public function get_post_password()
    {
        $post_formart = get_post($this->get_id());
        return $post_formart->post_password;
    }

    public function get_installments()
    {
        return $this->cart_item->get_installments();
    }

    public function get_billing_email()
    {
        return $this->object->get_billing_email();
    }

    public function get_billing_first_name()
    {
        return $this->object->get_billing_first_name();
    }

    public function get_billing_last_name()
    {
        return $this->object->get_billing_last_name();
    }

    public function get_billing_address_1()
    {
        return $this->object->get_billing_address_1();
    }

    public function get_billing_address_2()
    {
        return $this->object->get_billing_address_2();
    }

    public function get_billing_city()
    {
        return $this->object->get_billing_city();
    }
    
    public function get_billing_company()
    {
        return $this->object->get_billing_company();
    }
    
    public function get_billing_country()
    {
        return $this->object->get_billing_country();
    }
    
    public function get_billing_phone()
    {
        return $this->object->get_billing_phone();
    }
    
    public function get_billing_postcode()
    {
        return $this->object->get_billing_postcode();
    }
    
    public function get_billing_state()
    {
        return $this->object->get_billing_state();
    }
    
    public function calculate_totals()
    {
        return $this->object->calculate_totals();
    }

    public function get_date_created()
    {
        return $this->object->get_date_created();
    }
    
}
