<?php

namespace Polen\Includes;

class Polen_Bancos
{

    private $table_polen_bancos;

    public function __construct() {
        global $wpdb;
        $this->table_polen_bancos = $wpdb->base_prefix . 'polen_bancos';
    }

    public function listar( $args = array() ) {
        global $wpdb;
        $sql = "SELECT `codigo`, `nome`, `nome_resumido` FROM `" . $this->table_polen_bancos . "`";

        if( isset( $args['orderby'] ) && in_array( strtolower( $args['orderby'] ), array( 'codigo', 'nome', 'nome_resumido', 'rand' ) ) ) {
            if( strtolower( $args['orderby'] ) == 'rand' ) {
                $sql .= " ORDER BY RAND()";
            } else {
                $sql .= " ORDER BY `" . $args['orderby'] . "`";
            }
        } else {
            $sql .= " ORDER BY `nome`";
        }

        if( isset( $args['order'] ) && ( $args['order'] == 'ASC' || $args['order'] == 'DESC' ) ) {
            $sql .= " " . $args['order'];
        } else {
            $sql .= " ASC";
        }

        $res = $wpdb->get_results( $sql );

        if( count( $res ) > 0 ) {
            return $res;
        }
    }
}