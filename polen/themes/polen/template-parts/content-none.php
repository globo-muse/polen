<?php

/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Polen
 */

global $wp_query;

?>

<section class="no-results not-found">
	<header class="page-header">
		<div class="row">
			<div class="col-12 col-md-8 m-md-auto">
				<h1>
					<?php printf(
						esc_html__('%s Resultado%s para "%s"', 'polen'),
						$wp_query->found_posts,
						$wp_query->found_posts > 1 ? 's' : '',
						'<span>' . get_search_query() . '</span>'
					); ?>
				</h1>
			</div>
		</div>
	</header><!-- .page-header -->
	<div class="content">
		<div class="row my-3">
			<div class="col-12 col-md-8 m-md-auto">
				<div class="box-round">
					<div class="row">
						<div class="col-12 p-4">
							<div class="row">
								<div class="col-12 text-center">
									<img width="186" src="<?php echo TEMPLATE_URI; ?>/assets/img/errors/not-found.png" alt="Nenhum resultado encontrado">
								</div>
								<div class="col-12 text-center mt-4">
									<h3>Nenhum resultado encontrado.</h3>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-12 col-md-8 m-md-auto">
        <?php
        $inputs = new Material_Inputs();
        $inputs->material_button_link("btn-voltar", "Ver todos", site_url("shop"));
        ?>
			</div>
		</div>
	</div><!-- .page-content -->
</section><!-- .no-results -->
