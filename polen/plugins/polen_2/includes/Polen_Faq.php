<?php

namespace Polen\Includes;

use Polen\Admin\Polen_Admin_Custom_Post_Types;
use WP_Query;

class Polen_Faq {

    /**
     * Salvar slug do cpt para uso na classe
     * @var string
     */
    private string $slug_cpt;

    public function __construct()
    {
        $this->slug_cpt = 'post_' . Polen_Admin_Custom_Post_Types::POLEN_FAQ;
    }

    /**
     * Retornar perguntas e respostas do Custom Post Type FAQ
     *
     * @return array
     */
    public function get_faq(): array
    {
        $args = [
            'post_type' => $this->slug_cpt,
            'posts_per_page' => -1,
            'status' => 'publish',
        ];

        $query = new WP_Query($args);
        $contents = $query->get_posts();

        $data = $value = [];
        foreach ($contents as $content) {
            $value['question_id'] = $content->ID;
            $value['ask'] = $content->post_title;
            $value['answer'] = strip_tags($content->post_content, '<br>');

            $data[] = $value;
        }

        return $data;
    }
}