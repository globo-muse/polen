<?php

/**
 * Template name: Página Inicial
 */

get_header();
$inputs = new Material_Inputs();
?>

<main id="primary" class="site-main">
  <h1 class="d-none">Presenteie e supreenda com Vídeos Personalizados.</h1>

  <!-- Categorias -->
  <?php polen_front_get_categories_buttons(); ?>

  <!-- Banner Principal - Vídeo -->
  <?php polen_front_get_banner_video(); ?>

  <div class="row mb-3">
    <div class="col-sm-12">
      <!-- Listagem de Talentos - Destaques -->
      <?php polen_banner_scrollable(polen_get_new_talents(10), "Destaque"); ?>
    </div>
  </div>

  <!-- Banners -->
	<div class="row">
		<div class="col-12">
			<div id="product-carousel" class="owl-carousel owl-theme">
        <div class="item">
          <?php
            $banners = array(
              "mobile" => TEMPLATE_URI.'/assets/img/banners/b2b/banner-mobile.png',
              "desktop" => TEMPLATE_URI.'/assets/img/banners/b2b/banner-desktop.png'
            );
          ?>
          <?php polen_get_banner('Polen para Empresas', $banners, site_url('/empresas')); ?>
				</div>
        <div class="item">
          <?php
            $banners = array(
              "mobile" => TEMPLATE_URI.'/assets/img/banners/galo/banner-mobile.png',
              "desktop" => TEMPLATE_URI.'/assets/img/banners/galo/banner-desktop.png'
            );
          ?>
          <?php polen_get_banner('Galo Ídolos', $banners, site_url('tag/galo-idolos/')); ?>
				</div>
			</div>
		</div>
	</div>

  <!-- <div class="row mb-3">
		<div class="col-12">
      <div id="carouselExampleIndicators" class="carousel slide carousel-fade" data-ride="carousel">
        <ol class="carousel-indicators">
          <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
          <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
        </ol>
        <div class="carousel-inner">
          <div class="carousel-item active">
            <?php //polen_get_bbb_banner(site_url('tag/bbb/')); ?>
          </div>
          <div class="carousel-item">
            <?php //polen_get_galo_banner(site_url('tag/galo-idolos/')); ?>
          </div>
        </div>
        <button class="carousel-control-prev d-block d-md-none" type="button" data-target="#carouselExampleIndicators" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </button>
        <button class="carousel-control-next d-block d-md-none" type="button" data-target="#carouselExampleIndicators" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </button>
      </div>
		</div>
	</div> -->

  <!-- Como Funciona -->
  <?php //polen_front_get_tutorial(); ?>

  <!-- Listagem de Vídeos em Destaque -->
  <?php
    //$videos = ["3492", "3806", "3554", "2930", "3898", "3168"];
    //polen_front_get_videos(polen_get_home_stories($videos));

    $emojis = array(
      "b2b" => TEMPLATE_URI.'/assets/img/emoji/b2b.png',
      "musica" => TEMPLATE_URI.'/assets/img/emoji/music.png',
      "atrizes-e-atores" => TEMPLATE_URI.'/assets/img/emoji/arts.png',
      "apresentadores" => TEMPLATE_URI.'/assets/img/emoji/mic.png',
      "esporte" => TEMPLATE_URI.'/assets/img/emoji/esporte.png',
      "influencers" => TEMPLATE_URI.'/assets/img/emoji/selfie.png',
      "comediantes" => TEMPLATE_URI.'/assets/img/emoji/laugh.png'
    );
  ?>

  <div class="row">
    <?php/*
    <div class="col-sm-12 mb-4">
      <!-- Listagem de Talentos - B2B -->
      <?php polen_banner_scrollable(polen_get_talents_by_product_cat("b2b-only", 10), "Para Empresas", $emojis['b2b'], '/categoria/b2b-only'); ?>
    </div>
    */?>
    <div class="col-sm-12 mb-4">
      <!-- Listagem de Talentos - Música -->
      <?php polen_banner_scrollable(polen_get_talents_by_product_cat("musica", 10), "Música", $emojis['musica'], '/categoria/musica'); ?>
    </div>
    <div class="col-sm-12 mb-4">
      <!-- Listagem de Talentos - Atrizes e Atores -->
      <?php polen_banner_scrollable(polen_get_talents_by_product_cat("atrizes-e-atores", 10), "Atrizes e Atores", $emojis['atrizes-e-atores'], '/categoria/atrizes-e-atores'); ?>
    </div>
    <div class="col-sm-12 mb-4">
      <!-- Listagem de Talentos - Apresentadores -->
      <?php polen_banner_scrollable(polen_get_talents_by_product_cat("apresentadores", 10), "Apresentadores", $emojis['apresentadores'], '/categoria/apresentadores'); ?>
    </div>
    <div class="col-sm-12 mb-4">
      <!-- Listagem de Talentos - Esporte -->
      <?php polen_banner_scrollable(polen_get_talents_by_product_cat("esporte", 10), "Esporte", $emojis['esporte'], '/categoria/esporte'); ?>
    </div>
    <div class="col-sm-12 mb-4">
      <!-- Listagem de Talentos - Influencers -->
      <?php polen_banner_scrollable(polen_get_talents_by_product_cat("influencers", 10), "Influencers", $emojis['influencers'], '/categoria/influencers'); ?>
    </div>
    <div class="col-sm-12 mb-4">
      <!-- Listagem de Talentos - Comediantes -->
      <?php polen_banner_scrollable(polen_get_talents_by_product_cat("comediantes", 10), "Comediantes", $emojis['comediantes'], '/categoria/comediantes'); ?>
    </div>
  </div>

  <!-- Banners -->
	<div class="row d-flex justify-content-center my-4">
		<div class="col-xs-12 col-sm-6">
      <?php $inputs->material_button_link("todos", "Ver todos os ídolos", home_url( "shop" ), false, "", array()); ?>
		</div>
	</div>

  <!-- Polen na Mídia -->
  <?php //polen_get_media_news(); ?>

</main><!-- #main -->

<?php
get_footer();
