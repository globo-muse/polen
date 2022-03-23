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
    <section id="about-banner">
      <div class="row">
        <div class="col-12">
          <div class="banner">
            <div class="content">
              <h5>Polen - Conecta fãs com ídolos</h5>
              <h2>Surpreenda e emocione com vídeos<br>personalizados</h2>
              <div class="arrow">
                <a href="#about-video">
                  <img src="<?php echo TEMPLATE_URI; ?>/assets/icons/arrow-down.svg" alt="Ver">
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section id="about-text" class="my-5">
      <div class="row">
        <div class="col-12">
          <h2 class="typo typo-title"><?php the_title(); ?></h2>
        </div>
        <div class="col-12">
          <?php
          while ( have_posts() ) :
            the_post();
            get_template_part( 'template-parts/content', 'page' );
          endwhile; // End of the loop.
          ?>
        </div>
      </div>
    </section>
    <section id="about-video">
      <div class="row">
        <div class="col-12">
          <video id="polen-about" playsinline poster="<?php echo TEMPLATE_URI; ?>/assets/img/cover-video-about.jpg">
            <source src="<?php echo TEMPLATE_URI; ?>/assets/video/about-video.mp4" type="video/mp4">
          </video>
        </div>
      </div>
      <script>
        polVideoTag("#polen-about");
      </script>
    </section>
	</main><!-- #main -->

<?php
get_footer();
