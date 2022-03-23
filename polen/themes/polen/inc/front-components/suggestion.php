<?php


function polen_front_get_suggestion_box()
{
?>
	<div class="row">
		<div class="col-12">
			<div class="box-round p-4">
				<div class="row">
					<div class="col-3 col-md-2 col-lg-1">
						<img src="<?php echo TEMPLATE_URI; ?>/assets/img/logo-round.svg" alt="Logo redonda">
					</div>
					<div class="col-9 col-md-10 col-lg-11">
						<p><strong>E aí, ficou com vontade de ver seu artista favorito no Polen?</strong></p>
						<a href="#pedirartista" class="btn btn-outline-light btn-md">Pedir artista</a>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

function polen_front_get_suggestion_form()
{
	wp_enqueue_script('suggestion-scripts');
?>
	<div class="row">
		<div class="col-12 col-md-12 mb-3">
			<h1>Pedir artista</h1>
		</div>
		<div class="col-12 col-md-12">
			<form id="talent-suggestion">
				<input type="hidden" name="action" value="invite_talent">
				<input type="hidden" name="security" value=<?php echo wp_create_nonce('invite_talent'); ?>>
				<p class="mb-4">
					<input type="text" id="fan_name" name="fan_name" placeholder="Seu nome" class="form-control form-control-lg" required />
				</p>
				<p class="mb-4">
					<input type="email" id="fan_email" name="fan_email" placeholder="Seu e-mail" class="form-control form-control-lg" required />
				</p>
				<p class="mb-4">
					<input type="text" id="talent_name" name="talent_name" placeholder="Nome do seu ídolo" class="form-control form-control-lg" required />
				</p>
				<p class="mb-4">
					<input type="text" id="talent_instagram" name="talent_instagram" placeholder="Instagram do seu ídolo" class="form-control form-control-lg" />
				</p>
				<p class="mb-4">
					<input type="submit" value="Enviar" class="btn btn-primary btn-lg btn-block" />
				</p>
			</form>
		</div>
	</div>
<?php
}
