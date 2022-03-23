<?php
namespace Polen\Admin\Partials;

use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Polen_Video_Info;

defined( 'ABSPATH' ) || die;
if ( ! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}



class Polen_Video_Info_Display extends \WP_List_Table
{

    public function __construct()
    {
        parent::__construct();
        $this->_column_headers = array( 
            $this->get_columns(),           // columns
            $this->get_hidden_columns(),      // hidden
            $this->get_sortable_columns(),  // sortable
        );
    }

    public function prepare_items()
    {
        $per_page = 100;
        $video_info_model = new Polen_Video_Info();
        $videos_infos = $video_info_model->get_results( '1', '1', '%d', $per_page, 'ORDER BY ID DESC');
        // $videos_infos = Polen_Video_Info::get_results( [ "1" => '1'], $per_page, 'ID DESC');
        $this->items = $videos_infos;
    }

    public function get_columns()
    {
        return array(
            'order_id'   => 'Order',
            'talent_id'  => 'Artista',
            'vimeo_id'   => 'Homenageado',
            'video_link'   => 'Video',
            'created_at' => 'Dt Upload',
        );
    }

    public function get_sortable_columns()
    {
        return array();
    }

    public function get_hidden_columns()
    {
        return array();
    }

    /***********************
     * Columns Mount
     */
    public function column_order_id( $param )
    {
        $url = admin_url( "post.php?post=%s&action=edit" );
        return sprintf("<a href='{$url}' target='_blank'>#%s</a>", $param->order_id, $param->order_id);
    }

    public function column_talent_id( $param )
    {
        $user = get_user_by( 'ID', $param->talent_id );
        return $user->display_name;
    }

    public function column_vimeo_id( $param )
    {
        $order = wc_get_order( $param->order_id );
        if( !empty( $order )) {
            $car_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
            return $car_item->get_name_to_video();
        }
        return 'ORDER_ERROR';
    }

    public function column_video_link( $param )
    {
        $url = site_url( "v/{$param->hash}" );
        return "<a href='{$url}' target='_blank'>Página do video</a>";
    }

    public function column_created_at( $param )
    {
        $date = new \DateTime( $param->created_at, wp_timezone() );
        return $date->format( 'd/m/Y' );
    }
}
