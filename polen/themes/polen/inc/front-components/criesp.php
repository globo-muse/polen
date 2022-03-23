<?php

function criesp_get_home_banner($link)
{
?>
	<div class="row mt-4">
		<div class="col-12">
			<div class="criesp-banner">
				<img class="image" src="<?php echo TEMPLATE_URI . '/assets/img/criesp/bg-criesp.jpg'; ?>" alt="Fundo Criança Esperança">
				<div class="content">
					<img src="<?php echo TEMPLATE_URI . '/assets/img/criesp/logo-criesp.png';  ?>" alt="Logo Criança Esperança" />
					<p class="mt-3">Na Polen 100% do valor dos vídeos serão revertidos em doações para o Criança Esperança.</p>
					<a href="<?php echo $link; ?>" class="btn btn-primary btn-md">Doe agora<span class="ml-2"><?php Icon_Class::polen_icon_chevron_right(); ?></span></a>
				</div>
			</div>
		</div>
	</div>
<?php
}

function criesp_get_modal()
{
?>
	<div class="modal fade show" id="criespModal" tabindex="-1" role="dialog" aria-labelledby="criespLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content white-modal">
				<div class="modal-header">
					<div class="col-12 modal-logo">
						<img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/logo-black.png';  ?>"></img>
						<img class="img-responsive criesp-logo" src="<?php echo TEMPLATE_URI . '/assets/img/criesp/logo-criesp-color.png';  ?>"></img>
					</div>
					<button onclick="closeCriespModal()" type="button" class="close" data-dismiss="modal" aria-label="Close">
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-12 d-flex justify-content-center">
							<img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/criesp/modal-doe.png';  ?>"></img>
						</div>
						<div class="col-12">
							<h2>Seja bem-vindo à Polen</h2>
							<p>Aqui sua doação vira um vídeo-Polen personalizado do seu artista favorito! Além de se emocionar com seu ídolo, 100% do valor da doação vai para a UNESCO. Ahh para DOAR é MUITO fácil!</p>
							<h3>Como funciona</h3>
						</div>
						<div class="col-md-12">
							<ul class="order-flow half">
								<li class="item itempayment-approved complete">
									<span class="background status">1</span>
									<span class="text">
										<p class="description">Escolha aqui no site um dos artistas Polen | Criança Esperança</p>
									</span>
								</li>
								<li class="item itempayment-approved complete">
									<span class="background status">2</span>
									<span class="text">
										<p class="description">Faça a sua doação através do botão DOAR</p>
									</span>
								</li>
								<li class="item itempayment-approved complete">
									<span class="background status">3</span>
									<span class="text">
										<p class="description">Você recebe um vídeo de agradecimento personalizado do seu ídolo</p>
									</span>
								</li>
							</ul>
						</div>
						<div class="col-md-12">
							<p><b>Os vídeos serão entregues em até 15 dias após confirmação da doação.</b></p>
						</div>
					</div>
				</div>
				<div class="modal-footer d-flef justify-content-center">
					<button onclick="closeCriespModal()" type="button" class="btn btn-secondary" data-dismiss="modal">Começar</button>
				</div>
			</div>
		</div>
	</div>
<?php
}

function criesp_get_donation_box()
{
?>
	<section class="row donation-box custom-donation-box mt-4 mb-4">
		<div class="col-md-12">
			<header class="row mb-3">
				<div class="col">
					<h2>Sobre a doação</h2>
				</div>
			</header>
		</div>
		<div class="col-md-12">
			<div class="box-round py-4 px-4">
				<div class="row">
					<div class="col-md-12">
						<figure class="image">
							<img src="<?php echo TEMPLATE_URI . '/assets/img/criesp/logo-criesp-color.png';  ?>" alt="Logo da empresa de doação">
						</figure>
						<p><strong>Sobre o Criança Esperança</strong></p>
						<p class="small">Há 36 anos, o Criança Esperança cria oportunidades de desenvolvimento para crianças e jovens em todo o país. Os recursos arrecadados pela campanha são depositados na conta da UNESCO que os repassa aos projetos selecionados, anualmente, por meio de um edital público.</p>
					</div>
					<div class="col-md-12 mt-3">
						<p><strong>Fotos</strong></p>
						<div class="image-slider">
							<div class="image-slider-content">
								<div>
									<figure class="item">
										<img width="107" height="102" src="<?php echo TEMPLATE_URI; ?>/assets/img/criesp/criesp1.png" alt="Foto 1">
									</figure>
								</div>
								<div>
									<figure class="item">
										<img width="107" height="102" src="<?php echo TEMPLATE_URI; ?>/assets/img/criesp/criesp2.png" alt="Foto 2">
									</figure>
								</div>
								<div>
									<figure class="item">
										<img width="107" height="102" src="<?php echo TEMPLATE_URI; ?>/assets/img/criesp/criesp3.png" alt="Foto 3">
									</figure>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12 mt-3">
						<p><strong>Videos</strong></p>
						<div class="video-slider">
							<video muted="" autoplay="" loop="" playsinline="" controls="" poster="https://especiaiscomunicacaoprod.s3.amazonaws.com/criesp/doacoes/crianca/doacao/maik-doacao.png?Expires=1627496328&amp;AWSAccessKeyId=AKIAJXGK6DAEMAYESHFQ&amp;Signature=a1SxmAD%2BObKTL06O%2FhkTZUIu3dE%3D">
								<source src="https://player.vimeo.com/external/581340431.hd.mp4?s=b5409bc8aef8c09550fb041a6db6dc5a2c324c15&profile_id=174" type="video/mp4">
							</video>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php
}

function criesp_get_thankyou_box()
{
?>
	<div class="row mb-5">
		<div class="col-md-12">
			<div class="box-round p-4 criesp-thankyou-box">
				<div class="row">
					<div class="mt-2 mb-4 col-md-12 text-center">
						<img width="192" src="<?php echo TEMPLATE_URI . '/assets/img/criesp/logo-criesp.png';  ?>" alt="Logo Criança Esperança" />
					</div>
				</div>
				<h4 class="title">Obrigado por ajudar o Criança Esperança.</h4>
				<p class="description m-0">
					Na Polen 100% do valor dos vídeos serão revertidos em doações para o Criança Esperança.
					Em até 15 dias o seu ídolo vai enviar o seu video-agradecimento.
				</p>
			</div>
		</div>
	</div>
<?php
}

/**
 * Cria o card do CRIESP onde mostra
 * @param WP_Post $post
 * @param stdClass Polen_Update_Fields
 */
function criesp_get_send_video_date()
{
?>
	<div class="col-md-12 mt-3">
		<div class="row">
			<div class="col-12 col-md-12 m-md-auto">
				<div class="row">
					<div class="col-12 col-md-12 text-center text-md-center">
						<span class="skill-title">Você receberá seu vídeo até</span>
						<p class="p mb-0 mt-2">
							<span class="skill-value">
								<?php Icon_Class::polen_icon_clock(); ?>
								<?php 
									$date = date("d/m/Y");
									echo date( "d/m/Y", strtotime('+15 days') );
								?>
							</span>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}
