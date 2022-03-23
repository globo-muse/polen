<?php
namespace Polen\Includes\Sendgrid;

use SendGrid;
use SendGrid\Mail\Mail;

class Polen_Sendgrid_Emails
{
    public $apiKey = "";

    public $sg;
    public $mail;

    public function __construct( $apiKey )
    {
        $this->apiKey = $apiKey;
        $this->sg = new SendGrid( $this->apiKey );
        $this->mail = new Mail();
    }


    public function send_email()
    {
        $response = $this->sg->send( $this->mail );
        return $response;
    }


    public function set_template_id( $template_id )
    {
        $this->mail->setTemplateId( $template_id );
    }


    public function set_from( $email, $name )
    {
        $this->mail->setFrom( $email, $name );
    }


    public function set_to( $email, $name )
    {
        $this->mail->addTo( $email, $name );
    }


    public function set_template_data( $key, $value )
    {
        $this->mail->addDynamicTemplateData( $key, $value );
    }

}
