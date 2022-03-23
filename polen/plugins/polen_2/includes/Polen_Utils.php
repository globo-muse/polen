<?php
namespace Polen\Includes;

class Polen_Utils
{
    /**
     * Sanitize as entradas de input contra XSS
     * @param string
     * @return string
     */
    public static function sanitize_xss_br_escape( $txt )
    {
        $string_escaped = htmlspecialchars($txt);
        return nl2br($string_escaped);
    }


    /**
     * Remove o Sanitize para apresentação desse conteúdo no site
     * o segundo parametro pode ser View ou Edit
     * Se for Edit ele remove o <br> e adiciona /r/n para 
     * TextÁreas ou textos raw de forma geral
     * @param string
     * @param string context 'edit
     * @return string
     */
    public static function remove_sanitize_xss_br_escape( $txt, $context = 'view' )
    {
        $string_escaped = stripslashes($txt);
        $string_escaped = htmlspecialchars_decode($string_escaped);
        if('edit' === $context) {
            $string_escaped = str_replace(array("<br />", "<br/>", "<br>"), PHP_EOL, $string_escaped);
        }
        return $string_escaped;
    }


    /**
     * Retona um pattern para o WPDB::Prepare por um ARRAY
     * Ex: INPUT  [100, 101, 102]
     *     OUTPUT %s, %s, %s
     * 
     * @param array
     * @return string
     */
    static public function pattern_array( array $var = [] )
    {
        return implode( ', ', array_fill( 0, count( $var ), '%s' ) );
    }


    /**
     * Escapa um SQL com entradas em ARRAY
     * Devolvendo o SQL Preparado
     * 
     * @param string
     * @param array
     * @return string
     */
    public static function esc_arr( $sql, $args )
    {
        global $wpdb;
        $query = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $args ) );
        return $query;
    }
}
