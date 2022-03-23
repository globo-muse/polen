<?php

namespace Polen\Admin\Partials;

if ( ! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if(is_admin())
{
    new Polen_Invite_Talent_Display();
}


class Polen_Invite_Talent_Display extends \WP_List_Table
{

    
    public function prepare_items()
    {
        $this->process_actions();
        $per_page = $this->get_items_per_page( 'name_per_page' );
        $current_page = $this->get_pagenum();
        $search = trim( filter_input( INPUT_GET, 's' ) );
        $newsletter_list = $this->get_name( $search, $this->get_orderby(), $this->get_order(), $current_page, $per_page );
        $total_items = $this->get_name_count( $search );       
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page
        ]);
        
        $this->_column_headers = array( 
            $this->get_columns(),           // columns
            $this->get_hiden_column(),      // hidden
            $this->get_sortable_columns(),  // sortable
        );

        $this->items = $newsletter_list;
    }
    
    /**
     * List all inserted occasions
     */
    public function get_name( $_query = null, $_orderby = null, $_order = 'ASC', int $_limit = 1, int $_offset = 0, $select = null )
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
        
        $query = !empty($_query) ? $wpdb->prepare(" AND (name LIKE '%%%s%%') ", $_query) : '';        
        $sql = $this->make_sql_select( $wpdb, $query, $orderby, $limit, $select );
        $results = $wpdb->get_results( $sql );
        return $results;
    }

    private function make_sql_select( $wpdb, $query, $orderby, $limit, $select = null )
    {
        if( empty( $select ) ) {
            $select = " id, name, page_source, is_mobile, DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') as created_at ";
        }
        return "SELECT {$select} FROM `" . $wpdb->base_prefix . "invite_talent` WHERE (1=1) {$query} {$orderby} {$limit}";
    }
    
    public function get_name_count( $_query = null )
    {
        global $wpdb;
        
        $query = !empty($_query) ? $wpdb->prepare(" AND (name LIKE '%%%s%%') ", $_query) : '';
        $sql = $this->make_sql_count( $wpdb, $query );

        $results = $wpdb->get_var( $sql );
        return $results;
    }
    
    private function make_sql_count( $wpdb, $query )
    {
        return "SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "wp_invite_talent` WHERE (1=1) {$query}";
    }

    public function get_columns()
    {
        return [
            'name' => 'Nome do Talento',
            'page_source' => 'Pagina',
            'is_mobile' => 'Mobile',
            'created_at' => 'Data cadastro',
            'delete' => 'Apagar'
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'nome' => [ 'nome', true ]
        ];
    }

    public function get_hiden_column()
    {
        return [];
    }
    
    private function get_orderby()
    {
        $orderby = trim( filter_input( INPUT_GET, 'orderby' ) );
        return $orderby;
    }
    
    private function get_order()
    {
        $order = trim( filter_input( INPUT_GET, 'order' ) );
        return $order;
    }
    
    public function column_name( $item )
    {
        return sprintf( '%1$s', $item->name );
    }

    public function column_page_source( $item )
    {
        return sprintf( '%1$s', $item->page_source );
    }

    public function column_is_mobile( $item )
    {
        return sprintf( '%1$s', $item->is_mobile );
    }
    
    public function column_created_at($param)
    {   
        return $param->created_at;
    }

    public function column_delete( $item )
    {
        $page = filter_input( INPUT_GET, 'page' );
        $delete_nonce = wp_create_nonce( 'polen_delete_invite' );
        $actions = [
            'delete' => sprintf('<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Apagar', $page, 'delete', $item->id, $delete_nonce )
        ];
        
        return sprintf( '%1$s %2$s', '', $this->row_actions( $actions, true ) );
    }

    public function process_actions(){
        if( 'delete' === $this->current_action() ) {
            if( $_GET['page'] == 'invite_talent' ){
                $nonce = esc_attr( $_REQUEST['_wpnonce'] );
                if ( ! wp_verify_nonce( $nonce, 'polen_delete_invite' ) ) {
                    die( 'Não foi possível apagar o item' );
                }
                else {
                    global $wpdb;
                    if( !empty( $_GET['id'] ) ){
                        $wpdb->delete( $wpdb->prefix.'invite_talent', array('id' => absint( $_GET['id'] ) ) );
                    }
                }

            }
        }
    }
    
}
