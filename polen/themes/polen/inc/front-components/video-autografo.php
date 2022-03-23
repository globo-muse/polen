<?php

function va_get_home_banner($title, $description, $link, $images = array("mobile" => "", "desktop" => ""))
{
	if (!$title || !$description) {
		return;
	}
?>
	<div class="row mt-4">
		<div class="col-12">
			<div class="va-banner">
				<img class="image mobile-img" src="<?php echo $images['mobile']; ?>" alt="<?php echo $title; ?>" />
				<img class="image desktop-img" src="<?php echo $images['desktop']; ?>" alt="<?php echo $title; ?>" />
				<div class="content">
					<div class="row">
						<div class="col-12 col-md-6">
							<h2><?php echo $title; ?></h2>
							<p class="mt-3"><?php echo $description; ?></p>
							<a href="<?php echo $link; ?>" class="btn btn-primary btn-md">Conheça<span class="ml-2"><?php Icon_Class::polen_icon_chevron_right(); ?></span></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

function va_get_home_banner_de_porta_em_porta($link)
{
?>
	<div class="row mt-4">
		<div class="col-12">
			<div class="va-banner">
				<img class="image mobile-img" src="<?php echo TEMPLATE_URI . '/assets/img/video-autografo/va-banner-mobile.png'; ?>" alt="De Porta em Porta">
				<img class="image desktop-img" src="<?php echo TEMPLATE_URI . '/assets/img/video-autografo/va-banner-desktop.png'; ?>" alt="De Porta em Porta">
				<div class="content">
					<h2>De Porta em Porta</h2>
					<p class="mt-3">
						Agora você pode comprar o livro<br>
						e ter um autógrafo em vídeo do Luciano Huck.
					</p>
					<a href="<?php echo $link; ?>" class="btn btn-primary btn-md">Conheça<span class="ml-2"><?php Icon_Class::polen_icon_chevron_right(); ?></span></a>
				</div>
			</div>
		</div>
	</div>
<?php
}

function va_magalu_box_thank_you()
{
?>
	<div class="row mb-4">
		<div class="col-12">
			<div class="magalu-box">
				<div class="header-box text-center py-4 px-5">
					<?php /*<img src="<?php echo TEMPLATE_URI . '/assets/img/video-autografo/lu.png'; ?>" alt="Lu"></img>*/ ?>
					<h3 style="line-height: 24px;">Obrigado por pedir seu<br>vídeo-autógrafo</h3>
				</div>
				<div class="content-box">
					<p>Você receberá seu vídeo-autógrafo<br>em até 30 dias.</p>
				</div>
			</div>
		</div>
	</div>
<?php
}

function va_magalu_box_cart( $product )
{
?>
	<div class="row mb-4">
		<div class="col-12">
			<div class="magalu-box">
				<div class="header-box text-center py-4 px-5">
					<?php /*<img src="<?php echo TEMPLATE_URI . '/assets/img/video-autografo/lu.png'; ?>" alt="Lu"></img>*/ ?>
					<h3 style="line-height:20px;">Para receber seu vídeo-autógrafo personalizado do autor, você precisa:</h3>
				</div>
				<div class="content-box mt-3 px-2">
					<div class="row">
						<div class="col-12 col-md-6 m-md-auto d-flex align-items-center">
							<ul class="order-flow half">
								<li class="item itempayment-approved complete">
									<span class="background status">1</span>
									<span class="text">
										<?php if( 'de-porta-em-porta' == $product->get_sku() ) : ?>
											<p class="description">Compre o livro De Porta em Porta no site da <a href="<?php echo ''; ?>" target="_blank"><b>Magalu</b></a></p>
										<?php else: ?>
											<?php
												$link = $product->get_meta( '_promotional_event_link_buy', true ); ?>
											<p class="description">Compre o livro<?= !empty( $link ) ? " <a href='{$link}' target='_blank'><b>aqui</b></a>" : ''; ?></p>
										<?php endif; ?>
									</span>
								</li>
								<li class="item itempayment-approved complete">
									<span class="background status">2</span>
									<span class="text">
										<!-- <p class="description">Após a compra, você receberá um e-mail da Magalu contendo o código único para resgatar seu vídeo-autógrafo</p> -->
										<p class="description">Copie o código enviado para o seu e-mail.</p>
									</span>
								</li>
								<li class="item itempayment-approved complete">
									<span class="background status">3</span>
									<span class="text">
										<p class="description">Valide o código no campo abaixo</p>
									</span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

function va_partners_footer( $product )
{
	if( empty( $product ) || $product->get_sku() != "de-porta-em-porta" ) {
		return false;
	}
?>
	<div class="row mb-3">
		<div class="col-12">
			<h2>Esse produto é feito em parceria com:</h2>
		</div>
		<div class="col-12">
			<img src="<?php echo TEMPLATE_URI . '/assets/img/video-autografo/magalu-logo.png'; ?>" alt="Magazine Luiza">
			<img src="<?php echo TEMPLATE_URI . '/assets/img/video-autografo/cia-das-letras-logo.png'; ?>" alt="Cia das Letras">
		</div>
	</div>
<?php
}

function va_get_banner_book(
	$product,
	$small = false,
	$img_bg = TEMPLATE_URI . "/assets/img/video-autografo/bg_lh_right.png"
	)
{
	$pep = new Promotional_Event_Product( $product );
	$img_book = $pep->get_url_image_product_with_size( 'polen-thumb-lg' );
	if('rebeldes-tem-asas' == $product->get_sku()) {
		$img_bg = TEMPLATE_URI . "/assets/img/video-autografo/rta-book-bg.png";
	}
?>
	<div class="row mb-3">
		<div class="col-12">
			<div class="va-top-banner<?php echo $small ? ' small' : ''; ?>">
				<div class="box-round">
					<img src="<?php echo $img_bg; ?>" alt="Fundo do Box" class="img-bg" />
				</div>
				<div class="content<?php echo $small ? '' : ' pb-2'; ?>">
					<img src="<?php echo $img_book; ?>" alt="Capa do Livro" class="book-cover" />
					<h1 class="title"><?php echo $small ? 'Livro - ' : ''; ?><?php echo $product->get_title(); ?></h1>
				</div>
			</div>
		</div>
	</div>
<?php
}

function va_get_book_infos( $product )
{
?>
	<div class="row mb-4">
		<div class="col-12">
			<div class="box-round book-info-wrapp py-3 px-3">
				<div class="row">
					<div class="col-12 truncate-wrapper">
						<h4 class="title">Sobre o Livro</h4>
						<input type="checkbox" name="expanded-1" id="expanded-1">
						<div class="truncate truncate-4">
							<?php echo $product->get_description(); ?>
						</div>
						<label for="expanded-1">Ver mais</label>
							<!-- <a href="javascript:showMoreText()" class="link-more-text show">Mostrar mais</a> -->
					</div>
				</div>
				<div class="row mb-4">
					<div class="col-12 col-md-6">
						<h4 class="title">Informações do livro</h4>
						<div class="row">
							<div class="col-3">
								<div class="book-info">
									<div class="title">Páginas</div>
									<?php Icon_Class::va_icons("pages"); ?>
									<div class="description"><?= $product->get_meta( '_promotional_event_pages_quantity', true ); ?></div>
								</div>
							</div>
							<div class="col-3">
								<div class="book-info">
									<div class="title">Idioma</div>
									<?php Icon_Class::va_icons("language"); ?>
									<div class="description"><?= $product->get_meta( '_promotional_event_language', true ); ?></div>
								</div>
							</div>
							<div class="col-3">
								<div class="book-info">
									<div class="title">Editora</div>
									<?php Icon_Class::va_icons("books"); ?>
									<div class="description"><?= $product->get_meta( '_promotional_event_publishing', true ); ?></div>
								</div>
							</div>
							<div class="col-3">
								<div class="book-info">
									<div class="title">Publicação</div>
									<?php Icon_Class::va_icons("calendar"); ?>
									<?php $published_in = date( 'd/m/Y', strtotime( $product->get_meta( '_promotional_event_published_in', true ) ) ); ?>
									<div class="description"><?= $published_in; ?></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php $stars = $product->get_meta( '_promotional_event_rating', true ); ?>
				<?php if($stars) : ?>
					<div class="row mb-3">
						<div class="col-12">
							<h4 class="title text-md-center">Avaliação</h4>
							<div class="row book-rate text-center">
								<div class="col-12">
									<?php polen_get_stars( $stars ); ?>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<script>
		function showMoreText() {
			document.querySelector(".link-more-text").classList.remove("show");
			document.querySelector(".more-text").classList.add("show");
		}
	</script>
<?php
}

function va_ctas($link = "#", $link_to_buy = "")
{
?>
	<div class="row mb-4">
		<div class="col 12">
			<div class="row mb-3">
				<div class="col-12">
					<a href="<?php echo $link; ?>" class="btn btn-primary btn-lg btn-block">Resgatar Vídeo-autógrafo</a>
				</div>
			</div>
			<?php if( !empty( $link_to_buy ) ) : ?>
			<div class="row">
				<div class="col-12">
					<a href="<?php echo $link_to_buy; ?>" class="btn btn-outline-primary btn-lg btn-block" target="_blank">Comprar o livro</a>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
<?php
}

function va_what_is( $product )
{
?>
	<div class="row va-what-is">
		<div class="col-12 text-center">
			<h3 class="title"><span class="ico mr-2"><?php Icon_Class::polen_icon_camera_video(); ?></span>O que é o Vídeo-autógrafo</h3>
			<p>O vídeo-autógrafo é uma nova maneira de conectar e criar novas experiências digitais entre leitores e seus autores favoritos. Ao adquirir uma cópia do livro <?= $product->get_title(); ?>, você pode ganhar um vídeo exclusivo e personalizado gravado por <?= $product->get_meta( '_promotional_event_author', true ); ?>.</p>
		</div>
	</div>
<?php
}

function va_front_get_talent_videos($talent, $product_id = 15)
{
	if (!$talent) {
		return;
	}
	$items = polen_get_videos_by_talent($talent);

	if( empty( $items ) ) {
		return false;
	}

	$video_url = home_url() . "/v/";
?>
	<div class="row">
		<div class="col-12">
			<h3 class="title">Vídeos-autógrafo</h3>
		</div>
	</div>
	<section id="talent-videos" class="row mb-1 banner-scrollable" data-public-url="<?php echo $video_url; ?>">
		<div class="col-md-12 p-0">
			<div class="banner-wrapper">
				<div class="banner-content type-video<?php if (sizeof($items) < 1) echo " ml-3 ml-md-0" ?>">
					<?php foreach ($items as $item) : ?>
						<div class="polen-card-video">
							<figure class="video-cover large">
								<img loading="lazy" src="<?= $item['cover']; ?>" alt="<?= $item['title']; ?>" data-url="<?= $item['video']; ?>">
								<a href="javascript:openVideoByHash('<?= $item['hash']; ?>')" class="video-player-button"></a>
							</figure>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<input type="hidden" id="product_id" value="<?php echo $product_id; ?>" />

	<div id="video-modal" class="background video-modal">
		<div class="video-card-body">
			<button id="close-button" class="close-button" onclick="hideModal()"><?php Icon_Class::polen_icon_close(); ?></button>
			<div id="video-box"></div>
		</div>
	</div>
<?php
}

function va_cart_form( $product, $coupon = "")
{
?>
	<div class="row mb-3">
		<div class="col-12">
			<div class="row">
				<div class="col-12 d-flex">
					<div class="mr-3 va-check-o">
						<?php Icon_Class::polen_icon_check_o(); ?>
					</div>
					<div>
						<p><strong>Seu código foi validado!</strong><br />Para continuar, preencha os dados abaixo:</p>
					</div>
				</div>
			</div>
			<form id="va-cart-form">
				<input type="hidden" name="action" value="create_orders_video_autograph" />
				<input type="hidden" name="coupon" value="<?php echo $coupon; ?>" />
				<input type="hidden" name="product" value="<?php echo $product->get_sku(); ?>" />
				<p>
					<label for="" class="lg">Nome</label>
					<input type="text" name="name" class="form-control form-control-lg" placeholder="Para quem é esse vídeo-autógrafo?" required />
				</p>
				<p>
					<label for="" class="lg">Cidade</label>
					<input type="text" name="city" class="form-control form-control-lg" placeholder="Cidade da pessoa homenageada" required />
				</p>
				<p>
					<label for="" class="lg">E-mail</label>
					<input type="email" name="email" class="form-control form-control-lg" placeholder="E-mail para acompanhar o pedido" required />
				</p>
				<!-- <p class="mb-2">
					<label>
						<input type="checkbox" class="form-control form-control-lg" name="accept_news" />
						<span class="woocommerce-terms-and-conditions-checkbox-text ml-2">Quero ficar por dentro de novidades como essa.</span>
					</label>
				</p> -->
				<p>
					<label>
						<input type="checkbox" class="form-control form-control-lg" name="terms" id="terms" required />
						<span class="woocommerce-terms-and-conditions-checkbox-text ml-2" style="line-height: 24px;">Li e concordo com o(s) <a href="http://polen.me/politica-de-privacidade/" class="woocommerce-terms-and-conditions-link" target="_blank">termos e condições</a></span>
					</label>
				</p>
				<p>
					<input type="hidden" name="security" value="<?= wp_create_nonce(Promotional_Event_Admin::NONCE_ACTION); ?>" />
					<input type="submit" class="btn btn-primary btn-lg btn-block" value="Pedir meu vídeo-autógrafo" />
				</p>
			</form>
		</div>
	</div>
	<script>
		const formId = '#va-cart-form';
		const form = document.querySelector(formId);
		form.addEventListener("submit", function(e) {
			e.preventDefault();
			polAjaxForm(formId, function(res) {
				polSpinner();
				blockUnblockInputs(formId, true);
				window.location.href = res.url;
			}, function(e) {
				polMessages.error(e.Error);
			});
		});
	</script>
<?php
}

function va_coupon( $product )
{
?>
	<div class="row mb-3">
		<div class="col-12">
			<h1 class="title mb-3">Insira o cupom</h1>
			<form id="va-check-code">
				<input type="hidden" name="action" value="check_coupon" />
				<input type="hidden" name="product" value="<?= $product->get_sku(); ?>" />
				<?php wp_nonce_field( 'check-coupon', 'security', true ); ?>
				<input type="text" name="coupon" class="form-control form-control-lg mb-2" placeholder="Insira o código que recebeu por e-mail" required />
				<input type="submit" class="btn btn-primary btn-lg btn-block" value="Checar" />
			</form>
		</div>
	</div>
	<script>
		const formId = '#va-check-code';
		const form = document.querySelector(formId);
		form.addEventListener("submit", function(e) {
			e.preventDefault();
			polAjaxForm(formId, function(res) {
				polSpinner();
				blockUnblockInputs(formId, true);
				window.location.href = res.url;
			}, function(e) {
				polMessages.error(e.Error);
			}, false);
		});
	</script>
<?php
}
