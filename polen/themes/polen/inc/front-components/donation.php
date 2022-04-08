<?php


function polen_donate_badge(string $text = "", bool $inside_card = true, bool $criesp = false, bool $yellow = false)
{
	if ($text === "") {
		return;
	}
?>
	<span class="donate-badge<?php echo $inside_card ? "" : " alt"; ?><?php echo $yellow ? " yellow" : ""; ?>">
		<?php $criesp ? Icon_Class::polen_icon_criesp() : Icon_Class::polen_icon_donate(); ?>
		<strong><?php echo $text; ?></strong>
	</span>
<?php
}

function polen_front_get_donation_box(string $img = "", string $text = "")
{
	if ($text === "") {
		return;
	}
?>
	<section class="row donation-box mt-4 mb-4">
		<div class="col-md-12">
			<header class="row mb-3">
				<div class="col">
					<h2>Sobre a doação</h2>
				</div>
			</header>
		</div>
		<div class="col-md-12">
			<div class="box-round py-4 px-4">
				<div class="row">
					<div class="col-2 col-md-1 text-center">
						<?php Icon_Class::polen_icon_donate(); ?>
					</div>
					<div class="col-10 col-md-11">
						<figure class="image">
							<img src="<?php echo $img; ?>" alt="Logo da empresa de doação">
						</figure>
						<p><?php echo $text; ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php
}
