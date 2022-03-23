<?php
include_once("components.php");
?>

<tbody>
	<table style="margin: auto; width: 98%; max-width: 720px">
		<tr>
			<td style="height: 120px">
				<h1 style="text-align: center">
					Envie sua homenagem
				</h1>
			</td>
		</tr>
		<tr>
			<td style="height: 200px; text-align: center">
				<img width="185" height="185" src="<?php Tributes_Email_Class::get_assets_url(); ?>/carousel-video.png" alt="" />
			</td>
		</tr>
		<?php Tributes_Email_Class::get_margin("40px"); ?>
		<tr>
			<td id="content">
				Oi Fulano,
				<p>
					Sobhan o convidou a participar da criação de uma
					vídeo montagem para Diego.<br />
					<strong>Seu prazo para envio é 30 de junho de
						2021.</strong>
				</p>

				<strong>Mensagem de boas-vindas e instruções de Diego
					Jovanholi:</strong><br />
				Estamos criando uma montagem de vídeo (ou "Colab")
				para diego. Você levará apenas um minuto para filmar
				e enviar seu vídeo. Deve ser um presente
				inesquecível que compartilha nosso amor e apreço
				coletivos. Não seja o último a enviar! Para saber
				mais e enviar seu vídeo, clique no botão abaixo.
				Leva apenas um minuto e você pode fazer isso de
				qualquer dispositivo.
			</td>
		</tr>
		<?php Tributes_Email_Class::get_margin("60px"); ?>
		<tr>
			<td style="text-align: center">
				<?php Tributes_Email_Class::get_button_link("Enviar Colab", "#urlenviartributo"); ?>
			</td>
		</tr>
		<?php Tributes_Email_Class::get_margin("80px"); ?>
	</table>
</tbody>
