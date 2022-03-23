<?php

namespace Polen\Admin\Partials\Occasions;

use \Polen\Includes\Polen_Occasion_List;

class Polen_Admin_Occasions_Display extends \WP_List_Table
{

    public function prepare_items()
    {
        $this->process_actions();

        $per_page = $this->get_items_per_page( 'occasion_per_page' );
        $current_page = $this->get_pagenum();
        
        $search = trim( filter_input( INPUT_GET, 's' ) );
        $occasion_list_repository = new Polen_Occasion_List();
        $occasion_list = $occasion_list_repository->get_occasion( $search, $this->get_orderby(), $this->get_order(), $current_page, $per_page );

        $total_items = $occasion_list_repository->get_occasion_count( $search );
//        
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page
        ]);
        
        $this->_column_headers = array( 
            $this->get_columns(),           // columns
            $this->get_hiden_column(),      // hidden
            $this->get_sortable_columns(),  // sortable
        );
        
        $this->items = $occasion_list;
    }
    
    
    public function get_columns()
    {
        return [
            'type' => 'Tipo',
            'description' => 'Descrição'
        ];
    }
    
    
    public function get_hiden_column()
    {
        return [ 'criated_at' => 'Dt Criação', 'updated_at' => 'Dt Atualização' ];
    }
    
    
    public function get_sortable_columns()
    {
        return [
            'type' => [ 'type', true ]
        ];
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
    
    
    public function column_type( $item )
    {

        $page = filter_input( INPUT_GET, 'page' );
        $delete_nonce = wp_create_nonce( 'occasion_delete_customer' );
        $actions = [
            'delete' => sprintf('<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Apagar', $page, 'delete', $item->id, $delete_nonce )
        ];
        return sprintf( '%1$s %2$s', $item->type, $this->row_actions( $actions, true ) );
    }
    
    
    public function column_description($param)
    {
        return $param->description;
    }
    

    /**
     * Mostra o form de adicao de uma Occasion
     */
    public function show_form_create_occasion()
    { ?>
        <div class="form-wrap">
            <form class="validate" method="post" action="">
                <?php wp_nonce_field('occasion_new', '_wpnonce', true, true); ?>
                <div class="form-field form-required term-name-wrap">
                    <label for="occasion_category">Ocasião</label>
                    <input name="occasion_category" id="occasion_category" type="text" value="" size="45" aria-required="true">
                    <p>Nome da ocasião.</p>
                </div>
                <div class="form-field term-description-wrap">
                        <label for="occasion_description">Descrição</label>
                        <input name="occasion_description" id="occasion_description" type="text" value="" size="45" aria-required="true"/>
                        <p>Texto de inspiração para usuário escolher e alterar na hora da compra.</p>
                </div>
                <?php submit_button( 'Cadastrar', 'primary', 'submit', true, array( 'id' => 'submit-add-occasion' ) ); ?>
            </form>
        </div>
    <?php
    }
    
    
    
    /**
     * Metodo que apresenta o search_box da WP_List_Table
     * @param Polen_Admin_Occasions_Display $occasion_display
     */
    public function show_form_search_occasion()
    {
        $page = esc_attr( $_REQUEST['page'] );
        
        echo '<form action="" method="GET">';
        echo "<input type=\"hidden\" name=\"page\" value=\"{$page}\"/>";
        $this->search_box( 'Occasions Search', 'search_occasion' );
        echo '</form>';
    }
    
    
    /**
     * Sends required variables to JavaScript land.
     *
     * @since 3.1.0
     */
//    public function _js_vars() {
//        $args = array(
//            'class' => get_class($this),
//            'screen' => array(
//                'id' => $this->screen->id,
//                'base' => $this->screen->base,
//            ),
//        );
//
//        printf("<script type='text/javascript'>list_args = %s;</script>\n", wp_json_encode($args));
//    }

    public function process_actions(){
        global $wpdb;

        if( 'delete' === $this->current_action() ) {
            if( $_GET['page'] == 'occasion-list' ){
                $nonce = esc_attr( $_REQUEST['_wpnonce'] );

                if ( ! wp_verify_nonce( $nonce, 'occasion_delete_customer' ) ) {
                die( 'Não foi possível apagar o item' );
                }
                else {
                    if( !empty( $_GET['id'] ) ){
                        $wpdb->delete( $wpdb->prefix.'occasion_list', array('id' => absint( $_GET['id'] ) ) );
                    }
                }

            }
        }
    }

}
