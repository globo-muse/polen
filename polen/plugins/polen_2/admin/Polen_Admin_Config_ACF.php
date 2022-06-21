<?php

namespace Polen\Admin;

class Polen_Admin_Config_ACF
{
    public function __construct($static = false)
    {
        if ($static) {
            add_action('acf/save_post', array($this, 'product_save'));
            add_filter('posts_where', array($this, 'replace_repeater_field'));
        }
    }

    /**
     * Salvar o maior publico por genero
     */
    public function product_save()
    {
        $man = get_field('man');
        $woman = get_field('woman');

        $genre = compact('man', 'woman');

        update_field('main_genre', array_search(max($genre), $genre));
    }

    /**
     * Configurar metas com repeater ACF para ser utilizado no WP_QUERY
     */
    public function replace_repeater_field($where)
    {
        return str_replace( "meta_key = 'age_group_$", "meta_key LIKE 'age_group_%", $where);
    }
}