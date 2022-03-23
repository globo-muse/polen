<?php
global $tribute;

// var_dump($tribute);
?>

<?php get_header('tributes'); ?>

<div class="container">
	<div class="row">
		<div class="col-12 mt-4">
			<?php polen_get_video_player_html($tribute); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-12 col-md-6 m-md-auto pt-5">
			<a href="<?php echo tribute_get_url_base_url(); ?>" class="btn btn-primary btn-lg btn-block">Criar seu Colab</a>
		</div>
	</div>
</div>

<?php get_footer('tributes'); ?>
