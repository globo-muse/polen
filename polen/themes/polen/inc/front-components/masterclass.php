<?php

function mc_get_home_banner($link)
{
?>
	<div class="row mt-4">
		<div class="col-12">
			<div class="mc-banner">
				<img class="image mobile-img" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/mc-banner-mobile.png'; ?>" alt="Polen Masterclass" />
				<img class="image desktop-img" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/mc-banner-desktop.png'; ?>" alt="Polen Masterclass" />
				<div class="content">
					<div class="left">
						<img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/masterclass-logo.png'; ?>" alt="Polen Masterclass"></img>
						<p class="mt-3">
							Aprenda como escolher, apreciar e <br>
							harmonizar vinhos com Ronnie Von
						</p>
						<a href="<?php echo $link; ?>" class="btn btn-primary btn-md">Conheça<span class="ml-2"><?php Icon_Class::polen_icon_chevron_right(); ?></span></a>
					</div>
					<div class="right">
						<img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/mask.png'; ?>" alt="Polen Masterclass"></img>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

function mc_get_home_banner_gustavo($link)
{
?>
	<div class="row mt-4">
		<div class="col-12">
			<div class="mc-banner">
				<img class="image mobile-img" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/mobile-banner-gustavo-bg.png'; ?>" alt="Polen Masterclass" />
				<img class="image desktop-img" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/banner-gustavo-bg.png'; ?>" alt="Polen Masterclass" />
				<div class="content">
					<div class="left">
						<img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/masterclass-logo.png'; ?>" alt="Polen Masterclass"></img>
						<p class="mt-3">
              Aprenda com dois mestres da comédia<br>a se comunicar melhor
						</p>
						<a href="<?php echo $link; ?>" class="btn btn-primary btn-md">Conheça<span class="ml-2"><?php Icon_Class::polen_icon_chevron_right(); ?></span></a>
					</div>
					<div class="right mr-4">
						<img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/mask-gustavo.png'; ?>" alt="Polen Masterclass" style="margin: 2em 4em;"></img>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

function mc_get_top_banner()
{
?>
	<div class="row mb-4">
		<div class="col-12">
			<div class="mc-landing-banner">
				<figure class="mc-logo">
					<img class="image" src="<?php echo TEMPLATE_URI; ?>/assets/img/masterclass/ronnievon-logo.png" alt="Logo Beabá do Vinho" />
				</figure>
				<div class="row">
					<div class="col-12 col-md-6 m-md-auto">
						<h1 class="title">Aprenda a escolher, apreciar e harmonizar vinhos com Ronnie Von</h1>
					</div>
				</div>
				<div class="mc-home-video mb-4">
					<video id="mc-video" playsinline poster="<?php echo TEMPLATE_URI; ?>/assets/img/masterclass/player-poster.jpg?v=2">
						<source src="https://player.vimeo.com/external/595532426.sd.mp4?s=ab2b9eebb3b1c17cd060ebe49d31ed2949472cea&profile_id=164" type="video/mp4">
					</video>
				</div>
				<div class="row">
					<div class="col-12 col-md-6 m-md-auto">
						<h2 class="subtitle">Participe do grupo de pré-inscrição no WhatsApp para ter um desconto exclusivo no primeiro dia das inscrições.</h2>
					</div>
				</div>
				<div class="row mt-4">
					<div class="col-12 col-md-6 m-md-auto">
						<form action="" id="form-email-masterclass">
							<?php //TODO action e nonce
							?>
							<input type="hidden" name="action" value="send_form_request">
							<input type="hidden" name="page_source" value="<?= filter_input(INPUT_SERVER, 'REQUEST_URI'); ?>" />
							<input type="hidden" name="is_mobile" value="<?= polen_is_mobile() ? "1" : "0"; ?>" />
							<input type="hidden" name="security" value=<?php echo wp_create_nonce("send-form-request"); ?>>
							<input type="email" name="email" class="form-control form-control-lg" placeholder="Digite seu e-mail" required />
							<input type="submit" value="Quero ganhar desconto" class="btn btn-primary btn-lg btn-block mt-4 gradient" />
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
		const success = "<?php echo master_class_url_success(); ?>";

		polVideoTag("#mc-video");

		const formName = "form#form-email-masterclass";
		document.querySelector(formName).addEventListener("submit", function(evt) {
			evt.preventDefault();
			polAjaxForm(formName, function() {
				window.location.href = success;
			}, function(err) {
				polMessages.error(err);
			});
		});
	</script>
<?php
}

function mc_get_carrossel_how_to() {
?>
	<div class="row mb-4">
		<div class="col-12">
			<h3 class="title mb-2">Como funciona?</h3>
		</div>
		<div class="col-12">
			<!-- <div id="how-to-carousel" class="owl-carousel owl-theme">
				<div class="item">
					<div class="box-round py-3 px-3">
						<div class="row">
							<div class="col-12 mb-1 d-flex justify-content-center">
								<?php //Icon_Class::polen_icon_camera_video(); ?>
							</div>
							<div class="col-12">
								<h4>Ao vivo</h4>
								<p class="text-center">Participe de aulas ao vivo e converse em tempo real, tirando todas suas dúvidas com Ronnie Von</p>
							</div>
						</div>
					</div>
				</div>
				<div class="item">
					<div class="box-round py-3 px-3">
						<div class="row">
							<div class="col-12 mb-1 d-flex justify-content-center">
								<?php //Icon_Class::polen_icon_clock(); ?>
							</div>
							<div class="col-12">
								<h4>Duração</h4>
								<p class="text-center">Participe do curso com duração de x dias, exclusivo e feito sobre medida para amantes de vinho.</p>
							</div>
						</div>
					</div>
				</div>
				<div class="item">
					<div class="box-round py-3 px-3">
						<div class="row">
							<div class="col-12 mb-1 d-flex justify-content-center">
								<?php //Icon_Class::polen_icon_hand_thumbs_up(); ?>
							</div>
							<div class="col-12">
								<h4>Disponibilidade</h4>
								<p class="text-center">Tenha acesso e tire suas dúvidas diretamanente com o Ronnie Von.</p>
							</div>
						</div>
					</div>
				</div>
			</div> -->
		</div>
	</div>
<?php
}

function mc_get_box_content()
{
	?>
	<div class="row">
		<div class="col-12 mb-3">
			<h2>Conteúdo do curso</h2>
		</div>
		<div class="col-12">
			<div class="box-round p-4 masterclass-content-box">
				<div class="row">
					<div class="col-2">
						<img src="<?php echo TEMPLATE_URI . "/assets/img/masterclass/taca.svg"; ?>" alt="Ícone garrafa" />
					</div>
					<div class="col-10 pl-0 ml-0">
						<p class="description"><strong>História e Importância do Vinho:</strong> quais os principais tipos de vinhos.</p>
					</div>
				</div>
				<div class="row mt-4">
					<div class="col-2">
						<img src="<?php echo TEMPLATE_URI . "/assets/img/masterclass/garrafa.svg"; ?>" alt="Ícone garrafa" />
					</div>
					<div class="col-10 pl-0 ml-0">
						<p class="description"><strong>Só vinho caro tem qualidade?</strong> Como escolher vinho bom e barato.</p>
					</div>
				</div>
				<div class="row mt-4">
					<div class="col-2">
						<img src="<?php echo TEMPLATE_URI . "/assets/img/masterclass/taca_garrafa.svg"; ?>" alt="Ícone garrafa" />
					</div>
					<div class="col-10 pl-0 ml-0">
						<p class="description"><strong>O que ler nos rótulos das garrafas para escolher seu vinho?</strong> Tipos de taças para cada tipo de vinho.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
