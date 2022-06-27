<?php

namespace Polen\Admin;

use Polen\Includes\Module\Polen_Product_Module;
use Polen\Includes\Module\Resource\Metrics;

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
     * Gerenciar regra gerenciar campos ACF quando post do tipo produto for salvo
     */
    public function product_save()
    {
        if (get_post_type(get_the_ID()) != 'product') {
            return null;
        }

        $this->save_public_main();
        $this->save_region_main();
    }

    /**
     * Salvar o tipo público do publico que o talento tem maior influencia
     * @return void|null
     */
    private function save_public_main()
    {
        $man = get_field('man');
        $woman = get_field('woman');

        $genre = compact('man', 'woman');
        update_field('main_genre', array_search(max($genre), $genre));
    }

    /**
     * Salvar a região que o talento tem maior influencia
     * @return void|null
     */
    private function save_region_main()
    {
        $product = wc_get_product(get_the_ID());
        $product_module = new Polen_Product_Module($product);

        $metrics_talent = new Metrics();
        $influence = $product_module->get_influence_by_region();

        if (empty($influence)) {
            return null;
        }

        foreach ($influence as $value) {
            $metrics_talent->set_percentage_by_regions($value['state_and_city']['state_id'], (int) $value['percentage']);
        }

        $total = $metrics_talent->get_percentage_by_regions();
        update_field('main_region', array_search(max($total), $total));
    }

    /**
     * Configurar metas com repeater ACF para ser utilizado no WP_QUERY
     */
    public function replace_repeater_field($where)
    {
        return str_replace( "meta_key = 'age_group_$", "meta_key LIKE 'age_group_%", $where);
    }
}