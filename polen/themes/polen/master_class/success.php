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
get_header();
?>
<main id="primary" class="site-main">
	<div class="mc-landing-banner">
		<figure class="mc-logo">
			<img class="image" src="<?php echo TEMPLATE_URI; ?>/assets/img/masterclass/ronnievon-logo.png" alt="Logo Beabá do Vinho" />
		</figure>
		<div class="row mt-3">
			<div class="col-12 col-md-6 m-md-auto">
				<h1 class="title">Falta pouco para confirmar a sua pré-inscrição e garantir o seu desconto.</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-12 col-md-6 m-md-auto">
				<div class="row">
					<div class="col-12 my-4">
						<h2 class="subtitle">A Masterclass Beabá do Vinho com Ronnie Von vai acontecer no dia 16 de Setembro. Siga as instruções abaixo para não ficar de fora:</h2>
					</div>
					<div class="col-12">
						<p class="subtitle"><strong>1) Verifique o seu email.</strong> Se não recebeu nenhum email na caixa de entrada, olhe sua caixa de SPAM ou promoções.</p>
					</div>
					<div class="col-12">
						<p class="subtitle"><strong>2) Entre no grupo do WhatsApp.</strong> Nós vamos enviar o link com desconto exclusivo SOMENTE por lá.</p>
					</div>
					<div class="col-12">
						<p class="subtitle"><strong>3) Responda a pesquisa.</strong> Queremos te conhecer melhor para que o curso seja ainda mais incrível.</p>
					</div>
				</div>
			</div>

		</div>
		<div class="row mt-4">
			<div class="col-12 col-md-6 m-md-auto">
				<a href="https://joinzap.app/ronnievon" class="btn btn-lg btn-block gradient mb-3" target="_blank">Entrar no grupo de Whatsapp</a>
				<a href="https://surveys.hotjar.com/bd41fb29-96c7-4e1a-af06-9b4568873012" class="btn btn-lg btn-block gradient" target="_blank">Responder pesquisa</a>
			</div>
		</div>
	</div>
	<div class="row my-4 mc-content">
		<div class="col-12">
			<h3 class="title text-center mb-4">Realização</h3>
		</div>
		<div class="col-12 d-flex justify-content-around">
			<img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/polen-masterclass.png'; ?>" alt="Polen Masterclass"></img>
			<img class="img-responsive" src="<?php echo TEMPLATE_URI . '/assets/img/masterclass/todo-vino.png'; ?>" alt="Todo Vino"></img>
		</div>
	</div>

</main><!-- #main -->

<?php
get_footer();
