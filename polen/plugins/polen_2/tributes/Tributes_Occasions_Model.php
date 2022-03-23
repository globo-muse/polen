<?php
namespace Polen\Tributes;

class Tributes_Occasions_Model
{

    const TABLE_NAME = 'tributes_occasions';

    /**
     * Pega o nome da Tabela
     */
    public static function table_name()
    {
        global $wpdb;
        return $wpdb->base_prefix . self::TABLE_NAME;
    }

    /**
    * Pegar todos os registros da tabela
    */
    public static function get_all()
    {
        global $wpdb;
        $table_name = self::table_name();
        return $wpdb->get_results(
            "SELECT * FROM {$table_name};"
        );
    }

}