<?php
defined('ABSPATH') || die;

get_header();
?>

<main id="primary" class="site-main">

	<?php sa_get_modal(); ?>

	<?php //polen_front_get_banner_with_carousel(true);
	?>

	<?php polen_front_get_news(social_get_products_by_category_slug(social_get_category_base()), "Os artistas que apoiam essa causa", null, true);
	?>

	<?php polen_front_get_tutorial(); ?>

	<?php //polen_front_get_suggestion_box();
	?>

</main><!-- #main -->

<?php isset($_COOKIE[POL_COOKIES['CRIESP_BANNER_HOME']]) || criesp_get_modal(); ?>

<?php
get_footer();

// echo '<pre>';var_dump('social_is_in_social_app:',social_is_in_social_app());echo '</pre>';
// echo '<pre>';var_dump('social_get_category_base:',social_get_category_base());;echo '</pre>';
// echo '<pre>';var_dump('social_get_products_by_category_slug:',social_get_products_by_category_slug(social_get_category_base()));;echo '</pre>';
// echo '<br>ASD';
