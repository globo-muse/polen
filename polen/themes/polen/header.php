<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Polen
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="<?php bloginfo('name'); ?>">
	<meta name="apple-touch-fullscreen" content="yes">
	<!-- <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"> -->
	<meta name="theme-color" content="#000000">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php polen_get_header_objects(); ?>
	<?php wp_head(); ?>
  <?php include_once TEMPLATE_DIR . '/inc/analitics_header.php'; ?>
</head>
<?php $is_single = is_singular() && is_product(); ?>

<body <?php body_class("theme-dark"); ?>>
	<?php wp_body_open(); ?>
    <?php include_once TEMPLATE_DIR . '/inc/analitics_init_body.php'; ?>
  <?php if ($is_single) : ?>
    <div class="bg-single">
      <div class="top-fixed">
  <?php endif; ?>
        <div class="container header-single">
          <header id="masthead" class="masthead row pt-3 pb-4<?php echo social_is_in_social_app() || master_class_is_app() ? " header-home" : ""; ?>">
            <div class="col-8 col-sm-6 d-flex align-items-center">
              <?php polen_the_theme_logos(); ?>
            </div>
            <?php if(!polen_is_landingpage()) : ?>
              <?php pol_get_menu(); ?>
            <?php endif; ?>
          </header>
        </div>
  <?php if ($is_single) : ?>
      </div>
      <img src='<?= polen_get_thumbnail(get_the_id())['image']; ?>' alt='<?= get_the_title(); ?>' class="idol-cover"></img>
      <div class="container">
        <h2 class="typo idol-name"><?= get_the_title(); ?></h2>
      </div>
    </div>
  <?php endif; ?>
	<div id="page" class="container site">
