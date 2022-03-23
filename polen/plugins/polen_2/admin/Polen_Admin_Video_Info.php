<?php
namespace Polen\Admin;

use Polen\Admin\Partials\Polen_Video_Info_Display;

defined( 'ABSPATH' ) || die;

class Polen_Admin_Video_Info
{
    public function __construct( $static = false )
    {
        if( $static ) {
            $this->create_admin_menu();
        }
    }


    public function create_admin_menu()
    {
        add_action( 'admin_menu', array( $this, 'polen_video_info' ) );
    }

    /**
     * Admin Menu
     */
    public function polen_video_info(){
        add_submenu_page(
            'woocommerce-marketing',
            'Ultimos Vídeos - Cadastro',
            'Ultimos Vídeos',
            'manage_options',
            'videoinfo-list',
            [$this, 'show_video_info_details'],
            5
        );
    }

    public function show_video_info_details()
    {
        $video_info_display = new Polen_Video_Info_Display();
        $video_info_display->prepare_items();
        
        echo '<div class="wrap">';
        echo '<div id="icon-users" class="icon32"></div>';
        echo '<h1 class="wp-heading-inline">' . translate('Ultimos Vídeos') . '</h1>';
        echo '<hr class="wp-header-end">';
        $video_info_display->display();
        echo '</div>';
    }
}