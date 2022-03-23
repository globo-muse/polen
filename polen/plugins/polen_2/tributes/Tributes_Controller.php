<?php

namespace Polen\Tributes;

use Polen\Includes\Vimeo\Polen_Vimeo_Factory;
use Polen\Includes\Vimeo\Polen_Vimeo_Response;

class Tributes_Controller
{

    const NONCE_ACTION_GET_LINKS_DOWNLOADS = 'tributes_nonce_get_links_downloads';
    
    public function create_tribute()
    {
        $data_input = [];
        $data_input[ 'name_honored' ]    = filter_input( INPUT_POST, 'name_honored', FILTER_SANITIZE_SPECIAL_CHARS );
        $data_input[ 'slug' ]            = filter_input( INPUT_POST, 'slug', FILTER_SANITIZE_SPECIAL_CHARS );
        $data_input[ 'hash' ]            = Tributes_Model::create_hash();
        $data_input[ 'deadline' ]        = $this->treat_deadline_date( filter_input( INPUT_POST, 'deadline' ) );
        $data_input[ 'occasion' ]        = filter_input( INPUT_POST, 'occasion', FILTER_SANITIZE_SPECIAL_CHARS );;
        $data_input[ 'creator_name' ]    = filter_input( INPUT_POST, 'creator_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $data_input[ 'creator_email' ]   = filter_input( INPUT_POST, 'creator_email', FILTER_VALIDATE_EMAIL );
        $data_input[ 'welcome_message' ] = filter_input( INPUT_POST, 'welcome_message', FILTER_SANITIZE_SPECIAL_CHARS );
        $security                        = filter_input( INPUT_POST, 'security' );

        if( !wp_verify_nonce( $security, 'tributes_add' ) ) {
            wp_send_json_error( 'Erro de segurança', 403 );
            wp_die();
        }
        
        if( !$this->validate_slug_not_empty( $data_input[ 'slug' ] ) ) {
            wp_send_json_error( 'Endereço não pode ser em branco', 401 );
            wp_die();
        }
        try {
            $new_id = Tributes_Model::insert( $data_input );
            $new_tribute = Tributes_Model::get_by_id( $new_id );
            $return_ajax = array(
                'hash' => $new_tribute->hash,
                'url_redirect' => tribute_get_url_invites( $new_tribute->hash ),
            );

            $invite_model = new Tributes_Invites_Controller();
            $invite = $invite_model->create_a_invites( $data_input[ 'creator_name' ], $data_input[ 'creator_email' ], $new_id );
            $email_invite_content = \tributes_email_create_content_invite( $invite->hash );
            tributes_send_email( $email_invite_content, $invite->name_inviter, $invite->email_inviter );

            wp_send_json_success( $return_ajax, 201 );
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
        }
        wp_die();
    }

    /**
     * Verifica se o Slug já existe
     */
    public function check_slug_exists()
    {
        $slug    = filter_input( INPUT_POST, 'slug' );

        if( !$this->validate_slug_not_empty( $slug ) ) {
            wp_send_json_error( 'Porfavor escolha um endereço', 401 );
            wp_die();
        }
        $tribute = Tributes_Model::get_by_slug( $slug );
        if( !empty( $tribute ) ) {
            wp_send_json_error( 'Esse endereço já existe, tente outro', 403 );
            wp_die();
        }
        wp_send_json_success( 'Endereço disponível', 200 );
        wp_die();       
    }


    /**
     * 
     */
    public function check_hash_exists( $hash )
    {
        $hash    = filter_input( INPUT_POST, 'hash' );
        $tribute = Tributes_Model::get_by_hash( $hash );
        if( !empty( $tribute ) ) {
            wp_send_json_error( 'Código já existe', 403 );
            wp_die();
        }
        wp_send_json_success( 'Código livre', 200 );
        wp_die();   
    }


    /**
     * Handler do endpoint para pegar a lista de links de downloads
     */
    public function get_links_downloads()
    {
        $tribute_id = filter_input( INPUT_POST, 'tribute_id', FILTER_SANITIZE_NUMBER_INT );
        $nonce      = filter_input( INPUT_POST, 'security' );
        try {
            $this->validate_nonce( $nonce, self::NONCE_ACTION_GET_LINKS_DOWNLOADS );
            $invites_completed = Tributes_Invites_Model::get_vimeo_processed_by_trubute_id( $tribute_id );
            $result = [];
            $vimeo_api = Polen_Vimeo_Factory::create_vimeo_instance_with_redux();
            foreach( $invites_completed as $invite ) {
                $vimeo_response = new Polen_Vimeo_Response( $vimeo_api->request( $invite->vimeo_id ) );
                if( !$vimeo_response->is_error() ) {
                    $result[] = $vimeo_response->get_download_best_quality_url();
                }
            }
            wp_send_json_success( $result, 200 );
            wp_die();
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
            wp_die();
        }
    }


    /**
     * 
     */
    private function treat_deadline_date( $deadline )
    {
        $date_time = \DateTime::createFromFormat( 'd/m/Y', $deadline );
        if( $date_time === false ) {
            wp_send_json_error( 'Data inválida', 401 );
            wp_die();
        }
        return $date_time->format( 'Y-m-d' );
    }
    

    /**
     * 
     */
    private function validate_slug_not_empty( $slug )
    {
        return ( empty( trim( $slug ) ) ) ? false : true;
    }


    /**
     * Pega o maximo de Invites possiveis por Tribute
     * @return int
     */
    static public function tribute_max_invites()
    {
        return 9;
    }

    /**
     * Validacao de Nonce
     * @param string
     * @param string
     * @throws \Exception
     */
    private function validate_nonce( $nonce, $action )
    {
        if( ! wp_verify_nonce( $nonce, $action ) ) {
            throw new \Exception( 'Erro na segurança', 403 );
        }
    }

}