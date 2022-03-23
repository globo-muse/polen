<?php
namespace Polen\Api;

use Exception;
use Polen\Includes\Emails\Polen_WC_Customer_New_Account;
use Polen\Includes\Polen_Campaign;
use Polen\Includes\Polen_Order_Review;
use Polen\Includes\Polen_SignInUser_Strong_Password;
use WP_Error;
use WP_Query;

class Api_User
{

    public function sign_on( $request )
    {
        $email           = $request->get_param( 'email' );
        $password        = $request->get_param( 'password' );
        $terms_coditions = $request->get_param( 'terms_conditions' );
        $user_name       = $request->get_param( 'user_name' );
        $campaing        = $request->get_param( 'campaign' );

        if( empty( $terms_coditions ) ) {
            return api_response( [ 'message' => 'Aceite os termos e condições do site' ], 403 );
        }

        if( !$this->check_security_password( $password ) ) {
            $strong_password = new Polen_SignInUser_Strong_Password();
            return api_response( [ 'message' => $strong_password->get_default_message_error() ], 403 );
        }

        if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            return api_response( [ 'message' => 'Email inválido' ], 403 );
        }

        try {
            $this->create_user_custumer( $email, $user_name, $password, [ 'campaign' => $campaing ] );
        } catch ( Exception $e ) {
            return api_response( $e->getMessage(), $e->getCode() );
        }

        return api_response( [ 'message' => 'Usuário cadastrado com sucesso' ], 201 );
    }


    public function check_permission_create_item( \WP_REST_Request $request )
    {
        return true;
    }

    public function check_security_password( $password )
    {
        $strong_password = new Polen_SignInUser_Strong_Password();
        return $strong_password->verify_strong_password( $password );
    }


    /**
     * Recuperar dados do usuario
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function my_account(\WP_REST_Request $request): \WP_REST_Response
    {
        $email = $request->get_param('email');
        if(empty($email)) {
            return api_response(['message' => 'Email Obrigatório'], 403);
        }

        $user = get_user_by('email', $email);
        if(empty($user)) {
            return api_response(['message' => 'Não existe nenhum usuario com esse email'], 403);
        }

        $response = [
            'ID' => $user->data->ID,
            'name' => get_user_meta($user->data->ID,'first_name', true) . ' ' . get_user_meta($user->data->ID,'last_name', true),
            'first_name' => get_user_meta($user->data->ID,'first_name', true),
            'last_name' => get_user_meta($user->data->ID,'last_name', true),
            'phone' => get_user_meta($user->data->ID,'billing_phone', true),
            'email' => $user->data->user_email,
            'display_name' => $user->data->display_name,
            'date_registered' => $user->data->user_registered,
        ];

        return api_response($response, 200);
    }


    /**
     * Atualizar senha do usuario
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function update_pass(\WP_REST_Request $request): \WP_REST_Response
    {
        $data = $request->get_params();

        $user = get_user_by('email', $data['email']);
        if(empty($user)) {
            return api_response(['message' => 'Não existe nenhum usuario com esse email'], 403);
        }

        $check = wp_authenticate($data['email'], $data['current_pass']);
        if (is_wp_error($check)) {
            return api_response(['message' => 'Senha atual incorreta'], 403);
        }

        if(!$this->check_security_password(sanitize_text_field($data['new_pass']))) {
            $strong_password = new Polen_SignInUser_Strong_Password();
            return api_response( ['message' => $strong_password->get_default_message_error() ], 403 );
        }

        wp_set_password($data['new_pass'], $user->data->ID);

        return api_response( ['message' => 'Senha Atualizada' ], 200 );
    }

    /**
     * Atualizar dados da conta
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function update_account(\WP_REST_Request $request): \WP_REST_Response
    {
        $data = $request->get_params();

        $user = get_user_by('email', $data['email']);
        if(empty($user)) {
            return api_response(['message' => 'Não existe nenhum usuario com esse email'], 403);
        }

        $args = [
            'ID' => $user->data->ID,
            'first_name' => $data['user_name'] ?? get_user_meta($user->data->ID,'first_name', true),
            'last_name' => $data['last_name'] ?? get_user_meta($user->data->ID,'last_name', true),
            'display_name' => $data['display_name'] ?? $user->data->display_name,
        ];

        wp_update_user($args);
        update_user_meta($user->data->ID, 'billing_phone', $data['phone']);

        return api_response(['message' => 'Dados Atualizados'], 200);
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function commments(\WP_REST_Request $request): \WP_REST_Response
    {
        $slug = $request['slug'];

        $product_id = wc_get_product_id_by_sku( $slug );

        if (empty($product_id)) {
            return api_response('Talento não encontrado', 404);
        }

        $product = wc_get_product($product_id);
        $product_post = get_post($product->get_id());
        $talent = get_user_by('id', $product_post->post_author);

        $reviews = Polen_Order_Review::get_order_reviews_by_talent_id($talent->ID);
        $comments = [];

        foreach ($reviews as $review) {

            $review_id = $review->comment_ID;
            $rate = get_comment_meta($review_id, 'rate');

            $user_name = $review->comment_author;
            if( empty( $user_name ) ) {
                $user = get_user_by( 'id', $review->user_id );
                $user_name = $user->display_name;
            }

            $user_email = $review->comment_author_email;
            if( empty( $user_email ) ) {
                $user = get_user_by( 'id', $review->user_id );
                $user_email = $user->user_email;
            }

            $comments[] = array(
                'comment_id' => $review->comment_ID,
                'display_name_author' => preg_replace('/@.*/', '', $user_name),
                'author_email' => $user_email,
                'comment' => $review->comment_content,
                'rate' => (int) $rate[0],
                'comment_date' => $review->comment_date,
                'ip_comment' => $review->comment_author_IP
            );
        }

        return api_response($comments, 200);
    }
}
