<?php

/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.3.0
 */

if (!defined('ABSPATH')) {
	exit;
}

$obj = polen_queried_object();
$title = $obj ? "{$obj->name}: " : "";

?>
<div class="clear"></div>
<div class="row my-3">
  <div class="col-12">
    <?php polen_front_get_categories_buttons(); ?>
  </div>
	<div class="col-12">
		<h1 class="<?php echo $obj ? "d-none" : ""; ?>"><?php echo $title; ?>Escolha seu ídolo e peça seu vídeo personalizado!</h1>
	</div>
</div>
<section class="row my-4 card-list">
	<div class="col-md-12 p-0 p-md-0">
		<div class="banner-wrapper">
			<div class="banner-content">
