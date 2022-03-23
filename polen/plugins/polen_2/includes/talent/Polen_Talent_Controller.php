<?php

namespace Polen\Includes\Talent;

use Exception;
use Polen\Includes\{Polen_Talent, Polen_Order, Debug};
use WC_Order;
use Vimeo\Vimeo;
use Vimeo\Exceptions\{VimeoRequestException, ExceptionInterface};

use Polen\Includes\Polen_Video_Info;
use Polen\Includes\Vimeo\{Polen_Vimeo_Factory, Polen_Vimeo_Response, Polen_Vimeo_Vimeo_Options};
use Polen\Includes\Cart\{Polen_Cart_Item_Factory, Polen_Cart_Item};
use Polen\Includes\Module\Polen_Order_Module;
use WC_Emails;
use WP_REST_Request;

class Polen_Talent_Controller extends Polen_Talent_Controller_Base
{
    public function login()
    {
        
        $username = sanitize_email( filter_input( INPUT_POST, 'login' ) );
        $password = trim( filter_input( INPUT_POST, 'password' ) );

        $auth_result = wp_authenticate( $username, $password );
        if( is_wp_error( $auth_result ) ) {
            wp_send_json_error( null, 401 );
        }
        
        //TODO: Verificar se a role é talent
        
        $response = array(
            'user_name' => '',
            'user_email' => '',
            'display_name' => ''
        );
        echo wp_send_json( $auth_result );
    }
    
    public function get_total_a_receber()
    {
        
    }
     
    /**
     * O Talento aceita ou rejeita um pedido de video
     *
     */
    public function talent_accept_or_reject(){
        $response = array();
        if( !isset( $_POST['security'] ) || !wp_verify_nonce( $_POST['security'], 'polen-order-accept-nonce' ) ) {
        //    $response = array( 'success' => false, 'message' => 'nonce_fail' );
            wp_send_json_error( 'nonce_fail', 403 );
            wp_die();
        }
   
        if( !isset( $_POST['order'] ) ) {
            // $response = array( 'success' => false, 'message' => 'order_fail' );
            wp_send_json_error( 'order_fail', 403 );
            wp_die();
        }

        if( !isset( $_POST['type'] ) || ( trim( $_POST['type']) != 'accept' && trim( $_POST['type']) != 'reject' ) ){
            // $response = array( 'success' => false, 'message' => 'type_fail' );
            wp_send_json_error( 'type_fail', 403 );
            wp_die();
        }

       global $wpdb;

       require_once ABSPATH . '/wp-includes/pluggable.php';
       $talent_id = get_current_user_id();
       $order_id = trim($_POST['order']); 
       $type = strip_tags( $_POST['type'] );

       if( empty( $order_id ) ) {
        //    wp_send_json_error( 'Erro na order', 403 );
           wp_send_json_error( 'Sem order_id', 403 );
           wp_die();
       }

       $sql_product = " SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'product' and post_author = ".$talent_id;
       $talent_products = $wpdb->get_results( $sql_product );

       
       if( is_countable( $talent_products ) && count( $talent_products ) > 0 ){
           $first_product = reset($talent_products);

           if( is_object( $first_product ) && isset( $first_product->ID ) ){
               $sql = " SELECT order_items.order_id
                   FROM {$wpdb->prefix}woocommerce_order_items as order_items
                   LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
                   LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
                   WHERE posts.post_type = 'shop_order'
                       AND order_items.order_id = ".$order_id."
                       AND order_items.order_item_type = 'line_item'
                       AND order_item_meta.meta_key = '_product_id'
                       AND order_item_meta.meta_value = '$first_product->ID'";
               $order_list = $wpdb->get_results( $sql );

               if( is_countable( $order_list ) && count( $order_list ) == 0 ){
                //    $response = array( 'success' => false, 'data' => 'Pedido não é desse talento' );
                   wp_send_json_error( 'Pedido não é desse talento', 403 );
                   wp_die();   
               }else{
                // Debug::def( $_POST);die;
                   $order = wc_get_order( $order_id );
                   WC_Emails::instance();
                   if($order){
                       if( $type == 'accept' ){
                           $order->update_status( Polen_Order::ORDER_STATUS_TALENT_ACCEPTED );
                           $response = 'Vídeo aceito com sucesso';
                           $response = [ "code" => "1", "" ]; 
                       }                            
                       if( $type == 'reject' ){
                           $reason = $_POST[ 'reason' ];
                           $description = $_POST[ 'description' ];
                           $item_cart = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
                           $item_cart->add_meta_data( 'reason_reject', $reason, true );
                           $item_cart->add_meta_data( 'reason_reject_description', $description, true );
                           $order->update_status( 'talent-rejected', "Motivo: {$reason}", true );
                           $order->add_order_note( "Motivo: {$reason}. Descricao: {$description}" );
                           $response = [ "code" => "2" ]; 
                       }  
                      
                   }
               }
           }else{
            //    $response = array( 'success' => false, 'message' => '' );  
               wp_send_json_error( 'Talento sem produto', 403 );
               wp_die();   
           }
           
       }

       wp_send_json_success( $response );
       wp_die();
    }

   public function get_data_description(){
       $response = array();

       if( !isset( $_POST['security'] ) || !wp_verify_nonce( $_POST['security'], 'polen-order-data-nonce' ) ) {
           $response = array( 'success' => false, 'message' => 'nonce_fail' );     
       }
   
       if( !isset( $_POST['order'] ) ) {
           $response = array( 'success' => false, 'message' => 'order_fail' );     
       }

       global $wpdb;
       $polen_talent = new Polen_Talent();

       require_once ABSPATH . '/wp-includes/pluggable.php';
       $talent_id = get_current_user_id();
       $order_id = trim($_POST['order']); 
       $logged_user = new \WP_User( $talent_id );

       $sql_product = " SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'product' and post_author = " . $talent_id;
       $talent_products = $wpdb->get_results($sql_product);

       if (is_countable($talent_products) && count($talent_products) > 0) {
           $first_product = reset($talent_products);

           if (is_object($first_product) && isset($first_product->ID)) {
               $sql = " SELECT order_items.order_id
               FROM {$wpdb->prefix}woocommerce_order_items as order_items
               LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
               LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
               WHERE posts.post_type = 'shop_order'
                   AND posts.post_status IN ( 'wc-payment-approved' )
                   AND order_items.order_item_type = 'line_item'
                   AND order_item_meta.meta_key = '_product_id'
                   AND order_item_meta.meta_value = '$first_product->ID'
                   AND order_items.order_id = ".$order_id;
               $order_list = $wpdb->get_results($sql);
                
               if (is_countable($order_list) && count($order_list) == 0) {
                   $response = array( 'success' => false, 'message' => 'Itens nao encontrados', 'data' => 0 );     
               } else {
                   $obj = array();
                   $robj = array();
                   foreach ($order_list as $obj_order):
                       $obj['order_id'] = $obj_order->order_id;
                       $obj['expiration'] = $polen_talent->video_expiration_time($logged_user, $obj['order_id']); 
                       $order = wc_get_order($obj_order->order_id);

                       $obj['total'] = $order->get_formatted_order_total();
                       foreach ($order->get_items() as $item_id => $item) {
                           $obj['email'] = $item->get_meta('email_to_video', true);
                           $obj['instructions'] = $item->get_meta('instructions_to_video', true);
                           $obj['name'] = $item->get_meta('name_to_video', true);
                           $obj['from'] = $item->get_meta('offered_by', true);
                           $obj['category'] = $item->get_meta('video_category', true);
                       }

                       $robj[] = $obj;
                   endforeach;

                   $response = array( 'success' => true, 'message' => 'Dados obtidos com sucesso', 'data' => $robj );     
               }
           }
       }

       echo wp_json_encode( $response );
       wp_die();
   }
    
    /**
     * Handler para o AJAX onde é executado quando o Talento, seleciona um video e
     * envia, antes do envio é criado no Vimeo um Slot para receber o Video com o 
     * mesmo tamanho em bytes
     * 
     * @global type $Polen_Plugin_Settings
     * @throws VimeoRequestException
     */
    public function make_video_slot_vimeo()
    {
        global $Polen_Plugin_Settings;

        $client_id = $Polen_Plugin_Settings['polen_vimeo_client_id'];
        $client_secret = $Polen_Plugin_Settings['polen_vimeo_client_secret'];
        $token = $Polen_Plugin_Settings['polen_vimeo_access_token'];
        
        $lib = new Vimeo( $client_id, $client_secret, $token );
        
        $order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
        $file_size = filter_input( INPUT_POST, 'file_size', FILTER_SANITIZE_NUMBER_INT );
        // $name_to_video = filter_input( INPUT_POST, 'name_to_video' );
        
        try {
            $polen_order = new Polen_Order_Module(wc_get_order($order_id));
            $args = Polen_Vimeo_Vimeo_Options::get_option_insert_video( $file_size, $polen_order->get_name_to_video() . " #{$order_id}");
            $vimeo_response = $lib->request('/me/videos', $args, 'POST');
            
            $response = new Polen_Vimeo_Response( $vimeo_response );
            
            if( $response->is_error() ) {
                throw new VimeoRequestException( $response->get_developer_message(), 500 );
            }
            
            // $order = wc_get_order( $order_id );
            // $cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
            
            // $video_info = $this->mount_video_info( $order, $cart_item, $response);
            // $video_info->insert();
            
            //recalcula o tempo de resposta do talento
            // $this->average_video_response( get_current_user_id() );

            wp_send_json_success( $response->response, 200 );
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
        }
        wp_die();
    }


    public function handler_send_vimeo_complete()
    {
        $vimeo_id = filter_input( INPUT_POST, 'vimeo_id' );
        $order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
        try {
            wp_send_json_success($this->send_video_vimeo_complete($vimeo_id, $order_id));
        } catch(Exception $e) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
        }
        wp_die();
    }


    public function send_video_vimeo_complete($vimeo_id, $order_id)
    {
        $order = wc_get_order( $order_id );
        $cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
        
        $lib = Polen_Vimeo_Factory::create_vimeo_instance_with_redux();
        $vimeo_response = $lib->request($vimeo_id);
        $response = new Polen_Vimeo_Response( $vimeo_response );
        if($response->is_error()) {
            throw new Exception('Erro com o processamento do Vimeo', 500);
        }
        $video_info = Polen_Video_Info::get_by_order_id($order_id);
        if(!empty($video_info)) {
            $video_info->delete();
        }

        $video_info = $this->mount_video_info( $order, $cart_item, $response);
        $video_info->insert();
        
        // recalcula o tempo de resposta do talento
        $this->average_video_response( get_current_user_id() );
        return true;
    }
    
    
    /**
     * Criando um Polen_Video_Info para salvar a primeira vez no Banco as info
     * quando o Vimeo recebo o REQUEST do slot
     * @param type $order
     * @param type $cart_item
     * @param type $response
     * @return Polen_Video_Info
     */
    public function mount_video_info(
        WC_Order $order,
        Polen_Cart_Item $cart_item,
        Polen_Vimeo_Response $response,
        string $video_logo_status = Polen_Video_Info::VIDEO_LOGO_STATUS_WAITING )
    {
            $order_polen = new Polen_Order_Module($order);
            $video_info = new Polen_Video_Info();
            $video_info->is_public = $cart_item->get_public_in_detail_page();
            $video_info->order_id = $order_polen->get_id();
            $video_info->talent_id = get_current_user_id();
            $video_info->vimeo_id = $response->get_vimeo_id();
            $video_info->vimeo_process_complete = 0;
            $video_info->vimeo_link = $response->get_vimeo_link();
            $video_info->first_order = $cart_item->get_first_order();
            $video_info->vimeo_url_download = 'get_in_time';//$response->get_download_source_url();
            $video_info->vimeo_iframe = $response->get_iframe();
            $video_info->video_logo_status = $video_logo_status;
            return $video_info;
    }

    /**
     * O pedido estará como completo
     */
    public function talent_order_completed() {
        if( !isset( $_POST['order'] ) ) {
            wp_send_json_error(array( 'success' => false, 'message' => 'order_fail'), 500);
            wp_die();
        }

        require_once ABSPATH . '/wp-includes/pluggable.php';
        $talent_id = get_current_user_id();
        $order_id = filter_input( INPUT_POST, 'order', FILTER_SANITIZE_NUMBER_INT );
        
        $checked = $this->check_product_and_order( $talent_id, $order_id );
        if( $checked ){
            $vimeo_id = filter_input( INPUT_POST, 'vimeo_id' );
            try {
                $order = wc_get_order($order_id);
                $polen_order = new Polen_Order_Module($order);
                if($polen_order->get_is_first_order()) {
                    Polen_Talent::set_first_order_status_by_talent_id($talent_id, true);
                }
                $this->send_video_vimeo_complete($vimeo_id, $order_id);
                wp_send_json_success(array( 'success' => true, 'message' => 'Pedido completo!' ));
            } catch(Exception $e) {
                wp_send_json_error(array( 'success' => false, 'message' => 'No vimeo' ), 500);
            }
        }else{
            wp_send_json_error(array( 'success' => false, 'message' => 'Falha na relação talento/produto' ), 500);
        }
        wp_die();
    }

    /**
     * Verifica produto, pedido e talento
     */
    public function check_product_and_order( $talent_id, $order_id ){
        if( !$talent_id || !$order_id ){
            return false;
        }

        global $wpdb;
        $sql_product = " SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'product' and post_author = ".$talent_id;
        $talent_products = $wpdb->get_results( $sql_product );
        
        if( is_countable( $talent_products ) && count( $talent_products ) > 0 ){
            $first_product = reset($talent_products);
 
            if( is_object( $first_product ) && isset( $first_product->ID ) ){
                $sql = " SELECT order_items.order_id
                    FROM {$wpdb->prefix}woocommerce_order_items as order_items
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
                    LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
                    WHERE posts.post_type = 'shop_order'
                        AND order_items.order_id = ".$order_id."
                        AND order_items.order_item_type = 'line_item'
                        AND order_item_meta.meta_key = '_product_id'
                        AND order_item_meta.meta_value = '$first_product->ID'";
                $order_list = $wpdb->get_results( $sql );
 
                if( is_countable( $order_list ) && count( $order_list ) == 0 ){
                    return false;
                }else{
                    return true;
                }
            }else{
                return false;
            }
        }

        return false;
    }

    /**
     * Recalcula o tempo médio que o talento atende o vídeo
     */
    public function average_video_response( $talent_id ){
        $polen_talent = new Polen_Talent();
        return $polen_talent->average_video_response($talent_id);
    }


    /**
     * O Talento aceita ou rejeita um pedido de video
     *
     */
    public function talent_accept_or_reject_api( $type, $order_id, $reason_reject = '', $description_reject = '' )
    {
        $response = array();
   
        if( empty( $order_id ) ) {
            throw new Exception( 'Order nao pode ser valor nulo', 401  );
        }

        if( empty( $type ) || ( trim( $type ) != 'accept' && trim( $type ) != 'reject' ) ){
            throw new Exception( 'Opcoes tem que ser Aceitar ou Negar', 401  );
        }

        global $wpdb;

        require_once ABSPATH . '/wp-includes/pluggable.php';
        $talent_id = get_current_user_id();
        if( empty( $talent_id ) ) {
            throw new Exception( 'Usuário não está logado', 403  );
        }
        $order_id = trim( $order_id ); 
        $type = strip_tags( $type );

        if( empty( $order_id ) ) {
            throw new Exception( 'Order nao pode ser valor nulo', 401  );
        }

        $sql_product = " SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'product' and post_author = {$talent_id};";
        $talent_products = $wpdb->get_results( $sql_product );

        $talent_products_ids_txt = ''; 
        if( !empty( $talent_products ) ) {
            foreach( $talent_products as $talent_product ) {
                $talent_products_ids[] = $talent_product->ID;
                $talent_products_ids_txt = implode( ',', $talent_products_ids );
            }
        }

        if( is_countable( $talent_products ) && count( $talent_products ) > 0 ){
            $first_product = reset($talent_products);


            if( is_object( $first_product ) && isset( $first_product->ID ) ){
                $sql = " SELECT order_items.order_id
                    FROM {$wpdb->prefix}woocommerce_order_items as order_items
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
                    LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
                    WHERE posts.post_type = 'shop_order'
                        AND order_items.order_id = ".$order_id."
                        AND order_items.order_item_type = 'line_item'
                        AND order_item_meta.meta_key = '_product_id'
                        AND order_item_meta.meta_value IN ( {$talent_products_ids_txt})";
                $order_list = $wpdb->get_results( $sql );

                if( is_countable( $order_list ) && count( $order_list ) == 0 ){
                    throw new Exception( 'Order não é desse usuário', 403 );  
                } else {
                    $order = wc_get_order( $order_id );
                    WC_Emails::instance();
                    if( $order ){
                        if( $type == 'accept' ){
                            $order->update_status( Polen_Order::ORDER_STATUS_TALENT_ACCEPTED, 'Talento Aceitou fazer o pedido' );
                            $response = [ "code" => "1", "" ]; 
                        }
                        if( $type == 'reject' ) {
                            if(empty($reason_reject)) {
                                throw new Exception('Razão para negar o pedido nao pode ser nulo', 403);
                            }
                            $item_cart = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
                            $item_cart->add_meta_data( 'reason_reject', $reason_reject, true );
                            $item_cart->add_meta_data( 'reason_reject_description', $description_reject, true );
                            $order->update_status( 'talent-rejected', "Motivo: {$reason_reject}" );
                            $order->add_order_note( "Motivo: {$reason_reject}. Descricao: {$description_reject}" );
                            $response = [ "code" => "2" ];
                        }
                    }
                }
            } else {
                throw new Exception( 'Usuário sem pedidos', 403 );
            }
            
        }

        return $response;
    }
}
