<?php

use Polen\Tributes\Tributes_Invites_Model;

global $my_tributes;
get_header('tributes');
?>

<main id="my-tributes">
	<div class="container py-3 tribute-container tribute-app">
		<div class="row mb-5">
			<div class="col-md-12">
				<h1 class="title text-center">Seus tributos</h1>
			</div>
		</div>
		<?php foreach ($my_tributes as $tribute) :
            $result_sucess = Tributes_Invites_Model::get_videos_sent_and_not( $tribute->ID );
            $sent = $result_sucess->video_sent;
            $not_sent = $result_sucess->video_not_sent;

            $total_success = ( $sent / ( $sent + $not_sent ) ) * 100;
            if( ( $sent + $not_sent ) == 0 ) {//divisão por zero
                $total_success = 0;
            }
        ?>
			<div class="row mb-4 pb-3 border-bottom">
				<div class="col-md-3">
					<p>Pra quem é o Colab?</p>
					<p><strong><?php echo $tribute->name_honored; ?></strong></p>
				</div>
				<div class="col-md-3">
					<p>Data de Vencimento</p>
					<p><strong><?php echo date('d/m/Y', strtotime( $tribute->deadline ) ); ?></strong></p>
				</div>
				<div class="col-md-3">
					<p>% de sucesso</p>
					<p><strong><?php echo number_format( $total_success ); ?>%</strong></p>
				</div>
				<div class="col-md-3">
					<a href="<?php echo tribute_get_url_tribute_detail( $tribute->hash ); ?>" class="btn btn-primary btn-lg btn-block">Visualizar</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</main>

<?php get_footer('tributes'); ?>
