<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Polen\Includes\Vimeo;

/**
 * Description of Polen_Vimeo_Vimeo_Options
 *
 * @author rodolfoneto
 */
class Polen_Vimeo_Vimeo_Options 
{    
    static public function get_option_insert_video( $file_size, $name_of_video_to )
    {
        return array(
            'upload' => [
                'approach' => 'tus',
                'size' => $file_size,
            ],
            'privacy' => [
                "view" => "disable",
                "download" => true,
            ],
            'name' => "Video para {$name_of_video_to}",
            'embed' => [
                'color' => '#ef00b8',
                'buttons' => [
                    'embed' => false,
                    'fullscreen' => true,
                    'hd' => false,
                    'like' => false,
                    'scaling' => false,
                    'share' => false,
                    'watchlater' => false,
                ],
                'logos' => [
                    'vimeo' => false
                ],
                'playbar' => false,
                'privacy' => [
                    'download' => true
                ],
                'title' => [
                    'name' => 'hide',
                    'owner' => 'hide',
                    'portrait' => 'hide'
                ],
                'volume' => false,
                'uri' => "/presets/120906813",
                "interactions"=> [
                    "buy" => [
                        "download" => "available"
                    ]
                ]
            ]
        );
    }

    static public function get_option_insert_video_server_side(  $name_of_video_to )
    {
        return array(
            'privacy' => [
                "view" => "disable",
                "download" => true,
            ],
            'name' => "Video para {$name_of_video_to}",
            'embed' => [
                'color' => '#ef00b8',
                'buttons' => [
                    'embed' => false,
                    'fullscreen' => true,
                    'hd' => false,
                    'like' => false,
                    'scaling' => false,
                    'share' => false,
                    'watchlater' => false,
                ],
                'logos' => [
                    'vimeo' => false
                ],
                'playbar' => false,
                'privacy' => [
                    'download' => true
                ],
                'title' => [
                    'name' => 'hide',
                    'owner' => 'hide',
                    'portrait' => 'hide'
                ],
                'volume' => false,
                'uri' => "/presets/120906813",
                "interactions"=> [
                    "buy" => [
                        "download" => "available"
                    ]
                ]
            ]
        );
    }
}
