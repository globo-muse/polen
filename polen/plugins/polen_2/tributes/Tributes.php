<?php

namespace Polen\Tributes;

class Tributes
{
    public function __construct( bool $static = false )
    {
        new Tributes_Rewrite_Rules( $static );
        new Tributes_API_Router( $static );
    }


    /**
     * Se esta em alguma das paginas do Tribute APP /trbutes/*
     * 
     * @return bool
     */
    public static function is_tributes_app()
    {
        $tributes_app      = get_query_var( 'tributes_app' );
        $tribute_operation = get_query_var( 'tribute_operation' );
        if( $tributes_app == '1' && in_array( $tribute_operation, Tributes_Rewrite_Rules::TRIBUTES_OPERATIONS ) ) {
            return true;
        }
        return false;
    }


    /**
     * Se esta na Home de tributos /tributes
     * 
     * @return bool
     */
    public static function is_tributes_home()
    {
        $tributes_app      = get_query_var( 'tributes_app' );
        $tribute_operation = get_query_var( 'tribute_operation' );
        if( $tributes_app == '1' && $tribute_operation == Tributes_Rewrite_Rules::TRIBUTES_OPERATION_HOME) {
            return true;
        }
        return false;
    }



    /**
     * Se está no form de criacao de um tribute /tribute/create
     * 
     * @return bool
     */
    public static function is_tributes_create()
    {
        $tributes_app      = get_query_var( 'tributes_app' );
        $tribute_operation = get_query_var( 'tribute_operation' );
        if( $tributes_app == '1' && $tribute_operation == Tributes_Rewrite_Rules::TRIBUTES_OPERATION_CREATE ) {
            return true;
        }
        return false;
    }
}
