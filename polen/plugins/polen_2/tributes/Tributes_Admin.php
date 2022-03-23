<?php

namespace Polen\Tributes;

use Polen\Admin\Partials\Tributes_Display;

class Tributes_Admin
{
    public function __construct( $static = false )
    {
        if( $static ) {
            add_action( 'admin_menu', array( $this, 'tributes_add_admin_menu' ) );
        }
    }

    /**
     * Admin Menu
     */
    public function tributes_add_admin_menu(){
        $hook = add_menu_page(
                'Colabs',
                'Colabs',
                'manage_options',
                'tributes',
                array( $this, 'list_tritutes' ),
                'dashicons-images-alt');       
        
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
            'option' => 'tributes_per_page'
        );
        add_screen_option( $option, $args );
    }

    public function list_tritutes()
    {
        $newsletter_display = new Tributes_Display();
        $newsletter_display->prepare_items();
        echo '<div class="wrap">';
        echo '<div id="icon-users" class="icon32"></div>';
        echo '<h1 class="wp-heading-inline">' . translate('Colabs') . '</h1>';
        echo '<hr class="wp-header-end">';
        $newsletter_display->show_form_search_email();
        $newsletter_display->views();
        $newsletter_display->display();
        echo '</div>';
    }
}