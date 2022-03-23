<?php

/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Polen
 */

get_header(); ?>

<main id="primary" class="site-main">
  <?php
  bus_get_header();
  bus_get_tutorial();
  bus_grid(bus_get_talents(), "Nossos Ídolos");
  bus_get_form();
  ?>
</main>

<?php get_footer();
