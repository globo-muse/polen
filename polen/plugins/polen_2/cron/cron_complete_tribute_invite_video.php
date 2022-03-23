<?php

include_once dirname( __FILE__ ) . '/init.php';

use Vimeo\Vimeo;
use Vimeo\Exceptions\{ExceptionInterface, VimeoRequestException};
use Polen\Includes\Vimeo\Polen_Vimeo_Response;
use Polen\Tributes\Tributes_Invites_Model;

$client_id = $Polen_Plugin_Settings['polen_vimeo_client_id'];
$client_secret = $Polen_Plugin_Settings['polen_vimeo_client_secret'];
$access_token = $Polen_Plugin_Settings['polen_vimeo_access_token'];

$vimeo_api = new Vimeo($client_id, $client_secret, $access_token);
$invites = Tributes_Invites_Model::get_vimeo_not_processed_yet();

echo "Total Colabs: " . count( $invites ) . "\n";
echo "START\n";
foreach ( $invites as $invite ) {
    try {
        $response = new Polen_Vimeo_Response( $vimeo_api->request( $invite->vimeo_id ) );
        if( $response->is_error() ) {
            throw new VimeoRequestException( $response->get_error() );
        }
        
        if( $response->video_processing_is_complete() ) {
            $data_update = array(
                'ID' => $invite->ID,
                'vimeo_thumbnail' => $response->get_image_url_640(),
                'vimeo_process_complete' => '1',
                'vimeo_link' => $response->get_vimeo_link(),
                'duration' => $response->get_duration(),
                'vimeo_url_file_play' => $response->get_play_link(),
                'video_sent_date' => date('Y-m-d H:i:s')
            );
            Tributes_Invites_Model::update( $data_update );
            echo "Invite: {$invite->vimeo_id} \n";
        }
        
    } catch ( ExceptionInterface $e ) {
        echo "Triste dia: {$invite->vimeo_id} -> {$e->getMessage()}\n";
    }
}

echo( "END \n" );
