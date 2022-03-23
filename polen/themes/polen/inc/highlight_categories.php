<?php

/**
 * Retornar categorias destacadas no admin
 * @return array
 */
function highlighted_categories(): array
{
  global $Polen_Plugin_Settings;
  $terms_ids = $Polen_Plugin_Settings['highlight_categories'];

  if (empty($terms_ids)) {
    return [];
  }

  $data = [];
  foreach ($terms_ids as $term_id) {
    $thumbnail_id = get_term_meta($term_id, 'thumbnail_id', true);
    $image_url    = wp_get_attachment_url($thumbnail_id);

    $term = get_term_by('id', $term_id, 'product_cat');

    $data[] = [
      'term_id' => $term_id,
      'name' => $term->name,
      'slug' => $term->slug,
      'img' => $image_url,
      'count' => $term->count,
    ];
  }

  return $data;
}
