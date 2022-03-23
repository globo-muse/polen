<?php

/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Polen
 */

get_header();
global $wp_query;
?>

<main id="primary" class="site-main">

	<?php if (have_posts()) : ?>
		<section class="row mb-4">
			<div class="col-md-12">
				<header class="row mb-3">
					<div class="col-12 d-flex justify-content-between align-items-center">
						<h1 class="mr-2">
							<?php printf(
								esc_html__('%s Resultado%s para "%s"', 'polen'),
								$wp_query->found_posts,
								$wp_query->found_posts > 1 ? 's' : '',
								'<span>' . get_search_query() . '</span>'
							); ?>
						</h1>
					</div>
				</header>
			</div>
			<div class="col-md-12 p-0 p-md-0 banner-scrollable">
				<div class="banner-wrapper">
					<div class="banner-content">
						<?php
						while (have_posts()) :
							the_post();
							$product = wc_get_product(get_the_ID());
							if( !empty( $product ) && !is_wp_error( $product ) ) {
								$item_data = _polen_get_info_talent_by_product_id($product);
								polen_front_get_card($item_data, "responsive");
							}

							/**
							 * Run the loop for the search to output the results.
							 * If you want to overload this in a child theme then include a file
							 * called content-search.php and that will be used instead.
							 */
						//get_template_part( 'template-parts/content', 'search' );

						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			</div>
		</section>


	<?php
	/* Start the Loop */

	//the_posts_navigation();

	else :
		get_template_part('template-parts/content', 'none');

	endif;
	?>

</main><!-- #main -->

<?php
//get_sidebar();
get_footer();
