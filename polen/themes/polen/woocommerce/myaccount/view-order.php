<?php

/**
 * @version 3.0.0
 */
defined('ABSPATH') || exit;

use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Video_Info;
use \Polen\Includes\Cart\Polen_Cart_Item_Factory;

$notes = $order->get_customer_order_notes();
$order_number = $order->get_order_number();
$order_status = $order->get_status();

$order_item_cart = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
$email_billing = $order_item_cart->get_email_to_video();

$order_array = Order_Class::polen_get_order_flow_obj($order_number, $order_status, $email_billing);
polen_set_fan_viewed( $order );

global $Polen_Plugin_Settings;
$whatsapp_form = $Polen_Plugin_Settings['polen_whatsapp_form'];

$number = $order->get_meta( Polen_Order::WHATSAPP_NUMBER_META_KEY );

?>
<div class="row">
	<div class="col-md-12 mb-5">
		<h1>Acompanhar pedido</h1>
	</div>
	<div class="col-md-12">
		<?php polen_get_order_flow_layout( $order_array, $order_number, $number, $whatsapp_form ); ?>
	</div>
</div>

<?php

$order_is_completed = Polen_Order::is_completed($order);
$url_watch_video = $order_is_completed == true ? polen_get_link_watch_video_by_order_id($order_number) : '';
?>

<?php if ($order_is_completed) :
	$video_info = Polen_Video_Info::get_by_order_id( $order->get_id() );
	if( !empty( $video_info ) ) :
	?>
		<div class="row my-3">
			<div class="col-12">
			<?php if( $video_info->is_vimeo_process_complete() ) : ?>
				<a href="<?php echo $url_watch_video; ?>" class="btn btn-outline-light btn-lg btn-block">Assistir vídeo</a>
			<?php else: ?>
				<a href="" class="btn btn-outline-light btn-lg btn-block disabled">Video sendo processado, aguarde alguns minutos</a>
			<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
