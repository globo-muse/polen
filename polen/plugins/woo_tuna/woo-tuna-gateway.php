<?php
/*
Plugin Name: Tuna Payments para WooCommerce
Plugin URI: https://www.tuna.uy/
Description: Conecte Tuna no seu site do WooCommerce para aceitar pagamentos online de múltiplos provedores de pagamentos.
Version: 2.0
 * Requires PHP:      7.2
 * Requires at least: 5.0
 * Tested up to: 5.5.3
 * WC requires at least: 3.0.0
 * WC tested up to: 4.7.1
 * Author: Tuna Inc.
 * Author URI: https://www.tuna.uy/
*/

// Plugin constants.
define('WC_TUNA_PLUGIN_FILE', __FILE__);

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'tuna_payment_init', 0);
function tuna_payment_init()
{
	// If the parent WC_Payment_Gateway class doesn't exist
	// it means WooCommerce is not installed on the site
	// so do nothing
	if (!class_exists('WC_Payment_Gateway')) return;

	if (get_option('tuna_payment_operation_mode') === false) {
		add_option('tuna_payment_operation_mode', 'sandbox', null, 'yes');
	}
	if (get_option('tuna_payment_antifraud_config') === false) {
		add_option('tuna_payment_antifraud_config', '', null, 'yes');
	}
	// If we made it this far, then include our Gateway Class
	include_once('woo-tuna.php');

	// Now that we have successfully included our class,
	// Lets add it too WooCommerce
	add_filter('woocommerce_payment_gateways', 'tuna_add_payment_gateway');
	function tuna_add_payment_gateway($methods)
	{
		$methods[] = 'TUNA_Payment';
		return $methods;
	}
}

// Add custom action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tuna_payment_action_links');
function tuna_payment_action_links($links)
{
	$plugin_links = array(
		'<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=tuna_payment') . '">Configurações</a>',
	);

	// Merge our new link with the default ones
	return array_merge($plugin_links, $links);
}

add_action('woocommerce_order_actions', 'tuna_sv_wc_add_order_check_status');
function tuna_sv_wc_add_order_check_status($actions)
{
	global $theorder;

	// bail if the order has been paid for or this action has been run
	if ('tuna_payment' != $theorder->get_payment_method()) {
		return $actions;
	}

	// add "verify status" custom action
	$actions['wc_tuna_get_status'] = __('Verificar status do pagamento', 'tuna-payment');
	return $actions;
}

add_action('woocommerce_order_action_wc_tuna_get_status', 'tuna_sv_wc_process_order_check_payment_action');
function tuna_sv_wc_process_order_check_payment_action($order)
{
	$tuna  = wc_get_payment_gateway_by_order($order);
	// add the order note
	// translators: Placeholders: %s is a user's display name
	$message = __('Status do Pagamento: ' . $tuna->get_status($order), 'tuna-payment');
	$order->add_order_note($message);
}

add_action('wp_enqueue_scripts', 'tuna_enqueue_custom_js');
function tuna_enqueue_custom_js()
{
	$operation_mode = get_option('tuna_payment_operation_mode');
	$antifraud_config = get_option('tuna_payment_antifraud_config');

	if (function_exists('is_woocommerce')) {
		if (is_checkout()) {
			wp_enqueue_script('tuna-checkout-form', plugins_url('templates/js/tuna-checkout-form.js', __FILE__), [], '', true);
      wp_enqueue_script('tuna-checkout-info', plugins_url('templates/js/tuna-checkout-info.js', __FILE__), [], '', true);

			if ($operation_mode === "production") {
				wp_enqueue_script('tunajs', plugins_url('templates/js/tuna.js', __FILE__), [], '', true);
			} else {
				wp_enqueue_script('tunajs', plugins_url('templates/js/tuna-sandbox.js', __FILE__), [], '', true);
			}

			wp_enqueue_script('jquery-mask', plugins_url('templates/js/jquery.mask.min.js', __FILE__), [], '', true);
			wp_enqueue_style('tuna-components', plugins_url('templates/css/tuna.components.min.css', __FILE__));
		}

		if ($antifraud_config != null) {
			$config = json_decode($antifraud_config);
			if ($config->{'UseKonduto'}) {
				wp_enqueue_script('konduto_main', plugins_url('templates/js/konduto_main.js', __FILE__), [], '', true);
				$params = array(
					'public_key' => $config->{'KondutoPublicKey'}
				);
				wp_localize_script('konduto_main', 'params', $params);

				if (is_checkout()) {
					wp_enqueue_script('konduto_set_user_id', plugins_url('templates/js/konduto_set_user_id.js', __FILE__), [], '', true);
				}
			}
			if ($config->{'UseSift'}) {
				wp_enqueue_script('sift_snippet', plugins_url('templates/js/sift_snippet.js', __FILE__), [], '', true);
				$params = array(
					'account_key' => $config->{'SiftAccountKey'},
					'session_id' => wp_get_session_token()
				);
				wp_localize_script('sift_snippet', 'params', $params);
			}
		}
	}
}
