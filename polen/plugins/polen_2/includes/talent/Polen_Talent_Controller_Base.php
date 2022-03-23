<?php

namespace Polen\Includes\Talent;

class Polen_Talent_Controller_Base
{
    
    public function __construct()
    {
        $this->init();
        //$this->check_permission();
    }

    public function __destroy()
    {
        $this->finish();
    }

    protected function init()
    {

    }

    protected function check_permission()
    {
        require_once ABSPATH . '/wp-includes/pluggable.php';
        $user = wp_get_current_user();
        $roles = $user->roles;
        if( array_search( 'user_talent', $roles ) !== false ) {
            $is_enabled = get_user_meta( $user->ID, 'talent_enabled', true );
            if( $is_enabled ){
                return true;
            }
            return false; 
        }
        return false;
    }

    protected function finish()
    {
        wp_die();
    }
 
}