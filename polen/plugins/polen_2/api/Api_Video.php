<?php

namespace Polen\Api;

use Polen\Includes\API\Polen_Api_Video_Info;
use Polen\Includes\Polen_Video_Info;
use Polen\Includes\Vimeo\Polen_Vimeo_Factory;
use Polen\Includes\Vimeo\Polen_Vimeo_Response;
use WP_REST_Response;

class Api_Video
{
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
        $polen_api_video_info = new Api_Controller();
        $data = $polen_api_video_info->propare_item_video( $video_info );

        return rest_ensure_response( $data );
    }


    /**
     * 
     * @param \WP_REST_Request
     */
    public function get_download_link_by_hash( $request )
    {
        $hash = (int) $request['id'];
        $video_info = Polen_Video_Info::get_by_hash( $hash );

        if( empty( $video_info ) ) {
            return api_response( $video_info, 404 );
        }

        $vimeo_api = Polen_Vimeo_Factory::create_vimeo_instance_with_redux();
        $vimeo_response = new Polen_Vimeo_Response( $vimeo_api->request( $video_info->vimeo_id ) );
        if( $vimeo_response->is_error() ) {
            return api_response( $vimeo_response->get_error(), 404 );
        }
        $result = [ 
            'download_link' => $vimeo_response->get_download_best_quality_url()
        ];
        return rest_ensure_response( $result );
    }
}