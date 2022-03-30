<?php

/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

use Polen\Includes\Module\Factory\Polen_Product_Module_Factory;
use Polen\Includes\Module\Polen_Product_Module;

defined('ABSPATH') || exit;

global $product;
global $Polen_Plugin_Settings;
global $post;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}

use Polen\Includes\Polen_Order_Review;

$polen_product = Polen_Product_Module_Factory::create_product_from_campaing($product);
$terms         = wp_get_object_terms($polen_product->get_id(), 'product_tag');
$categories    = wp_get_object_terms($polen_product->get_id(), 'product_cat');
$videos        = $polen_product->get_videos();//polen_get_videos_by_talent($Talent_Fields);
$image_data    = polen_get_thumbnail($polen_product->get_id());
$talent_id     = $polen_product->get_user_talent_id();

$inputs = new Material_Inputs();

?>

<div id="product-<?php $polen_product->get_id(); ?>" <?php wc_product_class('', $product); ?>>

  <?php /*
  <div class="row mt-5">
    <div class="col-12 col-md-6 m-md-auto">
      <div class="idol-tabs">
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="b2b-tab" data-toggle="pill" href="#pills-b2b" role="tab" aria-controls="pills-b2b" aria-selected="true">Vídeo para empresa</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="b2c-tab" data-toggle="pill" href="#pills-b2c" role="tab" aria-controls="pills-b2c" aria-selected="false">Vídeo para uso pessoal</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  */ ?>

  <div class="row mt-5 mb-5">
    <div class="col-12">
      <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-b2b" role="tabpanel" aria-labelledby="b2b-tab">
          <div class="row">
            <!-- CONTEUDO -->
            <div class="col-sm-12 col-md-7 left-column">
              <!-- Bio -->
              <?php if ($polen_product->get_description()) : ?>
                <div class="row mb-4">
                  <div class="col-12">
                    <h2 class="mb-3 typo typo-title">Biografia</h2>
                  </div>
                  <div class="col-12">
                    <p class="typo typo-p"><?php echo $polen_product->get_description(); ?></p>
                  </div>
                  <div class="col-12">
                    <hr></hr>
                  </div>
                </div>
              <?php endif; ?>
              <!-- Influencia -->
              <div class="row mb-4">
                <div class="col-12">
                  <h2 class="mb-3 typo typo-title">Influência por região</h2>
                </div>
                <div class="col-12 mb-3">
                  <div class="row">
                    <div class="col-12">
                      <h5 class="typo typo-p">Região Norte</h5>
                    </div>
                    <div class="col-12 d-flex align-items-center">
                      <div class="progress w-100 mr-3">
                        <div class="progress-bar" role="progressbar" style="width: 12.4%" aria-valuenow="12.4" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <h5 class="typo typo-p m-0">12,4%</h5>
                    </div>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <div class="row">
                    <div class="col-12">
                      <h5 class="typo typo-p">Região Nordeste</h5>
                    </div>
                    <div class="col-12 d-flex align-items-center">
                      <div class="progress w-100 mr-3">
                        <div class="progress-bar" role="progressbar" style="width: 8.4%" aria-valuenow="12.4" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <h5 class="typo typo-p m-0">8,4%</h5>
                    </div>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <div class="row">
                    <div class="col-12">
                      <h5 class="typo typo-p">Região Sul</h5>
                    </div>
                    <div class="col-12 d-flex align-items-center">
                      <div class="progress w-100 mr-3">
                        <div class="progress-bar" role="progressbar" style="width: 17.4%" aria-valuenow="12.4" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <h5 class="typo typo-p m-0">17,4%</h5>
                    </div>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <div class="row">
                    <div class="col-12">
                      <h5 class="typo typo-p">Região Suldeste</h5>
                    </div>
                    <div class="col-12 d-flex align-items-center">
                      <div class="progress w-100 mr-3">
                        <div class="progress-bar" role="progressbar" style="width: 20.4%" aria-valuenow="12.4" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <h5 class="typo typo-p m-0">20,4%</h5>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <hr></hr>
                </div>
              </div>
              <!-- Faixa Etária -->
              <div class="row mb-4">
                <div class="col-12">
                  <h2 class="mb-3 typo typo-title">Faixa etária</h2>
                </div>
                <div class="col-12 mb-3">
                  <div class="row">
                    <div class="col-12">
                      <h5 class="typo typo-p">13-17</h5>
                    </div>
                    <div class="col-12 d-flex align-items-center">
                      <div class="progress w-100 mr-3">
                        <div class="progress-bar" role="progressbar" style="width: 12.4%" aria-valuenow="12.4" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <h5 class="typo typo-p m-0">12,4%</h5>
                    </div>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <div class="row">
                    <div class="col-12">
                      <h5 class="typo typo-p">18-28</h5>
                    </div>
                    <div class="col-12 d-flex align-items-center">
                      <div class="progress w-100 mr-3">
                        <div class="progress-bar" role="progressbar" style="width: 8.4%" aria-valuenow="12.4" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <h5 class="typo typo-p m-0">8,4%</h5>
                    </div>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <div class="row">
                    <div class="col-12">
                      <h5 class="typo typo-p">28-38</h5>
                    </div>
                    <div class="col-12 d-flex align-items-center">
                      <div class="progress w-100 mr-3">
                        <div class="progress-bar" role="progressbar" style="width: 17.4%" aria-valuenow="12.4" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <h5 class="typo typo-p m-0">17,4%</h5>
                    </div>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <div class="row">
                    <div class="col-12">
                      <h5 class="typo typo-p">38-48</h5>
                    </div>
                    <div class="col-12 d-flex align-items-center">
                      <div class="progress w-100 mr-3">
                        <div class="progress-bar" role="progressbar" style="width: 20.4%" aria-valuenow="12.4" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <h5 class="typo typo-p m-0">20,4%</h5>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <hr></hr>
                </div>
              </div>
              <!-- Tags -->
              <?php if ($terms) : ?>
                <div class="row mb-5">
                  <div class="col-12">
                    <h2 class="mb-3 typo typo-title">Tags</h2>
                  </div>
                  <div class="col-12">
                    <?php if (count($terms) > 0) : ?>
                      <?php foreach ($terms as $k => $term) : ?>
                        <a href="<?= get_tag_link($term); ?>" class="tag-link mb-2"><?= $term->name; ?></a>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
            <!-- /CONTEUDO -->
            <!-- BOTAO DE COMPRA -->
            <div class="cta-b2b col-sm-12">
              <div class="box-line p-4">
                <div class="row">
                  <div class="col-12 d-flex justify-content-between mb-2">
                    <h5 class="typo typo-p"><strong>Vídeo para meu negócio</strong></h5>
                    <h5 class="typo typo-p">
                      <strong>
                        <?php
                          if (get_post_meta(get_the_ID(), 'polen_price_range_b2b', true)) {
                            echo get_post_meta(get_the_ID(), 'polen_price_range_b2b', true);
                          } else {
                            echo "Sob consulta";
                          }
                        ?>
                      </strong>
                    </h5>
                  </div>
                  <div class="col-12">
                    <p class="typo typo-p">Os valores dos vídeos variam de acordo com cada necessidade, analisamos cada pedido para termos a solução ideal para o seu negócio.  </p>
                  </div>
                  <div class="col-12">
                    <?php $inputs->material_button_link("", "Falar com a equipe de vendas", "https://wa.me/5581981233638?text=", true, "", array(),); ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php /*
        <div class="tab-pane fade" id="pills-b2c" role="tabpanel" aria-labelledby="b2c-tab">
          <div class="row">
            <!-- CONTEUDO -->
            <div class="col-sm-12 col-md-7">
              <!-- Deadline do Ídolo -->
              <?php echo $polen_product->template_deadline_html_page_details(); ?>

              <!-- Bio -->
              <div class="row mt-4">
                <div class="col-12">
                  <h2 class="mb-3 typo typo-subtitle-large">Biografia</h2>
                  <p class="typo typo-p"><?php echo $polen_product->get_description(); ?></p>
                </div>
              </div>

              <!-- Doação -->
              <?php $polen_product->template_donation_box_page_details(); ?>

              <!-- Tags -->
              <div class="row mt-4">
                <div class="col-12">
                  <h2 class="mb-3 typo typo-subtitle-large">Tags</h2>
                </div>
                <div class="col-12">
                  <?php if (count($terms) > 0) : ?>
                    <?php foreach ($terms as $k => $term) : ?>
                      <a href="<?= get_tag_link($term); ?>" class="tag-link mb-2"><?= $term->name; ?></a>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>

            </div>
            <!-- /CONTEUDO -->
            <!-- BOTAO DE COMPRA -->
            <div class="col-sm-12 col-md-5">
              <!-- Botão de adicionar ao carrinho -->
              <div class="row mt-3 mb-1 talent-page-footer">
                <div class="col-12 pb-3">
              <?php if($polen_product->get_in_stock()) : ?>
                    <?php $inputs->pol_combo_advanced(
                    "select_type",
                    "select_type",
                    array(
                  $polen_product->b2c_combobox_advanced_item_html($inputs),
                  $polen_product->b2b_combobox_advanced_item_html($inputs),
                      )); ?>
                <?php echo $polen_product->template_buy_buttons($inputs, $polen_product); ?>
              <?php else: ?>
                    <?php echo $polen_product->template_button_others_talents($inputs); ?>
              <?php endif; ?>
                </div>
              </div>
              <!-- --------------------------------------------- -->
            </div>
            <!-- /BOTAO DE COMPRA -->
          </div>
        </div>
        */ ?>
      </div>
    </div>
  </div>

	<!-- Tags -->
	<!-- <div class="row">
		<div class="col-12 d-flex align-items-center">
			<?php //if(sizeof($videos) > 0) : ?>
				<div>

				</div>
			<?php //endif; ?>
			<?php //$polen_product->get_share_button(); ?>
		</div>
			<?php //if($videos && sizeof($videos) > 0): ?>
				<div class="col-12 mt-3">
					<?php //polen_front_get_videos_single($polen_product->get_user_talent_id(), $videos, $image_data); ?>
				</div>
			<?php //else: ?>
				<div class="col-12 col-md-6 m-md-auto">
					<?php //polen_front_get_talent_mini_bio($image_data, $polen_product->get_title(), $categories[0]->name); ?>
				</div>
			<?php //endif; ?>
	</div> -->

	<!-- <div class="row">
		<div class="col-12 col-md-6 m-md-auto d-flex justify-content-center">
			<?php //echo $polen_product->get_donate_badge_html(); ?>
		</div>
	</div> -->

	<!-- Share -->
	<?php //$polen_product->get_share_button(); ?>

  <!-- Avaliações -->
	<?php //polen_talent_review($reviews); ?>
	<?php //$polen_product->template_review_box_page_details(); ?>

	<!-- Como funciona? -->
	<?php //polen_front_get_tutorial(); ?>

	<!-- Produtos Relacionados -->
	<?php polen_box_related_product_by_product_id(get_the_ID());
	?>

</div>

<?php

//TODO botar numa função no local correto --------------------------------------------------
$array_social = array();
$array_sites = array("facebook", "twitter", "instagram", "linkedin", "youtube");

foreach ($array_sites as $key => $site) {
	if(!empty($Talent_Fields->$site)) {
		$array_social[] = urlencode($Talent_Fields->$site);
	}
}

$logo_dark = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );

// Novo conteúdo Schema.org
$total_reviews = Polen_Order_Review::get_number_total_reviews_by_talent_id($talent_id);
$sum_reviews = Polen_Order_Review::get_sum_rate_by_talent($talent_id);
pol_print_schema_data_extended($talent_id, $reviews, $total_reviews, $sum_reviews, $product);

?>

<script>
  jQuery(document).ready(function($) {
    $(window).scroll(function() {
        var scroll = $(window).scrollTop();
        if (scroll >= 620) {
          $(".cta-b2b").addClass("fixed-scroll");
        } else {
          $(".cta-b2b").removeClass("fixed-scroll");
        }
    });
  });
</script>

<?php do_action('woocommerce_after_single_product'); ?>
