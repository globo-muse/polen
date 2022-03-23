<?php

namespace Polen\Admin\Partials;
defined( 'ABSPATH' ) || die;

use Polen\Tributes\Tributes_Controller;
use Polen\Tributes\Tributes_Model;

if ( ! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if(is_admin())
{
    new Tributes_Display();
}


class Tributes_Display extends \WP_List_Table
{

    
    public function prepare_items()
    {
        $per_page = $this->get_items_per_page( 'tributes_per_page' );
        $current_page = $this->get_pagenum();
        $search = trim( filter_input( INPUT_GET, 's' ) );
        $newsletter_list = $this->get_name();
        $total_items = $this->get_name_count();       
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
    public function get_name()
    {
        $results = Tributes_Model::get_all();
        return $results;
    }
    
    public function get_name_count( $_query = null )
    {
        $results = Tributes_Model::get_total();
        return $results;
    }

    public function get_columns()
    {
        return [
            'name_honored' => 'P/Quem É',
            'slug' => 'Slug',
            'occasion' => 'Ocasião',
            'creator_name' => 'Nome do resp.',
            'deadline' => 'Dt Final',
            'tx_success' => 'Tx Sucesso',
            'links_download' => 'Download',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            // 'deadline' => [ 'deadline', true ]
        ];
    }

    public function get_hiden_column()
    {
        return [
            'creator_email' => 'Email resp.',
            'welcome_message' => 'Instrução',
        ];
    }
    
    private function get_orderby()
    {
        // $orderby = trim( filter_input( INPUT_GET, 'orderby' ) );
        // return $orderby;
        return '';
    }
    
    private function get_order()
    {
        // $order = trim( filter_input( INPUT_GET, 'order' ) );
        // return $order;
        return '';
    }
    
    public function column_name_honored( $item )
    {
        $url = admin_url( '/admin.php' ) . "?page=tributes_details&tribute_id={$item->ID}";
        return sprintf( '<a href="%s">%s<a/>', $url, $item->name_honored );
    }

    public function column_slug( $item )
    {
        return sprintf( '%1$s', $item->slug );
    }

    public function column_occasion( $item )
    {
        return sprintf( '%1$s', $item->occasion );
    }
    
    public function column_creator_name($param)
    {   
        return $param->creator_name;
    }

    public function column_creator_email($param)
    {   
        return $param->creator_email;
    }

    public function column_welcome_message($param)
    {   
        return $param->welcome_message;
    }

    public function column_deadline($param)
    {
        $date = date( 'd/m/Y', strtotime( $param->deadline ) );
        return $date;
    }

    public function column_links_download($param)
    {
        $link_end_point = admin_url( 'admin-ajax.php?action=tribute_get_links_downloads' );
        $nonce = wp_create_nonce( Tributes_Controller::NONCE_ACTION_GET_LINKS_DOWNLOADS );
        return sprintf( '<a class="link-downloads-btn" data-tribute-id="%2$s" nonce="%3$s" href="%1$s">Downloads</a>', $link_end_point, $param->ID, $nonce );
    }

    public function column_tx_success($item)
    {
        $tx_success_value = tributes_tax_success_tribute( $item->ID );
        return sprintf( '%1$s', $tx_success_value ) . '%';
    }
    
}
