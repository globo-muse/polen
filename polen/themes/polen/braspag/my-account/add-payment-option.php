<div class="row woocommerce-Payment-Options payment-options">
	<!-- Dados do cartão -->
	<div class="col-md-12">
		<h1 class="mb-4">Adicionar Cartão</h1>
		<form id="form-add-card" action="/">
			<div id="form-add-card" class="row">
				<div class="col-12 mb-3">
					<label><?php echo __('Número do cartão', 'cubo9'); ?></label>
					<input type="text" placeholder="<?php echo __('Número do cartão', 'cubo9'); ?>" class="form-control form-control-lg" name="braspag_creditcardNumber" id="braspag_creditcardNumber" aria-describedby="<?php echo __('Número do cartão de crédito', 'cubo9'); ?>" required />
				</div>
				<div class="col-12 mb-3">
					<label><?php echo __('Nome impresso no cartão de crédito', 'cubo9'); ?></label>
					<input type="text" placeholder="<?php echo __('Nome impresso no cartão de crédito', 'cubo9'); ?>" class="form-control form-control-lg" name="braspag_creditcardName" id="braspag_creditcardName" aria-describedby="<?php echo __('Nome impresso no cartão de crédito', 'cubo9'); ?>" maxlength="50" required />
				</div>
				<div class="col-12 col-md-6 mb-3">
					<label><?php echo __('Validade', 'cubo9'); ?></label>
					<input type="text" placeholder="<?php echo __('Validade', 'cubo9'); ?>" class="form-control form-control-lg" name="braspag_creditcardValidity" id="braspag_creditcardValidity" aria-describedby="<?php echo __('Validade', 'cubo9'); ?>" required />
				</div>
				<div class="col-12 col-md-6 mb-4">
					<label><?php echo __('Código de segurança', 'cubo9'); ?></label>
					<input type="text" placeholder="<?php echo __('Código de segurança', 'cubo9'); ?>" class="form-control form-control-lg" name="braspag_creditcardCvv" id="braspag_creditcardCvv" aria-describedby="<?php echo __('Código de segurança', 'cubo9'); ?>" required />
				</div>
				<div class="col-12">
					<button type="submit" id="braspag-save-my-card" class="woocommerce-Button btn btn-primary btn-lg btn-block">
						Adicionar cartão
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
