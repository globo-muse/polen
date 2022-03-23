<?php

/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Polen
 */

get_header();
?>

<main id="primary" class="row site-main">

	<section class="col-12 col-md-4 m-md-auto error-404 not-found">
		<header class="page-header text-center">
			<img src="<?php echo TEMPLATE_URI; ?>/assets/img/errors/404.png" alt="Erro 404">
			<h1 class="page-title"><?php esc_html_e('Ops! página não encontrada', 'polen'); ?></h1>
		</header><!-- .page-header -->

		<div class="page-content text-center mt-4">
			<p><?php esc_html_e('Nada encontrado no endereço digitado. Por favor tente outro endereço.', 'polen'); ?></p>
			<a href="<?php echo get_home_url(); ?>" class="btn btn-outline-light btn-lg mt-4">Voltar para home</a>
		</div><!-- .page-content -->
	</section><!-- .error-404 -->

</main><!-- #main -->

<?php
get_footer();
