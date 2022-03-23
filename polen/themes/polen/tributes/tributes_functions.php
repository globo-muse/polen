<?php

use Polen\Tributes\{ Tributes, Tributes_Invites_Model, Tributes_Occasions_Model, Tributes_Questions_Model, Tributes_Rewrite_Rules};

if( !defined( 'ABSPATH' ) ) {
    echo 'Silence is Golden';
    die;
}

//Funcoes responsaveis pelos emails
include_once TEMPLATE_DIR . '/tributes/tributes_functions_emails.php';

function is_tribute_app() {
    return Tributes::is_tributes_app();
}

function is_tribute_home() {
    return Tributes::is_tributes_home();
}

function is_tribute_create() {
    return Tributes::is_tributes_create();
}



/****************************************
 ******************** URLs **************
 ****************************************/

 /**
 * Pega a URL da pagina que faz os convites
 *
 * @param string $tribute_hash
 * @return string URL completa
 */
function tribute_get_url_base_url() {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH );
}


 /**
 * Pega a URL da pagina que faz os convites
 *
 * @param string $tribute_hash
 * @return string URL completa
 */
function tribute_get_url_new_tribute() {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH . '/novo/' );
}


 /**
 * Pega a URL da pagina de detalhes do Colab
 *
 * @param string $tribute_hash
 * @return string URL completa
 */
function tribute_get_url_tribute_detail( $tribute_hash ) {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH . "/{$tribute_hash}/detalhes" );
}

/**
 * Pega a URL da pagina que faz os convites
 *
 * @param string $tribute_hash
 * @return string URL completa
 */
function tribute_get_url_invites( $tribute_hash ) {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH . "/{$tribute_hash}" );
}

/**
 * Pega a URL dos meus tributos
 *
 * @param string $tribute_hash
 * @return string URL completa
 */
function tribute_get_url_my_tributes() {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH . "/meus-colabs" );
}

/**
 * Pega a URL da pagina de sucesso depois que envia o video
 *
 * @param string $tribute_hash
 * @param string $invite_hash
 * @return string URL completa
 */
function tribute_get_url_send_video( $tribute_hash, $invite_hash ) {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH . "/{$tribute_hash}/invite/{$invite_hash}/" );
}

/**
 * Pega a URL da pagina de sucesso depois que envia o video
 *
 * @param string $tribute_hash
 * @param string $invite_hash
 * @return string URL completa
 */
function tribute_get_url_send_video_success( $tribute_hash, $invite_hash ) {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH . "/{$tribute_hash}/invite/{$invite_hash}/sucesso" );
}



/**
 * Pega a URL da pagina que faz os convites
 * 
 * @param string $tribute_hash
 * @return string URL completa
 */
function tribute_get_url_final_video( $slug ) {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH . "/video/{$slug}" );
}



//*****************************************/



/**
 * Pega todas as questões cadastradas
 * @return array stdClass $obj->question
 */
function tribute_get_questions() {
    return Tributes_Questions_Model::get_all();
}

/**
 * Pega todas as ocasiões cadastradas
 * @return array stdClass $obj->occasion
 */
function tribute_get_occasions() {
    return Tributes_Occasions_Model::get_all();
}


/**
 * Pega a taxa de sucesso de um Colab pelo tribute_id
 * @param int
 * @return float
 */
function tributes_tax_success_tribute( $tribute_id ) {
    $result_sucess = Tributes_Invites_Model::get_videos_sent_and_not( $tribute_id );
    $sent = $result_sucess->video_sent;
    $not_sent = $result_sucess->video_not_sent;

    if( ( $sent + $not_sent ) == 0 ) {//divisão por zero
        $total_success = 0;
    } else {
        $total_success = ( $sent / ( $sent + $not_sent ) ) * 100;
    }
    return $total_success;
}


/**
 * 
 */
function tributes_get_tribute_status( $tribute )
{
    $tax_sucess = tributes_tax_success_tribute( $tribute->ID );
    if( $tribute->completed == '1' && !empty( $tribute->vimeo_url_file_play ) ) {
        return 'Colab pronto';
    } elseif ( $tribute->completed == '1' && empty( $tribute->vimeo_url_file_play ) ) {
        return 'Processando o Colab';
    } elseif ( $tribute->completed == '0' && $tax_sucess == 100 ) {
        return 'Processando o Colab';
    } else {
        return 'Aguardando videos';
    }
}
