<?php

/** Template Name: Página - Lacta */

use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Order;

// $talents = polen_get_talents_by_campaignn('lacta');
$talents = polen_get_talents_by_campaignn('lacta');

get_header();
?>

<main id="primary" class="site-main">
  <div class="row">
    <header class="lacta-top-banner col-12 d-flex align-items-center">
      <div class="content mt-2 col-md-5 col-sm-12">
        <h1 class="title">Um pedacinho de emoção para quem você ama.</h1>
        <p class="typo typo-text mt-2">Neste Natal, a Lacta vai aproximar ídolos e fãs com vídeos personalizados emocionantes!</p>
      </div>
      <div class="lacta-logo">
        <svg width="82" height="32" viewBox="0 0 82 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M69.5923 4.91611C70.5507 6.9401 71.4201 9.04878 72.2007 11.2153C69.6897 11.2506 67.6742 11.6642 66.4844 12.6423C67.3877 9.97327 68.4237 7.38329 69.5923 4.91611ZM64.4646 19.5837C64.7059 18.6014 64.9642 17.6261 65.2395 16.655C67.1901 15.7136 70.7864 15.3028 73.6064 15.4863C74.0101 16.8385 74.3813 18.2062 74.7201 19.5837H79.941C77.8407 12.1398 75.0292 5.25203 72.1258 0.402344H67.0602C64.1569 5.25203 61.3453 12.1398 59.2451 19.5837H64.4646Z" fill="white" />
          <path d="M80.4644 21.8702C75.1828 23.8476 68.9739 25.6698 60.1708 25.6698C44.6958 25.6698 33.8546 21.8702 19.5215 21.8702C13.53 21.8702 8.40367 22.8201 5.09668 23.89V18.1328V12.48V0.402344H0V29.746C5.52858 27.9549 11.3663 26.2556 19.5215 26.2556C33.5145 26.2556 43.7685 32.0001 60.1708 32.0001C69.0671 32.0001 75.9238 30.3939 81.6387 27.6444C81.3296 25.7926 80.9273 23.8773 80.4644 21.8702Z" fill="white" />
          <path d="M38.1464 19.9859C40.6799 19.9859 43.3969 19.3987 45.4675 18.0085V13.3451C43.0582 14.9513 40.8338 15.6613 38.5176 15.6613C34.4089 15.6613 33.3278 12.9739 33.3278 9.69937C33.3278 6.14679 34.687 4.32463 37.3744 4.32463C39.2276 4.32463 40.2777 5.43684 40.4019 7.47495H45.3447C45.2205 2.47142 42.1944 0 37.3758 0C30.7039 0 28.1704 4.38673 28.1704 9.69937C28.1704 15.7855 30.7336 19.9859 38.1464 19.9859Z" fill="white" />
          <path d="M17.9468 4.91665C18.9051 6.94064 19.7746 9.04932 20.5551 11.2159C18.0442 11.2512 16.0286 11.6647 14.8388 12.6428C15.7435 9.97381 16.7795 7.38383 17.9468 4.91665ZM13.5939 16.6569C15.5459 15.7155 19.1394 15.3048 21.9609 15.4883C22.3646 16.8404 22.7358 18.2081 23.0745 19.5857H28.294C26.1938 12.1418 23.3822 5.25398 20.4789 0.404297H15.4132C12.5099 5.25398 9.69836 12.1418 7.59814 19.5857H12.8176C13.0604 18.6033 13.3187 17.6266 13.5939 16.6569Z" fill="white" />
          <path d="M51.3682 19.5837H56.4649V4.72697H61.5615V0.402344H46.2715V4.72697H51.3682V19.5837Z" fill="white" />
        </svg>
      </div>
    </header>
  </div>
  <div class="row mt-4">
    <div class="col-12">
      <?php
      //TODO -- função para trazer esses dados
      // $videos = ["461", "421", "422"];
      $orders_ids = Polen_Order_Module::get_orders_ids_by_campaign_and_status( 'lacta', Polen_Order::ORDER_STATUS_COMPLETED );
      if( count( $orders_ids ) >= 3 ) {
        polen_front_get_videos( polen_get_home_stories( $orders_ids ), "Últimos vídeos gravados" );
      }
      ?>
    </div>
  </div>
  <?php getTutorialLacta(); ?>
  <?php
  polen_front_get_news($talents, null, null, false, "lacta");
  polen_get_lacta_banner_2("https://www.lacta.com.br");
  ?>
  <?php
  while (have_posts()) :
    the_post();
    get_template_part('template-parts/content', 'page');
  endwhile; // End of the loop.
  ?>

</main><!-- #main -->

<?php
get_footer();
