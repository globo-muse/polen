<?php
namespace Polen\Includes\v2;

class Polen_Talent_DB
{
    /**
     * Salvar tabela para uso dessa classe
     */
    public string $table;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->table = $wpdb->base_prefix . 'polen_talents';
    }

    /**
     * Retornar informações básicas do talento
     */
    public function get_column(string $column_name, int $user_id)
    {
        global $wpdb;
        $sql = "
            SELECT `{$column_name}`
            FROM `" . $this->table . "`
            WHERE `user_id`=" . $user_id;

        $data = $wpdb->get_results($sql, ARRAY_N)[0];
        if (!$data) {
            return '';
        }

        return $data[0];
    }
}


