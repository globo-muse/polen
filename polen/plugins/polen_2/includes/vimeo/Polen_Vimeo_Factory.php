<?php

namespace Polen\Includes\Vimeo;

use Vimeo\Vimeo;

class Polen_Vimeo_Factory
{

    /**
     * Criar uma instancia da API do Vimeo
     * @param string
     * @param string
     * @param string
     * 
     * @return \Vimeo\Vimeo
     */
    static public function create_vimeo_instance(
        string $client_id,
        string $client_secret,
        string $access_token
    ) {
        $vimeo_instance = new Vimeo( $client_id, $client_secret, $access_token );
        return $vimeo_instance;
    }


    /**
     * Criar um instancia da API do Vimeo para o polen com os dados do Redux
     * @return \Vimeo\Vimeo
     */
    static public function create_vimeo_instance_with_redux()
    {
        global $Polen_Plugin_Settings;

        $client_id = $Polen_Plugin_Settings['polen_vimeo_client_id'];
        $client_secret = $Polen_Plugin_Settings['polen_vimeo_client_secret'];
        $access_token = $Polen_Plugin_Settings['polen_vimeo_access_token'];
        
        $vimeo_instance = self::create_vimeo_instance( $client_id, $client_secret, $access_token );
        return $vimeo_instance;
    }


    /**
     * Criar uma instancia da API do Vimeo para Triutos
     * @return \Vimeo\Vimeo
     */
    static public function create_vimeo_colab_instance_with_redux(): \Vimeo\Vimeo
    {
        global $Polen_Plugin_Settings;

        $client_id = $Polen_Plugin_Settings['polen_vimeo_tribute_client_id'];
        $client_secret = $Polen_Plugin_Settings['polen_vimeo_tribute_client_secret'];
        $access_token = $Polen_Plugin_Settings['polen_vimeo_tribute_access_token'];
        
        $vimeo_instance = self::create_vimeo_instance( $client_id, $client_secret, $access_token );
        return $vimeo_instance;
    }
}
