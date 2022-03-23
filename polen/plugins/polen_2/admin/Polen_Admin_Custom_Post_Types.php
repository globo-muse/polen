<?php

namespace Polen\Admin;

use Exception;

class Polen_Admin_Custom_Post_Types {

    public function __construct()
    {
        add_action('init', array($this, 'create_cpt_polen_media'));
    }

    /**
     * Adicionar custom post type Polen na midia
     *
     * @since    1.0.0
     */
    function create_cpt_polen_media()
    {
        $name = 'Polen na Mídia';
        $singularName = 'Mídia';

        $labels = array(
            'name' => $name,
            'singular_name' => $singularName,
            'menu_name' => $name,
            'parent_item_colon' => 'Parent',
            'all_items' => "Todos os {$name}",
            'view_item' => "Visualizar {$singularName}",
            'add_new_item' => "Adicionar Nova {$singularName}",
            'add_new' => "Adicionar {$singularName}",
            'edit_item' => "Editar {$singularName}",
            'update_item' => "Atualizar {$singularName}",
            'search_items' => "Pesquisar {$singularName}",
            'not_found' => 'Registro não encontrado',
            'not_found_in_trash' => 'Nenhum registro encontrado na lixeira',
        );

        register_post_type( 'post_polen_media',
            array(
                'menu_icon' => 'dashicons-admin-site',
                'labels' => $labels,
                'public' => true,
                'has_archive' => false,
                'supports'  => array('title', 'editor', 'thumbnail'),
            )
        );
    }
}