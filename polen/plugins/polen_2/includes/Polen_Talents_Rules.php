<?php

namespace Polen\Includes;

class Polen_Talents_Rules
{
    const SLUG_RULES  = 'product_rules';

    public function __construct($static = false)
    {
        if ($static) {
            add_action('init', [$this, 'create_taxonomy_rules']);
        }
    }

    /**
     * Registrar taxonomia de regras de uso
     */
    public function create_taxonomy_rules()
    {
        register_taxonomy(
            self::SLUG_RULES,
            'product',
            array(
                'label' => 'Regras para uso',
                'rewrite' => array('slug' => self::SLUG_RULES),
                'hierarchical' => true,
            )
        );
    }

    /**
     * Pegar todas os Casos de Uso cadastrados no Admin
     */
    public function get_all()
    {
        return get_terms([
            'taxonomy'   => self::SLUG_RULES,
            'hide_empty' => false,
        ]);
    }

    /**
     * Pegar todos os produtos de um caso de uso pelo slug
     * do caso de uso
     *
     * @param string
     */
    public function get_products_by_slug(string $slug)
    {
        return get_posts(array(
            'post_type' => 'product',
            'numberposts' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => self::SLUG_RULES,
                    'field' => 'slug',
                    'terms' => $slug,
                    'operator' => 'IN',
                )
            ),
        ));
    }

    /**
     * Retornar regra especifica de um determinado produto
     *
     * @param $product_id
     * @return array
     */
    public function get_terms_by_product($product_id): array
    {
        $terms = get_the_terms($product_id, self::SLUG_RULES);

        if (empty($terms)) {
            return [];
        }

        $data = [];
        foreach ($terms as $term) {
            $rule['rule_id'] = $term->term_id;
            $rule['rule_name'] = $term->name;
            $rule['rule_slug'] = $term->slug;

            $data[] = $rule;
        }

        return $data;
    }
    
}