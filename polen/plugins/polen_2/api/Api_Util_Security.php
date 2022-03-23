<?php
namespace Polen\Api;

use Polen\Includes\Debug;
use WC_Logger;

/**
 * Classe auxiliar para geração de hash de nonce
 */
class Api_Util_Security
{
    /**
     * Cria um nonce para API
     * @param string
     * @return string
     */
    public static function create_nonce($nonce_action = '-1')
    {
        $nonce = self::make_hash($nonce_action);
        return $nonce;
    }
    

    /**
     * Verifica um Nonce para a API
     * @param string chave para criacao do nonce e verificar o match 
     * @param string nonce recebido que sera verificado
     * @return bool
     */
    public static function verify_nonce($nonce_action, $nonce)
    {
        $current_nonce = self::make_hash($nonce_action);
        if($current_nonce === $nonce) {
            return true;
        }
        return false;
    }


    /**
     * Responsavel para geracao do nonce em sí
     * @param stirng
     * @return string
     */
    protected static function make_hash($nonce_action)
    {
        return substr(md5($nonce_action), 0, 12);
    }
}
