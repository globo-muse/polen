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


//https://joinzap.app/ronnievon
get_header(); ?>

<main id="primary" class="site-main">
  <div class="row bus-thankyou">
    <div class="col-12 col-md-8 m-md-auto text-center">
      <h1 class="title mb-3">Obrigado!</h1>
      <p class="description">A nossa equipe de vendas está ansiosa para falar com você! Entraremos em contato em breve para falar sobre as soluções da Polen para o seu negócio.</p>
      <a href="<?php echo home_url(); ?>" class="link">Voltar para página inicial</a>
    </div>
  </div>
</main>

<?php get_footer();
