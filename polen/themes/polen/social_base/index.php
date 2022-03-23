<?php

defined('ABSPATH') || die;

Header( "HTTP/1.1 307 Temporary Redirect" );
Header( "Location: " . home_url() );

use Polen\Social_Base\Social_Base_Product;
use Polen\Social_Base\Social_Base_Rewrite;

$social_slug = $GLOBALS[ Social_Base_Rewrite::QUERY_VARS_SOCIAL_SLUG ];

// $products = Social_Base_Product::get_all_products_by_slug_campaign( $social_slug );
$args = array(
    'status' => 'publish',
    'meta_key' => '_social_base_slug_campaign',
    'meta_value' => $slug_campaign,
    'orderby' => 'menu_order',
    'order' => 'DESC',
);
$items = _polen_get_info_talents_by_args( $args );

get_header();
?>

<main id="primary" class="site-main">

	<?php sa_get_modal(); ?>

	<?php //polen_front_get_banner_with_carousel(true);
	?>

	<?php polen_front_get_news( $items, "Os artistas que apoiam essa causa", null, true );
	?>

	<?php polen_front_get_tutorial(); ?>

	<?php //polen_front_get_suggestion_box();
	?>

</main><!-- #main -->

<?php
get_footer();
