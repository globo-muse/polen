<?php

namespace Polen\Includes;

use Polen\Admin\Partials\Polen_Invite_Talent_Display;
use Polen\Includes\Invite_Talent\Polen_Invite_Talent_Model;

include_once WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-settings-api.php';
include_once WP_PLUGIN_DIR . '/woocommerce/includes/emails/class-wc-email.php';
include_once WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-emails.php';

class Polen_Invite_Talent
{
    public function __construct( $static = false ) {
        if( $static ) {
            add_action( 'admin_menu', array( $this, 'signin_invite_talent_menu' ) );
            add_action( 'wp_ajax_polen_invite_talent', array( $this, 'invite_talent' ) );
            add_action( 'wp_ajax_nopriv_polen_invite_talent', array( $this, 'invite_talent' ) );
        }
    }

    
    /**
     * Admin Menu
     */
    public function signin_invite_talent_menu(){
        $hook = add_menu_page(
                'Convidar Artista',
                'Convidar Artista',
                'manage_options',
                'invite_talent',
                array( $this, 'list_invite_talent' ),
                'dashicons-buddicons-replies');       
        
        add_action( "load-$hook", [ $this, 'add_options' ] );
    }
    

    /**
     * Add Opcaoes na pagina do Admin
     */
    public function add_options()
    {
        $option = 'per_page';
        $args = array(
            'label' => 'Itens por pagina',
            'default' => 10,
            'option' => 'name_per_page'
        );
        add_screen_option( $option, $args );
    }


    /**
     * Handler do Admin apresentação da pagina
     */
    public function invite_talent(){
        $nonce = esc_attr( $_POST['security'] );
        $talent_name = trim( $_POST['talent_name'] );
        $page_source = trim( $_POST[ 'page_source' ] );
        $is_mobile = trim( $_POST[ 'is_mobile' ] );

        if ( ! wp_verify_nonce( $nonce, 'invite_talent' ) ) {
            wp_send_json_error( array( 'response' => 'Não foi possível completar a solicitação' ), 403 );
            wp_die();
        }
    
        if( isset( $talent_name ) && !empty( $talent_name ) ) {
            $invite = new Polen_Invite_Talent_Model();
            $invite->name = $talent_name;
            $invite->page_source = $page_source;
            $invite->is_mobile = $is_mobile;
            $invite->insert();
            wp_send_json_success( array( 'response' => 'Sua dica de talento foi cadastrada, faremos o possivel para convida-lo' ), 403 );
            wp_die();
        } else {
            wp_send_json_error( array( 'response' => 'Precisamos saber o nome do seu talento favorito' ), 403 );
            wp_die();
        }
    }

    
    public function list_invite_talent()
    {
        $newsletter_display = new Polen_Invite_Talent_Display();
        $newsletter_display->prepare_items();
        echo '<div class="wrap">';
        echo '<div id="icon-users" class="icon32"></div>';
        echo '<h1 class="wp-heading-inline">' . translate('Convidar Artistas') . '</h1>';
        echo '<hr class="wp-header-end">';
        // $newsletter_display->link_export_email_to_csv();
        $newsletter_display->show_form_search_email();
        $newsletter_display->views();
        $newsletter_display->display();
        echo '</div>';
    }

}
