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
<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $user_login ) ); ?></p>
<?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>
<p><?php printf( esc_html__( 'Thanks for creating an account on %1$s. Your username is %2$s. You can access your account area to view orders, change your password, and more at: %3$s', 'woocommerce' ), esc_html( $blogname ), '<strong>' . esc_html( $user_login ) . '</strong>', make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
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
		<a href="<?php echo home_url(); ?>" class="btn" target="_blank">Ir para o <?= get_bloginfo('name'); ?></a>
	</p>
<?php

do_action( 'woocommerce_email_footer', $email );