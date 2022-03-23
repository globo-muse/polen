<?php
/**
 * Retornar talentos de acordo com a campanha
 *
 * @param string $campaignn
 * @return array
 */
function polen_get_talents_by_campaignn(string $campaignn): array
{
    $args = array(
        'post_type' => 'product',
        'post_status' => 'private',
        'orderby' => 'title',
        'order' => 'ASC',
        'tax_query' => array(
            array(
               'taxonomy' => 'campaigns',
                'field' => 'slug',
                'terms' => $campaignn,
            )
        ),
    );

    $query = new WP_Query($args);
    $query->get_posts();
    $talents = [];
    foreach ($query->get_posts() as $talents_campaign) {
        $product = wc_get_product($talents_campaign->ID);
        $ids = $product->get_category_ids();
        $category = _polen_get_first_category_object($ids);

        $talents[] = [
            'ID' => $product->get_id(),
            'name' => $product->get_title(),
            'image' => get_the_post_thumbnail_url($talents_campaign->ID),
            'talent_url' => event_promotional_url_detail_product( $product ),
            'price' => $product->get_price(),
            'in_stock' => $product->is_in_stock(),
            'slug' => $product->get_slug(),
            'category' => $category->name,
        ];
    }

    return $talents;
}

/**
 * Verificar se a página atual é
 *
 * @return bool
 */
function is_page_campaignn_lacta(): bool
{
    $page = false;
    $current_template = get_page_template_slug(get_queried_object_id());
    $templates = wp_get_theme()->get_page_templates();
    $template_name = $templates[$current_template];
    if ($template_name === 'Página - Lacta') {
        $page = true;
    }

    return $page;
}
