<?php

use Polen\Includes\Polen_Order_Review;

get_header();

$product = wc_get_product($product_id);
$product_post = get_post($product->get_id());
$talent = get_user_by('id', $product_post->post_author);

$reviews = Polen_Order_Review::get_order_reviews_by_talent_id($talent->ID);

?>

<main id="primary" class="site-main">
	<header class="page-header mb-3">
		<div class="row">
			<div class="co-12 col-md-12">
				<h1 class="page-title"><?php esc_html_e('Comentários', 'polen'); ?></h1>
			</div>
		</div>
	</header><!-- .page-header -->
	<?php
	if ($reviews) {
		foreach ($reviews as $review) {
			$review_id = $review->comment_ID;
			$date = new DateTime($review->comment_date);
			$rate = get_comment_meta($review_id, "rate");

			$user_name = $review->comment_author;
			if( empty( $user_name ) ) {
				$user = get_user_by( 'id', $review->user_id );
				$user_name = $user->display_name;
			}
			polen_comment_card(array(
				"id" => $review_id,
				"name" => $user_name,
				"date" => $date->format('d/m/Y'),
				"rate" => (int) $rate[0],
				"comment" => $review->comment_content
			));
		}
	} else {
	?>
		<div class="row">
			<div class="co-12 col-md-12 text-center mt-4">
				<h1 class="page-title">Este ídolo ainda não possui nenhum review.</h1>
			</div>
			<div class="col-12 page-content text-center mt-4 mb-4">
				<p>Peça agora mesmo um vídeo personalizado de <a href="<?php echo get_home_url(); ?>/talento/<?php echo $product->slug; ?>"><?php echo $product->name; ?></a> para ter uma experiência inédita e deixe aqui seu review!</p>
			</div>
		</div>
	<?php
	}
	?>
</main><!-- #main -->

<?php

get_footer();
