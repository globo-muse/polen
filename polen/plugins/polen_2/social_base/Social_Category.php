<?php
namespace Polen\Social;

use Exception;

defined( 'ABSPATH' ) || die;

class Social_Category
{
    
    public $term_id;
    public $name;
    public $slug;
    public $term_group;
    public $term_taxonomy_id;
    public $taxonomy;
    public $description;
    public $parent;
    public $count;
    public $filter;

    public function __construct( $slug )
    {
        $category = get_term_by( 'slug', $slug, 'product_cat' );
        if( empty( $category ) ) {
            throw new Exception( "Categoria social {$slug} não encontrada.", 403 );
        }
        $this->term_id = $category->term_id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->term_group = $category->term_group;
        $this->term_taxonomy_id = $category->term_taxonomy_id;
        $this->taxonomy = $category->taxonomy;
        $this->description = $category->description;
        $this->parent = $category->parent;
        $this->count = $category->count;
        $this->filter = $category->filter;
    }


    static public function get_category_by_slug( $slug )
    {
        return new self( $slug );
    }
}