<?php
namespace Polen\Includes\Vimeo;

class Polen_Vimeo_Create_Folder
{

    /**
     * Criar uma pasta para jogar os videos dentro dela para melhorar a organizacao
     * @param \Vimeo\Vimeo vimeo_api
     * @param string folder_name
     * @return Polen_Vimeo_Response
     */
    static function create_folder( $vimeo_api, $folder_name )
    {
        $vimeo_api = Polen_Vimeo_Factory::create_vimeo_colab_instance_with_redux();
        $args = [
            'name' => $folder_name,
        ];
        $result = new Polen_Vimeo_Response( $vimeo_api->request( "/me/projects", $args, 'POST' ) );
        return $result;
    }
}