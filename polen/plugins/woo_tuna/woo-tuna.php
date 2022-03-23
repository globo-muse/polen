<?php

require 'utils/tuna-fees.php';

class TUNA_Payment extends WC_Payment_Gateway
{
	// Setup our Gateway's id, description and other values
	function __construct()
	{
		// The global ID for this Payment method
		$this->id = "tuna_payment";

		// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		$this->method_title = __("Tuna Payments para Woocommerce", 'tuna-payment');

		// The description for this Payment Gateway, shown on the actual Payment options page on the backend
		$this->method_description = __("Conecte Tuna no seu site do WooCommerce para aceitar pagamentos online de múltiplos provedores de pagamentos.", 'tuna-payment');

		// The title to be used for the vertical tabs that can be ordered top to bottom
		$this->title = __("Tuna Payments para Woocommerce", 'tuna-payment');

		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		$this->icon = null;

		// Bool. Can be set to true if you want payment fields to show on the checkout
		// if doing a direct integration, which we are doing in this case
		$this->has_fields = true;

		// Supports the default credit card form
		$this->supports = array('default_credit_card_form');

		// This basically defines your settings which are then loaded with init_settings()
		$this->init_form_fields();

		// After init_settings() is called, you can get the settings and load them into variables, e.g:
		// $this->title = $this->get_option( 'title' );
		$this->init_settings();

		// Turn these settings into variables we can use
		foreach ($this->settings as $setting_key => $value) {
			$this->$setting_key = $value;
		}

		update_option("tuna_payment_operation_mode", $this->operation_mode );
		update_option("tuna_payment_antifraud_config", $this->antifraud_config );

		// Lets check for SSL
		#add_action('admin_notices', array($this,	'do_ssl_check'));
		/*add_action('woocommerce_view_order', array($this, 'pending_payment_message'));*/
		/*add_action('woocommerce_thankyou', array($this, 'pending_payment_message'));*/
		add_action('woocommerce_api_tuna_payment_callback', array($this, 'payment_callback'));
		add_action('woocommerce_api_tuna_payment_pix_verify', array($this, 'payment_pix_verify'));
    add_action('woocommerce_api_tuna_payment_crypto_verify', array($this, 'payment_crypto_verify'));
		add_action('woocommerce_order_details_before_order_table', array($this,'pending_payment_message'));
		#add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));
		// Save settings
		if (is_admin()) {
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		}
	} // End __construct()

	public function pending_payment_message($order_id)
	{
		$order = new WC_Order($order_id);

		if ('pending' === $order->get_status() && 'tuna_payment' == $order->get_payment_method()) {
			$url = '';
			$pixImage= '';
			$pixContent= '';
      $cryptoCoinValue= '';
			$cryptoCoinRateCurrency= '';
      $cryptoCoinAddr= '';
			$cryptoCoinQRCodeUrl= '';
      $cryptoCoin= '';
			$comments = wc_get_order_notes([
				'order_id' => $order->get_id(),
				'type' => 'internal',
			]);
			foreach ($comments as $comment) {
				if (strpos($comment->content, 'Boleto:') === 0) {
					$url = str_replace('Boleto:','',$comment->content);
				}
				if (strpos($comment->content, 'PixImage:') === 0) {
					$pixImage = str_replace('PixImage:','',$comment->content);
				}
				if (strpos($comment->content, 'PixKey:') === 0) {
					$pixContent = str_replace('PixKey:','',$comment->content);
				}
        if (strpos($comment->content, 'CryptoCoinValue:') === 0) {
					$cryptoCoinValue = str_replace('CryptoCoinValue:','',$comment->content);
				}
        if (strpos($comment->content, 'CryptoCoinRateCurrency:') === 0) {
					$cryptoCoinRateCurrency = str_replace('CryptoCoinRateCurrency:','',$comment->content);
				}
        if (strpos($comment->content, 'CryptoCoinAddr:') === 0) {
					$cryptoCoinAddr = str_replace('CryptoCoinAddr:','',$comment->content);
				}
        if (strpos($comment->content, 'CryptoCoinQRCodeUrl:') === 0) {
					$cryptoCoinQRCodeUrl = str_replace('CryptoCoinQRCodeUrl:','',$comment->content);
				}
        if (strpos($comment->content, 'CryptoCoin:') === 0) {
					$cryptoCoin = str_replace('CryptoCoin:','',$comment->content);
				}
			}
			if ($url != '') {
				$html = '<div class="woocommerce-info">';
				$html .= sprintf('<a class="button" href="%s" target="_blank" style="display: block !important; visibility: visible !important;">%s</a>', esc_url($url), __('Visualizar boleto &rarr;', 'tuna_payment'));

				$message = sprintf(__('%sAtenção!%s Ainda não registramos o pagamento deste pedido.', 'tuna_payment'), '<strong>', '</strong>') . '<br />';
				$message .= __('Por favor clique no botão ao lado e pague o boleto pelo seu Internet Banking.', 'tuna_payment') . '<br />';
				$message .= __('Caso preferir, você pode imprimir e pagá-lo em qualquer agência bancária ou casa lotérica.', 'tuna_payment') . '<br />';
				$message .= __('Ignore esta mensagem caso ja tenha efetuado o pagamento. O pedido será atualizado assim que houver a compensação.', 'tuna_payment') . '<br />';

				$html .= apply_filters('woo_tuna_pending_payment_message', $message, $order);

				$html .= '</div>';

				echo $html;
			}
			if ($pixContent != '') {
				$html = wc_get_template(
					'tuna-pix.php',
					array(
						'src'                  => $pixImage,
						'qr_image'             => $pixImage,
						'qr_code'              => $pixContent,
						'expiration_date'      => '30 min',
						'text_expiration_date' => __( 'Válido até: ', 'tuna-payment' ),
					),
					'',
					plugin_dir_path(WC_TUNA_PLUGIN_FILE) . 'templates/'
				);
				$html .= '<script>';
				$html .= 'function doPoll(purchaseid) {countPoll++;';
				$html .= '	jQuery.ajax({';
				$html .= '			type: "POST",';
				$html .= '					data: {partnerUniqueId: purchaseid, fetch: false},';
				$html .= '				cache: false,';
				$html .= '				url: "/?wc-api=tuna_payment_pix_verify",';
				$html .= '				success: function (data) {';
				$html .= '			if (data == "OK1" || countPoll>200){';
				$html .= '					clearTimeout(timeout);';
				$html .= '				window.location.reload();';
				$html .= '		}';
				$html .= '			timeout = setTimeout(function () {';
				$html .= '				doPoll(purchaseid);';
				$html .= '				}, 6000);			';
				$html .= '	},';
				$html .= '	error: function (data) {';
				$html .= '		$("#woocommerce-info").hide();';
				$html .= '		clearTimeout(timeout);';
				$html .= '	}';
				$html .= '	});';
				$html .= '}';
				$html .= 'jQuery(document).ready(function($) {doPoll('.$order_id.');});';
				$html .= 'var countPoll=0;';
				$html .= '</script>';

				echo $html;
			}
      if ($cryptoCoinQRCodeUrl != '') {
        $html = wc_get_template(
          'tuna-crypto.php',
          array(
            'src'                       => $cryptoCoinQRCodeUrl,
            'crypto_coin_value'         => $cryptoCoinValue,
            'crypto_coin_rate_currency' => $cryptoCoinRateCurrency,
            'crypto_coin_addr'          => $cryptoCoinAddr,
            'crypto_coin_qrcode_url'    => $cryptoCoinQRCodeUrl,
            'crypto_coin'               => $cryptoCoin,
            'expiration_date'           => '10 min',
            'text_expiration_date'      => __( 'Válido até: ', 'tuna-payment' ),
          ),
          '',
          plugin_dir_path(WC_TUNA_PLUGIN_FILE) . 'templates/'
        );
        $html .= '<script>';
				$html .= 'function doPoll(purchaseid) {countPoll++;';
				$html .= '	jQuery.ajax({';
				$html .= '			type: "POST",';
				$html .= '					data: {partnerUniqueId: purchaseid, fetch: false},';
				$html .= '				cache: false,';
				$html .= '				url: "/?wc-api=tuna_payment_crypto_verify",';
				$html .= '				success: function (data) {';
				$html .= '			if (data == "OK1" || countPoll>200){';
				$html .= '					clearTimeout(timeout);';
				$html .= '				window.location.reload();';
				$html .= '		}';
				$html .= '			timeout = setTimeout(function () {';
				$html .= '				doPoll(purchaseid);';
				$html .= '				}, 6000);			';
				$html .= '	},';
				$html .= '	error: function (data) {';
				$html .= '		$("#woocommerce-info").hide();';
				$html .= '		clearTimeout(timeout);';
				$html .= '	}';
				$html .= '	});';
				$html .= '}';
				$html .= 'jQuery(document).ready(function($) {doPoll('.$order_id.');});';
				$html .= 'var countPoll=0;';
				$html .= '</script>';

				echo $html;
			}
		}
	}

	public function payment_pix_verify()
	{
		if (empty($_POST['partnerUniqueId']['id'])) {
			echo print_r("ERROR PARTNERUNIQUEID REQUIRED");
			exit;
		}

		$orderID = sanitize_text_field($_POST['partnerUniqueId']['id']);
		$customer_order = wc_get_order((int) $orderID);

        if ($customer_order == null) {
			echo print_r("ERROR PARTNERUNIQUEID INVALID[".$orderID.']');
			exit;
		}/*
		$current_user = wp_get_current_user();
		if ($current_user  == null) {
			echo print_r("ERROR USER INVALID");
			exit;
		}
		if ($current_user  == null) {
			echo print_r("ERROR USER INVALID");
			exit;
		}*/
		$response = $this->get_tuna_status($customer_order);
		if ($response->code == -1){
				$customer_order->update_status('failed', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));
				echo print_r("OK");
				exit;
		}

		switch (strval($response->status)) {
			case '4':
			case '-1':
			case '6':
			case 'N':
			case 'A':
			case 'B':
			case 'E':
				$customer_order->update_status('failed', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));
				echo print_r("OK");
				break;
			case '5':
			case '7':
			case '3':
				$customer_order->update_status('refunded', __('Tuna Payments: Compra ressarcida!', 'tuna-payment'));
				echo print_r("OK");
				break;
			case '8':
			case '9':
			case '2':
				// Changing the order for processing and reduces the stock.
				$customer_order->update_status('payment-approved', __('Tuna Payments: Pagamento confirmado.', 'tuna-payment'));
				$customer_order->payment_complete();
				wc_reduce_stock_levels((int) $orderID);
				echo print_r("OK");
				break;
			case '0':
			case '1':
			case 'C':
			case 'P':
			case '1':
			case '0':
			case 'D':
				echo print_r("WAIT");
				break;
		}
		exit;
	}

  public function payment_crypto_verify()
	{
		if (empty($_POST['partnerUniqueId']['id'])) {
			echo print_r("ERROR PARTNERUNIQUEID REQUIRED");
			exit;
		}

		$orderID = sanitize_text_field($_POST['partnerUniqueId']['id']);
		$customer_order = wc_get_order((int) $orderID);

        if ($customer_order == null) {
			echo print_r("ERROR PARTNERUNIQUEID INVALID[".$orderID.']');
			exit;
		}/*
		$current_user = wp_get_current_user();
		if ($current_user  == null) {
			echo print_r("ERROR USER INVALID");
			exit;
		}
		if ($current_user  == null) {
			echo print_r("ERROR USER INVALID");
			exit;
		}*/
		$response = $this->get_tuna_status($customer_order);
		if ($response->code == -1){
				$customer_order->update_status('failed', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));
				echo print_r("OK");
				exit;
		}

		switch (strval($response->status)) {
			case '4':
			case '-1':
			case '6':
			case 'N':
			case 'A':
			case 'B':
			case 'E':
				$customer_order->update_status('failed', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));
				echo print_r("OK");
				break;
			case '5':
			case '7':
			case '3':
				$customer_order->update_status('refunded', __('Tuna Payments: Compra ressarcida!', 'tuna-payment'));
				echo print_r("OK");
				break;
			case '8':
			case '9':
			case '2':
				// Changing the order for processing and reduces the stock.
				$customer_order->update_status('payment-approved', __('Tuna Payments: Pagamento confirmado.', 'tuna-payment'));
				$customer_order->payment_complete();
				wc_reduce_stock_levels((int) $orderID);
				echo print_r("OK");
				break;
			case '0':
			case '1':
			case 'C':
			case 'P':
			case '1':
			case '0':
			case 'D':
				echo print_r("WAIT");
				break;
		}
		exit;
	}

	public function payment_callback()
	{
		$auth = apache_request_headers();
		$parameters = json_decode(file_get_contents('php://input'));
		//Get only Authorization header
		$valid = $auth['Authorization'];
		$valid2 = $auth['authorization'];
		if (empty($parameters->partnerUniqueId)) {
			echo print_r("ERROR PARTNERUNIQUEID REQUIRED");
			echo(print_r($parameters));
			exit;
		}
		if (empty($parameters->statusId)) {
			echo print_r("ERROR STATUS REQUIRED");
			echo(print_r($parameters));
			exit;
		}
		if (empty($valid)&& empty($valid2)) {
			echo print_r("ERROR APPKEY REQUIRED");
			echo(print_r($auth));
			exit;
		}

		$appkey = sanitize_text_field($valid);
		if (empty($appkey)) {
			$appkey = sanitize_text_field($valid2);
		}

		$orderID = sanitize_text_field($parameters->partnerUniqueId);
		$status = sanitize_text_field($parameters->statusId);
		if ('Bearer '.$this->partner_key != $appkey) {
			echo print_r("ERROR APPKEY INVALID");
			echo(print_r($auth));
			exit;
		}

		$customer_order = wc_get_order((int) $orderID);
		if ($customer_order == null) {
			echo print_r("ERROR PARTNERUNIQUEID INVALID");
			echo(print_r($parameters));
			exit;
		}

		switch (strval($status)) {
			case '0':
				$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
				break;
			case '1':
				$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
				break;
			case '4':
			case '-1':
			case '6':
			case 'N':
			case 'A':
			case 'B':
			case 'E':
				$customer_order->update_status('failed', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));

				break;
			case '5':
			case '7':
			case '3':
				$customer_order->update_status('refunded', __('Tuna Payments: Compra ressarcida!', 'tuna-payment'));

				break;
			case '8':
			case '9':
			case '2':
				// Changing the order for processing and reduces the stock.
				$customer_order->update_status('payment-approved', __('Tuna Payments: Pagamento confirmado.', 'tuna-payment'));
				$customer_order->payment_complete();
				wc_reduce_stock_levels((int) $orderID);
				break;
			case 'C':
			case 'P':
			case '1':
			case '0':
				$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
				break;
			case 'D':
				$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
				break;
		}

		echo print_r("OK");

		exit;
	}

	function GUIDv4($trim = true)
	{
		// Windows
		if (function_exists('com_create_guid') === true) {
			if ($trim === true)
				return trim(com_create_guid(), '{}');
			else
				return com_create_guid();
		}

		// OSX/Linux
		if (function_exists('openssl_random_pseudo_bytes') === true) {
			$data = openssl_random_pseudo_bytes(16);
			$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
			$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
			return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		}

		// Fallback (PHP 4.2+)
		mt_srand((double)microtime() * 10000);
		$charid = strtolower(md5(uniqid(rand(), true)));
		$hyphen = chr(45);                  // "-"
		$lbrace = $trim ? "" : chr(123);    // "{"
		$rbrace = $trim ? "" : chr(125);    // "}"
		$guidv4 = $lbrace.
				substr($charid,  0,  8).$hyphen.
				substr($charid,  8,  4).$hyphen.
				substr($charid, 12,  4).$hyphen.
				substr($charid, 16,  4).$hyphen.
				substr($charid, 20, 12).
				$rbrace;

		return $guidv4;
	}

	public function payment_fields()
	{
		if (!function_exists('wc_get_template')) {
			require_once '/includes/wc-core-functions.php';
		}

		$tmpUserID = $this->GUIDv4(true);
		$current_user = wp_get_current_user();
    $installment_fees = array(
            $this->installment_fee1==''?0:$this->installment_fee1,
            $this->installment_fee2==''?0:$this->installment_fee2,
            $this->installment_fee3==''?0:$this->installment_fee3,
            $this->installment_fee4==''?0:$this->installment_fee4,
            $this->installment_fee5==''?0:$this->installment_fee5,
            $this->installment_fee6==''?0:$this->installment_fee6,
            $this->installment_fee7==''?0:$this->installment_fee7,
            $this->installment_fee8==''?0:$this->installment_fee8,
            $this->installment_fee9==''?0:$this->installment_fee9,
            $this->installment_fee10==''?0:$this->installment_fee10,
            $this->installment_fee11==''?0:$this->installment_fee11,
            $this->installment_fee12==''?0:$this->installment_fee12
        );
		$this->save_log($this->installment_fee4);
        wc_get_template(
			'tuna-checkout-form.php',
			array(
				'token_session_id' => $this->get_session_id($tmpUserID),
				'allow_boleto_payment' => $this->allow_boleto,
				'allow_pix_payment' => $this->allow_pix,
        'allow_crypto_payment' => $this->allow_crypto, 
				'max_parcels_number' => $this->max_parcels_number,
                'min_parcel_value' => $this->min_parcel_value==''?0:$this->min_parcel_value,
                'installment_fees' => $installment_fees,
				'tmpUserID' =>($current_user->ID==0?$tmpUserID:$current_user->ID),
				'internal_session_id' =>  wp_get_session_token()
			),
			'',
			plugin_dir_path(WC_TUNA_PLUGIN_FILE) . 'templates/'
		);
	}

	public function get_tuna_status($order)
	{
		$url_production = 'https://engine.tunagateway.com/api/Payment/Status';
		$url_sandbox = 'https://sandbox.tuna-demo.uy/api/Payment/Status';

		$url = ($this->operation_mode === "production") ? $url_production : $url_sandbox;

		$cItem = [
			"AppToken" => $this->partner_key,
			"Account" => $this->partner_account,
			"PartnerUniqueID" => $order->get_id() . "",
			"PaymentDate" => $order->get_date_created()->format('Y-m-d')
		];
		#$this->save_log(json_encode($cItem));
		$api_response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Content-Type'  => 'application/json'
				),
				'body' => json_encode($cItem)
			)
		);
		if (is_wp_error($api_response))
			throw new Exception(__('Problemas com o processo de pagamento, recarregue a página.', 'tuna-payment'));

		if (empty($api_response['body']))
			throw new Exception(__('Problemas com o processo de pagamento, recarregue a página.', 'tuna-payment'));

        #$this->save_log($api_response['body']);
		return json_decode($api_response['body']);
	}

	public function get_status($order)
	{
		$response = $this->get_tuna_status($order);
		if ($this->automatic_update == "yes") {
			$orderID = $order->get_id();
			$customer_order = $order;
			if ($response->code == -1)
				$customer_order->update_status('failed', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));

			switch (strval($response->status)) {
				case '0':
					$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
					break;
				case '1':
					$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
					break;
				case '4':
				case '-1':
				case '6':
				case 'N':
				case 'A':
				case 'B':
				case 'E':
					$customer_order->update_status('failed', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));

					break;
				case '5':
				case '7':
				case '3':
					$customer_order->update_status('refunded', __('Tuna Payments: Compra ressarcida!', 'tuna-payment'));

					break;
				case '8':
				case '9':
				case '2':
					$customer_order->update_status('payment-approved', __('Tuna Payments: Pagamento confirmado.', 'tuna-payment'));
					$customer_order->payment_complete();
					// Changing the order for processing and reduces the stock.
					wc_reduce_stock_levels((int) $orderID);
					break;
				case 'C':
				case 'P':
				case '1':
				case '0':
					$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
					break;
				case 'D':
					$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
					break;
			}
		}

		return $this->get_end_status($response->status);
	}

	public function get_session_id($tmpUserID)
	{
		$current_user = wp_get_current_user(); 
		$url_production = 'https://token.tunagateway.com/api/Token/NewSession';
		$url_sandbox = 'https://token.tuna-demo.uy/api/Token/NewSession';

		$url = ($this->operation_mode === "production") ? $url_production : $url_sandbox;
		add_option('tuna_operation_mode', $this->operation_mode);

		$cItem = [
			"AppToken" => $this->partner_key,
			"Customer" => [
				"Email" => $current_user->user_email . '',
				"ID" => ($current_user->ID==0?$tmpUserID:$current_user->ID) . '',
			]
		];
		$tmpSessionID = '';
		try
		{
		#$this->save_log(json_encode($cItem));
		$api_response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Content-Type'  => 'application/json'
				),
				'body' => json_encode($cItem)
			)
		);
		if (is_wp_error($api_response))
			throw new Exception(__('Problemas com o processo de pagamento, recarregue a página.', 'tuna-payment'));

		if (empty($api_response['body']))
			throw new Exception(__('Problemas com o processo de pagamento, recarregue a página.', 'tuna-payment'));

        $this->save_log($api_response['body']);
		$response = json_decode($api_response['body']);
		
		
			$tmpSessionID = __(strval($response->sessionId));
		}catch (\Exception $e) {}
		return $tmpSessionID;
	}

	public function save_log($txt)
	{
		$filename = "newfile.txt";
		$file = fopen($filename, "a");

		if ($file == false) {
			echo ("Error in opening new file");
			exit();
		}

		fwrite($file, $txt . " | " . date("j/n/Y h:i:s") . "\n");
		fclose($file);
	}

	// Build the administration fields for this specific Gateway
	public function init_form_fields()
	{
		$this->form_fields = array(
			'title' => array(
				'title'		=> __('Título no carrinho', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'desc_tip'	=> __('Título que o usuário vai ver na tela de pagamento.', 'tuna-payment'),
				'default'	=> __('Cartão de Crédito, Pix, Bitcoin ou Boleto', 'tuna-payment'),
			),
			'description' => array(
				'title'		=> __('Descrição', 'tuna-payment'),
				'type'		=> 'textarea',
				'desc_tip'	=> __('Descrição que o usuário vai ver na tela de pagamento.', 'tuna-payment'),
				'default'	=> __('Pague com seu cartão de crédito, pix, bitcoin ou boleto através da Tuna.', 'tuna-payment'),
				'css'		=> 'max-width:350px;'
			),
      'allow_boleto' => array(
				'title'		=> __('Boleto', 'tuna-payment'),
				'label'		=> __('Habilitar Boleto', 'tuna-payment'),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
      'allow_pix' => array(
				'title'		=> __('Pix', 'tuna-payment'),
				'label'		=> __('Habilitar Pix', 'tuna-payment'),
				'type'		=> 'checkbox',
				'default'	=> 'no',
      ),
      'allow_crypto' => array(
				'title'		=> __('Bitcoin', 'tuna-payment'),
				'label'		=> __('Habilitar Bitcoin', 'tuna-payment'),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
      'automatic_update' => array(
				'title'		=> __('Atualização automática de pedidos', 'tuna-payment'),
				'label'		=> __('Habilite essa opção para que quando seus pagamentos sejam atualizados pela verificação de status, a ordem também seja automaticamente atualizada.', 'tuna-payment'),
				'type'		=> 'checkbox',
				'default'	=> 'no',
	  ),
			'title_credentials' => array(
				'title' => __('Credenciais', 'tuna-payment'),
				'type' => 'title',
				'description' => sprintf(
					__('Insira suas credenciais. Se ainda não as têm, %s', 'tuna-payment'),
					"<a target='_blank' href='https://console.tuna.uy/register?utm_source=woocommerce&utm_medium=site&utm_campaing=wc-settings&utm_content=plugins '>crie sua conta</a> para obtê-las. Para testes, utilize sandbox com <a target='_blank' href='https://dev.tuna.uy/tuna-payment-api#section/Authentication?utm_medium=plugin&utm_source=wp&utm_content=integrations&utm_campaign=v1.1'>essas chaves</a> e esses <a target='_blank' href='https://dev.tuna.uy/api-guides/payment#test-data?utm_medium=plugin&utm_source=wp&utm_content=integrations&utm_campaign=v1.1'>dados de cartão</a>"
				),
			),
			'partner_key' => array(
				'title'		=> __('Partner Key', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'desc_tip'	=> __('Insira suas credenciais. Partner key  informado em console.tuna.uy', 'tuna-payment'),
				'default'	=> __('partner_key', 'tuna-payment'),
			),
			'partner_account' => array(
				'title'		=> __('Partner Account', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'desc_tip' => __('Insira suas credenciais. Partner account informado em console.tuna.uy', 'tuna-payment'),
				'default'	=> __('partner-account', 'tuna-payment'),
			),
			'antifraud_config' => array(
				'title'		=> __('Configuração de Antifraude', 'tuna-payment'),
				'type'		=> 'textarea',
				'desc_tip' => __('Insira a sua configuração de antifraude disponível em console.tuna.uy', 'tuna-payment'),
				'default'	=> __('', 'tuna-payment'),
			),
			'operation_mode' => array(
				'title' => esc_attr__('Modo de operação', 'tuna-payment'),
				'type' => 'select',
				'default' => 'sandbox',
				'options' => array(
					'production' => 'Production',
					'sandbox' => 'Sandbox'
				),
			),
      'title_credit_card_options' => array(
				'title' => __('Configurações para Cartão de Crédito', 'tuna-payment'),
				'type' => 'title'
			),
      'max_parcels_number' => array(
				'title' => esc_attr__('Número máximo de parcelas', 'tuna-payment'),
				'type' => 'select',
				'default' => '12',
				'options' => array(
					'1' => '1x',
					'2' => '2x',
					'3' => '3x',
					'4' => '4x',
					'5' => '5x',
					'6' => '6x',
					'7' => '7x',
					'8' => '8x',
					'9' => '9x',
					'10' => '10x',
					'11' => '11x',
					'12' => '12x',
				),
			),
      'min_parcel_value' => array(
				'title'		=> __('Valor da menor parcela', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'desc_tip'	=> __('Este é menor valor de parcela que será exibido nas opções de pagamento. Caso não queira utilizar, deixe o valor como 0.0', 'tuna-payment'),
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee1' => array(
				'title'		=> __('Juros para 1ª parcela', 'tuna-payment'),
        		'type'		=> 'text',
				'required'  => true,
        		'desc_tip'	=> __('É possível oferecer descontos com taxas negativas de juros. Exemplo: -10.0', 'tuna-payment'),
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee2' => array(
				'title'		=> __('Juros para 2ª parcela', 'tuna-payment'),
        		'type'		=> 'text',
				'required'  => true,
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee3' => array(
				'title'		=> __('Juros para 3ª parcela', 'tuna-payment'),
        		'type'		=> 'text',
				'required'  => true,
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee4' => array(
				'title'		=> __('Juros para 4ª parcela', 'tuna-payment'),
                'type'		=> 'text',
				'required'  => 'true',
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee5' => array(
				'title'		=> __('Juros para 5ª parcela', 'tuna-payment'),
            'type'		=> 'text',
			'required'  => 'true',
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee6' => array(
				'title'		=> __('Juros para 6ª parcela', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => 'true',
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee7' => array(
				'title'		=> __('Juros para 7ª parcela', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee8' => array(
				'title'		=> __('Juros para 8ª parcela', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee9' => array(
				'title'		=> __('Juros para 9ª parcela', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee10' => array(
				'title'		=> __('Juros para 10ª parcela', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee11' => array(
				'title'		=> __('Juros para 11ª parcela', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'default'	=> __('0.0', 'tuna-payment'),
			),
      'installment_fee12' => array(
				'title'		=> __('Juros para 12ª parcela', 'tuna-payment'),
				'type'		=> 'text',
				'required'  => true,
				'default'	=> __('0.0', 'tuna-payment'),
			)
		);
	}

	// Submit payment and handle response
	public function process_payment($order_id)
	{
		$orderID= $order_id;
		global $woocommerce;
		$current_user = wp_get_current_user();
		// Get this Order's information so that we know
		// who to charge and how much
		$customer_order = new WC_Order($order_id);

		// Decide which URL to post to
		$url_production = 'https://engine.tunagateway.com/api/Payment/Init';
		$url_sandbox = 'https://sandbox.tuna-demo.uy/api/Payment/Init';

		$url = ($this->operation_mode === "production") ? $url_production : $url_sandbox;

        // Adição POLEN
        $user = get_user_by('email', $customer_order->get_billing_email());
        $fullName = $user->first_name . ' ' . $user->last_name;

        if (empty(trim($fullName))) {
            $fullName = preg_replace('/[0-9\@\.\;]+/', ' ', $user->display_name) . ' Bollet';
        }

		$custormerID =($current_user->ID==0?sanitize_text_field($_POST["tuna_tmpuser_id"]):$current_user->ID) . '';

		$itens = [];
		$cart = WC()->cart->get_cart();

		foreach ($cart as $cart_item) {
			$product = wc_get_product($cart_item['product_id']);
			$cItem = [[
				"Amount" => floatval($product->get_price()),
				"ProductDescription" => $product->get_name(),
				"ItemQuantity" =>  $cart_item['quantity'],
				"CategoryName" => $product->get_type(),
				"AntiFraud" => [
					"Ean" => $product->get_sku()
				]
			]];
			$itens = array_merge($itens, $cItem);
		}

		$documentType = "CPF";
		$documentValue = sanitize_text_field($_POST['tuna_document']);
		if (strlen($documentValue) > 17) {
			$documentType = "CNPJ";
		}

        $documentValue = preg_replace('/[\.\-\/" "]+/', '', $documentValue);
		$installments = (int)sanitize_text_field($_POST['tuna_installments']);
		$PaymentMethodType = "1";
		$cardInfo = null;
		$boletoInfo = null;

		if (sanitize_text_field($_POST["tuna_is_boleto_payment"]) == "true") {
			$customer_order->set_payment_method_title('Boleto');
			$PaymentMethodType = "3";
			$installments = 1;
			$boletoInfo = [
				"BillingInfo" => [
					"Document" => $documentValue,
					"Name" => $fullName,
					"DocumentType" => $documentType,
                    "Address" => [
                        "Street" => $customer_order->get_billing_address_1(),
                        "Number" => '000',
                        "Complement" => 'altos',
                        "Neighborhood" => "A",
                        "City" => 'Curitiba',
                        "State" => 'PR',
                        "Country" => $customer_order->get_billing_country() != null ? $customer_order->get_billing_country() : "BR",
                        "PostalCode" => $customer_order->get_billing_postcode() ? preg_replace('/[\.\-\/" "]+/', '', $customer_order->get_billing_postcode() ) : 80250080,
                        "Phone" => $customer_order->get_billing_phone() ?? '999999999999',
                    ]
				]
			];
		} else {
		  	if (sanitize_text_field($_POST["tuna_is_pix_payment"]) == "true") {
            $customer_order->set_payment_method_title('Pix');
            $PaymentMethodType = "D";
            $installments = 1;
		    } elseif (sanitize_text_field($_POST["tuna_is_crypto_payment"]) == "true") {
            $customer_order->set_payment_method_title('Bitcoin');
            $PaymentMethodType = "E";
            $installments = 1;
        } else {
            $customer_order->set_payment_method_title('Cartão de Crédito');
            $order_total = floatval($customer_order->get_total());

            $fees = array(
                $this->installment_fee1==''?0:$this->installment_fee1,
                $this->installment_fee2==''?0:$this->installment_fee2,
                $this->installment_fee3==''?0:$this->installment_fee3,
                $this->installment_fee4==''?0:$this->installment_fee4,
                $this->installment_fee5==''?0:$this->installment_fee5,
                $this->installment_fee6==''?0:$this->installment_fee6,
                $this->installment_fee7==''?0:$this->installment_fee7,
                $this->installment_fee8==''?0:$this->installment_fee8,
                $this->installment_fee9==''?0:$this->installment_fee9,
                $this->installment_fee10==''?0:$this->installment_fee10,
                $this->installment_fee11==''?0:$this->installment_fee11,
                $this->installment_fee12==''?0:$this->installment_fee12
            );
            $fee = $fees[$installments - 1] / 100;
            $installment_amount = get_installment_amount($order_total, $fee, $installments);
            $installment_amount = number_format($installment_amount, 2, '.', '');
            
            if ($installment_amount > 0) {
                // Fee object body
                $order_fee = new WC_Order_Item_Fee();
                $order_fee->set_name('Acréscimo de juros');
                $order_fee->set_amount($installment_amount);
                $order_fee->set_total($installment_amount);
                
                // Add Fee item to the order
                $customer_order->add_item($order_fee);
                $customer_order->calculate_totals();
                $customer_order->update_status('on-hold');
                $customer_order->save();
            } elseif ($installment_amount < 0) {
              // Fee object body
              $order_fee = new WC_Order_Item_Fee();
              $order_fee->set_name('Desconto');
              $order_fee->set_amount($installment_amount);
              $order_fee->set_total($installment_amount);
              
              // Add Fee item to the order
              $customer_order->add_item($order_fee);
              $customer_order->calculate_totals();
              $customer_order->update_status('on-hold');
              $customer_order->save();
          }

            $cardInfo = [
                "TokenProvider" => "Tuna",
                "CardHolderName" => sanitize_text_field($_POST["tuna_card_holder_name"]),
                "BrandName" => sanitize_text_field($_POST["tuna_card_brand"]),
                "ExpirationMonth" => (int)sanitize_text_field($_POST["tuna_expiration_month"]),
                "ExpirationYear" =>  (int)sanitize_text_field($_POST["tuna_expiration_year"]),
                "Token" => sanitize_text_field($_POST["tuna_card_token"]),
                "TokenSingleUse" => ($current_user->ID==0?1:0),
                "SaveCard" => false,
                "BillingInfo" => [
                    "Document" => $documentValue,
					"Name" => $fullName,
                    "DocumentType" => $documentType,
                    "Address" => [
                        "Street" => $customer_order->get_billing_address_1(),
                        "Number" => "0",
                        "Complement" => '',
                        "Neighborhood" => "",
                        "City" => $customer_order->get_billing_city(),
                        "State" => $this->get_state_code($customer_order->get_billing_state()),
                        "Country" => $customer_order->get_billing_country() != null ? $customer_order->get_billing_country() : "BR",
                        "PostalCode" => $customer_order->get_billing_postcode() ? preg_replace('/[\.\-\/" "]+/', '', $customer_order->get_billing_postcode() ) : 80250080,
                        "Phone" =>  $customer_order->get_billing_phone()
                    ]
                ]
            ];
            }
	    }

		$requestbody = [
			'AppToken' =>  $this->partner_key,
			'Account' => $this->partner_account,
			'PartnerUniqueID' => $order_id,
			'TokenSession' =>  sanitize_text_field($_POST["tuna_token_session_id"]),
			'Customer' => [
				'Email' => $customer_order->get_billing_email(),
				'Name' => $fullName,
				'ID' => $custormerID,
				'Document' => $documentValue,
				'DocumentType' => $documentType
			],
			"FrontData" => [
				"SessionID" => wp_get_session_token(),
				"Origin" => "WEBSITE",
				"IpAddress" => $_SERVER['REMOTE_ADDR'],
				"CookiesAccepted" => true
			],
			"ShippingItems" => [
				"Items" => [[
					"Type" => $customer_order->get_shipping_method(),
					"Amount" => floatval($customer_order->get_shipping_total()),
					"Code" =>  ""
				]]
			],
			"PaymentItems" => [
				"Items" => $itens
			],
			"PaymentData" => [
				'Countrycode' => $customer_order->get_billing_country() != null ? $customer_order->get_billing_country() : "BR",
				"AntiFraud" => [
					"DeliveryAddressee" => $fullName
				],
				"DeliveryAddress" => [
					"Street" =>  $customer_order->get_shipping_address_1(),
					"Number" => "0",
					"Complement" => "",
					"Neighborhood" => "",
					"City" => $customer_order->get_shipping_city(),
					"State" => $this->get_state_code($customer_order->get_shipping_state()),
					"Country" => $customer_order->get_shipping_country() != null ? $customer_order->get_shipping_country() : "BR",
                    "PostalCode" => $customer_order->get_shipping_postcode() ? preg_replace('/[\.\-\/" "]+/', '', $customer_order->get_shipping_postcode() ) : 80250080,
					"Phone" => ""
				],
				"SalesChannel" => "ECOMMERCE",
				"PaymentMethods" => [
					[
						"PaymentMethodType" => $PaymentMethodType,
                        "Amount" => floatval($customer_order->get_total()),
						"Installments" => $installments,
						"CardInfo" => $cardInfo,
						"BoletoInfo" => $boletoInfo
					]
				]
			]
		];

		#$this->save_log(json_encode($requestbody));
		$api_response = wp_remote_post($url, array(
			'headers'     => array(
				'Content-Type'  => 'application/json'
			),
			'body' => json_encode($requestbody), 'timeout'     => 120
		));
		if (is_wp_error($api_response))
			throw new Exception(__('We are currently experiencing problems trying to connect to this payment gateway. Sorry for the inconvenience.' . $api_response->get_error_message(), 'tuna-payment'));

		if (empty($api_response['body']))
			throw new Exception(__('Tuna Response was empty.', 'tuna-payment'));

		#$this->save_log($api_response['body']);
		$response = json_decode($api_response['body']);
		$redirectSuccess = false;

		try {
			if ($response->code == -1) {
				$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
				$woocommerce->cart->empty_cart();
				$redirectSuccess = true;
			} else {
				switch (strval($response->status)) {
					case '0':
						$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
						$woocommerce->cart->empty_cart();
						$redirectSuccess = true;
						break;
					case '1':
						$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
						$woocommerce->cart->empty_cart();
						$redirectSuccess = true;
						break;

					case '3':
					case '4':
					case '-1':
					case '6':
					case 'N':
					case 'A':
					case 'B':
					case 'E':
						$customer_order->update_status('failed', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));

						//$woocommerce->cart->empty_cart();
						wc_add_notice('Operação não pode ser completada, tente novamente! ' . $response->code->message->message, 'error');
						$redirectSuccess = true;
						break;
					case '5':
					case '7':
						$customer_order->update_status('refunded', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));
						//$woocommerce->cart->empty_cart();
						wc_add_notice('Operação não pode ser completada, tente novamente! ' . $response->code->message->message, 'error');
						$redirectSuccess = true;
						break;
						break;
					case '8':
					case '9':
					case '2':
						$customer_order->update_status('payment-approved', __('Tuna Payments: Pagamento confirmado.', 'tuna-payment'));
						// Changing the order for processing and reduces the stock.
						$customer_order->payment_complete();
						wc_reduce_stock_levels((int) $orderID);
						$woocommerce->cart->empty_cart();
						$redirectSuccess = true;
						break;
					case 'C':
					case 'P':
					case '1':
					case '0':
						$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));

						$woocommerce->cart->empty_cart();
						$redirectSuccess = true;
						break;
					case 'D':
						$customer_order->update_status('pending', __('Tuna Payments: Pagamento Pendente.', 'tuna-payment'));
						$woocommerce->cart->empty_cart();
						$redirectSuccess = true;
						break;
				}

				if (strval($response->code) == "1" && ($response->status == "C" || $response->status == "P")) {
					if (sanitize_text_field($_POST["tuna_is_boleto_payment"]) == "true") {
						if ($response->methods != null && $response->methods[0]->redirectInfo != null) {
							$customer_order->add_order_note('Boleto:'.$response->methods[0]->redirectInfo->url);
						}
					}
					if (sanitize_text_field($_POST["tuna_is_pix_payment"]) == "true") {
						if ($response->methods != null && $response->methods[0]->pixInfo != null) {
							$customer_order->add_order_note("PixImage:".$response->methods[0]->pixInfo->qrImage);
							$customer_order->add_order_note("PixKey:".$response->methods[0]->pixInfo->qrContent);
						}
					}
          if (sanitize_text_field($_POST["tuna_is_crypto_payment"]) == "true") {
						if ($response->methods != null && $response->methods[0]->cryptoInfo != null) {
							$customer_order->add_order_note("CryptoCoinValue:".$response->methods[0]->cryptoInfo->coinValue);
							$customer_order->add_order_note("CryptoCoinRateCurrency:".$response->methods[0]->cryptoInfo->coinRateCurrency);
              $customer_order->add_order_note("CryptoCoinAddr:".$response->methods[0]->cryptoInfo->coinAddr);
              $customer_order->add_order_note("CryptoCoinQRCodeUrl:".$response->methods[0]->cryptoInfo->coinQRCodeUrl);
              $customer_order->add_order_note("CryptoCoin:".$response->methods[0]->cryptoInfo->coin);
						}
					}
				}

				if ($redirectSuccess) {
					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url($customer_order),
					);
				} else {
					$customer_order->update_status('failed', __('Tuna Payments: Operação não pode ser completada, tente novamente!', 'tuna-payment'));
					wc_add_notice('Operação não pode ser completada, tente novamente! ' . $response->code->message->message, 'error');
					$customer_order->add_order_note('Tuna Payments Erro: ' . 	'Operação não pode ser completada, tente novamente!');
					return array(
						'result'   => 'error',
						'redirect' => $this->get_return_url($customer_order),
					);
				}
			}
		} catch (\Exception $e) {

			$customer_order->update_status('failed', __('Tuna Payments:  Operação não pode ser completada, tente novamente!', 'tuna-payment'));

			wc_add_notice($r['response_reason_text'] . $response->code->message->message, 'error');
			$customer_order->add_order_note('Tuna Payments Erro: ' . $r['response_reason_text']);
			return array(
				'result'   => 'error',
				'redirect' => $this->get_return_url($customer_order),
			);
		}
	}

	// Validate fields
	public function validate_fields()
	{
		return true;
	}

	// Check if we are forcing SSL on checkout pages
	// Custom function not required by the Gateway
	public function do_ssl_check()
	{
		if (get_option('woocommerce_force_ssl_checkout') == "no") {
			echo "<div class=\"error\"><p>" . sprintf(__("<strong>%s</strong> Atenção: WooCommerce não esta forçando o uso de HTTPS no seu fluxo de pagamento. Por favor, verifique seu certificado SSL e altere suas configurações em <a href=\"%s\">forçando o uso de páginas seguras</a>"), $this->method_title, admin_url('admin.php?page=wc-settings&tab=checkout')) . "</p></div>";
		}
	}

	function get_end_status($status)
	{
		$code = "Erro";
		switch ($status) {
			case '8':
			case '9':
			case '2':
				$code = "Completado";
				break;
			case '1':
			case '0':
			case 'C':
			case 'P':
				$code = "Aguardando Pagamento";
				break;
			case 'A':
			case '6':
				$code = "Erro";
				break;
			case '5':
			case '7':
			case '3':
				$code = "Reembolsado";
				break;
			case '4':
			case 'B':
			case 'N':
			case 'B':
				$code = "Negado";
				break;
			case 'E':
				$code = "Negado AntiFraude";
				break;
			case 'D':
				$code = "Aguardando AntiFraude";
				break;
		}

		return $code;
	}

	function get_state_code($state)
	{
		$code = $state;
		switch ($state) {
			case 'Bahia':
				$code = "BA";
				break;
			case 'Acre':
				$code = "AC";
				break;
			case 'Alagoas':
				$code = "AL";
				break;
			case 'Amapá':
				$code = "AP";
				break;
			case 'Amazonas':
				$code = "AM";
				break;
			case 'Bahia':
				$code = "BA";
				break;
			case 'Ceará':
				$code = "CE";
				break;
			case 'Distrito Federal':
				$code = "DF";
				break;
			case 'Espírito Santo':
				$code = "ES";
				break;
			case 'Goiás':
				$code = "GO";
				break;
			case 'Maranhão':
				$code = "MA";
				break;
			case 'Mato Grosso':
				$code = "MT";
				break;
			case 'Mato Grosso do Sul':
				$code = "MS";
				break;
			case 'Minas Gerais':
				$code = "MG";
				break;
			case 'Pará':
				$code = "PA";
				break;
			case 'Paraíba':
				$code = "PB";
				break;
			case 'Paraná':
				$code = "PR";
				break;
			case 'Pernambuco':
				$code = "PE";
				break;
			case 'Piauí':
				$code = "PI";
				break;
			case 'Rio de Janeiro':
				$code = "RJ";
				break;
			case 'Rio Grande do Norte':
				$code = "RN";
				break;
			case 'Rio Grande do Sul':
				$code = "RS";
				break;
			case 'Rondônia':
				$code = "RO";
				break;
			case 'Roraima':
				$code = "RR";
				break;
			case 'Santa Catarina':
				$code = "SC";
				break;
			case 'São Paulo':
				$code = "SP";
				break;
			case 'Sergipe':
				$code = "SE";
				break;
			case 'Tocantins':
				$code = "TO";
				break;
		}
		return $code;
	}
} // End of SPYR_Payment