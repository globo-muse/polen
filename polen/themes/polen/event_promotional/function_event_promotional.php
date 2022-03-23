<?php

use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Module\Polen_Product_Module;
use Polen\Includes\Polen_Talent;

function event_promotional_url_home()
{
    return site_url( Promotional_Event_Rewrite::BASE_URL . '/' );
}

function event_promotional_url_detail_product( $product )
{
    return event_promotional_url_home() . $product->get_sku();
}

function event_promotional_url_code_validation( $product )
{
    return event_promotional_url_detail_product( $product ) . '/validar-codigo';
}

function event_promotional_url_order( $product, $cupom_code )
{
    return event_promotional_url_detail_product( $product ) . '/pedido?cupom_code=' . $cupom_code;
}

function event_promotional_url_success( $product, $order_id, $order_key )
{
    return event_promotional_url_detail_product( $product ) . "/confirmado?order={$order_id}&order_key={$order_key}";
}

function event_promotional_is_home()
{
    $is_set = isset( $GLOBALS[ Promotional_Event_Rewrite::QUERY_VARS_EVENT_PROMOTIONAL_IS_HOME ] );
    if( $is_set && $GLOBALS[ Promotional_Event_Rewrite::QUERY_VARS_EVENT_PROMOTIONAL_IS_HOME ] == '1' ) {
        return true;
    }
    return false;
}

function event_promotional_is_detail_product()
{
    $is_set = isset( $GLOBALS[ Promotional_Event_Rewrite::QUERY_VARS_EVENT_PROMOTIONAL_DETAIL_PRODUCT ] );
    if( $is_set && $GLOBALS[ Promotional_Event_Rewrite::QUERY_VARS_EVENT_PROMOTIONAL_DETAIL_PRODUCT ] == '1' ) {
        return true;
    }
    return false;
}

function event_promotional_is_app()
{
    $is_set = isset( $GLOBALS[ Promotional_Event_Rewrite::QUERY_VARS_EVENT_PROMOTIONAL_APP ] );
    if( $is_set && $GLOBALS[ Promotional_Event_Rewrite::QUERY_VARS_EVENT_PROMOTIONAL_APP ] == '1' ) {
        return true;
    }
    return false;
}


function event_promotional_product_is_event_promotional( $product )
{
    $polen_product = new Polen_Product_Module( $product );
    return $polen_product->get_is_campaign();
}

function event_promotional_order_get_slug_event( $order )
{
    $polen_order = new Polen_Order_Module( $order );
    return $polen_order->get_campaign_slug();
}

function event_promotional_order_is_event_promotional( $order )
{
    $polen_order = new Polen_Order_Module( $order );
    return $polen_order->get_is_campaign();
}

function event_promotional_orders_ids_by_user_id_status( $product_id, $campaign, $status )
{
    $status = 'wc-' . $status;
    $orders_ids = Polen_Product_Module::get_orders_ids_by_product_id( $product_id, [ $status ] );
    $orders_ids_return = [];
    foreach( $orders_ids as $order_id ) {
        $polen_order = new Polen_Order_Module( wc_get_order( $order_id ) );
        if( is_a( $polen_order, 'Polen\Includes\Module\Polen_Order_Module' ) ){
            if( $campaign === $polen_order->get_campaign_slug() ) {
                $orders_ids_return[] = $order_id;
            }
        }
    }
    return $orders_ids_return;
}


/**
 *
 */
function event_promotional_get_order_flow_layout($array_status, $order_number, $whatsapp_number = "", $redux_whatsapp = 0)
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
    <div class="col-md-12 mb-3">
      <h2 class="title">Acompanhar pedido</h2>
    </div>
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

function event_promotional_get_order_flow_obj($order_number, $order_status, $email_billing = null )
{
    $flow_1_complement_email = '';
    if( !empty( $email_billing ) ) {
        $flow_1_complement_email = "<br />Todas as atualizações serão enviadas para o email <strong>{$email_billing}</strong>.";
    }
    $flow_1 = array(
        'pending' => array(
            'title' => 'Pendente de pagamento',
            'description' => 'Seu número de pedido #' . $order_number . ' está aguardando pagamento. ' . $flow_1_complement_email,
            'status' => 'fail',
        ),
        'payment-in-analysis' => array(
            'title' => 'Pagamento em análise',
            'description' => 'Seu número de pedido #' . $order_number . ' está em análise pela sua operadora de crédito.',
            'status' => 'complete',
        ),
        'payment-rejected' => array(
            'title' => 'Pagamento rejeitado',
            'description' => 'Seu número de pedido #' . $order_number . ' foi rejeitado pela sua operadora de crédito.',
            'status' => 'fail',
        ),
        'payment-approved' => array(
            'title' => 'Recebemos seu pedido de vídeo',
            'description' => 'Seu número de pedido é #' . $order_number . ' foi aprovado. ' . $flow_1_complement_email,
            'status' => 'complete',
        ),
    );

    $flow_2 = array(
        'order-expired' => array(
            'title' => 'Pedido expirado',
            'description' => 'Infelizmente o artista não aceitou o seu pedido em tempo hábil e seu pedido expirou.',
            'status' => 'fail',
        ),
        'talent-rejected' => array(
            'title' => 'O talento rejeitou',
            'description' => 'Infelizmente o talento não aceitou o seu pedido.',
            'status' => 'fail',
        ),
        'talent-accepted' => array(
            'title' => 'Vídeo aceito',
            'description' => 'O Autor aceitou o seu pedido.',
            'status' => 'complete',
        ),
        '_next-step' => array(
            'title' => 'Aguardando confirmação',
            'description' => 'Você será informado quando a sua solicitação de vídeo for aceita.',
            'status' => 'in-progress',
        ),
    );

    $url_user_order = site_url('my-account/view-order/' . $order_number);
    $flow_3 = array(
        'completed' => array(
            'title' => 'Seu vídeo está pronto!',
            'description' => 'Corre lá e confira seu vídeo.',
            'status' => 'complete',
        ),
        'cancelled' => array(
            'title' => 'Seu pedido foi cancelado',
            'description' => 'Seu pedido foi cancelado.',
            'status' => 'fail',
        ),
    );

    if (isset($flow_1[$order_status])) {
        $flows = array(
            $flow_1[$order_status],
            '_next-step_1' => array(
                'title' => 'Aguardando confirmação',
                'description' => 'Você será informado quando a sua solicitação de vídeo for aceita.',
                'status' => $flow_1[$order_status]['status'] === "fail" ? 'pending' : 'in-progress',
            ),
            '_next-step_2' => array(
                'title' => 'Aguardando gravação do vídeo',
                'description' => 'Quando o vídeo for disponibilizado, ele será exibido aqui.',
                'status' => 'pending',
            ),
        );
    } elseif (isset($flow_2[$order_status])) {
        $flows = array(
            'payment-approved' => array(
                'title' => 'Pagamento aprovado',
                'description' => 'Seu número de pedido #' . $order_number . ' foi aprovado.',
                'status' => 'complete',
            ),
            $flow_2[$order_status],
            '_next-step_2' => array(
                'title' => 'Aguardando gravação do vídeo',
                'description' => 'Quando o vídeo for disponibilizado, ele será exibido aqui.',
                'status' => 'in-progress',
            ),
        );
    } elseif (isset($flow_3[$order_status])) {
        $flows = array(
            'payment-approved' => array(
                'title' => 'Pagamento aprovado',
                'description' => 'Seu número de pedido #' . $order_number . ' foi aprovado.',
                'status' => 'complete',
            ),
            'talent-accepted' => array(
                'title' => 'O talento aceitou',
                'description' => 'O talento aceitou o seu pedido.',
                'status' => 'complete',
            ),
            $flow_3[$order_status],
        );
    }

    return $flows;
}
