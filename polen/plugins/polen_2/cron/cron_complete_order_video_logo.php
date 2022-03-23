<?php

include_once dirname( __FILE__ ) . '/init.php';

use Polen\Includes\Polen_Order;
use Vimeo\Vimeo;
use Vimeo\Exceptions\{ExceptionInterface, VimeoRequestException};
use Polen\Includes\Polen_Video_Info;
use Polen\Includes\Vimeo\Polen_Vimeo_Response;

$client_id = $Polen_Plugin_Settings['polen_vimeo_client_id'];
$client_secret = $Polen_Plugin_Settings['polen_vimeo_client_secret'];
$access_token = $Polen_Plugin_Settings['polen_vimeo_access_token'];

$vimeo_api = new Vimeo($client_id, $client_secret, $access_token);
// $videos = Polen_Video_Info::select_all_videos_incompleted();
$videos = Polen_Video_Info::select_all_videos_complete_video_logo_sended();

echo "Total Videos: " . count($videos) . "\n";
echo "START\n";
foreach ( $videos as $video ) {
    try {
        $response = new Polen_Vimeo_Response( $vimeo_api->request( $video->vimeo_id ) );
        if( $response->is_error() ) {
            throw new VimeoRequestException( $response->get_error() );
        }
        
        if( $response->video_logo_processing_is_complete() ) {
            $video->vimeo_process_complete = 1;
            //TODO colocar esse '300x435' em um lugar, tirar o hardcode
            $video->vimeo_thumbnail    = $response->get_image_url_custom_size( '300x435' );
            $video->duration           = $response->get_duration();
            $video->vimeo_url_download = 'get_in_time';
            $video->vimeo_file_play    = $response->get_play_link();
            $video->video_logo_status    = Polen_Video_Info::VIDEO_LOGO_STATUS_COMPLETED;
            $video->update();
            $order = wc_get_order( $video->order_id );
            $order->update_status( Polen_Order::SLUG_ORDER_COMPLETE, 'Video com logo concluido' );
            $vimeo_api->request( $video->vimeo_id . '/presets/120906813', [], 'PUT');
            $vimeo_api->request( $video->vimeo_id . '/pictures', [ 'time' => '0.01', 'active' => true ], 'POST' );
            echo "Achei: {$video->vimeo_id} \n";
        }
        
    } catch ( ExceptionInterface $e ) {
        echo "Triste dia: {$video->vimeo_id} -> {$e->getMessage()}\n";
    }
}

echo( "END \n" );
