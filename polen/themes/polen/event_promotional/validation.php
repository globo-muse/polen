<?php

/**
 * Template name: Página Inicial Vídeo Autógrafo
 */
$product = $GLOBALS[ Promotional_Event_Rewrite::GLOBAL_KEY_PRODUCT_OBJECT ];
$pep = new Promotional_Event_Product( $product );
$img = $pep->get_url_image_product_with_size( 'polen-thumb-lg' );
get_header();
?>

<main id="primary" class="site-main">
  <!-- Botão de Voltar -->
  <div class="row mb-4">
    <div class="col-12 col-md-8 m-md-auto">
      <a href="<?php echo get_home_url().'/produtos'."/".$product->slug; ?>">
        <div class="go-back-button">
          <i class='icon icon-left-arrow'></i>
        </div>
      </a>
    </div>
  </div>
	<div class="row">
		<div class="col-12 col-md-8 m-md-auto">
      <div class="row">
        <div class="col-12 mb-4">
          <?php polen_get_lacta_header_talent($img, $product->get_title()); ?>
        </div>
      </div>
      <div class="row">
        <div class="col-12 mb-4">
          <div class="lacta-instruction event-lacta">
            <div class="header-box text-center">
              <h3 style="line-height:20px;">Como resgatar meu vídeo</h3>
            </div>
            <div class="content-box mt-3 px-2">
              <div class="row">
                <div class="col-12 d-flex align-items-center">
                  <ul class="order-flow half">
                    <li class="item itempayment-approved complete">
                      <span class="background status">1</span>
                      <span class="text">
                        <p class="description">Compre produtos no site da <a href="<?= $product->get_meta( '_promotional_event_link_buy', true ); ?>" target="_blank"><b>Lacta</b></a>, usando o código do seu ídolo.</p>
                      </span>
                    </li>
                    <li class="item itempayment-approved complete">
                      <span class="background status">2</span>
                      <span class="text">
                        <p class="description">Os 50 primeiros compradores a usar o código do seu ídolo, vão receber um e-mail com o cupom para resgatar o vídeo.</p>
                      </span>
                    </li>
                    <li class="item itempayment-approved complete">
                      <span class="background status">3</span>
                      <span class="text">
                        <p class="description">Insira o cupom no campo abiaxo e clique em  "Checar”.</p>
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
			<?php lacta_coupon( $product ); ?>
		</div>
	</div>
</main><!-- #main -->

<?php
get_footer();
