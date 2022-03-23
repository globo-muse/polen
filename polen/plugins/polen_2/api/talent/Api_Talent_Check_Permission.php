<?php
namespace Polen\Api\Talent;

use Polen\Includes\Polen_Talent;
use Polen\Includes\v2\Polen_Talent_Many_Products;
use WP_REST_Request;

abstract class Api_Talent_Check_Permission
{

    /**
     * Verifica se o usuário é um Talent
     */
    public static function check_permission( WP_REST_Request $request )
    {
        if( !is_user_logged_in() ) {
            return false;
        }

        $user_id = get_current_user_id();
        if( empty( $user_id ) ) {
            return false;
        }

        $user = get_user_by( 'ID', $user_id );
        if( is_wp_error( $user ) ) {
            return false;
        }

        $is_talent = Polen_Talent::static_is_user_talent( $user );
        if( !$is_talent ) {
            return false;
        }

        $products_id = Polen_Talent_Many_Products::get_product_ids_by_user_id( $user_id );
        Api_Talent_Utils::set_globals_product_id( $products_id );
        return true;
    }
}
