<?php

use Polen\Tributes\{ Tributes_Invites_Model, Tributes_Model, Tributes_Rewrite_Rules};

if( !defined( 'ABSPATH' ) ) {
    echo 'Silence is Golden';
    die;
}

/**
 * Pega o path geral de todos os emails
 */
function tributes_get_email_path() {
    return TEMPLATE_DIR . '/tributes/emails/';
}

/**
 * Pega o path do email de envio do convite
 * @return string
 */
function tributes_get_path_email_send_invite() {
    return tributes_get_email_path() . 'invite_send_video.php';
}

/**
 * Pega o path do email de envio do convite
 * @return string
 */
function tributes_get_path_email_sended_complete() {
    return tributes_get_email_path() . 'invites_sended.php';
}

/**
 * Pega o path do email de envio do convite
 * @return string
 */
function tributes_get_path_email_complete_trubute() {
    return tributes_get_email_path() . 'complete_tribute.php';
}

/**
 * Cria o link para o Botao Envie Seu Video no email do invite
 */
function tributes_create_link_email_send_video( $invite_hash ) {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH . "/{$invite_hash}/set-email-clicked" );
}

/**
 * Cria o link da imagem de 1px 1px que seta o email como aberto
 */
function tributes_create_link_set_email_opened( $invite_hash ) {
    return site_url( Tributes_Rewrite_Rules::BASE_PATH . "/{$invite_hash}/set-email-readed" ) . '/';
}

/**
 * Enviar um email do Colab
 * @param string
 * @param string
 * @param string
 */
function tributes_send_email( $email_content, $to_name, $to_email ) {
    global $Polen_Plugin_Settings;
    $headers[] = "From: {$Polen_Plugin_Settings['polen_smtp_from_name']} <{$Polen_Plugin_Settings['polen_smtp_from_email']}>";
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $to = "{$to_name} <{$to_email}>";
    return wp_mail( $to, 'Video Colab Polen.me', $email_content, $headers );
}






/************************************************************
 **************** Conteudo dos Emails ***********************
 ************************************************************/

 const VIDEO_IMAGE = TEMPLATE_URI . "/tributes/assets/img/carousel-video.png";
 const SUCCESS_IMAGE = TEMPLATE_URI . "/tributes/assets/img/check-circle.png";
 const RECORD_IMAGE = TEMPLATE_URI . "/tributes/assets/img/ilustra-gravar.png";
 const EDIT_IMAGE = TEMPLATE_URI . "/tributes/assets/img/ilustra-editar.png";
 const EMOTION_IMAGE = TEMPLATE_URI . "/tributes/assets/img/ilustra-emocionar.png";

 function get_mail_header()
 {
	 $header_file 		= tributes_get_email_path() . "templates/header.php";
	 $header_content 	= file_get_contents($header_file);
	 $logo 				= TEMPLATE_URI . "/tributes/assets/img/logo.png";
	 $header 			= sprintf($header_content, $logo);
	 return $header;
 }

 function get_mail_footer()
 {
	$footer_file 		= tributes_get_email_path() . "templates/footer.php";
	$footer_content 	= file_get_contents($footer_file);
	$logo 				= TEMPLATE_URI . "/tributes/assets/img/logo-black.png";
	$facebook 			= TEMPLATE_URI . "/tributes/assets/img/facebook.png";
	$instagram 			= TEMPLATE_URI . "/tributes/assets/img/instagram.png";
	$tiktok 			= TEMPLATE_URI . "/tributes/assets/img/tiktok.png";
	$footer 			= sprintf($footer_content, $logo, $facebook, $instagram, $tiktok);
	return $footer;
 }

 function translate_date($date)
 {
	 $en_months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	 $pt_months = array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");
	 return str_ireplace($en_months, $pt_months, $date);
 }

/**
 * Cria o conteudo do email de convite
 * @param string
 * @return string
 */
function tributes_email_create_content_invite( $invite_hash ) {
    $path_email = tributes_get_path_email_send_invite();
    $email_content = file_get_contents( $path_email );

    $invites = Tributes_Invites_Model::get_by_hash( $invite_hash );
    $tribute = Tributes_Model::get_by_id( $invites->tribute_id );

    $date = date('d \d\e F \d\e o', strtotime($tribute->deadline));
    $content_formatted = sprintf(
        $email_content,
        $tribute->name_honored,
        $invites->name_inviter,
        $tribute->creator_name,
        $tribute->name_honored,
        translate_date($date),
		RECORD_IMAGE,
		EDIT_IMAGE,
		EMOTION_IMAGE,
        $tribute->creator_name,
        $tribute->welcome_message,
        tributes_create_link_email_send_video( $invites->hash ),
        tributes_create_link_set_email_opened( $invites->hash ),
    );
    return get_mail_header() . $content_formatted . get_mail_footer();
}

/**
 * Cria o conteudo do email de convites enviados
 * @param sdtClass wp_tributes
 * @return string
 */
function tributes_email_content_invites_sended( $tribute ) {
    $path_email = tributes_get_path_email_sended_complete();
    $email_content = file_get_contents( $path_email );

    $invites = Tributes_Invites_Model::get_all_by_tribute_id( $tribute->ID );
    foreach( $invites as $invite ) {
        $names[] = $invite->name_inviter;
    }

    $date = date('d \d\e F \d\e o', strtotime( $tribute->deadline ));
    $content_formatted = sprintf(
        $email_content,
		VIDEO_IMAGE,
        $tribute->creator_name,
        implode( ', ', $names ),
        $date,
        $tribute->creator_name,
        $tribute->welcome_message,
        tribute_get_url_invites( $tribute->hash )
    );
    return get_mail_header() . $content_formatted . get_mail_footer();
}


/**
 * Cria o conteudo do email de completo com sucesso
 * @param sdtClass wp_tributes
 * @return string
 */
function tributes_email_content_complete_tribute( $tribute ) {
    $path_email = tributes_get_path_email_complete_trubute();
    $email_content = file_get_contents( $path_email );

    $invites = Tributes_Invites_Model::get_all_video_sent_by_tribute_id( $tribute->ID );
    foreach( $invites as $invite ) {
        $names[] = $invite->name_inviter;
    }

    $content_formatted = sprintf(
        $email_content,
		SUCCESS_IMAGE,
        $tribute->creator_name,
        implode( ', ', $names ),
        tribute_get_url_final_video( $tribute->slug ),
    );
    return get_mail_header() . $content_formatted . get_mail_footer();
}

/**
 * Cria o conteudo do email de completo com sucesso para os convidados
 * @param sdtClass wp_tributes
 * @return string
 */
function tributes_email_content_complete_tribute_to_invites( $tribute, $invite_param ) {
    $path_email = tributes_get_path_email_complete_trubute();
    $email_content = file_get_contents( $path_email );

    $invites = Tributes_Invites_Model::get_all_video_sent_by_tribute_id( $tribute->ID );
    foreach( $invites as $invite ) {
        $names[] = $invite->name_inviter;
    }

    $content_formatted = sprintf(
        $email_content,
        TEMPLATE_URI . "/tributes/assets/img/check-circle.png",
        $invite_param->name_inviter,
        implode( ', ', $names ),
        tribute_get_url_final_video( $tribute->slug ),
    );
    return get_mail_header() . $content_formatted . get_mail_footer();
}
