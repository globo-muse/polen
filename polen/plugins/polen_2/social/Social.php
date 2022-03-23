<?php
namespace Polen\Social;

class Social
{
    public function __construct( $static = false )
    {
        if( $static ) {
            // new Social_Rewrite( $static );
        }
    }


    static public function is_social_app()
    {
        $social_app = isset( $GLOBALS[ Social_Rewrite::QUERY_VARS_SOCIAL_APP ] ) ? $GLOBALS[ Social_Rewrite::QUERY_VARS_SOCIAL_APP ] : false;
        if( $social_app === '1' ) {
            return true;
        }
        return false;
    }
}