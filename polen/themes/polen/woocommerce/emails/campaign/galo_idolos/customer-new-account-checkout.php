<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

wc_get_template( 'emails/campaign/galo_idolos/email-header.php', array( 'email_heading' => $email_heading ) );
?>

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
Senha Provisória: <?= $user_pass; ?>
</p>

<p class="btn_wrap">
	<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="btn" target="_blank">Acessar minha conta</a>
</p>
<?php

wc_get_template( 'emails/campaign/galo_idolos/email-footer.php' );