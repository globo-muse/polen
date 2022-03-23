<?php

namespace Polen\Includes\Emails;

class Polen_Email_Signin_Prerelease
{

    public function __construct()
    {
        $this->subject = sprintf( 'Você está na lista de espera da %s', get_bloginfo( 'name' ) );
        $this->template_base  = TEMPLATEPATH . '/woocommerce/';
    }

    function css( $css, $email ) {
        ob_start();
        include $this->template_base . 'emails/email-styles.php';
        $this->include = true;
        $css = ob_get_contents();
        ob_end_clean();
        return $css;
    }

	function trigger( $email )
    {
        // add_action( 'woocommerce_email_header', array( $this, 'email_css' ) );
        add_filter( 'woocommerce_email_styles', array( $this, 'css' ), 999, 2 );

        global $woocommerce;
        $mailer = $woocommerce->mailer();
        $message = $this->get_content_html( $email );
        $mailer->send( $email, $this->subject, $message );
    }

    public function get_content_html( $email )
    {
        return wc_get_template_html( 'emails/Polen_Signin_Prerelease.php', array( 'email_heading' => 'Prelançamento', 'email' => $email ), '', $this->template_base );
	}
}

new Polen_Email_Signin_Prerelease();