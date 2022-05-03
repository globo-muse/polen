<?php

namespace Polen\Admin;

class Polen_Admin_Metabox_B2C
{
    protected string $path_file = '';

    public function __construct()
    {
        $this->path_file = plugin_dir_path(__FILE__) . 'partials/metaboxes/b2c/';
        add_action('add_meta_boxes', array($this, 'add_metaboxes'));
    }

    public function add_metaboxes()
    {
        global $post, $current_screen;

        if( $current_screen && ! is_null( $current_screen ) && isset( $current_screen->id ) && $current_screen->id == 'shop_order' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' && $post->b2b != '1')
        {
            add_meta_box( 'Polen_Order_Details', 'Informações gerais B2C', array( $this, 'show_fields' ), 'shop_order', 'normal', 'low' );;
        }
    }

    /**
     * Gerar includes dos campos b2b
     */
    public function show_fields()
    {
        global $post;
        $order_id = $post->ID;
        $files = array(
            'metabox-order-details.php',
            'metabox-video-info.php',
        );

        foreach ($files as $file) {
            $file_path = $this->path_file . $file;
            if (file_exists($file)) {
                error_log(sprintf(__('Erro ao incluir %s '), $file), E_USER_ERROR);
            }
            include_once "{$file_path}";
        }
    }

}
