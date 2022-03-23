<?php
/**
 * Email Styles
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-styles.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load colors.
$bg        = get_option( 'woocommerce_email_background_color' );
$body      = get_option( 'woocommerce_email_body_background_color' );
$base      = get_option( 'woocommerce_email_base_color' );
$base_text = wc_light_or_dark( $base, '#202020', '#ffffff' );
$text      = get_option( 'woocommerce_email_text_color' );
$secondary = "#377dff";

// Pick a contrasting color for links.
$link_color = wc_hex_is_light( $base ) ? $base : $base_text;

if ( wc_hex_is_light( $body ) ) {
	$link_color = wc_hex_is_light( $base ) ? $base_text : $base;
}

$bg_darker_10    = wc_hex_darker( $bg, 10 );
$body_darker_10  = wc_hex_darker( $body, 10 );
$base_lighter_20 = wc_hex_lighter( $base, 20 );
$base_lighter_40 = wc_hex_lighter( $base, 40 );
$text_lighter_20 = wc_hex_lighter( $text, 20 );
$text_lighter_40 = wc_hex_lighter( $text, 40 );

// !important; is a gmail hack to prevent styles being stripped if it doesn't like something.
// body{padding: 0;} ensures proper scale/positioning of the email in the iOS native email app.
?>
body {
	padding: 0;
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;
}

#wrapper {
	background-color: <?php echo esc_attr( $bg ); ?>;
	margin: 0;
	padding: 70px 0;
	-webkit-text-size-adjust: none !important;
	width: 100%;
}

#template_container {
	box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1) !important;
	background-color: <?php echo esc_attr( $body ); ?>;
	border: 1px solid <?php echo esc_attr( $bg_darker_10 ); ?>;
	border-radius: 8px !important;
	overflow: hidden;
}

#template_header {
	<!-- background-color: <?php //echo esc_attr( $base ); ?>; -->
	border-radius: 3px 3px 0 0 !important;
	color: <?php echo esc_attr( $base_text ); ?>;
	border-bottom: 0;
	font-weight: bold;
	line-height: 100%;
	vertical-align: middle;
	font-family: Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
}

#template_header h1,
#template_header h1 a {
	text-align: center;
	color: <?php echo esc_attr( $base_text ); ?>;
	background-color: inherit;
}

#template_header_image img {
	margin-bottom: 40px;
	margin-left: 0;
	margin-right: 0;
}

#template_footer td {
	padding: 0;
	border-radius: 6px;
}

#template_footer #credit {
	border: 0;
	color: <?php echo esc_attr( $text_lighter_40 ); ?>;
	font-family: Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
	font-size: 12px;
	line-height: 150%;
	text-align: center;
	padding: 24px 0;
}

#template_footer #credit p {
	margin: 0 0 10px;
}

#body_content {
	background-color: <?php echo esc_attr( $body ); ?>;
}

#body_content table td {
	padding: 20px 48px 32px;
}

#body_content table td td {
	padding: 12px;
}

#body_content table td th {
	padding: 12px;
}

#body_content td ul.wc-item-meta {
	font-size: small;
	margin: 1em 0 0;
	padding: 0;
	list-style: none;
}

#body_content td ul.wc-item-meta li {
	margin: 0.5em 0 0;
	padding: 0;
}

#body_content td ul.wc-item-meta li p {
	margin: 0;
}

#body_content p {
	margin: 0 0 10px;
}

#body_content_inner {
	color: <?php echo esc_attr( $text_lighter_20 ); ?>;
	font-family: Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
	font-size: 14px;
	line-height: 150%;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

#body_content_inner table, #body_content_inner table td {
	border: none;
}

.td {
	color: <?php echo esc_attr( $text_lighter_20 ); ?>;
	border: 1px solid <?php echo esc_attr( $body_darker_10 ); ?>;
	vertical-align: middle;
}

.address {
	padding: 12px;
	color: <?php echo esc_attr( $text_lighter_20 ); ?>;
	border: 1px solid <?php echo esc_attr( $body_darker_10 ); ?>;
}

.text {
	color: <?php echo esc_attr( $text ); ?>;
	font-family: Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.link {
	color: <?php echo esc_attr( $link_color ); ?>;
}

.btn_wrap {
	padding: 20px 0 0;
	text-align: center;
}

.img_wrap {
	padding: 0 0 20px;
	text-align: center;
}

.image-icon {
	margin-right: 0;
	vertical-align: bottom;
}

.btn {
	display: inline-block;
    padding: 1rem;
	width: 70%;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.5;
    color: #fff;
    text-align: center;
	cursor: pointer;
    text-decoration: none;
    border: 1px solid transparent;
    border-radius: 8px;
	box-sizing: border-box;
	background-color: <?php echo esc_attr( $base ); ?>;
}

#header_wrapper {
	padding: 20px 40px;
	display: block;
}

h1 {
	margin: 0;
	padding-top: 20px;
	color: <?php echo esc_attr( $base ); ?>;
	font-family: Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
	font-size: 44px;
	font-style: normal;
	font-weight: 700;
	line-height: 45px;
	letter-spacing: -0.03em;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
	#text-shadow: 0 1px 0 <?php echo esc_attr( $base_lighter_20 ); ?>;
}

h2 {
	color: <?php echo esc_attr( $base ); ?>;
	display: block;
	font-family: Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
	font-size: 18px;
	font-weight: bold;
	line-height: 130%;
	margin: 0 0 18px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
	color: <?php echo esc_attr( $base ); ?>;
	display: block;
	font-family: Roboto, "Helvetica Neue", Helvetica, Arial, sans-serif;
	font-size: 16px;
	font-weight: bold;
	line-height: 130%;
	margin: 16px 0 8px;
	text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

p {
	font-size: 16px;
	line-height: 2;
	font-weight: 400;
	color: <?php echo esc_attr( $base_text ); ?>;
}

a {
	color: <?php echo esc_attr( $link_color ); ?>;
	font-weight: normal;
	text-decoration: underline;
}

img {
	border: none;
	display: inline-block;
	font-size: 14px;
	font-weight: bold;
	height: auto;
	outline: none;
	text-decoration: none;
	text-transform: capitalize;
	vertical-align: middle;
	margin-<?php echo is_rtl() ? 'left' : 'right'; ?>: 10px;
	max-width: 100%;
	height: auto;
}

.talent_card {
	padding: 16px;
	border-radius: 8px;
	box-sizing: border-box;
	background-color: <?php echo esc_attr( $secondary ); ?>
}

.card_thumb {
	float: left;
	margin-right: 16px;
	width: 48px;
	height: 48px;
	border-radius: 50%;
	overflow: hidden;
	background: no-repeat center/cover <?php echo esc_attr( $base ); ?>;
}

.card_title {
	font-size: 16px;
	font-weight: 700;
	color: #fff;
}

.card_subtitle {
	font-size: 14px;
	font-weight: 400;
	color: #fff;
	opacity: 0.4;
}

.card_price {
	font-size: 32px;
	font-weight: 700;
	color: #fff;
}

.order_card {
	margin: 20px 0;
	padding: 16px;
	border-radius: 8px;
	box-sizing: border-box;
	border: 1px solid rgba(255,255,255,0.3);
	text-align: center;
}

.order_number {
	font-size: 24px;
	font-weight: 700;
}

.details_title {
	font-size: 16px;
	font-weight: 700;
	line-height: 1;
	color: <?php echo esc_attr( $text ); ?>;
	opacity: 0.4;
}

.details_value {
	margin: 0;
	font-size: 26px;
	font-weight: 700;
	color: <?php echo esc_attr( $text ); ?>;
}

.details_value_small {
	margin: 0;
	font-size: 21px;
	font-weight: 500;
	color: <?php echo esc_attr( $text ); ?>;
}

.details_line {
	display: block;
	width: 100%;
	height: 2px;
	background-color: <?php echo esc_attr( $text ); ?>;
	opacity: 0.5;
}

<?php
