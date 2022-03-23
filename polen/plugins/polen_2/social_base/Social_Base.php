<?php
namespace Polen\Social_Base;

use Polen\Social_Base\Social_Base_Rewrite;

class Social_Base
{
    public function __construct( $static = false )
    {
        if( $static ) {
            new Social_Base_Rewrite( $static );
        }
    }


    static public function is_social_app()
    {
        $social_app = isset( $GLOBALS[ Social_Base_Rewrite::QUERY_VARS_SOCIAL_APP ] ) ? $GLOBALS[ Social_Base_Rewrite::QUERY_VARS_SOCIAL_APP ] : false;
        if( $social_app === '1' ) {
            return true;
        }
        return false;
    }
}