<?php

/**
 * Template name: Página Inicial Vídeo Autógrafo
 */
// session_start();
// if( !isset( $_SESSION[ Promotional_Event_Admin::SESSION_KEY_CUPOM_CODE ])
// 	|| empty( $_SESSION[ Promotional_Event_Admin::SESSION_KEY_CUPOM_CODE ] ))
// {
// 	wp_safe_redirect( event_promotional_url_code_validation() );
// 	exit;
// }

$product = $GLOBALS[ Promotional_Event_Rewrite::GLOBAL_KEY_PRODUCT_OBJECT ];
$cupom_code = filter_input( INPUT_GET, 'cupom_code', FILTER_SANITIZE_STRING );
$pep = new Promotional_Event_Product( $product );
$img = $pep->get_url_image_product_with_size( 'polen-thumb-lg' );
if( empty( $cupom_code) ) {
	wp_safe_redirect( event_promotional_url_code_validation( $product ) );
	exit;
}
get_header();
$coupon = $cupom_code;
?>

<main id="primary" class="site-main">

	<div class="row">
		<div class="col-12 col-md-8 m-md-auto">
      <div class="row">
        <div class="col-12 mb-4">
          <?php polen_get_lacta_header_talent($img, $product->get_title()); ?>
        </div>
      </div>
			<?php
			lacta_cart_form( $product, $coupon );
			?>
		</div>
	</div>

</main><!-- #main -->

<?php
get_footer();
