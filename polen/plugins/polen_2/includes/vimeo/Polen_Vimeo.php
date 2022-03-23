<?php
namespace Polen\Includes\Vimeo;

use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Video_Info;
use Polen\Includes\Talent\Polen_Talent_Controller;
use Vimeo\Exceptions\VimeoRequestException;

ABSPATH ?? die;

class Polen_Vimeo
{
        /**
     * Handler para o AJAX onde é executado quando o Talento, seleciona um video e
     * envia, antes do envio é criado no Vimeo um Slot para receber o Video com o 
     * mesmo tamanho em bytes
     * 
     * @param int
     * @param int
     * @param string
     * @throws VimeoRequestException
     * @return Polen_Vimeo_Response
     */
    public function make_video_slot_vimeo($order_id, $file_size)
    {
        $order       = wc_get_order($order_id);
        $polen_order = new Polen_Order_Module($order);
        if(empty($polen_order)) {
            throw new VimeoRequestException('Pedido não encontrado', 404);
        }
        $args = Polen_Vimeo_Vimeo_Options::get_option_insert_video(
            $file_size,
            $polen_order->get_name_to_video() . " #{$order_id}"
        );
        $lib            = Polen_Vimeo_Factory::create_vimeo_instance_with_redux();
        $vimeo_response = $lib->request('/me/videos', $args, 'POST');
        $response       = new Polen_Vimeo_Response($vimeo_response);
        if($response->is_error()) {
            throw new VimeoRequestException($response->get_developer_message(), 500);
        }
        // $talent_controller = new Polen_Talent_Controller();
        // $talent_controller->average_video_response(get_current_user_id());

        return $response;
    }
}
