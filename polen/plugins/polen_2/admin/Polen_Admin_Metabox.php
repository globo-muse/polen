<?php

namespace Polen\Admin;

class Polen_Admin_Metabox
{
    protected string $path_file = '';

    public function __construct()
    {
        $this->path_file = plugin_dir_path(__FILE__) . 'partials/metaboxes/b2c/';
        add_action('add_meta_boxes', array($this, 'add_metaboxes'));
    }

    public function add_metaboxes()
    {
        global $post, $current_screen;

        add_meta_box( 'Polen_Post_Media', 'Configurações Gerais', array( $this, 'metabox_polen_media' ), 'post_polen_media', 'normal', 'low' );
        add_meta_box( 'Polen_Refund_Order_tuna', 'Reembolsar pedido', array( $this, 'metabox_create_refund_order_tuna' ), 'shop_order', 'side', 'default' );

        if( $current_screen && ! is_null( $current_screen ) && isset( $current_screen->id ) && $current_screen->id == 'product' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' )  {
            $product_id = $post->ID;
            add_meta_box( 'Polen_Product_First_Order', 'Primeira Order', array( $this, 'metabox_create_first_order' ), 'product', 'side', 'default' );
            add_meta_box( 'Polen_Product_SEO', 'SEO', array( $this, 'metabox_SEO' ), 'product', 'normal', 'default' );
        }
    }


    /**
     * Adicionar metabox na edição de produtos
     */
    public function metabox_create_refund_order_tuna()
    {
        global $post;
        $product_id = $post->ID;
        if( file_exists( TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-refund-order-tuna.php' ) ) {
            require_once TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-refund-order-tuna.php';
        } else {
            require_once PLUGIN_POLEN_DIR . '/admin/partials/metaboxes/metabox-refund-order-tuna.php';
        }
    }

    /**
     * Adicionar metabox na edição de produtos
     */
    public function metabox_polen_media()
    {

        global $post;
        $product_id = $post->ID;
        if( file_exists( TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-polen-media.php' ) ) {
            require_once TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-polen-media.php';
        } else {
            require_once PLUGIN_POLEN_DIR . '/admin/partials/metaboxes/metabox-polen-media.php';
        }
    }

    public function metabox_create_first_order()
    {
        global $post;
        $product_id = $post->ID;
        if( file_exists( TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-first-order.php' ) ) {
            require_once TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-first-order.php';
        } else {
            require_once PLUGIN_POLEN_DIR . '/admin/partials/metaboxes/metabox-first-order.php';
        }
    }

    public function metabox_SEO($post)
    {
        if( file_exists( TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-seo.php' ) ) {
            require_once TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-seo.php';
        } else {
            require_once PLUGIN_POLEN_DIR . '/admin/partials/metaboxes/metabox-seo.php';
        }
    }
}
