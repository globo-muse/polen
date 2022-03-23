<?php
global $Polen_Plugin_Settings;
$phone_number = $Polen_Plugin_Settings['polen_whastsapp_phone'];
$mensagem = $Polen_Plugin_Settings['polen_whastsapp_text'];
$policies = $Polen_Plugin_Settings['polen_cookies_policities_text'];
?>

<?php if (!empty($phone_number) && event_promotional_is_app()) : ?>
	<a href="https://wa.me/<?php echo $phone_number ?>?text=<?= urlencode($mensagem); ?>" class="whatsapp_link" target="_blank"><?php Icon_Class::polen_icon_social("whatsapp") ?></a>
<?php endif; ?>

<div id="policies-box" class="policies-box d-none">
	<div class="container">
		<div class="row">
			<div class="col-12">
				<div class="box-round shadow p-4">
					<div>
						<?php echo $policies; ?>
					</div>
					<div class="mt-2 text-md-right">
						<button onclick="polAcceptCookies()" class="btn btn-primary btn-sm">Aceitar todos</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
