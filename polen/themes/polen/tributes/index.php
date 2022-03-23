<?php get_header('tributes'); ?>

<?php //TODO ajustar URLs
?>
<?php $create_url = tribute_get_url_new_tribute(); ?>
<?php $dashboard_url = tribute_get_url_my_tributes(); ?>

<main class="overflow-hidden">
	<div class="container py-3 tribute-container tribute-app">
		<section class="row mt-2 pb-5">
			<div class="col-md-6 d-flex align-items-center">
				<div class="row">
					<div class="col-md-12">
						<h1 class="title main-title text-center text-md-left">Dê o <span class="color">presente mais significativo</span> do mundo</h1>
						<p class="mt-4 text-center text-md-left">O Colab simplifica a criação de um vídeo-presente em grupo que você pode dar em qualquer ocasião importante.</p>
						<div class="row d-none d-md-block">
							<div class="col-10 mt-4">
								<a href="<?php echo $create_url; ?>" class="btn btn-primary btn-lg btn-block">Comece uma homenagem</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="presentation-wrap">
					<div class="presentation with-video">
						<video id="tribute-home-video">
							<source src="<?php echo TEMPLATE_URI; ?>/tributes/assets/video-presentation.mp4#t=0.1" type="video/mp4">
							</source>
						</video>
						<button id="btn-play" class="btn-play">
							<img src="<?php echo TEMPLATE_URI; ?>/tributes/assets/img/play.svg" alt="Botão play">
						</button>
					</div>
					<div class="presentation-extra-area">
						<div class="presentation">
							<img src="<?php echo TEMPLATE_URI; ?>/tributes/assets/img/tribute-img1.jpg" alt="Imagem de fundo 1">
						</div>
						<div class="presentation">
							<img src="<?php echo TEMPLATE_URI; ?>/tributes/assets/img/tribute-img1.jpg" alt="Imagem de fundo 1">
						</div>
						<div class="presentation">
							<img src="<?php echo TEMPLATE_URI; ?>/tributes/assets/img/tribute-img1.jpg" alt="Imagem de fundo 1">
						</div>
					</div>
				</div>
				<div class="row d-block d-md-none">
					<div class="col-12 mt-4 ml-auto mr-auto">
						<a href="<?php echo $create_url; ?>" class="btn btn-primary btn-lg btn-block">Comece uma homenagem</a>
					</div>
				</div>
			</div>
		</section>
		<section class="row tutorial mt-md-4 mb-4 pt-md-4 border-md-top">
			<div class="col-12">
				<h2 class="title text-center">Como funciona?</h2>
				<p class="text-center">Leva 60 segundos para começar e você pode criar seu Colab em qualquer dispositivo!</p>
				<div class="d-block d-md-flex justify-content-md-between mt-5">
					<div class="box-round how-to-tribute d-flex justify-content-between d-md-block">
						<div class="ico mr-4 d-flex align-items-center text-md-center d-md-block mr-md-0">
							<img src="<?php echo TEMPLATE_URI; ?>/tributes/assets/img/user-to-user-transmission.svg" alt="Convide Amigos">
						</div>
						<div class="text text-left text-md-center">
							<h4 class="title">Convide amigos</h4>
							<p class="description">Convide amigos e familiares para participar da celebração.</p>
						</div>
					</div>
					<div class="box-round how-to-tribute d-flex justify-content-between d-md-block">
						<div class="ico mr-4 d-flex align-items-center text-md-center d-md-block mr-md-0">
							<img src="<?php echo TEMPLATE_URI; ?>/tributes/assets/img/carousel-video.svg" alt="Colete vídeos">
						</div>
						<div class="text text-left text-md-center">
							<h4 class="title">Colete vídeos</h4>
							<p class="description">Todo mundo recebe um convite, faz um vídeo e o envia.</p>
						</div>
					</div>
					<div class="box-round how-to-tribute d-flex justify-content-between d-md-block">
						<div class="ico mr-4 d-flex align-items-center text-md-center d-md-block mr-md-0">
							<img src="<?php echo TEMPLATE_URI; ?>/tributes/assets/img/share-one.svg" alt="Compartilhe">
						</div>
						<div class="text text-left text-md-center">
							<h4 class="title">Compartilhe</h4>
							<p class="description">Envie os vídeos e peça que nossa equipe faça isso por você.</p>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section class="row mb-4 mt-0 mt-md-5 mb-md-0">
			<div class="col-12">
				<div class="box-round p-4 p-md-5">
					<div class="row align-items-center">
						<div class="text-center text-md-left col-md-7">
							<div class="title">Comece uma homenagem em 60 segundos ou menos!</div>
						</div>
						<div class="col-md-5 mt-3">
							<a href="<?php echo $create_url; ?>" class="btn btn-primary btn-lg btn-block">Comece uma homenagem</a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<a name="acompanheseupedido"></a>
		<section class="row mt-0 mt-md-4">
			<div class="col-12">
				<div class="box-round p-4 p-md-5">
					<div class="row align-items-center">
						<div class="text-center text-md-left col-md-7">
							<div class="title">Já pediu o seu Colab?<br /><small>Acompanhe o seu pedido</small></div>
						</div>
						<div class="col-md-5 mt-3">
							<form action="<?php echo $dashboard_url; ?>" method="POST">
								<input type="email" name="email" id="email" placeholder="Seu e-mail" class="form-control form-control-lg" required />
								<button type="submit" class="btn btn-primary btn-lg btn-block mt-2">Acompanhar</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</main>

<?php get_footer('tributes'); ?>
