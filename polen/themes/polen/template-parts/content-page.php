<?php

/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Polen
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <header class="entry-header">
    <?php //the_title( '<h1 class="entry-title">', '</h1>' );
    ?>
  </header><!-- .entry-header -->

  <div class="entry-content">
    <?php
    the_content();

    wp_link_pages(
      array(
        'before' => '<div class="page-links">' . esc_html__('Pages:', 'polen'),
        'after'  => '</div>',
      )
    );
    ?>
  </div><!-- .entry-content -->

  <?php /*if ( get_edit_post_link() ) : ?>
		<footer class="entry-footer">
			<?php
			edit_post_link(
				sprintf(
					wp_kses(
						__( 'Edit <span class="screen-reader-text">%s</span>', 'polen' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				),
				'<span class="edit-link">',
				'</span>'
			);
			?>
		</footer><!-- .entry-footer -->
	<?php endif; */ ?>
</article><!-- #post-<?php //the_ID();
                      ?> -->

<?php
$obj = polen_queried_object();
?>
<?php if ($obj) : ?>
  <div class="row mt-5 mb-5 pt-5">
    <div class="col-12 mt-5">
      <?php polen_banner_scrollable(polen_get_new_talents(6), "Destaque", "", polen_get_all_new_talents_url()); ?>
    </div>
  </div>
<?php endif; ?>
