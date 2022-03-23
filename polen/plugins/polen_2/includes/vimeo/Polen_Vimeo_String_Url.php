<?php

namespace Polen\Includes\Vimeo;

class Polen_Vimeo_String_Url
{
    /**
     * Remove o tamanho na URL do Vimeo
     * 
     * @param string $thumb_url_vimeo
     * @return type
     */
    static public function remove_size_for_image_url( $thumb_url_vimeo )
    {
        $img_url = $thumb_url_vimeo;
        $splited_url = explode( '_', $img_url );
        $url_pice = $splited_url[0];
        return $url_pice;
    }
    
    
    /**
     * Pegar um tamanho de thumb e adiciona na posicao exata da URL o tamanho que o usuário quer
     * excluindo a borda preta padrão do vimeo
     * 
     * @param string $size "400x600"
     * @param string $thumb_url_vimeo URL 
     * @return string url da imagem com tamanho
     */
    static public function get_image_url_custom_size( string $size, string $thumb_url_vimeo )
    {
//        $thumb_url_vimeo = $vimeo_url;
        $url_removed_size = self::remove_size_for_image_url( $thumb_url_vimeo );
        $url_removed_size .= '_' . $size . '.jpg';
        return $url_removed_size;
    }
}
