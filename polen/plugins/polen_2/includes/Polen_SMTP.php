<?php

namespace Polen\Includes;

if( ! defined( 'ABSPATH' ) ) {
    die( 'Silence is golden.' );
}

class Polen_SMTP
{
    
    public function __construct( $static = false ) {
        if( $static ) {
            add_action( 'phpmailer_init', array( $this, 'load_settings' ) );
        }
    }

    public function load_settings() {
        global $Polen_Plugin_Settings;
        if( $Polen_Plugin_Settings && isset( $Polen_Plugin_Settings['polen_smtp_on'] ) && (int) $Polen_Plugin_Settings['polen_smtp_on'] === (int) 1 ) {
            global $phpmailer;
            $phpmailer->isSMTP();
            $phpmailer->SMTPAuth   = true;
            $phpmailer->SMTPSecure = 'tls';
            $phpmailer->Host       = $Polen_Plugin_Settings['polen_smtp_host'];
            $phpmailer->Port       = $Polen_Plugin_Settings['polen_smtp_port'];
            $phpmailer->Username   = $Polen_Plugin_Settings['polen_smtp_user'];
            $phpmailer->Password   = $Polen_Plugin_Settings['polen_smtp_pass'];
            $phpmailer->From       = $Polen_Plugin_Settings['polen_smtp_from_email'];
            $phpmailer->FromName   = $Polen_Plugin_Settings['polen_smtp_from_name'];
        }
    }

}