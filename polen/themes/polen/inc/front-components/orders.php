<?php

/**
 *
 */
function polen_get_order_flow_layout($array_status, $order_number, $whatsapp_number = "", $redux_whatsapp = 0)
{
	//status: complete, in-progress, pending, fail
	//title: string
	//description: string

	if (empty($array_status) || !$array_status) {
		return;
	}

	$class = "";
	$new_array = array_values($array_status);

	if ($new_array[0]['status'] === "fail" || $new_array[0]['status'] === "in-progress") {
		$class = " none";
	}
	if ($new_array[1]['status'] === "complete" && $new_array[2]['status'] !== "fail") {
		$class = " half";
	}
	if ($new_array[2]['status'] === "complete") {
		$class = " complete";
	}

?>
	<div class="row">
		<div class="col-md-12">
			<ul class="order-flow<?php echo $class; ?>">
				<?php foreach ($array_status as $key => $value) : ?>
					<li class="item <?php echo "item" . $key; ?> <?php echo $value['status']; ?>">
						<span class="background status">
							<?php Icon_Class::polen_icon_check_o(); ?>
							<?php Icon_Class::polen_icon_exclamation_o(); ?>
						</span>
						<span class="text">
							<h4 class="title"><?php echo $value['title']; ?></h4>
							<p class="description"><?php echo $value['description']; ?></p>
							<?php
							if ($redux_whatsapp == "1" && !isset($first)) {
								$first = true;
								polen_form_add_whatsapp($order_number, $whatsapp_number);
							}
							?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
<?php
}


/**
 * Funcao que seta uma order como já visualizada pelo usuário fã
 * @param \WC_Order
 */
function polen_set_fan_viewed($order)
{
	if (!$order->meta_exists('polen_fan_viewed')) {
		$order->add_meta_data('polen_fan_viewed', 1, true);
		$order->save();
	}
}
