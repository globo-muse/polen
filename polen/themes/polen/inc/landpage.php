<?php /* Template Name: Landpage */ ?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="<?php bloginfo('name'); ?>">
	<meta name="apple-touch-fullscreen" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="theme-color" content="#000000">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
	<script>
		var polenObj = {
			base_url: '<?= site_url(); ?>',
			developer: <?php echo DEVELOPER ? 1 : 0; ?>
		};
	</script>

	<?php include_once TEMPLATE_DIR . '/inc/analitics_header.php'; ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<?php include_once TEMPLATE_DIR . '/inc/analitics_init_body.php'; ?>
	<div id="page" class="container site landing">
		<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'polen'); ?></a>

		<div class="row pt-md-5 px-4">
			<div class="d-none d-md-block col-md-6">
				<img src="<?php echo TEMPLATE_URI; ?>/assets/img/image_landing.png" alt="Print tirado do site">
			</div>
			<div class="col-12 col-md-6">
				<div class="row">
					<div class="col-12 mt-4 text-center text-md-left">
						<?php the_custom_logo(); ?>
					</div>
					<div class="col-12 mt-5 text-center text-md-left">
						<h1 class="title"><span>Quer receber vídeos do seu artista favorito gravados </span>especialmente<span> para você?</span></h1>
					</div>
					<div class="col-12 mt-4 text-center text-md-left d-md-none">
						<img src="<?php echo TEMPLATE_URI; ?>/assets/img/image_landing_mobile.png" width="233" height="292" alt="Print tirado do site">
					</div>
					<div class="col-12 col-md-12 mt-5">
						<div class="row justify-content-md-between">
							<div id="signin-newsletter" class="col-12">
								<h5 class="description">Experimente um novo jeito de se relacionar com seu artista favorito. Entre na lista de espera:</h5>
								<div class="row mt-3 mt-md-4">
									<div class="col-12 col-md-12 col-xl-7">
										<input type="email"  name="signin_newsletter" placeholder="Deixe seu email" class="form-control form-control-lg" />
										<input type="hidden" name="signin_newsletter_page_source" value="landing-page"/>
										<input type="hidden" name="signin_newsletter_is_mobile" value="<?= polen_is_mobile() ? '1' : '0'; ?>"/>
										<input type="hidden" name="signin_newsletter_event" value="pre-signin"/>
									</div>
									<?php
									$newsletter_signin_nonce = wp_create_nonce('news-signin');
									?>
									<div class="col-12 col-md-12 col-xl-5 mt-3 mt-md-3 mt-xl-0 d-md-flex align-items-md-center">
										<button class="signin-newsletter-button btn btn-primary btn-lg btn-block" code="<?php echo $newsletter_signin_nonce; ?>">Quero Experimentar</button>
									</div>
									<div class="col-12 col-md-12 mb-2 mb-md-0 text-center small signin-response"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<footer id="colophon" class="mt-5 px-4 site-footer landing-footer">
			<div class="row mt-5 pb-5 copyright">
				<div class="d-none d-md-block col-12 col-md-4 text-center text-md-left">
					<?php the_custom_logo(); ?>
				</div>
				<div class="col-12 col-md-4 pb-4 pb-md-0 order-md-1 text-center text-md-right social">
					<a href="https://www.facebook.com/Polen-107879504782470/" target="_blank"><?php Icon_Class::polen_icon_social("facebook"); ?></a>
					<a href="https://vm.tiktok.com/ZMeKtWr1H/" target="_blank"><?php Icon_Class::polen_icon_social("tiktok"); ?></a>
					<a href="https://www.instagram.com/polen.me/" target="_blank"><?php Icon_Class::polen_icon_social("instagram"); ?></a>
				</div>
				<div class="col-12 col-md-4 pt-3 pt-md-0 order-md-0 text-center">2021 @Polen</div>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
	</div><!-- #Container -->

	<?php wp_footer(); ?>

</body>

</html>
