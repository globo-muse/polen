<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?> 
<div style="height: 451px;">	
	<div style="width:300px; float:left">					
			<img src="<?php echo esc_html( $qr_image ); ?>" width="250px" />
			<input style="width:250px;margin-top:5px" id="tuna-qr-code" value="<?php echo esc_html( $qr_code ); ?>"></input> 
			<button style="width:250px;margin-top:5px"  onclick="copy_qr_code()" onclick="true"><?php echo esc_html( 'Pix Copia e Cola' ); ?></button>
			<script>
				function copy_qr_code() {
					var copyText = document.getElementById("tuna-qr-code");
					copyText.select();
					copyText.setSelectionRange(0, 99999)
					document.execCommand("copy");
				}
			</script>		   
	</div>
	<div style="float:left">			
		<p>1 - <?php echo esc_html( 'Abra o app do seu banco ou instituição financeira e entre no ambiente Pix'); ?></p>
		<p>2 - <?php echo esc_html( 'Escolha a opção pagar com qr code e escaneie ou copie e cole o código'); ?></p>
		<p>3 - <?php echo esc_html('Confirme as informações e finalize o pagamento' ); ?></p>		
		<p style="color:#999999;border-top:1px solid #CCCCCC"><?php echo esc_html('verificando pagamento...' ); ?></p>
	</div>
			
 </div>
 <div style="float:left;width:100%"><br/></div>
