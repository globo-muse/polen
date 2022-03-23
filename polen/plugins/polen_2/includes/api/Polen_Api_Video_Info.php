<?php

namespace Polen\Includes\API;

use Exception;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Video_Info;
use Polen\Includes\Vimeo\Polen_Vimeo_Factory;
use Polen\Includes\Vimeo\Polen_Vimeo_Response;
use Polen\Social_Base\Social_Base;
use Polen\Social_Base\Social_Base_Order;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Polen_Api_Video_Info
{
    private $namespace;
    private $resource_name;
    private $schema;

    public function __construct()
    {
        $this->namespace = 'polen/v1';
        $this->resource_name = 'video-infos';
    }

    
    public function register_routes()
    {
        register_rest_route( $this->namespace, '/' . $this->resource_name, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' )
            ),
            'schema' => array( $this, 'get_item_schema' )
        ) );

        register_rest_route( $this->namespace, '/' . $this->resource_name . '/hash/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_item_by_hash' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' )
            ),
            'schema' => array( $this, 'get_item_schema' )
        ) );

        register_rest_route( $this->namespace, '/' . $this->resource_name . '/order/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_item_by_order' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' )
            ),
            'schema' => array( $this, 'get_item_schema' )
        ) );

        register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)/video-logo-status', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( $this, 'update_video_logo_status_item' ),
                'permission_callback' => array( $this, 'update_video_logo_status_permissions_check' )
            ),
            'schema' => array( $this, 'get_item_schema' )
        ) );
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/hash/(?P<hash>[\d]+)/update_thumbnail', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( $this, 'update_thumb_video_by_hash' ),
                'permission_callback' => array( $this, 'update_video_logo_status_permissions_check' )
            ),
            'schema' => array( $this, 'get_item_schema' )
        ) );
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/hashs/update_thumbnail', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( $this, 'update_thumb_videos_by_hashs' ),
                'permission_callback' => array( $this, 'update_video_logo_status_permissions_check' )
            ),
            'schema' => array( $this, 'get_item_schema' )
        ) );

        register_rest_route( $this->namespace, '/' . $this->resource_name . '/video-logo-status/waiting', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_item_by_video_logo_waiting' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' )
            ),
            'schema' => array( $this, 'get_item_schema' )
        ) );
    }


    /**
     * 
     * @param WP_REST_Request
     * @param bool
     */
    public function get_items_permissions_check( $request )
    {
        $user = new Polen_Api_User( wp_get_current_user() );
        if( !$user->check_permission( 'get_items_permissions_check' ) ) {
            return new WP_Error( 'rest_forbidden', 'Você não tem acesso a esse endpoint', array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }


    /**
     * 
     * @param WP_REST_Request
     */
    public function update_video_logo_status_permissions_check( $request )
    {
        $user = new Polen_Api_User( wp_get_current_user() );
        if( !$user->check_permission( 'update_video_logo_status_permissions_check' ) ) {
            return new WP_Error( 'rest_forbidden', 'Você não tem acesso a esse endpoint', array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }


    /**
     * 
     * @param \WP_REST_Request
     */
    public function get_items( $request )
    {
        $videos_infos = Polen_Video_Info::select_all_videos_incompleted();
        
        $data = array();

        if( empty( $videos_infos ) ) {
            return rest_ensure_response( $videos_infos );
        }

        foreach( $videos_infos as $video_info ) {
            $response = $this->prepare_item_for_response( $video_info, $request );
            $data[] = $response;
        }
        
        return rest_ensure_response( $data );
    }


    /**
     * 
     * @param WP_REST_Request
     */
    public function get_item_by_video_logo_waiting( $request )
    {
        $videos_infos = Polen_Video_Info::select_by_video_logo_waiting();
        
        $data = array();

        if( empty( $videos_infos ) ) {
            return rest_ensure_response( $videos_infos );
        }

        foreach( $videos_infos as $video_info ) {
            $order = wc_get_order( $video_info->order_id );
            $response = $this->prepare_item_for_response( $video_info, $request );
            if( !empty( $order ) ) {
                if( Social_Base_Order::is_social( $order ) ) {
                    $response[ Social_Base_Order::ORDER_META_KEY_campaign ] = $order->get_meta( Social_Base_Order::ORDER_META_KEY_campaign );
                }
            }
            $data[] = $response;
        }
        
        return rest_ensure_response( $data );
    }

    /**
     * 
     * @param WP_REST_Request
     * @return WP_Error
     * @return WP_REST_Response
     */
    public function update_video_logo_status_item( $request )
    {
        $video_info_id = isset( $request[ 'ID' ] ) ? $request[ 'ID' ] : $request[ 'id' ];
        $status = $request->get_param( 'video_logo_status' );

        if( empty( $video_info_id ) ) {
            return new WP_Error( 'rest_forbidden', 'ID inválido', array( 'status' => $this->authorization_status_code() ) );
        }
        $statuses_possible = $this->get_status_possible();
        if( !in_array( $status, $statuses_possible ) ) {
            return new WP_Error( 'rest_forbidden', 'Status [video_logo_status] inválido', array( 'status' => $this->authorization_status_code() ) );
        }
        $video_info = Polen_Video_Info::get_by_id_static( $video_info_id );
        if( empty( $video_info ) ){
            return new WP_Error( 'rest_404', '', array( 'status' => 404 ) );
        }
        $video_info->video_logo_status = $status;
        $video_info->update();

        $data = $this->prepare_item_for_response( 
            Polen_Video_Info::get_by_id_static( $video_info_id ),
            $request
        );

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * 
     */
    public function get_status_possible()
    {
        return [
            Polen_Video_Info::VIDEO_LOGO_STATUS_COMPLETED,
            Polen_Video_Info::VIDEO_LOGO_STATUS_NO,
            Polen_Video_Info::VIDEO_LOGO_STATUS_SENDED,
            Polen_Video_Info::VIDEO_LOGO_STATUS_WAITING,
        ];
    }


    /**
     * 
     * @param \WP_REST_Request
     */
    public function get_item_by_hash( $request )
    {
        $hash = (int) $request['id'];
        $video_info = Polen_Video_Info::get_by_hash( $hash );

        if( empty( $video_info ) ) {
            return new WP_REST_Response( $video_info, 404 );
        }
        
        $data = $this->prepare_item_for_response( $video_info, $request );

        return rest_ensure_response( $data );
    }

    /**
     * Handler API que faz update da Thumb no Video Info
     */
    public function update_thumb_video_by_hash( $request )
    {
        $hash_vimeo = $request[ 'hash' ];
        try {
            $this->update_thumb_videoinfo_by_hash( $hash_vimeo );
            return api_response( 'atualizado com sucesso', 200 );
        } catch( Exception $e ) {
            return api_response( $e->getMessage(), $e->getCode() );
        }
    }

    /**
     * Handler API que faz update da Thumb no Video Info em lote
     * @param WP_REST_Request
     * @return WP_REST_Response
     */
    public function update_thumb_videos_by_hashs( $request )
    {
        $hashs = $request->get_param( 'hashs' );
        try {
            foreach( $hashs as $hash_vimeo ) {
                $this->update_thumb_videoinfo_by_hash( $hash_vimeo );
            }
            return api_response( 'atualizado com sucesso', 200 );
        } catch( Exception $e ) {
            return api_response( $e->getMessage(), $e->getCode() );
        }
    }

    /**
     * Handler API que faz update da Thumb no Video Info
     */
    public function update_thumb_videos( $request )
    {
        $hash_vimeo = $request[ 'hash' ];
        try {
            $this->update_thumb_videoinfo_by_hash( $hash_vimeo );
            return api_response( 'atualizado com sucesso', 200 );
        } catch( Exception $e ) {
            return api_response( $e->getMessage(), $e->getCode() );
        }
    }

    /**
     * Atualiza a Thumb setando no Vimeo depois atualizando na Base de Dados
     * A URL
     * @param string
     * @return int
     * @throws \Exception
     */
    public function update_thumb_videoinfo_by_hash( $hash_vimeo )
    {
        $vimeo_api = Polen_Vimeo_Factory::create_vimeo_instance_with_redux();
        $vimeo_response_raw = $vimeo_api->request( "/videos/{$hash_vimeo}" . '/pictures', [ 'time' => '0.01', 'active' => true ] );
        $vimeo_response = new Polen_Vimeo_Response( $vimeo_response_raw );
        if( $vimeo_response->is_error() ) {
            throw new Exception( $vimeo_response->get_error(), 500 );
        }
        $vimeo_response = new Polen_Vimeo_Response( $vimeo_api->request( "/videos/{$hash_vimeo}" ) );
        if( $vimeo_response->is_error() ) {
            throw new Exception( $vimeo_response->get_general_error(), 500 );
        }
        $video_info = Polen_Video_Info::get_by_hash( $hash_vimeo );
        if( empty( $video_info ) ) {
            throw new Exception( 'Vimeo Info not found', 404 );
        }
        $video_info->vimeo_thumbnail = $vimeo_response->get_image_url_base();
        return $video_info->update();
    }


    /**
     * 
     * @param \WP_REST_Request
     */
    public function get_item_by_order( $request )
    {
        $order_id = (int) $request['id'];
        $video_info = Polen_Video_Info::get_by_order_id( $order_id );

        if( empty( $video_info ) ) {
            return rest_ensure_response( $video_info );
        }
        
        $data = $this->prepare_item_for_response( $video_info, $request );

        return rest_ensure_response( $data );
    }

    /**
     * 
     * @param Polen\Includes\Polen_Video_Info
     * @param WP_REST_Request
     */
    public function prepare_item_for_response( $video_info, $request )
    {
        $post_data = array();

        $post_data[ 'ID' ] = $video_info->ID;
        $post_data[ 'order_id' ] = $video_info->order_id;
        $post_data[ 'talent_id' ] = $video_info->talent_id;
        $post_data[ 'is_public' ] = $video_info->is_public;
        $post_data[ 'vimeo_id' ] = $video_info->vimeo_id;
        $post_data[ 'hash' ] = $video_info->hash;
        $post_data[ 'vimeo_thumbnail' ] = $video_info->vimeo_thumbnail;
        $post_data[ 'vimeo_process_complete' ] = $video_info->vimeo_process_complete;
        $post_data[ 'vimeo_url_download' ] = $video_info->vimeo_url_download;
        $post_data[ 'vimeo_link' ] = $video_info->vimeo_link;
        $post_data[ 'vimeo_file_play' ] = $video_info->vimeo_file_play;
        $post_data[ 'duration' ] = $video_info->duration;
        $post_data[ 'vimeo_iframe' ] = $video_info->vimeo_iframe;
        $post_data[ 'first_order' ] = $video_info->first_order;
        $post_data[ 'video_logo_status' ] = $video_info->video_logo_status;
        $post_data[ 'created_at' ] = $video_info->created_at;
        $post_data[ 'updated_at' ] = $video_info->updated_at;

        $order = wc_get_order( $video_info->order_id );
        if(empty($order)) {
            return $post_data;
        }
        $polen_order = new Polen_Order_Module( $order );
        $post_data[ 'campaign' ] = $polen_order->get_campaign_slug();
        
        return $post_data;
    }

    // Sets up the proper HTTP status code for authorization.
    public function authorization_status_code() {

        $status = 401;
    
        if ( is_user_logged_in() ) {
            $status = 403;
        }
    
        return $status;
    }

    /**
     * 
     */
    public function get_item_schema()
    {
        if( !empty( $this->schema ) ) {
            return $this->schema;
        }

        $this->schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'Video info',
            'type' => 'object',
            'properties' => array(
                'ID' => array(
                    'description' => 'Id unico do video info',
                    'type'        => 'integer',
                    'context'     => array( 'view', 'edit', 'embed' ),
                    'readonly'    => true,
                ),
                'order_id' => array(
                    'description' => 'ID da Order do video-info',
                    'type' => 'integer'
                )
            )
        );
        
        return $this->schema;
    }
}
