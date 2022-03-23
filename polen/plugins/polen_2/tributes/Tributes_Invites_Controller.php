<?php
namespace Polen\Tributes;

use Exception;
use Polen\Includes\Vimeo\Polen_Vimeo_Response;
use Polen\Includes\Vimeo\Polen_Vimeo_Vimeo_Options;
use Vimeo\Exceptions\ExceptionInterface;
use Vimeo\Exceptions\VimeoRequestException;
use Vimeo\Vimeo;

class Tributes_Invites_Controller
{

    /**
     * Inseri na base um Invite inicial
     */
    public function create_tribute_invites()
    {
        $tribute_hash = filter_input( INPUT_POST, 'tribute_hash' );
        $tribute      = Tributes_Model::get_by_hash( $tribute_hash );
        $tribute_id   = filter_input( INPUT_POST, 'tribute_id' );
        $friends_list = $_POST[ 'friends' ];
        $security     = filter_input( INPUT_POST, 'security' );
        if( !wp_verify_nonce( $security, 'tributes_create_invites' ) ) {
            wp_send_json_error( 'Erro de segurança', 403 );
            wp_die();
        }
        if ( empty( $tribute ) || $tribute->ID !== $tribute_id ) {
            wp_send_json_error( 'Colab Inválido', 401 );
            wp_die();
        }

        if( empty( $friends_list ) ) {
            wp_send_json_error( 'lista de convites inválida', 401 );
            wp_die();
        }

        $emails = $_POST[ 'friends' ][ 'email' ];
        $names  = $_POST[ 'friends' ][ 'name' ];
        try {
            self::validate_max_number_invites( $tribute_id, count( $emails ) );
            for( $i = 0; $i < count( $emails ); $i++ ) {
                $email = filter_var( $emails[ $i ], FILTER_VALIDATE_EMAIL );
                $name  = filter_var( $names[ $i ], FILTER_SANITIZE_SPECIAL_CHARS );
                if( !empty( $email ) ) {
                    $invite               = $this->create_a_invites( $name, $email, $tribute->ID );
                    $email_invite_content = \tributes_email_create_content_invite( $invite->hash );
                    tributes_send_email( $email_invite_content, $invite->name_inviter, $invite->email_inviter );
                }
            }
            wp_send_json_success( 'Convites criados com sucesso', 201 );
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
        }
        wp_die();
    }


    /**
     * Criar um registro de Invites
     * @param string $name_inviter
     * @param string $email_inviter
     * @param int $tribute_id
     * @return stdClass wp_tributes_invites
     */
    public function create_a_invites( $name_inviter, $email_inviter, $tribute_id )
    {
        $data_input = [];
        $data_input[ 'name_inviter' ]  = $name_inviter;
        $data_input[ 'email_inviter' ] = filter_var( $email_inviter, FILTER_VALIDATE_EMAIL );
        $data_input[ 'tribute_id' ]    = $tribute_id;
        $data_input[ 'hash' ]          = Tributes_Invites_Model::create_hash( $tribute_id );
        $new_id      = Tributes_Invites_Model::insert( $data_input );
        $new_tribute = Tributes_Invites_Model::get_by_id( $new_id );
        return $new_tribute;
    }


    /**
     * 
     */
    public function get_all_invite_by_tribute_id()
    {
        $tribute_id = filter_input( INPUT_POST, 'tribute_id' );
        $nonce      = filter_input( INPUT_POST, 'security' );
        $tributes   = Tributes_Invites_Model::get_all_by_tribute_id( $tribute_id );
        wp_send_json_success( $tributes, 200 );
        wp_die();
    }


    /**
     * @param int
     * @return int
     */
    public function get_count_invites_by_tribute_id( $tribute_id )
    {
        return Tributes_Invites_Model::get_count_by_tribute_id( $tribute_id );
    }


    /**
     * Handler para o admin-ajax onde é executado quando o convidado ao Colab,
     * seleciona um video e
     * envia, antes do envio é criado no Vimeo um Slot para receber o Video com o 
     * mesmo tamanho em bytes
     * 
     * @global type $Polen_Plugin_Settings
     * @throws VimeoRequestException
     */
    public function make_video_slot_vimeo()
    {
        global $Polen_Plugin_Settings;

        $client_id = $Polen_Plugin_Settings['polen_vimeo_client_id'];
        $client_secret = $Polen_Plugin_Settings['polen_vimeo_client_secret'];
        $token = $Polen_Plugin_Settings['polen_vimeo_access_token'];

        $tribute_hash = filter_input( INPUT_POST, 'tribute_hash' );
        $invite_hash = filter_input( INPUT_POST, 'invite_hash' );
        $invite_id   = filter_input( INPUT_POST, 'invite_id', FILTER_SANITIZE_NUMBER_INT );
        $file_size   = filter_input( INPUT_POST, 'file_size', FILTER_SANITIZE_NUMBER_INT );

        $tribute = Tributes_Model::get_by_hash( $tribute_hash );
        $invite = Tributes_Invites_Model::get_by_id( $invite_id );

        if( $tribute->ID != $invite->tribute_id || $invite->hash != $invite_hash ) {
            wp_send_json_error( 'Dados do Colab inválidos', 403 );
            wp_die();
        }

        $name_to_video = '';
        try {
            $lib = new Vimeo( $client_id, $client_secret, $token );
            $args = Polen_Vimeo_Vimeo_Options::get_option_insert_video( $file_size, $name_to_video );
            $args[ 'name' ] = "Colab para {$tribute->name_honored}";
            $vimeo_response = $lib->request( '/me/videos', $args, 'POST' );
            
            $response = new Polen_Vimeo_Response( $vimeo_response );
            
            if( $response->is_error() ) {
                throw new VimeoRequestException( $response->get_developer_message(), 500 );
            }
            
            $data_invite_update = array(
                'ID'                     => $invite->ID,
                'vimeo_id'               => $response->get_vimeo_id(),
                'vimeo_process_complete' => '0',
                'vimeo_link'             => $response->get_vimeo_link(),
            );

            Tributes_Invites_Model::update( $data_invite_update );
            wp_send_json_success( $response->response, 200 );
        } catch ( VimeoRequestException $e ) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
        }
        wp_die();
    }


    /**
     * Valida se ainda pode criar mais convites
     * @param int
     * @throws \Exception
     */
    static public function validate_max_number_invites( $tribute_id, $count_emails )
    {
        $max_invites = Tributes_Controller::tribute_max_invites();
        $total_alredy_invites = Tributes_Invites_Model::get_count_by_tribute_id( $tribute_id );
        if( $max_invites < ( $total_alredy_invites + $count_emails ) ) {
            $max_invites -= 1;
            throw new \Exception( "Limite de convites ({$max_invites}) atingidos.", 403 );
        }
    }


    /**
     * Reenvio de email
     */
    public function resend_email()
    {
        $nonce = filter_input( INPUT_POST, 'security' );
        //TODO: NONCE
        $invite_hash = filter_input( INPUT_POST, 'invite_hash' );
        $invite      = Tributes_Invites_Model::get_by_hash( $invite_hash );

        if( empty( $invite ) ) {
            wp_send_json_error( 'Convite inválido', 404 );
            wp_die();
        }

        if( $invite->video_sent == '1' ) {
            wp_send_json_error( 'Video já foi enviado', 401 );
            wp_die();
        }
        
        $last_email = strtotime( $invite->last_send_email );
        $current_time = strtotime( 'now' );
        $diff = $current_time - $last_email;
        if( 3600 > $diff ) {
            wp_send_json_error( 'Aguarde 1h para reenviar um email', 500 );
            wp_die();
        }
        
        $data_update = array(
            'ID' => $invite->ID,
            'last_send_email' => date('Y-m-d H:i:s')
        );
        Tributes_Invites_Model::update( $data_update );

        $email_invite_content = \tributes_email_create_content_invite( $invite->hash );
        if( tributes_send_email( $email_invite_content, $invite->name_inviter, $invite->email_inviter ) ) {
            wp_send_json_success( 'Convite enviado com sucesso', 200 );
        } else {
            wp_send_json_success( 'Erro no envio do email', 500 );
        }
        wp_die();
    }


    /**
     * Handler de deletar invites no Tributes Detalhes
     */
    public function delete_invite()
    {
        // var_dump($_POST);die;
        $tribute_hash = filter_input( INPUT_POST, 'tribute_hash' );
        $invite_hash  = filter_input( INPUT_POST, 'invite_hash' );
        $security     = filter_input( INPUT_POST, 'security' );
        if( !wp_verify_nonce( $security, 'tributes_delete_invite' ) ) {
            wp_send_json_error( 'Inválido', 401 );
            wp_die();
        }
        $tribute = Tributes_Model::get_by_hash( $tribute_hash );
        if( empty( $tribute ) ) {
            wp_send_json_error( 'Colab inválido', 401 );
            wp_die();
        }
        $invite = Tributes_Invites_Model::get_by_hash( $invite_hash );
        if( empty( $invite ) ) {
            wp_send_json_error( 'Convite inválido', 401 );
            wp_die();
        }
        if( $invite->tribute_id != $tribute->ID ) {
            wp_send_json_error( 'Exclusão inválida', 401 );
            wp_die();
        }
        if( !empty( $invite->vimeo_id ) && $invite->vimeo_error != '0' ) {
            wp_send_json_error( 'Impossível excluir um convite com o video já enviado', 401 );
            wp_die();
        }
        $data_input = array(
            'ID' => $invite->ID
        );
        $result_delete = Tributes_Invites_Model::delete( $data_input );
        if( !$result_delete ) {
            wp_send_json_error( 'Erro, tente novamente', 401 );
            wp_die();
        }
        wp_send_json_success( 'Excluido com sucesso', 200 );
        wp_die();
    }
}
