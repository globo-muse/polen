<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Polen
 */
?>

<footer id="colophon" class="site-footer pb-5">
	<?php if ( (!is_page('cart') || !is_cart()) &&
		(!is_page('checkout') || !is_checkout()) && !polen_is_landingpage()) : ?>
    <!-- Desktop Footer -->
		<div class="row d-none d-md-block">
			<div class="col-12">
				<div class="row justify-content-md-between">
          <div class="<?php echo is_front_page() ? "col-md-8" : "col-md-12" ?>">
            <div class="row">
              <div class="col-md-2 mt-4">
                <h5 class="title typo typo-title typo-small">Conta</h5>
                <ul class="footer-menu">
                  <li><a href="/register">Cadastro</a></li>
                  <li><a href="/my-account">Minha conta</a></li>
                  <li><a href="/acompanhamento-pedido">Acompanhe seu pedido</a></li>
                </ul>
              </div>
              <div class="col-md-2 mt-4">
                <h5 class="title typo typo-title typo-small">Institucional</h5>
                <ul class="footer-menu">
                  <li><a href="/sobre-nos">Sobre nós</a></li>
                  <li><a href="https://br.linkedin.com/company/polen-me" target="_blank" rel="noreferrer">Trabalhe conosco</a></li>
                  <li><a href="/polenmais" rel="noreferrer">Blog</a></li>
                </ul>
              </div>
              <div class="col-md-2 mt-4">
                <h5 class="title typo typo-title typo-small">Redes Sociais</h5>
                <ul class="footer-menu">
                  <li><a href="https://vm.tiktok.com/ZMeKtWr1H/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("tiktok"); ?>TikTok</a></li>
                  <li><a href="https://www.instagram.com/polen.me/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("instagram"); ?>Instagram</a></li>
                  <li><a href="https://www.facebook.com/Polen-107879504782470/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("facebook"); ?>Facebook</a></li>
                  <li><a href="https://twitter.com/polen_me/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("twitter"); ?>Twitter</a></li>

                </ul>
              </div>
              <div class="col-md-3 mt-4">
                <h5 class="title typo typo-title typo-small">Transparência</h5>
                <ul class="footer-menu">
                  <li><a href="//polen.me/polen/uploads/2022/04/termos-de-uso-polen.pdf" target="_blank" rel="noreferrer">Termos de uso</a></li>
                  <li><a href="/politica-de-privacidade">Política de privacidade</a></li>
                </ul>
              </div>
              <div class="col-md-3 mt-4">
                <h5 class="title typo typo-title typo-small">Para Empresas</h5>
                <ul class="footer-menu">
                  <li><a href="/empresas">Polen para empresas</a></li>
                  <li><a href="https://www.instagram.com/polenparaempresas/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("instagram"); ?>Instagram</a></li>
                  <li><a href="/empresas#faleconosco">Fale conosco</a></li>
                </ul>
              </div>
            </div>
          </div>
          <?php
						if(is_front_page()) {
							polen_form_signin_newsletter();
						}
					?>
				</div>
			</div>
		</div>
    <!-- Mobile Footer -->
		<div class="row d-block d-md-none">
			<div class="col-12">
				<div class="row">
          <?php
						if(is_front_page()) {
							polen_form_signin_newsletter("newsletter-mobile");
						}
					?>
          <div class="col-12 mt-3">
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="false">
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="heading-conta">
                  <h4 class="panel-title">
                    <a class="panel-button" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-conta" aria-expanded="false" aria-controls="collapse-conta">
                      <h5>Conta</h5>
                    </a>
                  </h4>
                </div>
                <div id="collapse-conta" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-conta">
                  <div class="panel-body">
                    <ul class="footer-menu">
                      <li><a href="/register">Página de cadastro</a></li>
                      <li><a href="<?php echo polen_get_all_talents_url(); ?>">Todos os Ídolos</a></li>
                      <li><a href="/my-account">Minha conta</a></li>
                      <li><a href="/acompanhamento-pedido">Acompanhe seu pedido</a></li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="heading-transparencia">
                  <h4 class="panel-title">
                    <a class="panel-button" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-transparencia" aria-expanded="false" aria-controls="collapse-transparencia">
                      <h5>Transparência</h5>
                    </a>
                  </h4>
                </div>
                <div id="collapse-transparencia" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-transparencia">
                  <div class="panel-body">
                    <ul class="footer-menu">
                      <li><a href="/termos-de-uso">Termos de uso</a></li>
                      <li><a href="/politica-de-privacidade">Política de privacidade</a></li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="heading-social">
                  <h4 class="panel-title">
                    <a class="panel-button" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-social" aria-expanded="false" aria-controls="collapse-social">
                      <h5>Redes Sociais</h5>
                    </a>
                  </h4>
                </div>
                <div id="collapse-social" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-social">
                  <div class="panel-body">
                    <ul class="footer-menu">
                      <li><a href="https://vm.tiktok.com/ZMeKtWr1H/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("tiktok"); ?>TikTok</a></li>
                      <li><a href="https://www.instagram.com/polen.me/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("instagram"); ?>Instagram</a></li>
                      <li><a href="https://www.facebook.com/Polen-107879504782470/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("facebook"); ?>Facebook</a></li>
                      <li><a href="https://twitter.com/polen_me/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("twitter"); ?>Twitter</a></li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="heading-institucional">
                  <h4 class="panel-title">
                    <a class="panel-button" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-institucional" aria-expanded="false" aria-controls="collapse-institucional">
                      <h5>Institucional</h5>
                    </a>
                  </h4>
                </div>
                <div id="collapse-institucional" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-institucional">
                  <div class="panel-body">
                    <ul class="footer-menu">
                      <li><a href="/sobre-nos">Sobre nós</a></li>
                      <li><a href="https://br.linkedin.com/company/polen-me" target="_blank" rel="noreferrer">Trabalhe conosco</a></li>
                      <li><a href="/polenmais" rel="noreferrer">Blog</a></li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="heading-para-empresas">
                  <h4 class="panel-title">
                    <a class="panel-button" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-para-empresas" aria-expanded="false" aria-controls="collapse-para-empresas">
                      <h5>Para Empresas</h5>
                    </a>
                  </h4>
                </div>
                <div id="collapse-para-empresas" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-para-empresas">
                  <div class="panel-body">
                    <ul class="footer-menu">
                      <li><a href="/empresas">Polen para empresas</a></li>
                      <li><a href="https://www.instagram.com/polenparaempresas/" target="_blank" rel="noreferrer"><?php Icon_Class::polen_icon_social("instagram"); ?>Instagram</a></li>
                      <li><a href="/empresas#faleconosco">Fale conosco</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<!-- <div class="row mt-5 copyright">
		<div class="col-md-12 pt-3 text-center">2021 @<?= get_bloginfo('name'); ?></div>
	</div>.site-info -->
</footer><!-- #colophon -->

<script>
  (function ($) {
    // Footer accordion close behavior
    $(document).on("click", ".panel-button", function (e) {
      let id = $(this).attr('href');
      $('.panel-button:not([href='+id+'])').addClass("collapsed").attr("aria-expanded","false");
      $('.collapse:not('+id+')').removeClass('show');
    });
  })(jQuery);
</script>

<?php include_once TEMPLATE_DIR . '/inc/custom-footer.php'; ?>

</div><!-- #Container -->

<?php wp_footer(); ?>
<?php do_action( 'polen_messages_service_error' ); ?>
<?php do_action( 'polen_messages_service_success' ); ?>
<?php Polen\Includes\Polen_Messages::clear_messages(); ?>
<?php include_once TEMPLATE_DIR . '/inc/analitics_footer.php'; ?>
</body>

</html>

<?php if( defined('DEV_ENV') && DEV_ENV ) : ?>
<!--
<?php print_r( get_included_files() ); ?>
-->
<?php endif; ?>
