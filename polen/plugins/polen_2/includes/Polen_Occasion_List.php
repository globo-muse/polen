<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Polen\Includes;

use \Polen\Admin\Partials\Occasions\Polen_Admin_Occasions_Display;


class Polen_Occasion_List
{
    
    public function __construct( $static = false ) {
        if( $static ) {
            add_action( 'admin_menu', [ $this, 'menu_occasion_list' ] );
            add_action( 'wp_ajax_get_occasion_description', array( $this, 'get_occasion_description' ) );
            add_action( 'wp_ajax_nopriv_get_occasion_description', array( $this, 'get_occasion_description' ) );
            //add_action( 'wp_ajax_update_occasion_item', array( $this, 'update_occasion_on_cad' ) ); 
            add_filter( 'set-screen-option', [ $this, 'polen_occasion_table_set_option' ], 10, 3 );
        }
    }

    /**
     * Add occasion menu
     */
    public function menu_occasion_list(){
        $hook = add_menu_page(
                'Categorias de Vídeo - Cadastro',
                'Categorias de Vídeos',
                'manage_options',
                'occasion-list',
                [ $this, 'list_inserted_occasion' ],
                'dashicons-editor-alignright');
        
        add_action( "load-$hook", [ $this, 'add_options' ] );
    }
    
    public function add_options()
    {
        $option = 'per_page';
        $args = array(
            'label' => 'Itens por pagina',
            'default' => 10,
            'option' => 'occasion_per_page'
        );
        add_screen_option( $option, $args );
    }
    
    
    /**
     * 
     * @param type $status
     * @param type $option
     * @param type $value
     * @return type
     */
    public function polen_occasion_table_set_option( $status, $option, $value )
    {
        return $value;
    }

    
    
    /**
     * Screen to insert occasion and description
     */
    public function list_inserted_occasion()
    { 

        $this->handler_post_create_occasion();
        
        $occasion_display = new Polen_Admin_Occasions_Display();
        $occasion_display->prepare_items();

        echo '<div class="wrap">';
        echo '<div id="icon-users" class="icon32"></div>';
        echo '<h1 class="wp-heading-inline">' . translate('Ocasiões de Vídeo') . '</h1>';
        echo '<hr class="wp-header-end">';
        $occasion_display->show_form_create_occasion();
        $occasion_display->show_form_search_occasion();
        $occasion_display->views();
        $occasion_display->display();
        
        echo '</div>';
//        require_once ABSPATH . 'wp-admin/admin-footer.php';
    }
    
    
    
    /**
     * Metodo chamado quando uma tentativa de inserir uma Occasion
     */
    public function handler_post_create_occasion()
    {
        $occasion_category = trim( filter_input( INPUT_POST, 'occasion_category', FILTER_FLAG_EMPTY_STRING_NULL ) );
        $occasion_description = trim( filter_input( INPUT_POST, 'occasion_description', FILTER_SANITIZE_STRING ) );
        if( isset( $_POST['_wpnonce']) )
            $_wpnonce = wp_verify_nonce( $_POST['_wpnonce'], 'occasion_new' );
        
        if( !empty($occasion_category) && !empty($occasion_description) && $_wpnonce === 1 ) {
            $this->set_occasion( $occasion_category, $occasion_description );
            
        }
    }

    /**
     * List all inserted occasions
     */
    public function get_occasion( $_query = null, $_orderby = null, $_order = 'ASC', int $_limit = 1, int $_offset = 0, $select = null )
    {
        global $wpdb;

        $order = ($_order === 'ASC') ? 'ASC' : 'DESC';
        $orderby = ($_orderby === 'type') ? " ORDER BY $_orderby $order " : "";
        
        $offset = ( !empty( $_offset )) ? ",{$_offset}" : '';
        $paged = ($_limit - 1) * $_offset;

        $limit = '';
        if( !empty( $_offset ) ){
            $limit = ( !empty( $_limit )) ? "LIMIT {$paged}{$offset}" : '';
        }
        
        $query = !empty($_query) ? $wpdb->prepare(" AND (type LIKE '%%%s%%') ", $_query) : '';        
        $sql = $this->make_sql_select( $wpdb, $query, $orderby, $limit, $select );
        $results = $wpdb->get_results( $sql );
        return $results;
    }
    
    /**
     * 
     * @param $wpdb
     * @param type $query
     * @param type $orderby
     * @param type $limit
     * @param string $select
     * @return type
     */
    private function make_sql_select( $wpdb, $query, $orderby, $limit, $select = null )
    {
        if( empty( $select ) ) {
            $select = '*';
        }
        return "SELECT {$select} FROM `" . $wpdb->base_prefix . "occasion_list` WHERE (1=1) {$query} {$orderby} {$limit}";
    }
    
    
    public function get_occasion_count( $_query = null )
    {
        global $wpdb;
        
        $query = !empty($_query) ? $wpdb->prepare(" AND (type LIKE '%%%s%%') ", $_query) : '';
        $sql = $this->make_sql_count( $wpdb, $query );

        $results = $wpdb->get_var( $sql );
        return $results;
    }
    
    
    private function make_sql_count( $wpdb, $query )
    {
        return "SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "occasion_list` WHERE (1=1) {$query}";
    }
    
    
    /**
     * 
     * @global type $wpdb
     * @param string $type
     * @param string $orderby
     * @param string $order
     * @return type
     */
    public function get_occasion_by_type( string $type, $refresh = null ){
        global $wpdb;
        
        if( !empty( $refresh ) ){
            $refresh = " ORDER BY RAND() ";
        }

        $sql = "SELECT type, description FROM `" . $wpdb->base_prefix . "occasion_list` WHERE type = %s {$refresh} LIMIT 1" ;
        $sql_prepared = $wpdb->prepare( $sql, trim( $type ) );
        $results = $wpdb->get_results( $sql_prepared );  
        return $results;
    }

    /**
     * Insert occasion
     */
    public function set_occasion( $type, $description ){
        if( !empty( $type ) && !empty( $description ) ){
            global $wpdb;
            $exists = $this->check_already_inserted( $type, $description );
            if( !$exists ){
                $inserted = $wpdb->insert( $wpdb->base_prefix."occasion_list", array( 'type' => trim( $type ), 'description' => trim( $description ) ) );

                if( $inserted > 0 ){
                    $this->export_occasion_json();
                    return "Cadastrado com sucesso!";
                }else{
                    return "Ocorreu um erro ao tentar cadastrar";
                }
            }else{
                return "Registro já cadastrado";
            } 
        }else{
            return "Está faltando dados";
        }   
    }
    
    /**
     * Checar se a occasion existe
     * 
     * @global \Polen\Includes\type $wpdb
     * @param type $type
     * @param type $description
     * @return boolean
     */
    public function check_already_inserted( $type, $description ){
        global $wpdb;
        $sql_prepared = $wpdb->prepare("SELECT COUNT(*) total  FROM `" . $wpdb->base_prefix . "occasion_list` WHERE type = %s AND description = %s;", trim( $type ), trim( $description ));
        $inserted = $wpdb->get_var( $sql_prepared );
        if( (int) $inserted > 0 ){
            return true;
        }else{
            return false;
        }
    }
    

    public function get_occasion_description(){
        if( isset( $_POST['occasion_type'] ) ){
            $refresh = null;
            if( isset( $_POST['refresh'] ) && $_POST['refresh'] == 1 ){
                $refresh = 1;
            }

            $occasion = $this->get_occasion_by_type( $_POST['occasion_type'], $refresh );

            if( !empty( $occasion ) ){
                echo wp_json_encode( array( 'success' => 1, 'response' => $occasion ) );
                die;
            }
        }
    }
    
    
    /**
     * Exporta occasions em um arquivo json
     * @param array $occasions
     * @return boolean
     */
    public function export_occasion_json( array $occasions = null )
    {
        if( empty( $occasions ) ) {
            $occasions = $this->get_occasion( null, 'type', 'ASC', 1, 0, 'id, type, description' );
        }
        $path_file = $this->get_path_occasion_json();
        $occasions_json = json_encode( $occasions );
        $file = fopen( $path_file, 'w' );
        fwrite( $file, $occasions_json );
        fclose( $file );
        return true;
    }
    
    
    /**
     * Pega o caminho do arquivo JSON
     * @return type
     */
    public function get_path_occasion_json()
    {
       return ABSPATH . '/occasions.json'; 
    }
    
    
    /**
     * Pega a URL do arquivo occasion.json
     * @return type
     */
    public function get_url_occasion_json()
    {
        return site_url('occasion.json');
    }

    /*
  CREATE TABLE `db_polen`.`wp_occasion_list` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(45) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_520_ci' NULL,
  `description` TEXT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_520_ci' NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`));
    */

}
