<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Polen
 */
?>

<div class="container mt-5">
	<footer id="colophon" class="site-footer border-top pb-5">
		<div class="row mt-4 copyright">
			<div class="col-12 text-center mb-4 col-md-6 text-md-left">
				<?php polen_the_theme_logos(); ?>
			</div>
			<div class="col-12 text-center col-md-6 text-md-right social">
				<a href="https://www.facebook.com/Polen-107879504782470/" target="_blank"><?php Icon_Class::polen_icon_social("facebook"); ?></a>
				<a href="https://www.instagram.com/polen.me/" target="_blank"><?php Icon_Class::polen_icon_social("instagram"); ?></a>
				<a href="https://vm.tiktok.com/ZMeKtWr1H/" target="_blank"><?php Icon_Class::polen_icon_social("tiktok"); ?></a>
			</div>
		</div>
	</footer>
</div>

</div><!-- #Container-fluid -->

<?php wp_footer(); ?>
<?php do_action('polen_messages_service_error'); ?>
<?php do_action('polen_messages_service_success'); ?>
<?php Polen\Includes\Polen_Messages::clear_messages(); ?>
<?php include_once TEMPLATE_DIR . '/inc/analitics_footer.php'; ?>
</body>

</html>
