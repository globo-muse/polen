<?php

namespace Polen\Includes;

use Polen\Includes\Emails\Polen_Email_Signin_Prerelease;
use \Polen\Admin\Partials\Polen_Newsletter_Display;

include_once WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-settings-api.php';
include_once WP_PLUGIN_DIR . '/woocommerce/includes/emails/class-wc-email.php';
include_once WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-emails.php';
class Polen_Signin_Newsletter
{
    public function __construct( $static = false ) {
        if( $static ) {
            add_action( 'admin_menu', array( $this, 'signin_newsletter_menu' ) );
            add_action( 'wp_ajax_polen_newsletter_signin', array( $this, 'newsletter_signin' ) );
            add_action( 'wp_ajax_nopriv_polen_newsletter_signin', array( $this, 'newsletter_signin' ) );
            add_filter( 'query_vars', array( $this, 'query_vars' ) );
            add_action( 'parse_request', array( $this, 'parse_request' ) );

            if( isset( $_GET['export_newsletter_emails'] ) && $_GET['export_newsletter_emails'] == true ){
                $csv = $this->export_to_csv();
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; filename=\"report.csv\";");
                header("Content-Transfer-Encoding: binary");
    
                echo $csv;
                exit;
            }
        }
    }
    
    public function signin_newsletter_menu(){
        $hook = add_menu_page(
                'Newsletter',
                'Newsletter',
                'manage_options',
                'signin-newsletter',
                array( $this, 'list_newsletter_emails' ),
                'dashicons-email');       
        
        add_action( "load-$hook", [ $this, 'add_options' ] );

        add_submenu_page(
            'signin-newsletter',
            'Exportar',
            'Exportar',
            'manage_options',
            'download_report',
            array( $this, 'export_email_newsletter') );
    }
    
    public function add_options()
    {
        $option = 'per_page';
        $args = array(
            'label' => 'Itens por pagina',
            'default' => 10,
            'option' => 'email_per_page'
        );
        add_screen_option( $option, $args );
    }

    public function newsletter_signin(){
        $nonce = esc_attr( $_POST['security'] );
        $email = trim( $_POST['email'] );
        $event = trim( $_POST['event'] );
        $page_source = trim( $_POST['page_source'] );
        $is_mobile = trim( $_POST['is_mobile'] );

        if ( ! wp_verify_nonce( $nonce, 'news-signin' ) ) {
            wp_send_json_error( array( 'response' => 'Não foi possível completar a solicitação' ), 403 );
            wp_die();
        }

        if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            wp_send_json_error( array( 'response' => 'Email inválido' ), 403 );
            wp_die();
        }
    
        if( isset( $email ) && !empty( $email ) ) {
            $newsletter = $this->set_email_to_newsletter( $email, $event, $page_source, $is_mobile );
            if( !empty( $newsletter ) ){
                wp_send_json_success( array( 'response' => $newsletter ), 201 );
                wp_die();
            } else {
                wp_send_json_error( array( 'response' => $newsletter ), 403 );
                wp_die();
            }
        } else {
            wp_send_json_error( array( 'response' => 'Não foi possível completar a solicitação' ), 403 );
            wp_die();
        }
    }


    /**
     * Insert email to newsletter table
     */
    public function set_email_to_newsletter( $email, $event, $page_source, $is_mobile = "0" ){
        if( !empty( $email ) ){
            global $wpdb;
            $exists = $this->check_already_inserted( $email );
            if( !$exists ){
                $insert_args = array( 'email' => $email, 'event' => $event, 'page_source' => $page_source, 'is_mobile' => $is_mobile ) ;
                $inserted = $wpdb->insert( $wpdb->base_prefix."newsletter_emails", $insert_args);

                if( $inserted > 0 ){
                    //$this->export_occasion_json();
                    return "Email Cadastrado!";
                }else{
                    return "Ocorreu um erro ao tentar cadastrar";
                }
            }else{
                return "E-mail já cadastrado";
            } 
        }else{
            return "Não foi possível realizar o cadastro";
        }   
    }
    
    /**
     * Checar se o e-mail já está cadastrado
     */
    public function check_already_inserted( $email ){
        global $wpdb;
        $sql_prepared = $wpdb->prepare( "SELECT COUNT(*) total  FROM `" . $wpdb->base_prefix . "newsletter_emails` WHERE email = '%s'", trim( $email ) );
        $inserted = $wpdb->get_var( $sql_prepared );
        if( (int) $inserted > 0 ){
            return true;
        }else{
            return false;
        }
    }
    
    public function list_newsletter_emails()
    {         
        if( isset( $_GET['export'] ) &&  $_GET['export'] == 'true' ){
            $this->export_to_csv();
        }

        $newsletter_display = new Polen_Newsletter_Display();
        $newsletter_display->prepare_items();
        echo '<div class="wrap">';
        echo '<div id="icon-users" class="icon32"></div>';
        echo '<h1 class="wp-heading-inline">' . translate('E-mails da Newsletter') . '</h1>';
        echo '<hr class="wp-header-end">';
        $newsletter_display->link_export_email_to_csv();
        $newsletter_display->show_form_search_email();
        $newsletter_display->views();
        $newsletter_display->display();
        echo '</div>';
    }
   
    public function query_vars($query_vars) {
        $query_vars[] = 'download_report';
        return $query_vars;
    }

    public function parse_request(&$wp) {
        if( array_key_exists( 'download_report', $wp->query_vars ) ) {
            $this->download_report();
            exit;
        }
    }
  
    public function export_email_newsletter() {  ?>
        <div class="wrap">
            <div id="icon-tools"></div>
            <h2>Exportar E-mails Cadastrados para Newsletter</h2>
            <p><a href="?page=download_report&export_newsletter_emails=true">Exportar</a></p>
        </div>
    <?php        
    }

    public function export_to_csv() {
        global $wpdb;
        $csv_output = '';
        $sql = "SELECT email, DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') as created_at FROM wp_newsletter_emails ";
        $values = $wpdb->get_results( $sql );

        foreach( $values as $row ):
            $csv_output .= $row->email . ",".$row->created_at. PHP_EOL;
        endforeach;    
        $csv_output .= "\n";

        return $csv_output;
    }

}
