<?php

namespace Polen\Admin;

use Exception;
use Polen\Includes\Vimeo\Polen_Vimeo_Factory;
use Polen\Includes\Vimeo\Polen_Vimeo_Response;

defined('ABSPATH') || die;

class Polen_Admin_Vimeo_Info
{

    public function __construct($static = false)
    {
        if($static) {
            add_action('wp_ajax_polen_vimeo_info', [$this, 'get_vimeo_info']);
            add_action('wp_ajax_nopriv_polen_vimeo_info', [$this, 'get_vimeo_info']);

            add_action('add_meta_boxes', array($this, 'add_metaboxes'));
        }
    }


    public function add_metaboxes()
    {
        global $current_screen;

        if( $current_screen && ! is_null( $current_screen ) && isset( $current_screen->id ) && $current_screen->id == 'product' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' )  {
            add_meta_box( 'Polen_Vimeo_Video_Info', 'Videos para a tela de destalhes do produto', array( $this, 'show_fields' ), 'product', 'normal', 'low' );
        }
    }


    /**
     * Gerar includes dos campos b2b
     */
    public function show_fields()
    {
        $file_path = plugin_dir_path(__FILE__) . 'partials/metaboxes/metabox-vimeo-video-info.php';
        include_once "{$file_path}";
    }


    /**
     * Callback: funcao que retorna alguns dados dos videos que
     * serao salvos no Produto
     */
    public function get_vimeo_info()
    {
        header('Content-Type: application/json;charset=utf-8');
        try {
            $vimeo_url_raw = filter_input(INPUT_POST, 'vimeo_id');
            if(empty($vimeo_url_raw)) {
                var_dump('video nao encontrado', 404);
            }

            $vimeo_api = Polen_Vimeo_Factory::create_vimeo_instance_with_redux();
            $vimeo_response = new Polen_Vimeo_Response($vimeo_api->request($vimeo_url_raw));
            if($vimeo_response->is_error()) {
                throw new Exception($vimeo_response->get_error(), 404);
            }
            $result = [
                'is_landscape' => $vimeo_response->is_landscape(),
                'link_play' => $vimeo_response->get_play_link(),
                'thumb' => $vimeo_response->get_image_url_640(),
            ];

            echo json_encode($result);
        } catch(Exception $e) {
            echo json_encode(['message' => $e->getMessage(), 'code' => $e->getCode()]);
        }
        wp_die();
    }
}
