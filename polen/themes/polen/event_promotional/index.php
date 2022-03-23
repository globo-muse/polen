<?php

/**
 * Template name: Página Inicial Vídeo Autógrafo
 */

use Polen\Includes\Module\Polen_Product_Module;
use Polen\Includes\Polen_Order;

$inputs = new Material_Inputs();

$product = $GLOBALS[ Promotional_Event_Rewrite::GLOBAL_KEY_PRODUCT_OBJECT ];

if( 'instock' == $product->get_stock_status() ) {
	$has_stock = true;
} else {
	$has_stock = false;
}

$image_data = polen_get_thumbnail($product->get_id());

$polen_product = new Polen_Product_Module( $product );
get_header();
?>

<main id="primary" class="site-main">

  <!-- Botão de Voltar -->
  <div class="row mb-4">
    <div class="col-12 col-md-6 m-md-auto d-md-none">
      <a href="<?php echo get_home_url()."/lacta"; ?>">
        <div class="go-back-button">
          <i class='icon icon-left-arrow'></i>
        </div>
      </a>
    </div>
  </div>

    <?php
    $orders_ids = event_promotional_orders_ids_by_user_id_status( $product->get_id(), 'lacta', Polen_Order::ORDER_STATUS_COMPLETED );
    if( count( $orders_ids ) > 0 ) :
      shuffle( $orders_ids );
      $orders_ids = array_slice( $orders_ids, 0, 7 );
      polen_front_get_videos( polen_get_home_stories( $orders_ids ), "" );
    ?>

  <!-- Perfil -->
	<div class="row mb-4">
    <div class="col-12 col-md-6 m-md-auto d-flex flex-wrap justify-content-left lacta-profile mini">
      <figure class="image">
        <img loading="lazy" src="<?php echo $image_data["image"] ?>" alt="<?php echo $product->get_title(); ?>">
      </figure>
      <div class="content ml-2 mt-1">
        <h2 class="name"><?php echo $product->get_title(); ?></h2>
        <h3 class="category m-0"><?= $polen_product->get_category_name(); ?></h3>
      </div>
		</div>
	</div>

  <?php else : ?>

  <!-- Perfil -->
	<div class="row mb-5">
    <div class="col-12 col-md-6 m-md-auto d-flex flex-wrap justify-content-center lacta-profile">
      <figure class="image mb-4">
        <img loading="lazy" src="<?php echo $image_data["image"] ?>" alt="<?php echo $product->get_title(); ?>">
      </figure>
      <h2><?php echo $product->get_title(); ?></h2>
		</div>
	</div>

  <?php endif; ?>

  <!-- Botão de adicionar ao carrinho -->
	<div class="row mb-5 talent-page-footer">
		<div class="col-12 col-md-6 m-md-auto event-lacta">
			<?php if($has_stock) : ?>
        <div class="btn-buy-b2b">
          <a href="<?php echo event_promotional_url_code_validation( $product ); ?>">
            <div class="mdc-button mdc-button--raised mdc-ripple-upgraded">
              Resgatar meu vídeo
            </div>
          </a>
        </div>
			<?php else: ?>
        <div class="lacta-btn-disable mb-3">
          <div class="mdc-button mdc-button--raised mdc-ripple-upgraded">
            Esgotado
          </div>
        </div>
        <?php $inputs->material_button_link_outlined("todos", "Escolher outro artista", home_url( "lacta" ), false, "", array(), $donate ? "donate" : ""); ?>
			<?php endif; ?>
		</div>
	</div>

  <!-- Bio -->
	<div class="row mb-5">
		<div class="col-12 col-md-6 m-md-auto d-flex">
      <div class="lacta-bio">
        <h5>Sobre o vídeo de <?php echo $product->get_title(); ?></h5>
        <p>
          Comprando produtos da Lacta você pode ganhar um vídeo exclusivo de <?php echo $product->get_title(); ?>
          com uma mensagem de fim de ano para se presentear ou surpreender quem você ama!
        </p>
      </div>
		</div>
	</div>

  <!-- Instruções -->
  <div class="row mb-5">
		<div class="col-12 col-md-6 m-md-auto event-lacta">
      <?php get_lacta_insctruction($product); ?>
		</div>
	</div>

  <!-- Banner -->
  <div class="row mb-4">
    <div class="col-12 col-md-6 m-md-auto">
      <div class="lacta-wrapper">
        <div class="lacta-carousel">
          <img src="<?php echo TEMPLATE_URI . '/assets/img/lacta/banner-1.jpg'; ?>" alt="Banner Lacta" style="width: 100%">
          <a href="<?= $product->get_meta( '_promotional_event_link_buy', true ); ?>" target="_blank" class="lacta-banner-link"></a>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-12 col-md-6 m-md-auto event-lacta">
      <a href="<?= $product->get_meta( '_promotional_event_link_buy', true ); ?>" target="_blank">
        <div class="mdc-button mdc-button--outlined mdc-ripple-upgraded" style="--mdc-ripple-fg-size:294px; --mdc-ripple-fg-scale:1.71077; --mdc-ripple-fg-translate-start:74.375px, -113.195px; --mdc-ripple-fg-translate-end:98px, -120px;">
          Compre agora
        </div>
      </a>
    </div>
  </div>

</main><!-- #main -->

<?php
get_footer();
