<?php

namespace Polen\Includes;

class Polen_Order_Review_Rewrite
{
    public function __construct( bool $static = false )
    {
        if( $static ) {
            add_action( 'init', function(){
                add_rewrite_rule( 'talento/([^/]*)/([^/]*)/?', 'index.php?talent_review_id=$matches[1]&variety=$matches[2]', 'top' );
            });
            
            add_filter( 'query_vars', function( $query_vars ) {
                $query_vars[] = 'talent_review_id';
                $query_vars[] = 'variety';
                return $query_vars;
            } );
            
            add_action( 'template_include', function( $template ) {
                if ( get_query_var( 'talent_review_id' ) == false || get_query_var( 'talent_review_id' ) == '' ) {
                    return $template;
                }
            
                if( get_query_var( 'variety' ) !== 'reviews' ) {
                    global $wp_query;
                    $wp_query->set_404();
                    status_header( 404 );
                    get_template_part( 404 );
                    exit();
                }
            
                
                $product_id = wc_get_product_id_by_sku( get_query_var( 'talent_review_id' ) );
                if( empty( $product_id )) {
                    global $wp_query;
                    $wp_query->set_404();
                    status_header( 404 );
                    get_template_part( 404 );
                    exit();
                }
             
                include TEMPLATE_DIR . '/reviews.php';
            } );
        }
    }
}