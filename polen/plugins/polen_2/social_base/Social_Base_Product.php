<?php
namespace Polen\Social_Base;

use Polen\Admin\Polen_Admin_Social_Base_Product_Fields;
use WC_Product;
use WC_Product_Query;

defined( 'ABSPATH' ) || die;

class Social_Base_Product
{
    const PRODUCT_META_IS_SOCIAL_BASE = '_is_social_base';
    const PRODUCT_META_SLUG_campaign = '_social_base_slug_campaign';
    const PRODUCT_META_VIDEO_TESTEMONIAL_URL = '_social_base_video_testimonial';

    public function __construct( \WC_Product $product )
    {
        $this->product = $product;
    }

    public static function product_is_social_base( WC_Product $product = null )
    {
        if( empty( $product) ) {
            return false;
        }

        if( $product->get_meta( Polen_Admin_Social_Base_Product_Fields::FIELD_NAME_IS, true ) !== 'yes' ) {
            return false;
        }

        return true;
    }

    public function get_url_wartermark_video_player()
    {
        return $this->product->get_meta( Polen_Admin_Social_Base_Product_Fields::FIELD_NAME_URL_WATERMARK, true );
    }


    public function get_campaign_slug()
    {
        return $this->product->get_meta( Polen_Admin_Social_Base_Product_Fields::FIELD_NAME_SLUG_CAMPAIGN, true );
    }

    // public static function product_is_social( $product, $category )
    // {
    //     $product_categories_ids = wc_get_product_cat_ids( $product->get_id() );
    //     if( in_array( $category->term_id, $product_categories_ids ) ) {
    //         return true;
    //     }
    //     return false;
    // }

    // public static function get_all_products_by_category( $category )
    // {
    //     $args = array(
    //         'status' => 'publish',
    //         'category' => $category->slug,
    //         'meta_key' => '_stock',
    //         'orderby' => 'menu_order',
    //         'order' => 'DESC',
    //     );
    //     $products = _polen_get_info_talents_by_args( $args );
    //     return $products;
    // }


    public static function get_all_products_by_slug_campaign( $slug_campaign )
    {
        $args = array(
            'status' => 'publish',
            'meta_key' => '_social_base_slug_campaign',
            'meta_value' => $slug_campaign,
            'orderby' => 'menu_order',
            'order' => 'DESC',
        );
        $pq = new WC_Product_Query( $args );
        return $pq->get_products();
    }
}