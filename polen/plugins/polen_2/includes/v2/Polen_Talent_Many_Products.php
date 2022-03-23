<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Polen\Includes\v2;

class Polen_Talent_Many_Products
{

    /**
     * Pega o produto_IDs pelo User_ID (se o Talent tiver vários produtos)
     * 
     * @param int
     * 
     * @return array
     */
    static public function get_product_ids_by_user_id( $user_id )
    {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'any',
            'author' => $user_id,
        );
        $posts = get_posts( $args );
        if( empty( $posts ) ) {
            return null;
        }
        $result = [];
        foreach( $posts as $post ) {
            $result[] = $post->ID;
        }
        return $result;
    }

}
    
