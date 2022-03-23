<?php

class Promotional_Event_Product
{
    public $product;

    public function __construct( \WC_Product $product )
    {
        $this->product = $product;
    }

    public function get_url_image_product_small()
    {
        return $this->get_url_image_product_with_size( 'small' );
    }

    public function get_url_image_product_with_size( $size )
    {
        $attachment_id = $this->product->get_image_id();
        $src = wp_get_attachment_image_src( $attachment_id, $size );
        if( empty( $src ) ) {
            return false;
        }
        return $src[ 0 ];
    }

    public function get_url_wartermark_video_player()
    {
        return $this->product->get_meta('_promotional_event_wartermark');
    }
}
