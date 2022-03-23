<?php
/**
 * Classe responsavel pelos usuário que teram acesso
 * 
 */

namespace Polen\Includes\API;

use WP_User;

class Polen_Api_User
{
    public $user;


    /**
     * @param WP_User
     */
    public function __construct( WP_User $user )
    {
        if( in_array( self::get_role(), $user->roles ) )
        {
            return false;
        }
        $this->user = $user;
    }

    public static function get_role()
    {
        return 'subscriber';
    }

    public function check_permission( $action )
    {
        if( !empty( $this->user ) )
        {
            return false;
        }
        return true;
    }
}
