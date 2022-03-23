<?php
namespace Polen\Includes\Talent;

use \Polen\Includes\Cart\Polen_Cart_Item_Factory;
use \Polen\Includes\Cart\Polen_Cart_Item;

class Polen_Talent_Part_Theme
{
    public function __construct( bool $static = false )
    {
        if( $static ) {
//            add_action( 'polen_before_upload_video', [ $this, 'check_permission' ] );
            add_action( 'polen_before_upload_video', [ $this, 'enqueue_script' ] );
        }
    }
    
    public function enqueue_script()
    {
        $order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
        $order = wc_get_order( $order_id );
        $item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
        $ajax_settings = $this->get_ajax_settings( $item );
        wp_localize_script(
            'polen-upload-video',
            'upload_video',
            $ajax_settings
        );
    }
    
    public function check_permission()
    {
        $order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
        if( empty( $order_id ) ) {
            //TODO: Quando o ID Não existir
            wp_die('Quando o ID Não existir');
        }
        
        $order = wc_get_order( $order_id );
        
        if( empty( $order ) ) {
            //TODO: Quando a $order não existir
            wp_die('Quando a $order não existir');
        }
    }
    
    private function get_ajax_settings( Polen_Cart_Item $item )
    {
        $order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
        $ajax_settings = array(
            'nonce'    => wp_create_nonce( 'upload_video_' . $order_id ),
            'action' => 'create_video_slot_vimeo',
            'email_to_video' => $item->get_email_to_video(),
            'instructions_to_video' => $item->get_instructions_to_video(),
            'name_to_video' => $item->get_name_to_video(),
            'offered_by' => $item->get_offered_by(),
            'video_category' => $item->get_video_category(),
            'video_to' => $item->get_video_to(),
            'order_id' => $order_id,
        );
        return $ajax_settings;
    }
}
