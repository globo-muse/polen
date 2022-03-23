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

get_header();
?>

	<main id="primary" class="site-main">
    <section id="media-news" class="my-4">
      <div class="row">
        <div class="col-12">
          <h1>Polen na Mídia</h1>
        </div>
      </div>
      <div class="row mt-3">
        <?php
          $args = array(
            'post_type' => 'post_polen_media',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'paged' => ( get_query_var('paged') ) ? get_query_var('paged') : 1,
            'orderby' => 'DESC',
            'order' => 'DESC',
          );

          $loop = new WP_Query( $args );
          if ($loop->have_posts()) :
            while ( $loop->have_posts() ) : $loop->the_post();
        ?>

        <div class="col-md-10 mb-4">
          <a href="<?php echo esc_attr(get_post_meta(get_the_ID(), 'url_media', true)); ?>" target="_blank" rel="noreferrer" title="<?php echo wp_strip_all_tags( get_the_content() ); ?>">
            <article>
              <div class="news-text">
                <h4><?php the_title(); ?></h4>
                <h2><?php echo wp_strip_all_tags( get_the_content() ); ?></h2>
                <h5><?php echo esc_attr(get_post_meta(get_the_ID(), 'date_media', true)); ?></h5>
              </div>
              <figure class="news-image" style="background: url('<?php the_post_thumbnail_url(); ?>')"></figure>
            </article>
          </a>
        </div>

        <?php
            endwhile;
          else :
        ?>
            <div class="col-sm-12">
              <p class="title typo">Sem posts para exibir</p>
            </div>
        <?php
          endif;
          wp_reset_postdata();
        ?>
        <!-- Paginação -->
        <div class="col-md-10 pagination">
          <?php echo show_pagination($args); ?>
        </div>
      </div>
    </section>
	</main><!-- #main -->

<?php
get_footer();
