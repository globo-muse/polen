<?php

/**
 * Criar o card com as estrelas da nota do Review especifico
 * @param int nota
 * @return HTML
 */
function polen_get_stars($quant)
{
	for ($i = 1; $i <= 5; $i++) {
		Icon_Class::polen_icon_star($i <= $quant);
	}
?>
	<span class="skill-value"><?php echo $quant; ?></span>
<?php
}


/**
 * Criar a tela com a lista dos comentários (Order_Review)
 * @param array [ ['id'=>xx,'rate'=>x,'name'=>'...','data'=>'...','comment'=>'...'] ]
 * @return HTML
 */
function polen_comment_card($args = array())
{
	if (empty($args)) {
		return;
	}
?>
	<div class="box-round mb-3">
		<div class="row p-4 comment-box">
			<div class="col-md-12 box-stars">
				<?php polen_get_stars($args['rate']); ?>
			</div>
			<div class="col-md-12 mt-3">
				<p>Avaliação por <?php echo $args["name"]; ?> - <?php echo $args["date"]; ?></p>
			</div>
			<div class="col-md-12 mt-2">
				<p class="alt">
					<input type="checkbox" name="expanded-<?php echo $args['id']; ?>" id="expanded-<?php echo $args['id']; ?>">
					<span class="truncate truncate-4"><?php echo $args['comment']; ?></span>
					<label for="expanded-<?php echo $args['id']; ?>">Exibir mais</label>
				</p>
			</div>
		</div>
	</div>
<?php
}


/**
 * Retorna o HTML com o form para a criação de uma Order_Review
 * @param int $order_id
 * @return HTML
 */
function polen_create_review($order_id)
{
	wp_enqueue_script('comment-scripts');
?>
	<div id="comment-box" class="box-round mb-3">
		<form action="./" id="form-comment">
			<div class="row p-4 comment-box">
				<pol-stars v-bind:rate="rate" v-bind:handle="changeRate"></pol-stars>
				<input type="hidden" name="action" value="create_order_review">
				<input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
				<div class="col-md-12 mt-3">
					<h4>Comentário</h4>
					<textarea name="comment" id="comment" rows="2" class="form-control" placeholder="Escreva sua avaliação" v-model="comment"></textarea>
					<button id="send-comment" class="btn btn-primary btn-lg btn-block mt-3" v-on:click="sendComment">Avaliar</button>
				</div>
			</div>
		</form>
	</div>
<?php
}

/**
 * Cria o card onde apresentar a quantidade de avaliacoes e a media das avaliações
 * @param WP_Post $post
 * @param stdClass Polen_Update_Fields
 */
function polen_card_talent_reviews_order(\WP_Post $post, $Talent_Fields)
{
  global $Polen_Plugin_Settings;
  $order_expires = $Polen_Plugin_Settings['order_expires'];
?>
	<div class="col-md-12 mt-3">
		<div class="row">
			<div class="col-12 col-md-6 m-md-auto">
				<div class="row">
					<div class="col-6 col-md-6 text-center text-md-center">
						<span class="skill-title">Prazo de entrega</span>
						<p class="p mb-0 mt-2">
							<span class="skill-value">
								<?php Icon_Class::polen_icon_calendar(); ?>
								<?php
									$date = date("d/m/Y");
									echo date( "d/m/y", strtotime('+'.$order_expires.' days') );
								?>
							</span>
						</p>
					</div>
					<div class="col-6 col-md-6 text-center text-md-center">
						<?php
						$total_reviews = get_post_meta($post->ID, "total_review", true);
						if (empty($total_reviews)) {
							$total_reviews = "0";
						}
						?>
						<a href="<?= polen_get_url_review_page(); ?>" class="no-underline">
							<span class="skill-title">Avaliações (<?php echo  $total_reviews; ?>)</span>
							<p class="p mb-0 mt-2 skill-value">
								<?php Icon_Class::polen_icon_star(true); ?>
								<?php
								$total_review = intval(get_post_meta($post->ID, "total_review", true));
								$sum_rate_reviews = intval(get_post_meta($post->ID, "sum_rate", true));
								$avg_rate = $total_review > 0 ? ($sum_rate_reviews / $total_review) : 0;
								?>
								<?php echo number_format($avg_rate, 1); ?>
							</p>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}
