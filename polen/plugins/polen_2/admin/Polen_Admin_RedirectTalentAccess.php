<?php

namespace Polen\Admin;

use Polen\Includes\Polen_Talent;
/**
 * Description of Polen_Admin_DisableTalenAccess
 *
 * @author rodolfoneto
 */
class Polen_Admin_RedirectTalentAccess
{
    public function __construct()
    {
        add_action( 'admin_init', array( $this, 'redirect_talent' ) );
    }
    
    public function redirect_talent()
    {
        $user = wp_get_current_user();
        $polen_talent = new Polen_Talent();
        if( ! defined('DOING_AJAX') && $polen_talent->is_user_talent( $user ) ) {
            $path = 'my-account';
            wp_redirect( site_url( $path ) );
        }
    }

}
