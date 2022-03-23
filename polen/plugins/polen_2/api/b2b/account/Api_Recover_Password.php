<?php
namespace Polen\Api\b2b\Account;

use Exception;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

class Api_Recover_password extends WP_REST_Controller
{
    protected $controller_access;

    public function __construct()
    {
        $this->namespace = 'polen/v1';
        $this->rest_base = 'b2b';

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, $this->rest_base . '/user/password/recover', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'recover_password'],
                'permission_callback' => [],
                'args' => []
            ],
        ] );
    }

    /**
     * Rota checkout
     *
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     * @throws Exception
     */
    public function recover_password(WP_REST_Request $request): \WP_REST_Response
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $client = $_SERVER['HTTP_USER_AGENT'];
        $nonce = $request->get_param('security');

        try {
//            if(!Api_Util_Security::verify_nonce($ip . $client, $nonce)) {
//                throw new Exception('Erro na segurança', 403);
//            }

            $email = $request->get_param('email') ?? null;

            if (null === $email) {
                throw new Exception('E-mail é obrigatório.', 422);
            }

            $user_wp = get_user_by('email', $email);
            if (!$user_wp) {
                throw new Exception('Usuário não encontrado', 503);
            }

            $new_password = wp_generate_password();

            $to = $email;
            $subject = 'Recuperação de Senha';
            $headers = array('Content-Type: text/html; charset=UTF-8');

            $args = array(
                'ID' => $user_wp->ID,
                'user_pass' => $new_password,
            );
            wp_update_user($args);

            $body = "<p>Segue abaixo sua nova senha para acesso ao Polen</p>";
            $body .= "<p><strong>Nova Senha: {$new_password}</strong></p>";

            wp_mail($to, $subject, $body, $headers);

            return api_response(null, 200);

        } catch(Exception $e) {
            return api_response($e->getMessage(), $e->getCode());
        }
    }

}
