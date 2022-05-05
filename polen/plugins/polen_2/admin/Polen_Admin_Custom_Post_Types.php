<?php

namespace Polen\Admin;

class Polen_Admin_Custom_Post_Types
{
    const POLEN_MEDIA = 'polen_media';
    const POLEN_QUIZ = 'polen_quiz';

    /**
     * Salvar todos os Custom Post Type
     * com as suas respectivas taxonomias
     * @var array
     */
    private array $cpt;

    public function __construct()
    {
        $this->set_theme_post_types();
        add_action('init', array($this, 'init_cpt_theme'));
    }

    /**
     * Adicionar post type e taxonomias ao tema
     *
     * @return void
     */
    private function set_theme_post_types(): void
    {
        $media = [
            'name' => 'Polen na Mídia',
            'singular_name'=> 'Mídia',
            'slug' => self::POLEN_MEDIA,
            'dashicon' => 'dashicons-admin-site',
            'taxonomy' => [],
        ];

        $quiz = [
            'name' => 'Perguntas Frequentes',
            'singular_name'=> 'Pergunta',
            'slug' => self::POLEN_QUIZ,
            'dashicon' => 'dashicons-format-chat',
            'taxonomy' => [],
        ];

        $this->cpt = [$media, $quiz];
    }

    /**
     * Adicionar todos os CPT para serem gerados automaticamente
     *
     * @return void|null
     */
    public function init_cpt_theme()
    {
        if (empty($this->cpt)) {
            return null;
        }

        foreach ($this->cpt as $parameter) {
            $this->theme_register_custom_post_types($parameter);
        }
    }

    /**
     * Renderizar todos os CPT automaticamente
     */
    private function theme_register_custom_post_types($parameter)
    {
        register_post_type('post_' . $parameter['slug'], array(
            'labels' => array(
                'name' => __($parameter['name']),
                'singular_label' => __($parameter['singular_name']),
                'menu_name' => __($parameter['name']),
                'parent_item_colon' => ('Parent'),
                'all_items' => __('Listar todos'),
                'view_item' => __('Visualizar'),
                'add_new_item' => __('Adicionar ' . $parameter['singular_name']),
                'add_new' => __('Adicionar ' . $parameter['singular_name']),
                'edit_item' => __('Editar ' . $parameter['singular_name']),
                'update_item' => __('Atualizar ' . $parameter['singular_name']),
                'search_items' => __('Pesquisar ' . $parameter['singular_name']),
                'not_found' => __('Registro não encontrado'),
                'not_found_in_trash' => __('Nenhum registro encontrado na lixeira'),
            ),
            'menu_icon' => $parameter['dashicon'] ? $parameter['dashicon'] : 'dashicons-welcome-widgets-menus',
            'public' => true,
            'has_archive' => false,
            'rewrite' => array('slug' => $parameter['slug']),
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions')
        ));

        if (!empty($parameter['taxonomy']) && count($parameter['taxonomy'])) {
            foreach ($parameter['taxonomy'] as $value) {
                register_taxonomy(
                    'tax_' . $parameter['slug'] . '_' . strtolower($value['slug']),
                    'post_' . $parameter['slug'],
                    array(
                        'label' => __($value['name']),
                        'rewrite' => array(
                            'slug' => $parameter['slug'] . '-' . str_replace(' ', '-', strtolower($value['name']))
                        ),
                        'hierarchical' => true,
                    )
                );
            }
        }
    }

}