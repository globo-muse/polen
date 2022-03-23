<?php

namespace Polen\Includes;

use Polen\Api\Api_Checkout;
use Polen\Includes\Module\Polen_Product_Module;

class Polen_Campaign
{

    const KEY_CAMPAIGN = 'campaigns';
    const LABEL_CAMPAIGN = 'Campanhas';
    const SLUG_CAMPAIGN = 'campanha';
    const KEY_USER_META_CAMPAIGN = Api_Checkout::USER_METAKEY;

    public function __construct( bool $static = false )
    {
        if( $static ) {
            add_action('init', [ $this, 'create_taxonomy_campaigns' ]);
        }
    }


    /**
     * Registrar taxonomia de campanha em produtos
     */
    public function create_taxonomy_campaigns()
    {
        register_taxonomy(
            self::KEY_CAMPAIGN,
            'product',
            array(
                'label' => self::LABEL_CAMPAIGN,
                'rewrite' => array( 'slug' => self::SLUG_CAMPAIGN ),
                'hierarchical' => true,
            )
        );
    }


    /**
     * 
     * @param WC_Order
     * @return bool
     */
    public static function get_is_order_campaing( $order )
    {
        $meta_key = $order->get_meta( Api_Checkout::ORDER_METAKEY, true );
        if( !empty( $meta_key ) ) {
            return true;
        }
        return false;
    }


    /**
     * 
     * @param WC_Order
     * @return string
     */
    public static function get_order_campaing_slug( $order )
    {
        $meta_key = $order->get_meta( Api_Checkout::ORDER_METAKEY, true );
        return $meta_key;
    }

    /**
     * Pega se um usuário foi criado em alguma campagina
     * @param int
     * @return bool
     */
    public static function get_is_user_campaing( $user_id )
    {
        $meta_campaign = get_user_meta( $user_id, self::KEY_USER_META_CAMPAIGN, true );
        if( empty( $meta_campaign ) ){
            return false;
        }
        return true;
    }

    /**
     * Pega o slug de campanha de um usuário
     * se ele foi criado em alguma campanha
     * @param int
     * @return string
     */
    public static function get_user_campaing_slug( $user_id )
    {
        $meta_campaign = get_user_meta( $user_id, self::KEY_USER_META_CAMPAIGN, true );
        return $meta_campaign;
    }

    /**
     * Pega o slug de campanha de um usuário
     * se ele foi criado em alguma campanha
     * @param WP_User
     * @return string
     */
    public static function get_user_is_created_checkout( $user )
    {
        $meta_is_checkout = get_user_meta( $user->ID, Polen_Checkout_Create_User::META_KEY_CREATED_BY, true );
        return $meta_is_checkout;
    }


    /**
     * 
     */
    public static function set_user_campaign( $customer_id, $campaign )
    {
        return update_user_meta( $customer_id, self::KEY_USER_META_CAMPAIGN, $campaign, true );
    }


    /**
     * 
     */
    public static function get_product_is_campaign( $product )
    {
        $polen_product = new Polen_Product_Module( $product );
        return $polen_product->get_is_campaign();
    }

    /**
     * 
     */
    public static function get_product_campaign_slug( $product )
    {
        $polen_product = new Polen_Product_Module( $product );
        return $polen_product->get_campaign_slug();
    }
}