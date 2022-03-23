<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

?>
<div class="wrapper">
<p>
Olá,<br />
Obrigada por entrar em nossa lista de espera para experimentar um novo jeito de se relacionar com seus artistas favoritos. Na Polen, você vai poder encomendar vídeos de artistas com mensagens gravadas por eles do jeito que você quiser.
Para você ir preparando suas primeiras encomendas, aqui vão algumas boas sugestões do que pedir aos talentos na Polen:
<ul>
<li>Peça um vídeo com um alô e um abraço, simples e direto.</li>
<li>Que tal dar um vídeo de aniversário? O artista dá parabéns a quem você quiser.</li>
<li>Ou os parabéns podem ser por algo diferente: uma conquista, o nascimento de um filho, uma promoção no trabalho.</li>
<li>Se você quiser contar algo importante a alguém de uma forma especial, que tal um vídeo da Polen?</li>
<li>Ou simplesmente peça um conselho ao seu artista favorito, respostas às suas perguntas, as curiosidades que quiser.</li>
</ul>
Gostou? Muito, né? E claro, quando o site estiver no ar eu volto aqui para te contar em primeira mão. Até já!<br />
Polen
</p>
</div>
<?php do_action( 'woocommerce_email_footer', $email ); ?>