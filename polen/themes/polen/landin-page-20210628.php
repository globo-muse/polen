<?php

$product_sku = get_query_var( 'lp_product_sku' );
$product_id = wc_get_product_id_by_sku(['sku' => $product_sku]);
$product = wc_get_product( $product_id );
$event = "landpage";

$utm_source = filter_input( INPUT_GET, 'utm_source' );
$utm_medium = filter_input( INPUT_GET, 'utm_medium' );
$utm_campaign = filter_input( INPUT_GET, 'utm_campaign' );

$cat_terms = wp_get_object_terms( $product_id, 'product_cat' );
$cat_term = $cat_terms[ 0 ];

$terms = wp_get_object_terms( $product_id, 'product_tag' );
$tags_arr = [];
foreach( $terms as $term ) {
    $tags_arr[] = $term->name;
}
$tags = implode( ',', $tags_arr );

$utm_source   = filter_input( INPUT_GET, 'utm_source' );
$utm_medium   = filter_input( INPUT_GET, 'utm_medium' );
$utm_campaign = filter_input( INPUT_GET, 'utm_campaign' );

$lp_signin_success = get_query_var( 'lp_signin_success' );

get_header();
?>
<div class="landpage-card">
		<div class="row">
			<div class="col-12 col-md-12 col-lg-10">
				<div class="row">
					<div class="col-7 m-auto m-md-0 col-md-4">
						<figure class="image-cropper">
							<?php echo $product->get_image(); ?>
						</figure>
					</div>
					<div class="col-12 mt-3 col-md-8 pl-md-5">
                    <?php
						if( !empty( $lp_signin_success ) && $lp_signin_success == '1' ) {
							echo "<h1 class='title'>Inscrição realizada com sucesso!</h1><p class='subtitle'>Enviaremos novidades para seu email.</p>";
						} else {
							echo $product->get_description();
						}
					?>

					<?php if( empty( $lp_signin_success ) ) : ?>
						<form action="./" method="POST" id="landpage-form" class="landpage-form">
							<div class="row">
								<div class="mt-4 col-md-9 mt-md-5">
									<div class="row">
										<div class="mb-3 col-md-12">
											<label for="signin_landpage" class="label">Deixe seu nome, email e participe:</label>
                                            <input type="hidden" name="action" value="polen_signin_lp_lead" />
											<input type="text" name="fan_name" id="fan_name" placeholder="Entre com o seu nome" class="form-control form-control-lg mb-3" required/>
											<input type="email" name="fan_email" id="fan_email" placeholder="Entre com o seu e-mail" class="form-control form-control-lg" required/>
											<input type="hidden" name="zapier" value="3" />
                      <input type="hidden" name="product_id" value="<?= $product_id; ?>" />
											<input type="hidden" name="is_mobile" value="<?= polen_is_mobile() ? "1" : "0"; ?>" />
											<input type="hidden" name="page_source" value="<?= $_SERVER['REQUEST_URI']; ?>" />
											<input type="hidden" name="category" value="<?= $cat_term->name; ?>" />
											<input type="hidden" name="tags" value="<?= $tags; ?>" />
											<input type="hidden" name="utm_source" value="<?= $utm_source; ?>" />
											<input type="hidden" name="utm_medium" value="<?= $utm_medium; ?>" />
											<input type="hidden" name="utm_campaign" value="<?= $utm_campaign; ?>" />
											<input type="hidden" name="signin_landpage_event" value="<?= $event; ?>" />
                                            <?php wp_nonce_field( 'landpage-signin', 'security' , true, true ); ?>
										</div>
										<div class="col-md-12">
											<button class="signin-landpage-button btn btn-primary btn-lg btn-block">Quero um vídeo Polen</button>
										</div>
									</div>
								</div>
							</div>
						</form>
					<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
get_footer();
