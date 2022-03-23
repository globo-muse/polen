<?php

use Polen\Tributes\Tributes_Invites_Model;
use Polen\Tributes\Tributes_Model;
use Polen\Tributes\Tributes_Rewrite_Rules;

$tribute_hash = get_query_var( Tributes_Rewrite_Rules::TRIBUTES_QUERY_VAR_TRIBUTES_HASH );
$invite_hash  = get_query_var( Tributes_Rewrite_Rules::TRIBUTES_QUERY_VAR_TRIBUTES_INVITE_HASH );
$invite = Tributes_Invites_Model::get_by_hash( $invite_hash );
$tribute = Tributes_Model::get_by_hash( $tribute_hash );
if( empty( $invite ) ) {
	die('error to find invite');
}
// echo "Send Video: {$tributes_hash} {$invite_hash}";
?>


<?php get_header('tributes'); ?>

<main id="invite-friends">
	<div class="container py-3 tribute-container tribute-app">
		<div class="row">
			<div class="mb-4 col-md-12">
				<h1 class="title text-center">Enviar seu vídeo</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-12 col-md-10 m-md-auto col-lg-9">
				<button class="btn btn-outline-light btn-lg btn-block mt-4" data-toggle="modal" data-target="#OrderActions">Instruções</button>
				<article class="box-round mt-3 px-4 py-4">
					<div class="row">
						<div class="col-12">
							<div class="text-center box-video">
								<div id="content-info" class="content-info show">
									<figure class="image wait show">
										<img src="<?php echo TEMPLATE_URI ?>/tributes/assets/img/upload-ico.svg" alt="Gravar vídeo agora" />
										<div class="row mt-4">
											<div class="col-9 m-auto">
												<p id="info" class="info">Grave um vídeo na vertical em seu celular de <b>até 15 segundos</b> com seu aplicativo de gravação preferido.</p>
											</div>
										</div>
									</figure>
									<figure class="image complete">
										<img src="<?php echo TEMPLATE_URI ?>/tributes/assets/img/done.svg" alt="Gravar vídeo agora" />
										<p id="video-message" class="py-2">Vídeo carregado com sucesso</p>
									</figure>
								</div>
								<div id="content-upload" class="mt-4 content-upload">
									<div class="spinner-border text-secondary" role="status">
										<span class="sr-only">Loading...</span>
									</div>
									<p class="my-4 progress-text"><strong id="progress-value">Enviando vídeo 0%</strong></p>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<form id="form-video-upload" method="post" enctype="multipart/form-data">
								<div class="form-group text-center">
									<button id="video-rec" data-toggle="modal" data-target="#OrderActions" class="btn btn-primary btn-lg btn-block show">Gravar vídeo</button>
									<div id="video-file-name" class="text-truncate ml-2"></div>
									<input type="file" class="form-control-file" id="file-video" name="file_data" accept="video/*">
								</div>
								<input type="hidden" id="tribute_hash" name="tribute_hash" value="<?= $tribute_hash; ?>">
								<input type="hidden" id="tribute_invite_hash" name="invite_hash" value="<?= $invite->hash; ?>">
								<input type="hidden" id="tribute_invite_id"   name="invite_id" value="<?= $invite->ID; ?>">
								<button type="submit" id="video-send" class="send-video btn btn-primary btn-lg btn-block">Enviar</button>
								<button id="video-rec-again" class="btn btn-outline-light btn-lg btn-block mt-3 video-rec">Não gostei, gravar outro video</button>
							</form>
						</div>
					</div>
				</article>
			</div>
		</div>
	</div>
</main>

<!-- Modal -->
<div class="modal fade" id="OrderActions" tabindex="-1" role="dialog" aria-labelledby="OrderActionsTitle" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="row modal-body">
				<!-- Início -->
				<div class="background col-12 talent-order-modal">
					<button type="button" class="modal-close" data-dismiss="modal" aria-label="Fechar">
						<i class="icon icon-close"></i>
					</button>
					<div class="body">
						<div class="row d-flex align-items-center">
							<div class="col-12">
								<h1 class="title text-center">Envie sua homenagem para <?= $tribute->name_honored; ?>.</h1>
							</div>
							<div class="col-12 mt-3">
								<p class="p text-center">Grave um vídeo de <b>até 15 segundos</b> para participar desse Colab.</p>
							</div>
							<div class="col-12 mt-3">
								<h2 class="subtitle text-center">Veja as instruções:</h2>
							</div>
							<div class="col-12 mt-3">
								<p class="p">Para</p>
								<span class="value small"><?= $tribute->name_honored; ?></span>
							</div>
						</div>
						<div class="row mt-4 py-4 border-bottom border-top">
							<div class="col">
								<p class="p">Ocasião</p>
								<span class="value small"><?= $tribute->occasion; ?></span>
							</div>
						</div>
						<!-- <div class="row mt-4">
							<div class="col">
								<p class="p">e-mail de contato</p>
								<span class="value small">asd</span>
							</div>
						</div> -->
						<div class="row mt-4">
							<div class="col">
								<p class="p">Instruções</p>
								<p class="text"><?= $tribute->welcome_message; ?></p>
							</div>
						</div>
						<div class="row my-4">
							<div class="col">
								<button class="btn btn-primary btn-lg btn-block video-rec show" data-dismiss="modal" aria-label="Fechar">Gravar vídeo</button>
							</div>
						</div>
					</div>
				</div>
				<!-- Fim -->
			</div>
		</div>
	</div>
</div>

<?php get_footer('tributes'); ?>
