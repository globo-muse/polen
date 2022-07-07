<?php
namespace Polen\Api;

use WP_REST_Request;

abstract class Api_Check_Permission
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

        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        return true;
    }
}
