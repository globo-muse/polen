<?php

namespace Polen\Includes;

// use Polen\Includes\Emails\Polen_Email_Signin_Prerelease;
// use \Polen\Admin\Partials\Polen_Newsletter_Display;

include_once WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-settings-api.php';
include_once WP_PLUGIN_DIR . '/woocommerce/includes/emails/class-wc-email.php';
include_once WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-emails.php';
class Polen_Signin_LP_Lead
{
    public function __construct( $static = false ) {
        if( $static ) {
            add_action( 'admin_menu', array( $this, 'signin_lp_lead_menu' ) );
            add_action( 'wp_ajax_polen_signin_lp_lead', array( $this, 'handler_ajax_signin_lp_lead' ) );
            add_action( 'wp_ajax_nopriv_polen_signin_lp_lead', array( $this, 'handler_ajax_signin_lp_lead' ) );

            //ROUTES /lp/SLUG-ARTISTA
            add_action( 'init',             array( $this, 'rewrites' ) );
            add_filter( 'query_vars',       array( $this, 'query_vars' ) );
            add_action( 'template_include', array( $this, 'template_include' ) );

            // if( isset( $_GET['export_signin_lp_lead'] ) && $_GET['export_signin_lp_lead'] == true ){
            //     $csv = $this->export_to_csv();
            //     header("Pragma: public");
            //     header("Expires: 0");
            //     header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            //     header("Cache-Control: private", false);
            //     header("Content-Type: application/octet-stream");
            //     header("Content-Disposition: attachment; filename=\"report.csv\";");
            //     header("Content-Transfer-Encoding: binary");
    
            //     echo $csv;
            //     exit;
            // }
        }
    }


    /**
     * Rewrite Rules lp/sku-talent
     */
    public function rewrites()
    {
        add_rewrite_rule( 'lp/([^/]*)/?/success', 'index.php?lp_product_sku=$matches[1]&lp_signin_success=1', 'top' );
        add_rewrite_rule( 'lp/([^/]*)/?',         'index.php?lp_product_sku=$matches[1]', 'top' );
    }

    public function query_vars( $query_vars )
    {
        $query_vars[] = 'lp_product_sku';
        $query_vars[] = 'lp_signin_success';
        return $query_vars;
    }

    public function template_include( $template )
    {
        if ( get_query_var( 'lp_product_sku' ) == false || get_query_var( 'lp_product_sku' ) == '' ) {
            return $template;
        }
        $GLOBALS['lp_sigin_lead'] = true;

        $product_sku = get_query_var( 'lp_product_sku' );
        $product_id = wc_get_product_id_by_sku(['sku' => $product_sku]);
       if( empty( $product_id ) ) {
           wp_redirect( site_url() );
           exit;
       }

        wp_enqueue_script('landpage-scripts');
        return get_template_directory() . '/landin-page-20210628.php';

    }

    public function table_name() {
        global $wpdb;
        return $wpdb->base_prefix . 'signin_lps';
    }
    
    public function signin_lp_lead_menu(){
        $hook = add_menu_page(
                'Cadastros LP',
                'Cadastros LP',
                'manage_options',
                'signin-lp_lead',
                array( $this, 'list_signin_lp_lead' ),
                'dashicons-email');       
        
        add_action( "load-$hook", [ $this, 'add_options' ] );

        add_submenu_page(
            'signin-lp-lead',
            'Exportar',
            'Exportar',
            'manage_options',
            'download_report',
            array( $this, 'export_lp_lead') );
    }
    
    public function add_options()
    {
        $option = 'per_page';
        $args = array(
            'label' => 'Itens por pagina',
            'default' => 10,
            'option' => 'signin_per_page'
        );
        add_screen_option( $option, $args );
    }

    public function handler_ajax_signin_lp_lead(){
        $nonce = $_POST['security'];

        $fan_name     = filter_input( INPUT_POST, 'fan_name' );
        $fan_email    = filter_input( INPUT_POST, 'fan_email' );
        $product_id   = filter_input( INPUT_POST, 'product_id' );
        $page_source  = filter_input( INPUT_POST, 'page_source' );
        $is_mobile    = filter_input( INPUT_POST, 'is_mobile' );
        $category     = filter_input( INPUT_POST, 'category' );
        $tags         = filter_input( INPUT_POST, 'tags' );
        $utm_source   = filter_input( INPUT_POST, 'utm_source' );
        $utm_medium   = filter_input( INPUT_POST, 'utm_medium' );
        $utm_campaign = filter_input( INPUT_POST, 'utm_campaign' );

        if ( ! wp_verify_nonce( $nonce, 'landpage-signin' ) ) {
            wp_send_json_error( array( 'response' => 'Não foi possível completar a solicitação' ), 403 );
            wp_die();
        }

        if( !filter_var( $fan_email, FILTER_VALIDATE_EMAIL ) ) {
            wp_send_json_error( array( 'response' => 'Email inválido' ), 403 );
            wp_die();
        }
    
        if( isset( $fan_email ) && !empty( $fan_email ) ) {
            $newsletter = $this->set_email_to_newsletter(
                $fan_name,
                $fan_email,
                $product_id,
                $page_source,
                $is_mobile,
                $category,
                $tags,
                $utm_source,
                $utm_medium,
                $utm_campaign
            );
            if( !empty( $newsletter ) ){
                if( $newsletter == "Te enviamos um email com mais informações") {
                    wp_send_json_success( array( 'response' => $newsletter ), 201 );
                    wp_die();
                } else {
                    wp_send_json_error( array( 'response' => $newsletter ), 403 );
                    wp_die();
                }
            }
        } else {
            wp_send_json_error( array( 'response' => 'Não foi possível completar a solicitação(2)' ), 403 );
            wp_die();
        }
    }


    /**
     * Insert email to newsletter table
     */
    public function set_email_to_newsletter(
        $fan_name,
        $fan_email,
        $product_id,
        $page_source,
        $is_mobile,
        $category,
        $tags,
        $utm_source,
        $utm_medium,
        $utm_campaign
    ) {
        if( !empty( $fan_email ) ){
            global $wpdb;
            $insert_args = array (
                'fan_name'     => $fan_name,
                'fan_email'    => $fan_email,
                'product_id'   => $product_id,
                'page_source'  => $page_source,
                'is_mobile'    => $is_mobile,
                'category'     => $category,
                'tags'         => $tags,
                'utm_source'   => $utm_source,
                'utm_medium'   => $utm_medium,
                'utm_campaign' => $utm_campaign
            );
            $inserted = $wpdb->insert( $this->table_name(), $insert_args);

            if( $inserted > 0 ) {
                return "Te enviamos um email com mais informações";
            } else {
                return "Ocorreu um erro ao tentar cadastrar";
            }
        } else {
            return "Não foi possível realizar o cadastro";
        }   
    }

    
    // public function list_signin_lp()
    // {         
    //     if( isset( $_GET['export'] ) &&  $_GET['export'] == 'true' ){
    //         $this->export_to_csv();
    //     }

    //     $newsletter_display = new Polen_Newsletter_Display();
    //     $newsletter_display->prepare_items();
    //     echo '<div class="wrap">';
    //     echo '<div id="icon-users" class="icon32"></div>';
    //     echo '<h1 class="wp-heading-inline">' . translate('E-mails da Newsletter') . '</h1>';
    //     echo '<hr class="wp-header-end">';
    //     $newsletter_display->link_export_email_to_csv();
    //     $newsletter_display->show_form_search_email();
    //     $newsletter_display->views();
    //     $newsletter_display->display();
    //     echo '</div>';
    // }
   

    // public function parse_request(&$wp) {
    //     if( array_key_exists( 'download_report', $wp->query_vars ) ) {
    //         // $this->download_report();
    //         exit;
    //     }
    // }
  

    // public function export_to_csv() {
    //     global $wpdb;
    //     $csv_output = '';
    //     $sql = "SELECT email, DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') as created_at FROM wp_newsletter_emails ";
    //     $values = $wpdb->get_results( $sql );

    //     foreach( $values as $row ):
    //         $csv_output .= $row->email . ",".$row->created_at. PHP_EOL;
    //     endforeach;    
    //     $csv_output .= "\n";

    //     return $csv_output;
    // }

}
