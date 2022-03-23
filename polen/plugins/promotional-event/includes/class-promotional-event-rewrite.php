<?php

use Polen\Includes\Polen_404;

class Promotional_Event_Rewrite
{

    const BASE_URL = 'produtos';

    const QUERY_VARS_EVENT_PROMOTIONAL_APP     = 'event_promotinal_app';
    const QUERY_VARS_EVENT_PROMOTIONAL_IS_HOME = 'event_promotinal_is_home';
    const QUERY_VARS_EVENT_PROMOTIONAL_VALIDATION = 'event_promotinal_validation';
    const QUERY_VARS_EVENT_PROMOTIONAL_ORDER = 'event_promotinal_order';
    const QUERY_VARS_EVENT_PROMOTIONAL_SUCCESS = 'event_promotinal_success';
    const QUERY_VARS_EVENT_PROMOTIONAL_DETAIL_PRODUCT = 'event_promotinal_detail_product';

    const QUERY_VARS_EP_PRODUCT_SLUG = 'ep_object';
    const GLOBAL_KEY_PRODUCT_OBJECT = 'ep_product_object';

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        add_action( 'init',             array( $this, 'rewrites' ) );
        add_filter( 'query_vars',       array( $this, 'query_vars' ), 10, 1 );
        add_action( 'template_include', array( $this, 'template_include' ) );
    }



    /**
     * 
     */
    public function rewrites()
    {
        add_rewrite_rule( self::BASE_URL . '/([^/]*)/?/confirmado',     'index.php?'.self::QUERY_VARS_EVENT_PROMOTIONAL_APP.'=1&'.self::QUERY_VARS_EP_PRODUCT_SLUG.'=$matches[1]&'.self::QUERY_VARS_EVENT_PROMOTIONAL_SUCCESS.'=1', 'top' );
        add_rewrite_rule( self::BASE_URL . '/([^/]*)/?/pedido',         'index.php?'.self::QUERY_VARS_EVENT_PROMOTIONAL_APP.'=1&'.self::QUERY_VARS_EP_PRODUCT_SLUG.'=$matches[1]&'.self::QUERY_VARS_EVENT_PROMOTIONAL_ORDER.'=1', 'top' );
        add_rewrite_rule( self::BASE_URL . '/([^/]*)/?/validar-codigo', 'index.php?'.self::QUERY_VARS_EVENT_PROMOTIONAL_APP.'=1&'.self::QUERY_VARS_EP_PRODUCT_SLUG.'=$matches[1]&'.self::QUERY_VARS_EVENT_PROMOTIONAL_VALIDATION.'=1', 'top' );
        add_rewrite_rule( self::BASE_URL . '/([^/]*)/?',                'index.php?'.self::QUERY_VARS_EVENT_PROMOTIONAL_APP.'=1&'.self::QUERY_VARS_EP_PRODUCT_SLUG.'=$matches[1]&'.self::QUERY_VARS_EVENT_PROMOTIONAL_DETAIL_PRODUCT.'=1', 'top' );
        add_rewrite_rule( self::BASE_URL . '[/]?$',                     'index.php?'.self::QUERY_VARS_EVENT_PROMOTIONAL_APP.'=1&'.self::QUERY_VARS_EVENT_PROMOTIONAL_IS_HOME.'=1', 'top' );
    }


    /**
     * 
     */
    public function query_vars( $query_vars )
    {
        $query_vars[] = self::QUERY_VARS_EVENT_PROMOTIONAL_APP;
        $query_vars[] = self::QUERY_VARS_EVENT_PROMOTIONAL_IS_HOME;
        $query_vars[] = self::QUERY_VARS_EVENT_PROMOTIONAL_VALIDATION;
        $query_vars[] = self::QUERY_VARS_EVENT_PROMOTIONAL_ORDER;
        $query_vars[] = self::QUERY_VARS_EVENT_PROMOTIONAL_SUCCESS;
        $query_vars[] = self::QUERY_VARS_EVENT_PROMOTIONAL_DETAIL_PRODUCT;
        $query_vars[] = self::QUERY_VARS_EP_PRODUCT_SLUG;
        return $query_vars;
    }


    /**
     * 
     */
    public function template_include( $template )
    {
        $app = get_query_var( self::QUERY_VARS_EVENT_PROMOTIONAL_APP );
        if( empty( $app ) || $app !== '1' ) {
            return $template;
        }

        $GLOBALS[ self::QUERY_VARS_EVENT_PROMOTIONAL_APP ] = $app;
        
        if( $this->is_home() ) {
            $GLOBALS[ self::QUERY_VARS_EVENT_PROMOTIONAL_IS_HOME ] = '1';
        }

        $ep_object_sku = get_query_var( self::QUERY_VARS_EP_PRODUCT_SLUG );
        $ep_object_id = wc_get_product_id_by_sku( $ep_object_sku );
        $ep_object = wc_get_product( $ep_object_id );

        if( !self::is_promotional_event( $ep_object ) ) {
            return Polen_404::set_default_404();
        }
        $GLOBALS[ self::GLOBAL_KEY_PRODUCT_OBJECT ] = $ep_object;

        if( $this->is_page_detail() ) {
            $GLOBALS[ self::QUERY_VARS_EVENT_PROMOTIONAL_DETAIL_PRODUCT ] = '1';
            return get_template_directory() . '/event_promotional/index.php';
        }

        if( $this->is_page_validation() ) {
            $GLOBALS[ self::QUERY_VARS_EVENT_PROMOTIONAL_VALIDATION ] = '1';
            return get_template_directory() . '/event_promotional/validation.php';
        }

        if( $this->is_page_order() ) {
            $GLOBALS[ self::QUERY_VARS_EVENT_PROMOTIONAL_ORDER ] = '1';
            return get_template_directory() . '/event_promotional/order.php';
        }

        if( $this->is_page_success() ) {
            $GLOBALS[ self::QUERY_VARS_EVENT_PROMOTIONAL_ORDER ] = '1';
            return get_template_directory() . '/event_promotional/success.php';
        }
    }



    /**
     * 
     */
    private function is_home()
    {
        $is_home = get_query_var( self::QUERY_VARS_EVENT_PROMOTIONAL_IS_HOME );
        if( !empty( $is_home ) || $is_home == '1' ) {
            return true;
        }
        return false;
    }


    /**
     * 
     */
    private function is_page_validation()
    {
        $page = get_query_var( self::QUERY_VARS_EVENT_PROMOTIONAL_VALIDATION );
        if( !empty( $page ) || $page == '1' ) {
            return true;
        }
        return false;
    }


    /**
     * 
     */
    private function is_page_order()
    {
        $page = get_query_var( self::QUERY_VARS_EVENT_PROMOTIONAL_ORDER );
        if( !empty( $page ) || $page == '1' ) {
            return true;
        }
        return false;
    }



    /**
     * 
     */
    private function is_page_success()
    {
        $page = get_query_var( self::QUERY_VARS_EVENT_PROMOTIONAL_SUCCESS );
        if( !empty( $page ) || $page == '1' ) {
            return true;
        }
        return false;
    }


    /**
     * 
     */
    private function is_page_detail()
    {
        $page = get_query_var( self::QUERY_VARS_EVENT_PROMOTIONAL_DETAIL_PRODUCT );
        if( !empty( $page ) || $page == '1' ) {
            return true;
        }
        return false;
    }

    static public function get_current_product()
    {
        return $GLOBALS[ self::GLOBAL_KEY_PRODUCT_OBJECT ];
    }

    static public function is_promotional_event( $product )
    {
        if( empty( $product ) ) {
            return false;
        }
        if( !event_promotional_product_is_event_promotional( $product ) ) {
            return false;
        }
        return true;
    }

}
