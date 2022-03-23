<?php

add_action( 'pre_get_posts', 'polen_search_woocommerce_only' );

function polen_search_woocommerce_only( $query ) {
    if( ! is_admin() && is_search() && $query->is_main_query() ) {
        $query->set( 'post_type', 'product' );
    }
}
