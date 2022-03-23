<?php
namespace Polen\Includes;

class Polen_404
{
    
    static public function set_default_404()
    {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        return locate_template( '404.php' );
    }
}