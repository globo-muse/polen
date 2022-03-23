<?php

/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.8.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');

use Polen\Includes\Polen_Occasion_List;
use Polen\Includes\Polen_Update_Fields;

$occasion_list = new Polen_Occasion_List();
$Talent_Fields = new Polen_Update_Fields();
?>

<!-- <div class="row mt-2">
	<div class="col-12">
		<div class="progress" style="height: 7px;">
			<div class="progress-bar bg-primary" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
		</div>
	</div>
</div> -->
<div class="row">
  <div class="col-12 col-md-6 order-md-2 mt-md-4">
    <?php
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
      $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
      $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
      $talent_id = get_post_field('post_author', $product_id);
      $thumbnail = polen_get_thumbnail($product_id);
      $talent = get_user_by('id', $talent_id);
      $is_social = social_product_is_social($_product, social_get_category_base());
      $talent_data = $Talent_Fields->get_vendor_data($talent_id);
      $product_name = str_replace('%', '&#37;', $_product->get_title());

      $talent_cart_detail = array(
        "has_details" => false,
        "avatar" => $thumbnail["image"],
        "alt" => $thumbnail["alt"],
        "name" => $_product->get_title(),
        "career" => $talent_data->profissao,
        "price" => $_product->get_price_html(),
        "from" => "",
        "to" => "",
        "category" => "",
        "mail" => "",
        "description" => ""
      );
    }
    polen_get_talent_card($talent_cart_detail, $is_social);
    $inputs = new Material_Inputs();

    if(!isset($cart_item["video_to"])) {
      $cart_item["video_to"] = "other_one";
    }
    $cart_item_basic = $cart_item;
    unset(
      $cart_item_basic["data"],
      $cart_item_basic["data_hash"],
      $cart_item_basic["key"],
    );
    if (is_user_logged_in()) {
      $current_user = wp_get_current_user();
      $email_to_video = $current_user->user_email;
      $offered_by = $current_user->display_name;
      $phone = get_user_meta($current_user->ID,'billing_phone',true);
      $cart_item_basic["offered_by"] = $offered_by;
      $cart_item_basic["email_to_video"] = $email_to_video;
      $cart_item_basic["phone"] = $phone;
    }
    ?>
    <script>
      const cart_items = <?php echo json_encode($cart_item_basic); ?>;
    </script>
  </div>
  <div class="col-12 mt-5 col-md-6 order-md-1 mt-md-4">
    <form id="cart-advanced" class="woocommerce-cart-form cart-advanced" action="<?php echo esc_url(wc_get_checkout_url()); ?>" method="post" v-on:submit="handleSubmit">
      <?php do_action('woocommerce_before_cart_table'); ?>
      <?php do_action('woocommerce_before_cart_contents'); ?>
      <?php

      foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
        $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
        $talent_id = get_post_field('post_author', $product_id);
        $talent_data = $Talent_Fields->get_vendor_data($talent_id);

        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) :
          $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
      ?>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php $inputs->input_hidden("cart-item-key", $cart_item_key); ?>

      <div class="cart-step cart-step1" v-bind:class="{'-disabled': activeItem != 1, '-done' : activeItem == 2}">
        <?php do_action('woocommerce_cart_contents'); ?>
        <header class="header mb-4">
          <h2 class="title" v-on:click="nextStep(1)">
            <div class="cart-step__ico cart-step__ico1"></div>
            <span>
              Informações do pedido
              <span href="javascript:void(0)" class="btn-edit" v-if="activeItem != 1">editar</span>
            </span>
          </h2>
        </header>
        <div class="cart-step__content">
          <?php
          $inputs->material_input(Material_Inputs::TYPE_TEXT, "offered_by", "offered_by", "Seu nome", true, "mb-3", !$offered_by ? array(
            "v-model" => "offered_by",
          ) : array(
            "value" => $offered_by,
            // "readonly" => "readonly"
          ));
          $inputs->material_input(Material_Inputs::TYPE_EMAIL, "email_to_video", "email_to_video", "Seu e-mail", true, "mb-3", !$email_to_video ? array(
            "v-model" => "email_to_video",
          ) : array(
            "value" => $email_to_video,
            "readonly" => "readonly"
          ));
          $inputs->material_input(Material_Inputs::TYPE_PHONE, "phone", "phone", "Seu Whatsapp (opcional)", false, "", !$phone ? array(
            "v-model" => "phone",
            "v-on:keyup" => "handlePhoneChange",
            "maxlength" => "15",
          ) : array(
            "value" => $phone,
            "readonly" => "readonly"
          ));
          $inputs->material_input_helper("Pode ficar tranquilo que enviaremos somente atualizações sobre o pedido");
          $inputs->material_button_outlined(Material_Inputs::TYPE_SUBMIT, "next1", "Avançar", "mt-4", array(
            // ":disabled" => "step1Disabled()",
            "v-on:click" => "nextStep(2)"
          ));
          ?>
        </div>
      </div>
      <div class="divisor" v-bind:class="{'-disabled' : activeItem != 2}"></div>
      <div class="cart-step cart-step2" v-bind:class="{'-disabled': activeItem != 2}">
        <header class="header mb-4">
          <h2 class="title">
            <div class="cart-step__ico cart-step__ico2"></div>
            Informações do vídeo
          </h2>
        </header>
        <div class="cart-step__content">
          <h3 class="subtitle">Para quem é o vídeo?*</h3>
          <?php
          $icons_path = TEMPLATE_URI . "/assets/img/pol_form_icons/";
          $inputs->pol_select_advanced("video_to", array(
            $inputs->pol_select_advanced_item($icons_path . "presente.png", "Presente", "other_one", $cart_item_basic["video_to"] == "other_one"),
            $inputs->pol_select_advanced_item($icons_path . "mim.png", "Para mim", "to_myself", $cart_item_basic["video_to"] == "to_myself")
          ), array("v-on:polselectchange" => "video_toHandle"));
          ?>
          <div class="mt-3" v-bind:class="{'d-none' : video_to == 'to_myself'}">
            <?php $inputs->material_input(Material_Inputs::TYPE_TEXT, "name_to_video", "name_to_video", "Quem vai receber o presente?", false, "", array(
              ":required" => "isForOther()",
              "minlength" => "3",
              "value" => $cart_item["name_to_video"]
            )); ?>
          </div>
          <h3 class="subtitle mt-4">Qual é a ocasião do vídeo?*</h3>
          <?php
          $inputs->pol_select_advanced("video_category", array(
            $inputs->pol_select_advanced_item($icons_path . "aniversario.png", "Aniversário", "aniversario", $cart_item["video_category"] == "aniversario"),
            $inputs->pol_select_advanced_item($icons_path . "casamento.png", "Casamento", "casamento", $cart_item["video_category"] == "casamento"),
            $inputs->pol_select_advanced_item($icons_path . "conselho.png", "Conselho", "conselho", $cart_item["video_category"] == "conselho"),
            $inputs->pol_select_advanced_item($icons_path . "formatura.png", "Formatura", "formatura", $cart_item["video_category"] == "formatura"),
            $inputs->pol_select_advanced_item($icons_path . "novidade.png", "Novidade", "novidade", $cart_item["video_category"] == "novidade"),
            // $inputs->pol_select_advanced_item($icons_path . "fim-de-ano.png", "Fim de ano", "fim-de-ano", $cart_item["video_category"] == "fim-de-ano"),
            $inputs->pol_select_advanced_item($icons_path . "outras.png", "Outras", "outras", $cart_item["video_category"] == "outras")
          ), array(
            "v-on:polselectchange" => "occasionHandle"
          ));
          ?>
          <h3 class="subtitle mt-4">Instruções para o vídeo*
            <?php polen_get_tooltip("
            <div class='box-textarea__placeholder'>
              <ol>
                <li>Não são permitidos pedidos comerciais, nem menções à marcas.</li>
                <li>Músicos não tem autorização para cantar trechos de músicas com direitos autorais.</li>
              </ol>
            </div>
            ", 'top'); ?>
          </h3>
          <div class="box-textarea">
            <?php $inputs->material_textarea("instructions_to_video", "instructions_to_video", "Instruções para o vídeo", true, array(
              "v-model" => "instructions_to_video",
              "v-on:change" => "handleInstructionsChange",
              "minlength" => "10"
            )); ?>
          </div>
          <div class="row mt-2">
            <div class="col-md-12" v-bind:class="{'d-none' : isInstructionsOk()}">
              <div id="prohibited-instruction-alert" class="alert alert-danger mt-2" role="alert">Lembre-se: Os talentos não tem autorização para cantar ou tocar trechos de músicas.</div>
            </div>
            <div class="col-12 col-md-12">
              <?php
              $social_class = '';
              $allow_video_on_page = isset($cart_item['allow_video_on_page']) ? $cart_item['allow_video_on_page'] : 'on';
              $checked_allow = '';
              if ($allow_video_on_page == 'on') {
                $checked_allow = 'checked';
              }
              if ($is_social) {
                $social_class = 'criesp';
              }
              ?>
              <label for="cart_allow_video_on_page_<?php echo $cart_item_key; ?>" class="d-flex">
                <?php
                printf(
                  '<input type="checkbox" name="allow_video_on_page" class="%s %s form-control form-control-lg" id="cart_allow_video_on_page_%s"
											data-cart-id="%s" %s>',
                  'polen-cart-item-data',
                  $social_class,
                  $cart_item_key,
                  $cart_item_key,
                  $checked_allow,
                );
                ?>
                <span class="ml-2">Permitir que o vídeo seja postado no perfil do artista</span>
              </label>
            </div>
          </div>
        </div>
      </div>
      <?php $inputs->material_button(Material_Inputs::TYPE_SUBMIT, "btn-buy", "Comprar agora", "mt-5", array(
        ":disabled" => "activeItem == 1",
        "v-on:click" => "submit = true"
      )); ?>
      <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
      <?php do_action('woocommerce_after_cart_contents'); ?>
      <?php do_action('woocommerce_after_cart_table'); ?>
    </form>
  </div>
  <?php /*
	<form class="woocommerce-cart-form col-12 col-md-6 order-md-1" action="<?php echo esc_url(wc_get_checkout_url()); ?>" method="post">
		<?php do_action('woocommerce_before_cart_table'); ?>


		<?php do_action('woocommerce_before_cart_contents'); ?>
		<?php
		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
			$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
			$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
			$talent_id = get_post_field('post_author', $product_id);
			$talent_data = $Talent_Fields->get_vendor_data($talent_id);

			if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) :
				$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
		?>
			<?php endif; ?>
		<?php endforeach; ?>

		<div class="row mt-3 py-2 cart-other">
			<?php do_action('woocommerce_cart_contents'); ?>
			<div class="col-12 col-md-12">
				<div class="row d-none">
					<div class="col-md-12 mb-3">
						<span class="form-title">Esse vídeo é para:</span>
					</div>
					<div class="col-md-12 mb-3">
						<?php
						$video_to = isset($cart_item['video_to']) ? $cart_item['video_to'] : '';
						$checked_other_one = '';
						if ($video_to == 'other_one' || empty( $video_to )) {
							$checked_other_one = 'checked';
						}
						$checked_to_myself = '';
						if ($video_to == 'to_myself') {
							$checked_to_myself = 'checked';
						}
						$checked_to_myself = 'checked';
						?>
						<label for="cart_video_to_<?php echo $cart_item_key; ?>_other">
							<input type="radio" class="polen-cart-item-data cart-video-to" id="cart_video_to_<?php echo $cart_item_key; ?>_other" data-cart-id="<?php echo $cart_item_key; ?>" name="video_to" value="other_one" <?php echo $checked_other_one; ?> /><span class="ml-2">Outra pessoa</span>
						</label>
						<label for="cart_video_to_<?php echo $cart_item_key; ?>_me">
							<input type="radio" class="polen-cart-item-data cart-video-to ml-4" id="cart_video_to_<?php echo $cart_item_key; ?>_me" data-cart-id="<?php echo $cart_item_key; ?>" name="video_to" value="to_myself" <?php echo $checked_to_myself; ?> /><span class="ml-2">Para mim</span>
						</label>
					</div>
				</div>

				<div class="row video-to-info mb-3 d-none">
					<div class="col-12 col-md-12">
						<?php
						$offered_by = isset($cart_item['offered_by']) ? $cart_item['offered_by'] : '';
						?>
						<label for="<?php echo 'cart_offered_by_' . $cart_item_key; ?>">Vídeo oferecido por</label>
						<?php
						printf(
							'<input type="text" placeholder="Vídeo oferecido por" class="%s form-control form-control-lg" id="cart_offered_by_%s" data-cart-id="%s" name="offered_by" value="%s" required="required" />',
							'polen-cart-item-data',
							$cart_item_key,
							$cart_item_key,
							$offered_by,
						);
						?>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-md-12">
						<?php
						$name_to_video = isset($cart_item['name_to_video']) ? $cart_item['name_to_video'] : '';
						?>
						<label for="<?php echo 'cart_name_to_video_' . $cart_item_key; ?>">Nome</label>
						<?php
						if ($is_social) {
							$name_placeholder = "Como você gostaria de ser chamado?";
						} else {
							$name_placeholder = "Para quem é esse vídeo-polen";
						}
						printf(
							'<input type="text" placeholder="'.$name_placeholder.'" class="%s form-control form-control-lg" id="cart_name_to_video_%s" data-cart-id="%s" name="name_to_video" value="%s" required="required"/>',
							'polen-cart-item-data',
							$cart_item_key,
							$cart_item_key,
							$name_to_video,
						);
						?>
					</div>
				</div>

				<?php
				if (is_user_logged_in()) {
					$current_user = wp_get_current_user();
					$email_to_video = $current_user->user_email;
					printf(
						'<input type="hidden" class="%s" id="cart_email_to_video_%s" data-cart-id="%s" name="email_to_video" value="%s" required="required" />',
						'polen-cart-item-data',
						$cart_item_key,
						$cart_item_key,
						$email_to_video,
					);
				?>
				<?php } else { ?>
					<div class="row mt-3">
						<div class="col-12 col-md-12">
							<?php
							$email_to_video = isset($cart_item['email_to_video']) ? $cart_item['email_to_video'] : '';
							?>
							<label for="<?php echo 'cart_email_to_video_' . $cart_item_key; ?>">e-mail</label>
							<?php
							printf(
								'<input type="email" placeholder="e-mail para atualizações do seu pedido" class="%s form-control form-control-lg" id="cart_email_to_video_%s" data-cart-id="%s" name="email_to_video" value="%s" required="required"  />',
								'polen-cart-item-data',
								$cart_item_key,
								$cart_item_key,
								$email_to_video,
							);
							?>
						</div>
					</div>
				<?php } ?>

				<div class="row mt-4">
					<div class="col-12 col-md-12">
						<?php if( !$is_social ) : ?>
							<label for="cart_video_category_<?php echo $cart_item_key; ?>">Qual ocasião do vídeo?</label>
						<?php else: ?>
							<label for="cart_video_category_<?php echo $cart_item_key; ?>">Ocasião do vídeo</label>
						<?php endif; ?>
					</div>
					<div class="col-md-12">
						<?php
						if( !$is_social ) :
							$video_category = isset( $cart_item['video_category' ] ) ? $cart_item[ 'video_category' ] : '';
							printf(
								'<select class="%s form-control form-control-lg custom-select select-ocasion" id="cart_video_category_%s" data-cart-id="%s" name="video_category" required="required"/>',
								'polen-cart-item-data',
								$cart_item_key,
								$cart_item_key
							);
							echo "<option value=''>Categoria</option>";
							$arr_occasion = $occasion_list->get_occasion(null, 'type', 'ASC', 1, 0, 'DISTINCT type');
							foreach ($arr_occasion as $occasion) :
								$selected = ( $occasion->type == $video_category ) ? 'selected ' : null;
								echo "<option value='" . $occasion->type . "' {$selected}>" . $occasion->type . "</option>";

							endforeach;
						else:
							printf(
								'<input type="text" placeholder="" class="%s form-control form-control-lg" id="cart_video_category_%s" data-cart-id="%s" name="video_category" value="%s" required="required" readonly />',
								'polen-cart-item-data',
								$cart_item_key,
								$cart_item_key,
								'Doação para o Criança Esperança',
							);
						endif;
						?>
						</select>
					</div>
				</div>
				<div class="row mt-4">
					<div class="col-12 col-md-12">
						<label for="cart_instructions_to_video_<?php echo $cart_item_key; ?>">
							<?php
								if($is_social) {
									echo("Cidade");
								} else {
									echo("Instruções para o vídeo");
								}
							?>
						</label>
					</div>
					<div class="col-md-12">
						<?php
						$instructions_to_video = isset($cart_item['instructions_to_video']) ? $cart_item['instructions_to_video'] : '';
						$product_name = str_replace( '%', '&#37;', $_product->get_title() );
						if ($is_social) {
							printf(
								"
								<input 	name=\"instructions_to_video\" placeholder=\"Sua cidade\"
									class=\"%s form-control form-control-lg\" id=\"cart_instructions_to_video_%s\"
									data-cart-id=\"%s\" required=\"required\" value=\"%s\" />",
								'polen-cart-item-data',
								$cart_item_key,
								$cart_item_key,
								$instructions_to_video,
							);
						} else {
							printf(
								"
								<div class=\"holder\">
									<div class=\"placeholder\">
										Escreva aqui o que você gostaria que <b>{$product_name}</b> falasse. Lembre-se:</b><br><br>
										1. <b>Não são permitidos pedidos comerciais</b>, nem menções à marcas.<br>
										2. Músicos <b>não</b> tem autorização para <b>cantar trechos de músicas</b> com direitos autorais.
									</div>
									<textarea 	name=\"instructions_to_video\"  rows=\"7\"
									class=\"%s form-control form-control-lg\" id=\"cart_instructions_to_video_%s\"
									data-cart-id=\"%s\" required=\"required\">%s</textarea>
								</div>",
								'polen-cart-item-data',
								$cart_item_key,
								$cart_item_key,
								$instructions_to_video,
							);
						}
						?>
					</div>
          <div class="col-md-12">
            <div id="prohibited-instruction-alert" class="alert alert-danger mt-2 d-none" role="alert">Lembre-se: Os talentos não tem autorização para cantar ou tocar trechos de músicas.</div>
          </div>
				</div>
				<!-- <div class="row pb-2">
					<div class="col-12 d-flex align-items-center reload-sugestions">
						<?php Icon_Class::polen_icon_reload("reload"); ?><a href="javascript:void(0)" class="link-alt video-instruction-refresh ml-2">Outra sugestão de instrução</a>
					</div>
				</div> -->
				<div class="row mt-3">
					<div class="col-12 col-md-12">
						<?php
						$social_class = '';
						$allow_video_on_page = isset($cart_item['allow_video_on_page']) ? $cart_item['allow_video_on_page'] : 'on';
						$checked_allow = '';
						if ($allow_video_on_page == 'on') {
							$checked_allow = 'checked';
						}
						if($is_social)
						{
							$social_class = 'criesp';
						}
						?>
						<label for="cart_allow_video_on_page_<?php echo $cart_item_key; ?>" class="d-flex">
							<?php
							printf(
								'<input type="checkbox" name="allow_video_on_page" class="%s %s form-control form-control-lg" id="cart_allow_video_on_page_%s"
											data-cart-id="%s" %s>',
								'polen-cart-item-data',
								$social_class,
								$cart_item_key,
								$cart_item_key,
								$checked_allow,
							);
							?>
							<span class="ml-2">Permitir que o vídeo seja postado no perfil do artista</span>
						</label>
					</div>
				</div>
				<div class="row actions">
					<div class="col-12 col-md-12 mb-4 mt-3">
						<button type="submit" class="btn btn-<?php echo $is_social ? 'success' : 'primary'; ?> btn-lg btn-block" name="" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>"><?php esc_html_e('Avançar', 'woocommerce'); ?></button>

						<?php //do_action( 'woocommerce_cart_actions' );
						?>

						<?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
					</div>
				</div>
			</div>
		</div>
    <input type="hidden" name="tuna_sessionid" value="<?php echo wp_create_nonce(); ?>">
		<?php do_action('woocommerce_after_cart_contents'); ?>
		<?php do_action('woocommerce_after_cart_table'); ?>
	</form>
    */ ?>
</div>

<?php do_action('woocommerce_before_cart_collaterals'); ?>

<div class="cart-collaterals">
  <?php
  /**
   * Cart collaterals hook.
   *
   * @hooked woocommerce_cross_sell_display
   * @hooked woocommerce_cart_totals - 10
   */
  do_action('woocommerce_cart_collaterals');
  ?>
</div>

<?php do_action('woocommerce_after_cart'); ?>
