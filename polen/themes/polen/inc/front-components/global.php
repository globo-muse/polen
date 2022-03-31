<?php

use Polen\Includes\Polen_Order;

function polen_get_search_form()
{
  $inputs = new Material_Inputs();
?>
  <button onclick="showSearchForm()" class="button-no-bg"><?php Icon_Class::polen_icon_search(); ?></button>
  <div id="search-box" class="search-box">
    <div class="row p-3">
      <div class="col-12 col-md-8 m-md-auto">
        <div class="row mb-3">
          <div class="col-12 text-right">
            <button onclick="hiddeSearchForm()" class="button-no-bg black"><?php Icon_Class::polen_icon_close(); ?></button>
          </div>
        </div>
        <form action="/" method="get">
          <div class="row">
            <div class="col-12 d-flex justify-content-between">
              <?php $inputs->material_input(Material_Inputs::TYPE_TEXT, "search", "s", "Buscar", true); ?>
              <?php $inputs->material_button(Material_Inputs::TYPE_SUBMIT, "btn-search", '<i class="icon icon-search"></i>', "ml-2", array("style" => "width: 54px;")); ?>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    function showSearchForm() {
      document.getElementById("search-box").classList.add("show");
      document.getElementById("search").focus();
    }

    function hiddeSearchForm() {
      document.getElementById("search-box").classList.remove("show");
    }
  </script>
<?php
}

function polen_front_get_banner_with_carousel($social = false)
{
  $carrousel = array(
    array(
      "mobile" => TEMPLATE_URI . "/assets/img/banner-home-mobile-new.png",
      "desktop" => TEMPLATE_URI . "/assets/img/img-home-desktop-new.jpeg"
    )
  );
  $carrousel2 = array(
    array(
      "mobile" => TEMPLATE_URI . "/assets/img/bg-setembro.png",
      "desktop" => TEMPLATE_URI . "/assets/img/bg-setembro.png"
    )
  );
?>
  <section class="top-banner mb-4">
    <div id="top-carousel" class="owl-carousel owl-theme">
      <?php if (!$social) : ?>
        <div class="item">
          <div class="carrousel">
            <?php foreach ($carrousel as $item) : ?>
              <figure class="image">
                <img loading="lazy" src="<?php echo $item['mobile']; ?>" alt="Presenteie e surpreenda com vídeos personalizados." class="mobile" />
                <img loading="lazy" src="<?php echo $item['desktop']; ?>" alt="Presenteie e surpreenda com vídeos personalizados." class="desktop" />
              </figure>
            <?php endforeach; ?>
          </div>
          <div class="content">
            <h2 class="title mb-5">Presenteie e<br />surpreenda com vídeos personalizados.</h2>
            <a href="<?php echo polen_get_all_talents_url(); ?>" class="banner-button-link">
              <span class="mr-3">Ver todos os artistas</span>
              <?php Icon_Class::polen_icon_chevron_right(); ?>
            </a>
          </div>
        </div>
      <?php else : ?>
        <div class="item">
          <div class="carrousel">
            <?php foreach ($carrousel2 as $item) : ?>
              <figure class="image">
                <img loading="lazy" src="<?php echo $item['mobile']; ?>" alt="Setembro é o mês da prevenção ao suicídio. Agir salva vidas!" class="mobile" />
                <img loading="lazy" src="<?php echo $item['desktop']; ?>" alt="Setembro é o mês da prevenção ao suicídio. Agir salva vidas!" class="desktop" />
              </figure>
            <?php endforeach; ?>
          </div>
          <div class="content">
            <h2 class="title mb-5">Setembro é o mês da prevenção ao suicídio.<br>Agir salva vidas!</h2>
            <a href="javascript:openModalSa()" class="banner-button-link button-yellow">
              <?php Icon_Class::polen_icon_donate(); ?>
              <span class="mr-3 ml-2">Veja os depoimentos</span>
              <?php Icon_Class::polen_icon_chevron_right(); ?>
            </a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <script>
    function openModalSa() {
      document.getElementById("sa-modal").classList.add("d-block");
      changeHash("sa-modal");
    }
    if (window.location.hash.substring(1) == "sa-modal") {
      openModalSa();
    }
  </script>
<?php
}

function polen_front_get_categories_buttons()
{
?>
  <section>
    <div class="row mb-2">
      <div class="col-12">
        <div class="content-category">
          <?php $categories = highlighted_categories(); ?>
          <?php foreach ($categories as $categorie) : ?>
            <a href="<?php echo get_category_link($categorie["term_id"]); ?>">
              <div class="btn btn-outline-dark category-button" ontouchstart="">
                <?php if ($categorie["img"]) : ?>
                  <img src="<?php echo $categorie["img"]; ?>" />
                <?php endif; ?>
                <h5><?php echo $categorie["name"]; ?></h5>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
<?php
}

function polen_front_get_banner_video()
{
  $mobile_video = array(
    "poster" => "https://i.vimeocdn.com/video/1344893855-6d42b6b001ce1aad37eda73011f78177e71754995fd86e590499b8c50605bf02-d_384x230",
    "video" => "https://player.vimeo.com/external/664445842.sd.mp4?s=0894358a43408ff52f0a8bcd516a69f7ead8b15f&profile_id=164",
    "class" => "video-mobile"
  );
  $desktop_video = array(
    "poster" => "https://i.vimeocdn.com/video/1344905857-204fca2864c8cea7552cd885c5f9edf2916ed5ca409c641f69532312e1c3e86d-d_1040x426",
    "video" => "https://player.vimeo.com/external/664451249.sd.mp4?s=d5c7153dc213adc440b30c6cc587690d7b44ec80&profile_id=165",
    "class" => "video-desktop"
  );
?>
  <section class="top-banner video-banner mb-4">
    <video class="video" autoplay muted loop playsinline poster="<?php echo polen_is_mobile() ? $mobile_video['poster'] : $desktop_video['poster']; ?>">
      <source src="<?php echo polen_is_mobile() ? $mobile_video['video'] : $desktop_video['video']; ?>" type="video/mp4">
    </video>
    <div class="content">
      <div class="row">
        <div class="col-12 mb-3">
          <h2 class="title text-center">Emocione quem você ama com vídeos personalizados dos seus ídolos</h2>
        </div>
        <!-- <div class="col-12 d-flex justify-content-center">
          <a href="<?php //echo polen_get_all_talents_url(); ?>" class="btn btn-primary btn-md">Ver todos os Ídolos</a>
        </div> -->
      </div>
    </div>
    <script>
      const home_video = {
        mobile: <?php echo json_encode($mobile_video); ?>,
        desktop: <?php echo json_encode($desktop_video); ?>
      }
    </script>
  </section>
<?php
}

function polen_front_get_banner()
{
  // $mobile_video = array(
  // 	"poster" => TEMPLATE_URI . "/assets/img/video_poster1.jpg",
  // 	"video" => TEMPLATE_URI . "/assets/video/home1.m4v",
  // 	"class" => "video-mobile"
  // );
  // $desktop_video = array(
  // 	"poster" => TEMPLATE_URI . "/assets/img/video_poster2.jpg",
  // 	"video" => TEMPLATE_URI . "/assets/video/home2.m4v",
  // 	"class" => "video-desktop"
  // );

  $carrousel = array(
    array(
      "mobile" => TEMPLATE_URI . "/assets/img/banner-home-mobile-new.png",
      "desktop" => TEMPLATE_URI . "/assets/img/img-home-desktop-new.jpeg"
    )
  );

?>
  <section class="top-banner mb-4">
    <?php /* <video id="video-banner" class="video" autoplay muted loop playsinline poster="<?php echo polen_is_mobile() ? $mobile_video['poster'] : $desktop_video['poster']; ?>">
			<source src="<?php echo polen_is_mobile() ? $mobile_video['video'] : $desktop_video['video']; ?>" type="video/mp4">
		</video>
		*/ ?>
    <div class="carrousel">
      <?php foreach ($carrousel as $item) : ?>
        <figure class="image">
          <img loading="lazy" src="<?php echo $item['mobile']; ?>" alt="Banner da home" class="mobile" />
          <img loading="lazy" src="<?php echo $item['desktop']; ?>" alt="Banner da home" class="desktop" />
        </figure>
      <?php endforeach; ?>
    </div>
    <div class="content">
      <h2 class="title mb-5">Presenteie e<br />surpreenda com vídeos personalizados.</h2>
      <a href="<?php echo polen_get_all_talents_url(); ?>" class="banner-button-link">
        <span class="mr-3">Ver todos os artistas</span>
        <?php Icon_Class::polen_icon_chevron_right(); ?>
      </a>
    </div>
    <?php /* <script>
			const home_video = {
				mobile: <?php echo json_encode($mobile_video); ?>,
				desktop: <?php echo json_encode($desktop_video); ?>
			}
		</script>
		*/ ?>
  </section>
<?php
}

// $size pode ser 'medium' e 'small'
function polen_front_get_card($item, $size = "small", $social = false, $campanha = "")
{
  // $product = wc_get_product($item['ID']);
  // $social = product_is_social_base($product);
  // $social == false ? $social = social_product_is_social(wc_get_product($item['ID']), social_get_category_base()) : false;
  $class = $size;
  if ($size === "small") {
    $class = "col-6 col-md-2";
  } elseif ($size === "medium") {
    $class = "col-6 col-md-3";
  }

  if ($social) {
    $size .= " yellow";
  }

  if ($campanha) {
    $size .= " promotional-" . $campanha;
  }

  if (isset($item['ID'])) {
    $image_data = polen_get_thumbnail($item['ID']);
  } else {
    $image = array();
    $image[] = '';
  }
  $donate = get_post_meta($item['ID'], '_is_charity', true);

?>
  <div class="<?= $class; ?>">
    <div class="polen-card <?= $size; ?>" itemscope itemtype="https://schema.org/Offer">
      <figure class="image">
        <?php if ($donate == 'yes') {
          polen_donate_badge("Social", true, false, false);
        } ?>
        <?php if (!$item['price_formatted']) : ?>
          <span class="donate-badge<?php echo $inside_card ? "" : " alt"; ?><?php echo $yellow ? " yellow" : ""; ?>">
            <strong>Para empresas</strong>
          </span>
        <?php endif; ?>
        <img loading="lazy" src="<?php echo $image_data["image"]; ?>" alt="<?php echo $image_data["alt"]; ?>" />
        <?php if (!$social) : ?>
          <div class="price text-right" itemprop="price">
            <?php if ($item['in_stock']) : ?>
              <?php if ($item['price_formatted']) : ?>
                <span><?php echo str_replace(",00", "", $item['price_formatted']); ?></span>
              <?php else : ?>
                <?php if (get_post_meta($item['ID'], 'polen_price_range_b2b', true)) : ?>
                  <?php
                    echo 'A partir de R$';
                    echo get_post_meta($item['ID'], 'polen_price_range_b2b', true);
                  ?>
                <?php else : ?>
                  <?php echo 'Sob consulta'; ?>
                <?php endif; ?>
              <?php endif; ?>
            <?php else : ?>
              <span>Esgotado</span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <a href="<?= $item["talent_url"]; ?>" class="link"></a>
      </figure>
      <?php $stock = $item['stock']; ?>
      <?php if($stock > 0 && $stock <= 10): ?>
        <span class="polen-card__low-stock"><?php echo $stock; ?> videos apenas</span>
      <?php endif; ?>
      <h4 class="title text-truncate">
        <a href="<?= $item["talent_url"]; ?>" title="<?= $item["name"]; ?>" itemprop="name"><?= $item["name"]; ?></a>
      </h4>
      <h5 class="category text-truncate">
        <a href="<?= $item["category_url"]; ?>"><?= $item["category"]; ?></a>
      </h5>
    </div>
  </div>
<?php
}

function createSlug($str, $delimiter = '-'){
  $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
  return $slug;
}

function polen_banner_scrollable($items, $title, $emoji = "", $link = "", $subtitle = "", $social = false)
{
  if (!$items) {
    return;
  }
?>
  <section class="row mb-2 banner-scrollable">
    <div class="col-md-12">
      <header class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <?php if ($link != "") : ?>
              <a href="<?php echo $link; ?>" class="typo typo-title">
            <?php endif; ?>
            <h2 class="typo typo-title mr-2">
              <?php if ($emoji != "") : ?>
                <img class="mr-1" loading="lazy" src="<?php echo $emoji; ?>" alt="<?php echo $title; ?>">
              <?php endif; ?>
              <?php echo $title; ?>
            </h2>
            <?php if ($link != "") : ?>
              </a>
            <?php endif; ?>
        </div>
        <?php if ($subtitle != "") : ?>
          <div class="col-12">
            <p class="my-1"><?php echo $subtitle; ?></p>
          </div>
        <?php endif; ?>
      </header>
    </div>
    <div class="col-md-12 p-0 p-md-0">
      <div class="banner-wrapper">
        <div id="banner-<?php echo createSlug($title); ?>" class="banner-content">
          <?php foreach ($items as $item) : ?>
            <?php polen_front_get_card($item, "responsive", $social); ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php ; ?>
    <?php if (count($items) > 6) : ?>
      <div class="col-md-12 mt-4 d-none d-md-block">
        <div class="pol-dots">
          <a href="javascript:polPrevCarousel('banner-<?php echo createSlug($title); ?>')">
            <i class='icon icon-left-arrow pol-prev-button mr-2'></i>
          </a>
          <a href="javascript:polNextCarousel('banner-<?php echo createSlug($title); ?>')">
            <i class='icon icon-right-arrow pol-next-button ml-2'></i>
          </a>
        </div>
      </div>
    <?php endif; ?>
  </section>
<?php
}

function polen_front_get_news($items, $title, $link, $social = "", $campanha = "")
{
  if (!$items) {
    return;
  }
?>
  <section class="row mb-2">
    <div class="col-md-12">
      <header class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <h2 class="typo typo-title mr-2"><?php echo $title; ?></h2>
          <?php if ($link) : ?>
            <a href="<?php echo $link; ?>" class="typo typo-link">Ver todos <?php Icon_Class::polen_icon_chevron_right(); ?></a>
          <?php endif; ?>
        </div>
      </header>
    </div>
    <div class="col-md-12">
      <div class="row card-list">
        <div class="col-md-12 p-0 p-md-0">
          <div class="banner-wrapper">
            <div class="banner-content">
              <?php foreach ($items as $item) : ?>
                <?php polen_front_get_card($item, "responsive", $social, $campanha); ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php
}

function polen_front_get_artists($items, $title, $social = false)
{
  if (!$items) {
    return;
  }
?>
  <section class="row pt-2 mb-5 all-artists">
    <div class="col-12 col-md-12">
      <header class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <h2 class="typo typo-title mr-2"><?= $title; ?></h2>
          <a href="#" class="typo typo-link">Ver todos <?php Icon_Class::polen_icon_chevron_right(); ?></a>
        </div>
      </header>
    </div>
    <div class="col-md-12">
      <div class="row">
        <?php foreach ($items as $item) : ?>
          <?php polen_front_get_card($item, "small", $social); ?>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-12">
      <div class="row mt-md-5 mt-4">
        <div class="col-md-12 text-center">
          <button type="button" class="btn btn-primary btn-lg">Ver todos</button>
        </div>
      </div>
    </div>
  </section>
<?php
}

function polen_front_get_videos($videos, $title = "Vídeos em destaque")
{
  if (!$videos) {
		return;
	}
  if( ! wp_script_is( 'owl-carousel', 'enqueued' ) ) {
      wp_enqueue_script('owl-carousel');
  }
?>
	<section id="talent-videos" class="row my-1 pb-4 mb-5">
    <div class="col-md-12">
      <header class="row my-3">
        <div class="col">
          <h2><?php echo $title; ?></h2>
        </div>
      </header>
    </div>
		<div class="col-md-12 p-0 mb-4">
			<div id="videos-carousel" class="owl-carousel owl-theme ">
          <?php foreach ($videos as $key=>$value) : ?>
            <?php if ($value['video_url']) : ?>
              <div class="item">
                <div class="polen-card-video">
                  <figure id="cover-box" class="video-cover" data-id="<?php echo $key; ?>">
                    <img loading="lazy" src="<?php echo $value['cover']; ?>" alt="">
                    <div class="video-player-button" data-id="<?php echo $key; ?>"></div>
                    <div class="video-icons">
                      <figure class="image-cropper color small">
                        <?php echo $value['talent_thumb']; ?>
                      </figure>
                      <figure class="image-cropper small">
                        <?php echo $value['initials']; ?>
                      </figure>
                    </div>
                  </figure>
                  <video id="video-box" class="video-cover src-box d-none" playsinline width="100%" height="100%" data-id="<?php echo $key; ?>">
                    <source src="<?php echo $value['video_url']; ?>" type="video/mp4">
                  </video>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
      </div>
		</div>
	</section>
  <script>
    (function($) {
      $('.video-player-button').on('click',function(){

        // Get video by data-id id
        let id = $(this).attr('data-id');
        const video = document.querySelector('#video-box[data-id="'+id+'"]');

        // Stop others videos
        const allVideos = document.querySelectorAll('#video-box:not([data-id="'+id+'"])');
        if (allVideos) {
          for (let i = 0; i < allVideos.length; i++) {
            allVideos[i].controls = false;
            allVideos[i].pause();
            allVideos[i].currentTime = 0;
          }
          $('#video-box:not([data-id="'+id+'"])').addClass("d-none");
          $('#cover-box:not([data-id="'+id+'"])').removeClass("d-none");
        }

        // Show video and remove cover
        $('#video-box[data-id="'+id+'"]').removeClass("d-none");
        $('#cover-box[data-id="'+id+'"]').addClass("d-none");

        // Play video
        video.controls = true;
        setImediate(function(){
          video.play();
        })

        video.addEventListener("ended", endVideo);

        function endVideo() {
          video.controls = false;
          // Show cover and remove video
          $('#video-box[data-id="'+id+'"]').addClass("d-none");
          $('#cover-box[data-id="'+id+'"]').removeClass("d-none");
        }
      });
    })(jQuery);
	</script>
<?php
}

function polen_front_get_tutorial()
{
?>
  <section class="row tutorial mt-4 mb-4">
    <div class="col-md-12">
      <header class="row mb-3">
        <div class="col">
          <h2 class="typo typo-title">Como funciona</h2>
        </div>
      </header>
    </div>
    <div class="col-md-12">
      <div class="box-round py-4 px-4">
        <div class="row">
          <div class="col-4">
            <div class="row">
              <div class="col-12 text-center icon subtitle"><?php Icon_Class::polen_icon_phone(); ?></div>
              <div class="col-12 text-center mt-2">
                <p>Escolha um ídolo para gravar seu vídeo.</p>
              </div>
            </div>
          </div>
          <div class="col-4">
            <div class="row">
              <div class="col-12 text-center icon subtitle"><?php Icon_Class::polen_icon_camera_video(); ?></div>
              <div class="col-12 text-center mt-2">
                <p>Receba seu vídeo exclusivo por e-mail.</p>
              </div>
            </div>
          </div>
          <div class="col-4">
            <div class="row">
              <div class="col-12 text-center icon subtitle"><?php Icon_Class::polen_icon_hand_thumbs_up(); ?></div>
              <div class="col-12 text-center mt-2">
                <p>Compartilhe o vídeo com todo mundo!</p>
              </div>
            </div>
          </div>
          <div class="col-12">
            <p class="faq-text">Ainda com dúvidas? <a href="/ajuda">Saiba mais</a></p>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php
}

/**
 * Funcao que retorna ou uma tag IMG ou um SPAN com as iniciais
 * @param int
 * @param string
 * @return IMG|SPAN
 */
function polen_get_avatar($user_id, $size = 'polen-square-crop-lg')
{
  if (is_plugin_active('wp-user-avatar/wp-user-avatar.php') && has_wp_user_avatar($user_id)) {
    return get_wp_user_avatar($user_id, $size);
  } else {
    $user = get_user_by('id', $user_id);
    $initials_name = polen_get_initials_name_by_user($user);
    return '<span>' . $initials_name   . '</span>';
  }
}

function polen_get_avatar_src($user_id, $size = 'polen-squere-crop-lg')
{
  if (is_plugin_active('wp-user-avatar/wp-user-avatar.php') && has_wp_user_avatar($user_id)) {
    return get_wp_user_avatar_src($user_id, $size);
  }
}

function polen_box_image_message($image, $text)
{
?>
  <div class="box-round">
    <div class="row p-4">
      <div class="col-md-12 text-center">
        <img src="<?php echo $image; ?>" alt="<?php echo $text; ?>" class="correct-margin">
      </div>
      <div class="col-md-12 text-center mt-4">
        <p><?php echo $text; ?></p>
      </div>
    </div>
  </div>
<?php
}

/**
 * Cria o box dos produtos relacionados dentro de content-single-product
 * @param int $product_id
 * @return HTML
 */
function polen_box_related_product_by_product_id($product_id)
{
  $args = polen_get_array_related_products($product_id);
  $cat_link = polen_get_url_category_by_product_id($product_id);
?>
  <div class="row">
    <div class="col-12 col-md-12">
      <?php polen_banner_scrollable($args, "Veja também", "",$cat_link); ?>
    </div>
  </div>
<?php
}


/**
 * Criar form para cadastro da newsletter ou outro lugar
 * no site onde pode-se cadastrar no table da newsletter
 * @param string $newsletter
 * @return HTML
 */
function polen_form_signin_newsletter(string $id = "newsletter", string $event = 'newsletter')
{
  $inputs = new Material_Inputs();
?>
  <div id="signin-newsletter" class="col-md-4 mt-4">
    <h5 class="title typo typo-title typo-small">Se conecte com a gente!</h5>
    <p class="description typo typo-p typo-small typo-double-line-height">Receba novidades e conteúdos exclusivos da Polen.</p>
    <form id="<?php echo $id; ?>" action="/" method="POST">
      <div class="row">
        <div class="col-md-8 mb-2 mb-md-0">
          <?php
          $inputs->input_hidden("action", "polen_newsletter_signin");
          $inputs->input_hidden("page_source", filter_input(INPUT_SERVER, 'REQUEST_URI'));
          $inputs->input_hidden("event", $event);
          $inputs->input_hidden("is_mobile", polen_is_mobile() ? "1" : "0");
          $inputs->input_hidden("security", wp_create_nonce('news-signin'));
          $inputs->material_input(Material_Inputs::TYPE_EMAIL, "email", "email", "E-mail", true);
          ?>
        </div>
        <div class="col-md-4 mt-2 mt-md-0 d-md-flex align-items-md-center">
          <!-- <input type="submit" value="Enviar" class="signin-newsletter-button btn btn-outline-light btn-lg btn-block" /> -->
          <?php $inputs->material_button(Material_Inputs::TYPE_SUBMIT, "botao", "Enviar"); ?>
        </div>
        <div class="col-md-8 mb-2 mb-md-0 small signin-response"></div>
      </div>
    </form>
  </div>
<?php
}


function polen_form_add_whatsapp($order_number, $whatsapp_number = "")
{
  wp_enqueue_script('form-whatsapp');
?>
  <div id="add-whatsapp" class="add-whatsapp row">
    <div class="col-12 col-md-6">
      <div class="box-round d-flex p-4 my-3">
        <div class="mr-2">
          <img width="57" src="<?php echo TEMPLATE_URI; ?>/assets/img/icon-whatsapp.png" alt="Ícone do Whatsapp" />
        </div>
        <div>
          <span v-if="!savedPhone">
            <p class="mb-2"><strong>Receba seu vídeo no Whatsapp</strong></p>
            <p>Caso você queira receber o seu vídeo via whatsapp, preencha o campo abaixo:</p>
          </span>
          <span v-if="savedPhone">
            <p>Você vai receber o vídeo no seu Whatsapp {{savedPhone}}</p>
            <p><button class="btn-link alt" v-on:click="handleEdit" v-bind:class="edit ? 'd-none' : ''">Editar</button></p>
          </span>
          <form action="/" method="POST" v-bind:class="edit ? '' : 'd-none'" v-on:submit.prevent="handleSubmit" id="form-add-whatsapp">
            <?php //TODO botar o value que precisa ser enviado ao endpoint
            ?>
            <input type="hidden" name="action" value="polen_whatsapp_form">
            <input type="hidden" name="page_source" value="<?= filter_input(INPUT_SERVER, 'REQUEST_URI'); ?>" />
            <input type="hidden" name="is_mobile" value="<?= polen_is_mobile() ? "1" : "0"; ?>" />
            <input type="hidden" name="security" value=<?php echo wp_create_nonce(Polen_Order::WHATSAPP_NUMBER_NONCE_ACTION); ?>>
            <input type="hidden" name="order" value="<?php echo $order_number; ?>" />

            <input type="hidden" id="phone_cache" value="<?php echo $whatsapp_number; ?>" />
            <input type="text" name="phone_number" v-model="phone" v-on:keyup="handleChange" placeholder="(00) 00000-0000" maxlength="15" class="form-control form-control-lg" style="background-color: transparent;" required />
            <button type="submit" class="btn btn-outline-light btn-lg mt-3 px-5">Salvar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php
}

function polen_get_share_icons()
{
?>
  <div class="share-options row mt-4 mb-4">
    <div class="share-button col-12 text-center">
      <button class="btn btn-outline-light btn-md" onclick="shareVideo('Compartilhado', null)"><span class="mr-2"><?php Icon_Class::polen_icon_share(); ?></span>Compartilhar</button>
    </div>
    <div class="share-icons col-12">
      <div class="row">
        <div class="col-12 text-center">
          <span class="mr-2"><?php Icon_Class::polen_icon_share(); ?></span>Compartilhar
        </div>
        <div class="col-12 d-flex justify-content-center mt-3">
          <div class="row">
            <div class="col-4 text-center">
              <a href="javascript:shareSocial.send(shareSocial.network.facebook);" class="share-icons__icon facebook"><?php Icon_Class::polen_icon_social("facebook"); ?></a>
            </div>
            <div class="col-4 text-center">
              <a href="javascript:shareSocial.send(shareSocial.network.twitter);" class="share-icons__icon twitter"><?php Icon_Class::polen_icon_social("twitter"); ?></a>
            </div>
            <div class="col-4 text-center">
              <a href="javascript:shareSocial.send(shareSocial.network.whatsapp);" class="share-icons__icon whatsapp"><?php Icon_Class::polen_icon_social("whatsapp"); ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    if (!navigator.share) {
      document.querySelector('.share-icons').classList.add("show");
    }
  </script>
<?php
}

function polen_get_share_button()
{
?>
  <button class="share-button btn btn-outline-light btn-md" onclick="shareVideo('Compartilhado', null)"><?php Icon_Class::polen_icon_share(); ?></button>
  <script>
    if (navigator.share) {
      document.querySelector('.share-button').classList.add("show");
    }
  </script>
<?php
}

function generic_get_about($main_title, $title, $text)
{
?>
  <section class="row donation-box custom-donation-box mt-4 mb-4">
    <div class="col-md-12">
      <header class="row mb-3">
        <div class="col">
          <h2><?php echo $main_title; ?></h2>
        </div>
      </header>
    </div>
    <div class="col-md-12">
      <div class="box-round py-4 px-4">
        <div class="row">
          <div class="col-md-12 mt-4">
            <p><strong><?php echo $title; ?></strong></p>
            <?php echo $text; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
  <script>
    const video = document.getElementById("sa-video-about");
    video.load();
    video.currentTime = 1;
  </script>
<?php
}

function polen_get_media_news() {
?>
  <section id="media-news" class="row my-5">
    <div class="col-sm-12">
      <div class="row">
        <div class="col-sm-12 mb-4">
          <h2 class="typo typo-title text-center">Polen na mídia</h2>
        </div>
        <div class="col-sm-12">
          <div class="veiculos">
            <a href="/polen-na-midia">
              <img src="<?php echo TEMPLATE_URI; ?>/assets/img/na-midia/globo.png" alt="O Globo"></img>
            </a>
            <a href="/polen-na-midia">
              <img src="<?php echo TEMPLATE_URI; ?>/assets/img/na-midia/folha.png" alt="Folha de São Paulo"></img>
            </a>
            <a href="/polen-na-midia">
              <img src="<?php echo TEMPLATE_URI; ?>/assets/img/na-midia/veja.png" alt="Veja SP"></img>
            </a>
            <a href="/polen-na-midia">
              <img src="<?php echo TEMPLATE_URI; ?>/assets/img/na-midia/exame.png" alt="Exame"></img>
            </a>
          </div>
        </div>
      </div>
    </div>
  <section>
<?php
}

function polen_get_toast($text)
{
  if (!$text || empty($text)) {
    return;
  }
?>
  <div id="pol-toast" class="pol-toast mb-5">
    <div class="ico mr-2"><img src="<?php echo TEMPLATE_URI; ?>/assets/img/emoji/festa.png" alt="Emoji Festa"></div>
    <div class="text">
      <?php echo $text; ?>
    </div>
    <button class="ml-2 pol-toast-close" onclick="polRemoveElement('#pol-toast')">
      <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 22.5C17.5228 22.5 22 18.0228 22 12.5C22 6.97715 17.5228 2.5 12 2.5C6.47715 2.5 2 6.97715 2 12.5C2 18.0228 6.47715 22.5 12 22.5Z" stroke="#767676" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M15 9.5L9 15.5" stroke="#767676" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M9 9.5L15 15.5" stroke="#767676" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>
  </div>
<?php
}

function polen_get_banner($title, $banners, $link)
{
?>
  <div class="row">
    <div class="col-12">
      <a href="<?php echo $link; ?>">
          <img loading="lazy" src="<?php echo $banners['desktop']; ?>" alt="<?php echo $title; ?>" class="img-responsive d-none d-md-block rounded" />
          <img loading="lazy" src="<?php echo $banners['mobile']; ?>" alt="<?php echo $title; ?>" class="img-responsive d-block d-md-none rounded" />
      </a>
    </div>
  </div>
<?php
}

function polen_get_tooltip($text, $placement = "right")
{
?>
  <button type="button"
    class="btn btn-tooltip"
    data-toggle="tooltip"
    data-html="true"
    data-placement="<?php echo $placement; ?>"
    title="<?php echo $text; ?>">
      ?
  </button>
<?php
}

function polen_alert($text = "")
{
  ?>
  <?php //polen_alert('Para melhorar a experiencia dos nossos ídolos, criamos um novo Dashboard. Para acessar <a href="https://idolo.polen.me" target="_blank">clique aqui</a>'); ?>
  <div class="alert alert-warning alert-dismissible fade show mb-5" role="alert">
    <?php echo $text; ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <?php
}
