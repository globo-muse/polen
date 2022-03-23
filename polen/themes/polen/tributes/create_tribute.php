<?php

use Polen\Tributes\Tributes_Occasions_Model;

get_header('tributes');
$occasions = Tributes_Occasions_Model::get_all();
?>
<main id="create-tribute">
	<div class="container py-3 tribute-container tribute-app">
		<div class="row">
			<div class="col-md-12">
				<h1 class="title text-center">Comece sua homenagem</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 mt-5">
				<form id="form-create-tribute" action="./" method="POST">
					<input type="hidden" name="action" value="create_tribute" />
					<input type="hidden" name="security" value="<?= wp_create_nonce('tributes_add'); ?>">
					<p class="mb-4">
						<label for="name_honored">Para quem é o Colab?</label>
						<input type="text" name="name_honored" id="name_honored" placeholder="Ex: Diego, Rodolfo" class="form-control form-control-lg" required>
					</p>
					<p class="mb-4">
						<label for="occasion">Qual é a ocasião?</label>
						<select name="occasion" id="occasion" class="form-control form-control-lg custom-select">
							<option value="">Selecione a ocasião</option>
							<?php foreach ($occasions as $occasion) : ?>
								<option value="<?= $occasion->occasion; ?>"><?= $occasion->occasion; ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<div class="mb-4">
						<label for="slug">Qual a URL do seu Colab?</label>
						<?php //TODO usar função pra pegar a URL
						?>
						<p class="d-flex mb-0 slug-wrap">https://polen.me/colab/v/<input type="text" name="slug" id="slug" placeholder="nome-do-Colab" class="input-tribute-url" required /></p>
						<small id="slug-message" class="slug-message"></small>
					</div>
					<div id="deadline-wrapp" class="mb-4">
						<label for="deadline">Qual o prazo?</label>
						<br />Recomendamos vários dias antes da entrega do Colab para que você tenha tempo de editar seu vídeo.
						<input type="hidden" name="deadline" id="deadline" maxlength="10" v-model="date" />
						<div class="row mt-2">
							<div class="col-12 d-flex justify-content-between col-md-8">
								<select id="date_day" class="form-control custom-select custom-select-sm" v-model="day" v-on:change="updateDate">
									<option value="">Dia</option>
									<option v-for="d in days">{{d}}</option>
								</select>
								<select id="date_month" class="form-control custom-select custom-select-sm" v-model="month" v-on:change="updateDate">
									<option value="">Mês</option>
									<option v-for="m in months" v-bind:value="m.index">{{m.name}}</option>
								</select>
								<select id="date_year" class="form-control custom-select custom-select-sm" v-model="year" v-on:change="updateDate">
									<option value="">Ano</option>
									<option v-for="y in years">{{y}}</option>
								</select>
							</div>
						</div>
					</div>
					<p class="mb-4">
						<label for="creator_name">Qual o seu nome?</label>
						<input type="text" name="creator_name" id="creator_name" placeholder="Seu nome" class="form-control form-control-lg" required />
					</p>
					<p class="mb-4">
						<label for="creator_email">Qual o seu e-mail?</label>
						<input type="email" name="creator_email" id="creator_email" placeholder="Entre com seu e-mail" autocapitalize="off" class="form-control form-control-lg" required />
					</p>
					<p class="mb-4">
						<label for="welcome_message">Instruções</label>
						<textarea name="welcome_message" id="welcome_message" cols="30" rows="5" class="form-control form-control-lg" required></textarea>
					</p>
					<div class="row">
						<div class="col-md-4 m-md-auto">
							<input type="submit" value="Avançar" class="btn btn-primary btn-lg btn-block" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</main>

<?php get_footer('tributes'); ?>
