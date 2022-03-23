<?php
/**
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p class="img_wrap">
	<img src="<?php echo get_template_directory_uri() . "/assets/img/email/boas-vindas.png"; ?>" alt="Menina segurando celular">
</p>

<?php /* translators: %s: Customer username */ ?>
<p>Olá</p>
<p>Sua conta foi criada com sucesso!<br>
Por isso, viemos te desejar boas-vindas à Polen.</p>
<p>Agora você pode visualizar seus dados e acompanhar o status do seu pedido fazendo login com as credenciais abaixo.</p>
<p>Recomendamos que você altere a sua senha após o primeiro login para se manter protegido.</p>

<p>Email: <?= $user_login; ?><br>
Senha Provisória: <?= $password_generated; ?>
</p>

<p class="btn_wrap">
	<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="btn" target="_blank">Acessar minha conta</a>
</p>
<?php

do_action( 'woocommerce_email_footer', $email );