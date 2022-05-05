<?php

namespace Polen\Includes\Vimeo;

use Polen\Includes\Debug;
use Polen\Includes\Vimeo\Polen_Vimeo_String_Url;

/**
 * Polen_Vimeo_Response é uma classe para interpretar o response do Vimeo
 */
class Polen_Vimeo_Response
{
    const STATUS_UPLOADING = 'uploading';
    const STATUS_TRANSCODING = 'transcoding';
    const STATUS_AVAILABLE = 'available';
    
    const URL_IMAGE_640_VIMEO_DEFULT = 'https://i.vimeocdn.com/video/default_640x360?r=pad';

    public $response;
    
    public function __construct( $response )
    {
        $this->response = $response;
    }
    
    /**
     * Pega o ID do vimeo 
     * @return string '/videos/XXXX
     */
    public function get_vimeo_id()
    {
        return $this->response['body']['uri'];
    }
    
    /**
     * Pega o link completo para o video
     * @return string URL
     */
    public function get_vimeo_link()
    {
        return $this->response['body']['link'];
    }
    
    /**
     * Se o RESPONSE é um erro, na API não criar uma Exception
     * @return boolean
     */
    public function is_error()
    {
        $return = false;
        if( $this->get_general_error() != null && $this->get_general_error() != '' ) {
            $return = true;
        }
        return $return;
    }
    
    /**
     * Pega um erro generico, quando não um erro voltado ao DEV
     * @return type
     */
    public function get_error()
    {
        return !empty( $this->get_developer_message() )
            ? $this->get_developer_message()
            : $this->get_general_error();
    }


    public function get_general_error()
    {
        if( isset( $this->response['body']['error'] ) ) {
            return $this->response['body']['error'];
        }
        return '';
    }
    
    /**
     * Pega a msg de erro do developer, alguns erros são genericos e volta essa
     * área uma msg especifica para o DEV
     * @return type
     */
    public function get_developer_message()
    {
        return isset( $this->response['body']['developer_message'] )
            ? $this->response['body']['developer_message']
            : null;
    }
    
    /**
     * Pega o status do Vimeo da ultima resposta
     * @return string
     */
    public function get_status()
    {
        return $this->response['body']['status'];
    }
    
    /**
     * Verifica se o processamento do video está completo pelo Vimeo
     * @return boolean
     */
    public function video_processing_is_complete()
    {
        if( 
                $this->response['body']['status'] == self::STATUS_AVAILABLE &&
                $this->response['body']['pictures']['sizes'][3]['link'] != self::URL_IMAGE_640_VIMEO_DEFULT
            ) {
            return true;
        }
        return false;
    }


    /**
     * Verifica se o processamento do video está completo pelo Vimeo
     * @return boolean
     */
    public function video_logo_processing_is_complete()
    {
        if( 
                $this->response['body']['status'] == self::STATUS_AVAILABLE
            ) {
            return true;
        }
        return false;
    }

    /**
     * Pega a URL base para qualquer tamanho
     * @return string URL
     */
    public function get_image_url_base()
    {
        return $this->response['body']['pictures']['base_link'];
    }

    
    /**
     * Pega a URL a tamanho 640
     * @return string URL
     */
    public function get_image_url_640()
    {
        return $this->response['body']['pictures']['sizes'][3]['link'];
    }
    
    /**
     * 
     * @return string
     */
    public function get_image_url_smaller()
    {
        return $this->response['body']['pictures']['sizes'][0]['link'];
    }
    
    /**
     * Pegar um tamanho de thumb e adiciona na posicao exata da URL o tamanho que o usuário quer
     * excluindo a borda preta padrão do vimeo
     * 
     * @param string $size "400x600"
     * @return string url da imagem com tamanho
     */
    public function get_image_url_custom_size( string $size )
    {
        $thumb_url_vimeo = $this->get_image_url_smaller();
        $url_removed_size = Polen_Vimeo_String_Url::get_image_url_custom_size( $size, $thumb_url_vimeo );
        return $url_removed_size;
    }

    
    /**
     * Pega a duração do video
     * @return int
     */
    public function get_duration()
    {
        return $this->response['body']['duration'];
    }
    
    /**
     * Pegar o codigo HTML do iframe
     * @return string
     */
    public function get_iframe()
    {
        return $this->response['body']['embed']['html'];
    }


    /**
     * Pegar o array com todas as qualidades possiveis para download
     */
    public function get_download_array()
    {
        return $this->response['body']['download'];
    }


    /**
     * Pega do response o array da qualidade Source
     */
    public function get_download_source()
    {
        $download_array = $this->get_download_array();
        foreach( $download_array as $download ) {
            if( $download['quality'] == 'source' ) {
                return $download;
            }
        }
        return null;
    }

    /**
     * Pega a URL para download do qualidade Source
     */
    public function get_download_source_url()
    {
        $download_source = $this->get_download_source();
        if( !empty( $download_source ) ) {
            return $download_source['link'];
        }
        return null;
    }

    /**
     * Pegar o array com a melhor qualidade possivel
     */
    public function get_download_best_quality()
    {
        $download_array = $this->get_download_array();
        return $download_array[ count( $download_array ) - 1 ];
    }


    /**
     * Pegar a URL com a melhor qualidade possivel
     */
    public function get_download_best_quality_url()
    {
        $download_array = $this->get_download_array();
        $best_quality_array = $download_array[ count( $download_array ) - 1 ];
        return $best_quality_array[ 'link' ];
    }


    /**
     * Pegar o array com todas as qualidades possiveis para play
     */
    public function get_play_array()
    {
        $array = $this->response['body']['files'];
        return $array;
    }


    /**
     * Pega a URI da folder recem criada
     */
    public function get_folder_uri()
    {
        return $this->response['body']['uri'];
    }


    /**
     * Pega do link do arquivo para play
     */
    public function get_play_link()
    {
        $files = $this->get_play_array();
        $result = array( 'height' => PHP_INT_MIN );
        foreach( $files as $file ) {
            if( isset( $file['height'] ) && $file['height'] >= $result['height'] ) {
                $result = $file;
            }
        }
        return $result[ 'link' ];
    }


    public function is_landscape():bool
    {
        $is_landscape = true;

        if($this->get_height() > $this->get_width()) {
            $is_landscape = false;
        }
        return $is_landscape;
    }

    
    public function is_portrait():bool
    {
        $is_portrait = true;

        if($this->get_width() > $this->get_height()) {
            $is_portrait = false;
        }
        return $is_portrait;
    }


    public function get_width()
    {
        return $this->response['body']['width'];
    }

    public function get_height()
    {
        return $this->response['body']['height'];
    }
}
