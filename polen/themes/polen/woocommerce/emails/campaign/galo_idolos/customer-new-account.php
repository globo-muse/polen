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

wc_get_template( 'emails/campaign/galo_idolos/email-header.php', array( 'email_heading' => "Boas-vindas ao Galo Ídolos" ) );

$user_obj = get_user_by( 'login', $user_login);
?>

<p class="img_wrap">
	<img src="<?php echo get_template_directory_uri() . "/assets/img/email/boas-vindas.png"; ?>" alt="Menina segurando celular">
</p>

<?php /* translators: %s: Customer username */ ?>
<p><?php printf( esc_html__( 'Olá %s,', 'woocommerce' ), esc_html( $user_obj->display_name ) ); ?></p>
<?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>
<p><?php printf( esc_html__( 'Thanks for creating an account on %1$s. Your username is %2$s. You can access your account area to view orders, change your password, and more at: %3$s', 'woocommerce' ), 'Galo Idolos', '<strong>' . esc_html( $user_login ) . '</strong>', make_clickable( esc_url( 'https://galoidolos.com.br/' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
<?php if ( $password_generated ) : ?>
	<?php /* translators: %s: Auto generated password */ ?>
	<p><?php printf( esc_html__( 'Sua senha foi gerada automaticamente: %s', 'woocommerce' ), '<strong>' . esc_html( $user_pass ) . '</strong>' ); ?></p>
<?php endif; ?>
<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

?>
	<p class="btn_wrap">
  <a href="http://galoidolos.com.br" class="btn" target="_blank" style="background:#FFCD00; color: #000;">Ir para o Galo Ídolos</a>
	</p>
<?php

wc_get_template( 'emails/campaign/galo_idolos/email-footer.php' );
