<?php
namespace Polen\Api\Talent;

class Api_Talent_Utils
{

    /**
     * Encapsula o metodo para setar os produtos_id de um talento
     * no GLOBAL para não precisar fazer mais selects
     */
    public static function set_globals_product_id( $product_id )
    {
        $GLOBALS[ 'api_talent_product_id' ] = $product_id;
    }
    

    /**
     * Encapsula o metodo de pegar os produtos_id do Global
     */
    public static function get_globals_product_id()
    {
        global $api_talent_product_id;
        return $api_talent_product_id;
    }
}
