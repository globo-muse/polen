<?php
global $invite, $trbute;
// var_dump( $invite, $trbute );

?>

<?php get_header('tributes'); ?>

<main id="invite-friends">
	<div class="container py-3 tribute-container tribute-app">
		<div class="row">
			<div class="mb-4 col-md-12">
				<h1 class="title text-center">Vídeo enviado com Sucesso</h1>
			</div>
			<div class="mb-5 col-md-12 text-center">
				<img src="<?php echo TEMPLATE_URI ?>/tributes/assets/img/mobile-checked.svg" alt="">
			</div>
			<div class="col-md-6 m-md-auto">
				<?php //TODO link final
				?>
				<a href="<?= tribute_get_url_base_url(); ?>" class="btn btn-outline-light btn-lg btn-block">Quero saber mais sobre os vídeos de presente</a>
			</div>
		</div>
	</div>
</main>

<?php get_footer('tributes'); ?>
