<?php

//Removendo o Dashboard até desenhar um melhor
wp_safe_redirect(site_url('my-account/orders/'));
exit;

use Polen\Includes\Polen_Talent;

$polen_talent = new Polen_Talent();
$current_user = wp_get_current_user();

$total_amount = $polen_talent->get_total_by_order_status_return_raw($current_user->ID, array( 'wc-payment-approved', 'wc-talent-accepted' ));
$amount_discount = polen_apply_polen_part_price( $total_amount );
$total_to_recive = $polen_talent->get_total_by_order_status_return_raw($current_user->ID);
$total_to_recive_discount = polen_apply_polen_part_price( $total_to_recive );
$all_amount_alredy_win = $polen_talent->get_total_by_order_status_return_raw( $current_user->ID, 'wc-completed' );
$all_amount_alredy_win_discount = polen_apply_polen_part_price( $all_amount_alredy_win );

if ($polen_talent->is_user_talent($current_user)) {
?>
	<section class="talent-dashboard-start">
		<header class="page-header">
			<div class="row">
				<div class="co-12 col-md-12">
					<h1>Olá, <?php echo $current_user->display_name; ?></h1>
					<p class="muted mt-2">Aceite ou recuse seus pedidos de vídeos.</p>
				</div>
			</div>
		</header><!-- .page-header -->

		<div class="page-content">
			<?php
			$total_time = $polen_talent->get_time_to_videos($current_user);
			if ($total_time) : ?>
				<div class="row mt-3">
					<div class="col-md-12">
						<div class="box-round p-3">
							<div class="row p-2">
								<div class="col-md-12 text-center">
									<img src="<?php echo TEMPLATE_URI; ?>/assets/img/mobile_hand.png" alt="Imagem pessoa com celular na mão" width="94" height="90" />
								</div>
							</div>
							<div class="row my-2">
								<div class="col-5">
									<p class="p">Invista hoje</p>
								</div>
								<div class="col-2">&nbsp;</div>
								<div class="col-5">
									<p class="p">E receba até</p>
								</div>
							</div>
							<div class="row mb-4">
								<div class="col-5">
									<span class="value small"><?php echo $total_time; ?></span>
								</div>
								<div class="col-2 d-flex align-items-center"><?php Icon_Class::polen_icon_chevron_right(); ?></div>
								<div class="col-5">
									<span class="value small"><?php echo wc_price( $amount_discount ); ?></span>
								</div>
							</div>
							<div class="row mb-2">
								<div class="col-md-12">
                  <?php
                  $inputs = new Material_Inputs();
                  $inputs->material_button_link("link1", "Ganhar agora", wc_get_account_endpoint_url('orders'));
                  ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<div class="row mt-3">
				<div class="col-md-12">
					<div class="msg msg-header">
						<div class="row">
							<div class="col-md-6 mb-3">
								<p class="p">Você já ganhou</p>
								<span class="value"><?php echo wc_price( $all_amount_alredy_win_discount ); ?></span>
							</div>
							<div class="col-md-6">
								<p class="p">Você tem para receber</p>
								<span class="value"><?php echo wc_price( $total_to_recive_discount ); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div><!-- .page-content -->
	</section><!-- .no-results -->
<?php
}
