<?php

use Polen\Tributes\Tributes_Invites_Model;
use Polen\Tributes\Tributes_Model;

global $tribute;

$invites = Tributes_Invites_Model::get_all_by_tribute_id($tribute->ID);
$hash = $tribute->hash;

$result_sucess = Tributes_Invites_Model::get_videos_sent_and_not($tribute->ID);
$sent = $result_sucess->video_sent;
$not_sent = $result_sucess->video_not_sent;

$total_success = ($sent / ($sent + $not_sent)) * 100;
if (($sent + $not_sent) == 0) { //divisão por zero
	$total_success = 0;
}

function getIcon($done)
{
	if ($done) {
		Icon_Class::polen_icon_check_o_alt();
	} else {
		Icon_Class::polen_icon_clock();
	}
}

$is_complete = $total_success === 100;

?>

<?php get_header('tributes'); ?>

<main id="tribute-details">
	<div class="container py-3 tribute-container tribute-app">
		<div class="row mb-4 py-3 border-bottom">
			<div class="col-md-3">
				<p>Pra quem é o Colab?</p>
				<p><strong><?php echo $tribute->name_honored; ?></strong></p>
			</div>
			<div class="col-md-3">
				<p>Data de Vencimento</p>
				<p><strong><?php echo date('d/m/Y', strtotime($tribute->deadline)); ?></strong></p>
			</div>
			<div class="col-md-3">
				<p>% de sucesso</p>
				<p><strong><?php echo number_format($total_success); ?>%</strong></p>
			</div>
			<div class="col-md-3">
				<p>Status</p>
				<p><strong><?php echo tributes_get_tribute_status($tribute); ?></strong></p>
			</div>
		</div>
		<?php if ($tribute->completed == '1' && !empty($tribute->vimeo_id)) : ?>
			<div class="row">
				<div class="col-md-12">
					<p>Link para o Colab</p>
					<p><strong><a href="<?php echo tribute_get_url_final_video($tribute->slug); ?>"><?php echo tribute_get_url_final_video($tribute->slug); ?></a></strong></p>
				</div>
			</div>
		<?php endif; ?>
		<div class="row">
			<div class="col-md-12 mt-4">
				<h2 class="subtitle subtitle-tribute">Pessoas</h2>
			</div>
			<div class="col-md-12">
				<div class="card-invite invites-list">
					<?php foreach ($invites as $invite) : ?>
						<div class="row mb-5 mb-md-4">
							<div class="col-md-5">
								<input type="text" value="<?php echo $invite->name_inviter; ?>" class="form-control form-control-lg" disabled />
								<?php if ($invite->video_sent) : ?>
									<span class="status mt-2"><?php getIcon(true); ?>Vídeo já foi enviado.</span>
								<?php elseif ($invite->email_opened) : ?>
									<span class="status mt-1 not-view"><?php getIcon(false); ?>Usuário não finalizou o vídeo</span>
								<?php else : ?>
									<span class="status mt-1 not-open"><?php getIcon(false); ?>Usuário não abriu o e-mail</span>
								<?php endif; ?>
							</div>
							<div class="col-md-5 mt-3 mt-md-0">
								<input type="email" value="<?php echo $invite->email_inviter; ?>" class="form-control form-control-lg" disabled />
							</div>
							<?php if (!$invite->video_sent) : $is_complete = false; ?>
								<div class="col-md-2 d-flex flex-column align-items-end justify-content-center align-items-md-start" style="height: 50px;">
									<a href="#" data_invite="<?php echo $invite->hash; ?>" data_tribute="<?php echo $tribute->hash; ?>" class="tribute_delete_invite"><?php Icon_Class::polen_icon_trash(); ?></a>
									<form action="./" id="form-<?php echo $invite->ID; ?>" method="POST" class="resend-email">
										<input type="hidden" name="action" value="tribute_resend_email" />
										<input type="hidden" name="invite_hash" value="<?php echo $invite->hash; ?>" />
										<input type="submit" class="d-inline-block send-button" value="Reenviar e-mail" />
									</form>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<?php if ($is_complete) : ?>
						<div class="row">
							<div class="col-12">
								<p>Todos os vídeos já foram enviados. Você receberá um e-mail quando seu Colab estiver pronto</p>
							</div>
						</div>
					<?php endif; ?>
					<input type="hidden" id="wpnonce" name="wpnonce" value="<?= wp_create_nonce('tributes_delete_invite'); ?>" />
				</div>
			</div>
		</div>
		<?php if (tributes_get_tribute_status($tribute) == 'Aguardando videos') : ?>
			<div class="row">
				<div class="col-12 mt-4 col-md-5 ml-md-auto mr-md-auto">
					<a href="<?php echo tribute_get_url_invites($tribute->hash); ?>" class="btn btn-primary btn-lg btn-block">Convite mais amigos</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php get_footer('tributes'); ?>
