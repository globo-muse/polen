<?php
namespace Polen\Api\Talent;

use Exception;
use Polen\Includes\Polen_Update_Fields;
use stdClass;
use WP_REST_Request;
use WP_REST_Server;

class Api_Talent_Payment
{

    /**
     * Metodo construtor
     */
    public function __construct()
    {
        $this->namespace = 'polen/v1';
        $this->rest_base = 'talents';
    }


    /**
     * 
     */
    public function register_routes()
    {
        //Rota para pegar um nonde válido
        register_rest_route($this->namespace, $this->rest_base . '/payment', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_talent_infos'],
                'permission_callback' => [Api_Talent_Check_Permission::class, 'check_permission'],
                'args' => []
            ]
        ] );
    }


    /**
     * 
     */
    public function get_talent_infos(WP_REST_Request $request)
    {
        try {
            $user_id = get_current_user_id();
            $update_fields = new Polen_Update_Fields();
            $user_talent_data = $update_fields->get_vendor_data($user_id);
            if(empty($user_talent_data)) {
                throw new Exception('Dados do talento não encontroado', 404);
            }
            $result = $this->prepare_item_for_response_talent_infos($user_talent_data);
            return api_response($result);
        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }


    /**
     * 
     */
    public function prepare_item_for_response_talent_infos(stdClass $user_talent_data)
    {
        $result = [];
        $result['legal_nature'] = $user_talent_data->natureza_juridica;
        $result['banking_agency'] = substr($user_talent_data->agencia, 0, 2) .
            str_repeat('#', strlen($user_talent_data->agencia) - 2);
        $result['account_number'] = substr($user_talent_data->conta, 0, 2) .
            str_repeat('#', strlen($user_talent_data->conta) - 4) . '-#';
        
        if($user_talent_data->natureza_juridica == 'PJ') {
            $result['name'] = $user_talent_data->razao_social;
            $result['document'] = substr($user_talent_data->cnpj, 0, 6) . '.###.###/####-##';
        } else {
            $result['name'] = $user_talent_data->nome;
            $result['document'] = substr($user_talent_data->cnpj, 0, 3) . '.###.###-##';
        }
        return $result;
    }
}
