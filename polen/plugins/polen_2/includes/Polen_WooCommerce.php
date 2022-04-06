<?php

namespace Polen\Includes;

use Polen\Admin\Polen_Admin_B2B_Product_Fields;
use Polen\Admin\Polen_Admin_Event_Promotional_Event_Fields;
use Polen\Admin\Polen_Admin_Social_Base_Product_Fields;

class Polen_WooCommerce 
{
    const ORDER_STATUS_PAYMENT_IN_REVISION = 'payment-in-revision';
    const ORDER_STATUS_PAYMENT_REJECTED    = 'payment-rejected';
    const ORDER_STATUS_PAYMENT_APPROVED    = 'payment-approved';
    const ORDER_STATUS_TALENT_REJECTED     = 'talent-rejected';
    const ORDER_STATUS_TALENT_ACCEPTED     = 'talent-accepted';
    const ORDER_STATUS_ORDER_EXPIRED       = 'order-expired';

    public $order_statuses = [];
    
    public function __construct( $static = false ) 
    {
        $this->order_statuses = array(
            'wc-payment-in-revision' => array(
                'label'                     => __( 'Aguardando confirmação do pagamento', 'polen' ),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Aguardando confirmação do pagamento <span class="count">(%s)</span>', 'Aguardando confirmação do pagamento <span class="count">(%s)</span>', 'polen' ),
            ),
            'wc-payment-rejected' => array(
                'label'                     => __( 'Pagamento rejeitado', 'polen' ),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Pagamento rejeitado <span class="count">(%s)</span>', 'Pagamento rejeitado <span class="count">(%s)</span>', 'polen' ),
            ),
            'wc-payment-approved' => array(
                'label'                     => __( 'Pagamento aprovado', 'polen' ),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Pagamento aprovado <span class="count">(%s)</span>', 'Pagamento aprovado <span class="count">(%s)</span>', 'polen' ),
            ),
            'wc-talent-rejected' => array(
                'label'                     => __( 'O talento não aceitou', 'polen' ),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'O talento não aceitou <span class="count">(%s)</span>', 'O talento não aceitou <span class="count">(%s)</span>', 'polen' ),
            ),
            'wc-talent-accepted' => array(
                'label'                     => __( 'O talento aceitou', 'polen' ),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'O talento aceitou <span class="count">(%s)</span>', 'O talento aceitou <span class="count">(%s)</span>', 'polen' ),
            ),
            'wc-order-expired' => array(
                'label'                     => __( 'Pedido expirado', 'polen' ),
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Pedido expirado <span class="count">(%s)</span>', 'Pedido expirado <span class="count">(%s)</span>', 'polen' ),
            ),
        );

        if( $static ) {
            add_action( 'init', array( $this, 'register_custom_order_statuses' ) );
            add_filter( 'wc_order_statuses', array( $this, 'add_custom_order_statuses' ) );
            add_filter( 'bulk_actions-edit-shop_order', array( $this, 'dropdown_bulk_actions_shop_order' ), 20, 1 );
            add_action( 'woocommerce_checkout_create_order', array( $this, 'order_meta' ), 12, 2 );
            add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
            add_action( 'admin_head', array( $this, 'remove_metaboxes' ) );

            add_action( 'init', function( $array ) {
                foreach ( $this->order_statuses as $order_status => $values ) 
                {
                    $action_hook = 'woocommerce_order_status_' . $order_status;
                    add_action( $action_hook, array( WC(), 'send_transactional_email' ), 10, 1 );
                    $action_hook_notification = 'woocommerce_order_' . $order_status . '_notification';
                    add_action( $action_hook_notification, array( WC(), 'send_transactional_email' ), 10, 1 );
                }
            } );

            add_filter( 'woocommerce_product_data_tabs', array( $this, 'charity_tab' ) );
            // add_filter( 'woocommerce_product_data_tabs', array( $this, 'promotional_event' ) );
            // add_filter( 'woocommerce_product_data_tabs', array( $this, 'social_base_event' ) );
            
            add_filter( 'woocommerce_product_data_panels', array( $this, 'charity_product_data_product_tab_content' ) );
            // add_filter( 'woocommerce_product_data_panels', array( $this, 'promotional_event_product_data_product_tab_content' ) );
            // add_filter( 'woocommerce_product_data_panels', array( $this, 'social_base_product_data_product_tab_content' ) );

            add_action( 'woocommerce_update_product', array( $this, 'on_product_save' ) );

            add_action( 'save_post_post_polen_media', array( $this, 'save_metabox_post' ) );
            add_action( 'save_post', array($this, 'seo_save_postdata') );
            // add_action( 'save_post', array( $this, 'change_status' ) );

            //Todas as compras gratis vão para o status payment-approved
            add_action( 'woocommerce_checkout_no_payment_needed_redirect', [ $this, 'set_free_order_payment_approved' ], 10, 3 );
        }
    }

    /**
     * Salvar os inputs customizados do CPT
     *
     * @param $post_id
     * @return mixed|void
     */
    public function save_metabox_post($post_id)
    {
        if (!current_user_can( 'edit_post', $post_id )) {
            return $post_id;
        }

        update_post_meta($post_id, 'url_media', sanitize_text_field($_POST['url_media']));
        update_post_meta($post_id, 'date_media', sanitize_text_field($_POST['date_media']));
    }

    /**
     * Salvar status de envio do email na atualização da order
     * para ser verificado no disparo do email
     */
    // public function change_status($post_id)
    // {
    //     if (!current_user_can( 'edit_page', $post_id )) {
    //         return $post_id;
    //     }

    //     if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
    //         return $post_id;
    //     }

    //     $meta_value = 0;
    //     if (isset($_POST['send_email']) && $_POST['send_email'] == 'on') {
    //         $meta_value = 1;
    //     }

    //     update_post_meta($post_id, 'send_email', $meta_value);
    // }


    /**
     * Colocar os status de uma order gratis como pagamento aprovado
     */
    public function set_free_order_payment_approved( $order_received_url, $order )
    {
        $order->set_status('payment-approved');
        $order->save();
        return $order_received_url;
    }


    public function register_custom_order_statuses() 
    {
        foreach ( $this->order_statuses as $order_status => $values ) 
        {
			register_post_status( $order_status, $values );
		}
    }

    public function add_custom_order_statuses( $order_statuses )
    {
        foreach ( $this->order_statuses as $order_status => $values ) 
        {
			$order_statuses[ $order_status ] = $values[ 'label' ];
		}
        return $order_statuses;
    }

    function dropdown_bulk_actions_shop_order( $actions ) 
    {
        foreach ( $this->order_statuses as $order_status => $values ) 
        {
			$actions[ $order_status ] = $values[ 'label' ];
		}
        return $actions;
    }

    public function order_meta( $order, $data ) 
    {
        $items = WC()->cart->get_cart();
        $key = array_key_first( $items );
        $billing_email = $items[ $key ][ 'email_to_video' ];
        if ( $billing_email && ! is_null( $billing_email ) && ! empty( $billing_email ) ) 
        {
            $order->update_meta_data( '_polen_customer_email', $billing_email );
            $order->update_meta_data( '_billing_email', $billing_email );
        }
    }

    public function remove_metaboxes() 
    {
        global $current_screen;
        if( $current_screen && ! is_null( $current_screen ) && isset( $current_screen->id ) && $current_screen->id == 'shop_order' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) 
        {
            remove_meta_box( 'woocommerce-order-items', 'shop_order', 'normal', 'high' );
        }

        remove_meta_box( 'postcustom', 'shop_order', 'normal', 'high' );
        remove_meta_box( 'woocommerce-order-downloads', 'shop_order', 'normal', 'high' );
        remove_meta_box( 'pageparentdiv', 'shop_order', 'side', 'high' );
    }

    public function add_metaboxes() {
        global $current_screen;


        add_meta_box( 'Polen_Post_Media', 'Configurações Gerais', array( $this, 'metabox_polen_media' ), 'post_polen_media', 'normal', 'low' );


        if( $current_screen && ! is_null( $current_screen ) && isset( $current_screen->id ) && $current_screen->id == 'shop_order' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' )
        {
            add_meta_box( 'Polen_Order_Details', 'Instruções', array( $this, 'metabox_order_details' ), 'shop_order', 'normal', 'low' );
            add_meta_box( 'Polen_Order_Details_Video_Info', 'Info do Video', array( $this, 'metabox_order_details_video_info' ), 'shop_order', 'normal', 'low' );
            add_meta_box( 'Polen_Refund_Order_tuna', 'Reembolsar pedido', array( $this, 'metabox_create_refund_order_tuna' ), 'shop_order', 'side', 'default' );
        }

        if( $current_screen && ! is_null( $current_screen ) && isset( $current_screen->id ) && $current_screen->id == 'product' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' )  {
            global $post;
            $product_id = $post->ID;
            add_meta_box( 'Polen_Product_First_Order', 'Primeira Order', array( $this, 'metabox_create_first_order' ), 'product', 'side', 'default' );
            add_meta_box( 'Polen_Product_SEO', 'SEO', array( $this, 'metabox_SEO' ), 'product', 'normal', 'default' );
        }
    }

    public function metabox_order_details() {
        global $post;
        $order_id = $post->ID;
        if( file_exists( TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-order-details.php' ) ) {
            require_once TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-order-details.php';
        } else {
            require_once PLUGIN_POLEN_DIR . '/admin/partials/metaboxes/metabox-order-details.php';
        }
    }

    public function metabox_order_details_video_info() {
        global $post;
        $order_id = $post->ID;
        if( file_exists( TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-video-info.php' ) ) {
            require_once TEMPLATEPATH . '/woocommerce/admin/metaboxes/metabox-video-info.php';
        } else {
            require_once PLUGIN_POLEN_DIR . '/admin/partials/metaboxes/metabox-video-info.php';
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

    public function seo_save_postdata( $post_id ) {
        if ( array_key_exists( 'meta-seo-title', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_seo_title',
                sanitize_text_field($_POST['meta-seo-title'])
            );
        }
        if ( array_key_exists( 'meta-seo-description', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_seo_description',
                sanitize_text_field($_POST['meta-seo-description'])
            );
        }
    }

    public function get_order_items( $order_id ) 
    {
        global $wpdb;

        $sql_items = "SELECT `order_item_id`, `order_item_name` FROM `" . $wpdb->base_prefix . "woocommerce_order_items` WHERE `order_id`=" . $order_id . " AND `order_item_type`='line_item'";
        $res_items = $wpdb->get_results( $sql_items );

        if( $res_items && ! is_null( $res_items ) && ! is_wp_error( $res_items ) && is_array( $res_items ) && ! empty( $res_items ) ) 
        {
            $items = array();

            $meta_labels = array(
                'offered_by'            => 'Oferecido por', 
                'video_to'              => 'Vídeo para', 
                'name_to_video'         => 'Quem vai receber?', 
                'email_to_video'        => 'E-mail',
                'video_category'        => 'Ocasião', 
                'instructions_to_video' => 'Instruções do vídeo', 
                'allow_video_on_page'   => 'Publico?',
            );
            $order = wc_get_order( $order_id );
            if( !empty( $order ) && "talent-rejected" == $order->get_status() ) {
                $meta_labels[ 'reason_reject' ] = 'Rejeitou por';
                $meta_labels[ 'reason_reject_description' ] = 'Explicação';
            }

            foreach( $res_items as $k => $item ) 
            {
                $sql = "SELECT `meta_key`, `meta_value` FROM `" . $wpdb->base_prefix . "woocommerce_order_itemmeta` WHERE `order_item_id`=" . $item->order_item_id . " AND `meta_key` IN ( 'offered_by', 'video_to', 'name_to_video', 'email_to_video', 'video_category', 'instructions_to_video', 'allow_video_on_page', 'reason_reject', 'reason_reject_description' )";
                $res = $wpdb->get_results( $sql );
                
                $args = array(
                    'id'   => $item->order_item_id,
                    'Talento' => $item->order_item_name,
                );

                if( $res && ! is_null( $res ) && ! is_wp_error( $res ) && is_array( $res ) && ! empty( $res ) ) 
                {
                    foreach( $res as $l => $meta ) {
                        $meta_key   = $meta->meta_key;
                        $meta_value = $meta->meta_value;
                        if( $meta_key == 'allow_video_on_page' ) {
                            $meta_value = ( $meta->meta_value == 'on' ) ? 'Sim' : 'Não';
                        }
                        $args[ $meta_labels[ $meta_key ] ] = $meta_value;
                    }
                }

                $items[] = $args;
            }

            return $items;
        }
    }

    public function charity_tab( $array ){
        $array['charity'] = array(
            'label'    => 'Caridade',
            'target'   => 'charity_product_data',
            'class'    => array(),
            'priority' => 90,
        );
        return $array;
    }

    public function charity_product_data_product_tab_content() {
        global $product_object;
    ?>
        <div id="charity_product_data" class="panel woocommerce_options_panel hidden">
            <div class='options_group'>
            <?php
                woocommerce_wp_checkbox(
                    array(
                        'id'      => '_is_charity',
                        'value'   => $product_object->get_meta( '_is_charity' ) == 'yes' ? 'yes' : 'no',
                        'label'   => 'Para Caridade',
                        'cbvalue' => 'yes',
                    )
                );
            ?>
            </div>
        
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'                => '_charity_name',
                        'value'             => $product_object->get_meta( '_charity_name' ),
                        'label'             => 'Charity Name',
                        'desc_tip'          => true,
                        'description'       => 'Nome da instituição de Caridade',
                        'type'              => 'text',
                    )
                );
                ?>
            </div>
        
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'                => '_url_charity_logo',
                        'value'             => $product_object->get_meta( '_url_charity_logo' ),
                        'label'             => 'Logo da instituição',
                        'desc_tip'          => true,
                        'description'       => 'Logo da instituição de Caridade',
                        'type'              => 'text',
                    )
                );
                ?>
            </div>
        
            <div class="options_group">
                <?php
                woocommerce_wp_textarea_input(
                    array(
                        'id'          => '_description_charity',
                        'value'       => $product_object->get_meta( '_description_charity' ),
                        'label'       => 'Descrição da instituição',
                        'desc_tip'    => true,
                        'description' => 'Descrição da instituição de caridade.',
                    )
                );
                ?>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => '_charity_subordinate_merchant_id',
                        'value'       => $product_object->get_meta( '_charity_subordinate_merchant_id' ),
                        'label'       => 'Subordinate Merchant ID',
                        'desc_tip'    => true,
                        'description' => 'Código Braspag para a instituição de caridade.',
                        'type'        => 'text',
                    )
                );
                ?>
            </div>
        </div>
    <?php
    }

    public function promotional_event_product_data_product_tab_content(){}

    public function on_product_save( $product_id ) {
        if( is_admin() ){
            $screen = get_current_screen();
            if ( $screen->base == 'post' && $screen->post_type == 'product' ){            
                $product                = wc_get_product( $product_id );
                $charity                = strip_tags(filter_input(INPUT_POST, '_is_charity'));
                $charity_name           = strip_tags(filter_input(INPUT_POST, '_charity_name'));
                $charity_url            = strip_tags(filter_input(INPUT_POST, '_url_charity_logo'));
                $charity_description    = strip_tags(filter_input(INPUT_POST, '_description_charity'));
                $charity_subordinate_id = strip_tags(filter_input(INPUT_POST, '_charity_subordinate_merchant_id'));

                $product->update_meta_data( '_is_charity', $charity );
                $product->update_meta_data( '_charity_name', $charity_name );
                $product->update_meta_data( '_url_charity_logo', $charity_url );
                $product->update_meta_data( '_description_charity', $charity_description );
                $product->update_meta_data( '_charity_subordinate_merchant_id', $charity_subordinate_id );

                do_action( Polen_Admin_Social_Base_Product_Fields::ACTION_NAME    , $product_id );
                do_action( Polen_Admin_B2B_Product_Fields::ACTION_NAME            , $product_id );
                do_action( Polen_Admin_Event_Promotional_Event_Fields::ACTION_NAME, $product_id );
                  
                remove_action( 'woocommerce_update_product', array( $this, 'on_product_save' ) );
                $product->save();
                add_action( 'woocommerce_update_product', array( $this, 'on_product_save' ) );
            }
        }  
    }

    private function save_meta( &$product, $value, $key )
    {
        if( !empty( $value ) ) {
            $product->update_meta_data( $key, $value );
        } else {
            $product->delete_meta_data( $key );
        }
    }
}