<?php

/**
 * Esse arquivo é responsavel por pegar qualquer
 * dado do banco de dados para o Front
 */

use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Debug;
use Polen\Includes\Polen_Video_Info;

/**
 * Pegar informações das categorias
 * @param ints $ids
 */
function _polen_get_first_category_object( $ids ) {
    foreach ($ids as $id) {
        return get_term( $id, 'product_cat', OBJECT );
    }
}


/**
 * Pegar os dados para preenchimento do card de artista pelo Obj Produto do artista
 * @param \WP_Product $talent_object
 * @return type
 */
function _polen_get_info_talent_by_product_id( \WC_Product $talent_object, $size = 'polen-thumb-md' ) {
        $talent = [];
        $talent['ID'] = $talent_object->get_id();
        $talent['image'] = $talent_object->get_image( $size );
        $talent['talent_url'] = $talent_object->get_permalink();
        $talent['price'] = $talent_object->get_price();
        if( 'WC_Product_Variable' == get_class( $talent_object ) ) {
            $talent['price_formatted'] = wc_price( $talent_object->get_variation_price('min') );
        } else {
            $talent['price_formatted'] = $talent_object->get_price_html();
        }
        $talent['name'] = $talent_object->get_title();
		    $talent['in_stock'] = $talent_object->is_in_stock();
        $talent['stock'] = $talent_object->get_stock_quantity();

        $ids = $talent_object->get_category_ids();
        $category = _polen_get_first_category_object( $ids );
        $talent['category_url'] = polen_get_url_category_by_term_id( $category->term_id );
        $talent['category'] = $category->name;
        return $talent;
}


/**
 * Usando a funcao _get_info_talent_by_product_id dentro de um loop para
 * retornar varios talentos no formato dos cards
 *
 * @param array $args
 * @return type
 */
function _polen_get_info_talents_by_args( array $args )
{
    $talents_objects = wc_get_products( $args );
    $talents = [];
    foreach ( $talents_objects as $talent_object ) {
        $talents[] = _polen_get_info_talent_by_product_id( $talent_object );
    }
    return $talents;
}


/**
 * Pegar os artistas recentes informando qual o maximo de resultado
 *
 * @param int quantity
 * @return array
 */
function polen_get_new_talents( int $quantity = 4 )
{
    $args = [
        'numberposts' => $quantity,
        'post_status' => 'publish',
        // 'order' => 'menu_order',
        'orderby' => 'menu_order',
    ];
    $talents = _polen_get_info_talents_by_args( $args );
    return $talents;
}


/**
 * Pegar dados e formatar para o cord de categorias
 *
 * @param \WP_Term $category_object
 * @return type
 */
function _polen_get_category_info( \WP_Term $category_object )
{
    $category = [];
    $category[ 'ID' ] = $category_object->term_id;
    $category[ 'title' ] = $category_object->name;
    $category[ 'url' ] = polen_get_url_category_by_term_id( $category[ 'ID' ] );

    $thumbnail_id = get_term_meta( $category_object->term_id, 'thumbnail_id', true );
    $category[ 'image' ] = wp_get_attachment_url( $thumbnail_id );

    return $category;
}


/**
 * Pegar as categorias que serao apresentadas na Home
 *
 * @param int quantity
 * @return array
 */
function polen_get_categories_home(int $quantity = 4)
{
    $args = [
        'taxonomy' => 'product_cat',
        'number' => $quantity,
        'hide_empty' => true,
        'order' => 'count',
        'exclude' => '15',
    ];

    $categories_object = get_terms( $args );
    $categories = [];
    foreach ( $categories_object as $category_object ) {
        $categories[] = _polen_get_category_info( $category_object );
    }
    return $categories;
}


/**
 * Pegar todos os artistas informando qual o maximo de resultado
 *
 * @param int quantity
 * @return array
 */
function polen_get_talents( int $quantity = 10 )
{
    $args = [
        'numberposts' => $quantity,
        'post_status' => 'publish',
        // 'order' => 'RAND',
        'orderby' => 'rand'
    ];
    $talents = _polen_get_info_talents_by_args( $args );
    return $talents;
}

/**
 * Retornar talentos por categorias
 *
 * @param string $slug
 * @param int $quantity
 * @return array|type
 */
function polen_get_talents_by_product_cat(string $slug, int $quantity = 10)
{
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $quantity,
        'product_cat' => $slug,
        'post_status' => 'publish',
    );

    $args['tax_query'] = array(
        array(
            'taxonomy' => 'campaigns',
            'terms' => array('lacta'),
            'field' => 'slug',
            'operator' => 'NOT IN',
        ),
    );

    return _polen_get_info_talents_by_args( $args );
}

/**
 * Retorna a URL do arquivo JSON das occasions para a tela do brief do videos
 * antes da compara pelo costumer.
 *
 * @return string
 */
function polen_get_occasions_json()
{
    $occasion_list = new Polen\Includes\Polen_Occasion_List();
    return $occasion_list->get_url_occasion_json();
}


/**
 * Pega os produtos relacionados (na mesma cat) a retorna um array para a func polen_banner_scrollable
 * @param int $product_id
 * @return array [['ID'=>xx,'talent_url'=>'...','name'=>'...','price'=>'...','category_url'=>'...','category'=>'...']]
 */
function polen_get_array_related_products( $product_id )
{
    $cat_terms = wp_get_object_terms( $product_id, 'product_cat');
    $cat_link = polen_get_url_category_by_product_id( $product_id );
    $terms_ids = array();
    if (count($cat_terms) > 0) {
        foreach ($cat_terms as $k => $term) {
            $terms_ids[] = $term->term_id;
        }
    }
    if (count($terms_ids) > 0) {
        $others = get_objects_in_term($terms_ids, 'product_cat');
        $arr_obj = array();
        $arr_obj[] = get_the_ID();
        shuffle($others);
        if (count($others)) {
            $args = array();
            foreach ($others as $k => $id) {
                if (!in_array($id, $arr_obj)) {
                    if (count($arr_obj) > 6) {
                        return $args;
                    }
                    $product = wc_get_product($id);
                    $arr_obj[] = $id;

                    if( 'publish' === $product->get_status() ) {
                        $args[] = array(
                            "ID" => $id,
                            "talent_url" => get_permalink($id),
                            "name" => $product->get_title(),
                            "price" => $product->get_regular_price(),
                            "price_formatted" => $product->get_price_html(),
                            "category_url" => $cat_link,
                            "category" => wc_get_product_category_list($id),
                            "in_stock" => $product->is_in_stock(),
                        );
                    }
                }
            }
            return $args;
        }
    }
}

function polen_get_home_stories( ...$orders_ids )
{
    $orders_ids_1 = $orders_ids[ 0 ];
    if( is_array( $orders_ids_1 ) ) {
        $orders_ids = $orders_ids_1;
    }
    $videos_info = Polen_Video_Info::get_by_complete_and_public_by_orders_ids( $orders_ids );
    $array_result = [];
    foreach( $videos_info as $vi ) {
		$order = wc_get_order($vi->order_id);
		if( !empty( $order ) ) {
            $cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
            $array_result[] = [
                'talent_id'    => $vi->talent_id,
                'talent_thumb' => polen_get_avatar($vi->talent_id, 'polen-square-crop-lg'),
                'cover'        =>  $vi->vimeo_thumbnail,
                'video_url'    => $vi->vimeo_file_play,
                'initials'     => polen_get_initials_name( $cart_item->get_name_to_video() ),
            ];
        }
    }
    return  $array_result;
}
