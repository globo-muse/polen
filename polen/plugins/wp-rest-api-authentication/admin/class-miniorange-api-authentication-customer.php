<?php

/** miniOrange provides to functionality to protect WP REST APIs from anonymous user and provide an authorized access to different WP APIs.
Copyright (C) 2015  miniOrange

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

 * @package 		miniOrange OAuth
 * @license		    https://docs.miniorange.com/mit-license MIT/Expat
 */

/**
This library is miniOrange Authentication Service.
Contains Request Calls to Customer service.

 **/

class Miniorange_API_Authentication_Customer {

	public $email;
	public $phone;
	
	private $defaultCustomerKey = "16555";
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

	function create_customer(){
		$url = get_option('host_name') . '/moas/rest/customer/add';
		$this->email 		= get_option('mo_api_authentication_admin_email');
		$this->phone 		= get_option('mo_api_authentication_admin_phone');
		$password 			= get_option('password');
		$firstName    		= get_option('mo_api_authentication_admin_fname');
		$lastName     		= get_option('mo_api_authentication_admin_lname');
		$company      		= get_option('mo_api_authentication_admin_company');
		
		$fields = array(
			'companyName' => $company,
			'areaOfInterest' => 'WP REST API Authentication',
			'firstname'	=> $firstName,
			'lastname'	=> $lastName,
			'email'		=> $this->email,
			'phone'		=> $this->phone,
			'password'	=> $password
		);
		$field_string = json_encode($fields);
		$headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '15',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,
 
		);
		
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html( $error_message );
			exit();
		}
		
		return wp_remote_retrieve_body($response);
	}

	function check_customer() {
		$url 	= get_option('host_name') . "/moas/rest/customer/check-if-exists";
		// $ch 	= curl_init( $url );
		$email 	= get_option("mo_api_authentication_admin_email");

		$fields = array(
			'email' 	=> $email,
		);
		$field_string = json_encode( $fields );
		$headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
			'redirection' => '15',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,
		);
			
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html( $error_message );
			exit();
		}
			
		return wp_remote_retrieve_body($response);
	}

	public function get_timestamp() {
	    $url = get_option ( 'host_name' ) . '/moas/rest/mobile/get-timestamp';
	    $headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
		$args = array(
			'method' =>'POST',
			'body' => array(),
			'timeout' => '5',
			'redirection' => '15',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,
		);
			
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html( $error_message );
			exit();
		}
			
		return wp_remote_retrieve_body($response);
	}


	function send_otp_token($email, $phone, $sendToEmail = TRUE, $sendToPhone = FALSE){
		$url = get_option('host_name') . '/moas/api/auth/challenge';
			
		$customerKey =  $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$username = get_option('mo_api_authentication_admin_email');
		$phone=get_option('mo_api_authentication_admin_phone');
		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$currentTimeInMillis = self::get_timestamp();

		/* Creating the Hash using SHA-512 algorithm */
		$stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . $currentTimeInMillis;
		$authorizationHeader = "Authorization: " . $hashValue;

		if( $sendToEmail ) {
			$fields = array(
				'customerKey' => $customerKey,
				'email' => $username,
				'authType' => 'EMAIL',
				);}
		else {
			$fields=array(
			'customerKey'=>$customerKey,
			'phone' => $phone,
			'authType' => 'SMS');
		}
		$field_string = json_encode($fields);
			
		$headers = array( 'Content-Type' => 'application/json');
		$headers['Customer-Key'] = $customerKey;
		$headers['Timestamp'] = $currentTimeInMillis;
		$headers['Authorization'] = $hashValue;
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,
	
		);
		
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html( $error_message );
			exit();
		}
			
		return wp_remote_retrieve_body($response);
	}

	function get_customer_key() {
		$url 	= get_option('host_name') . "/moas/rest/customer/key";
		$email 	= get_option("mo_api_authentication_admin_email");
		
		$password 			= get_option("password");
		
		$fields = array(
			'email' 	=> $email,
			'password' 	=> $password
		);
		$field_string = json_encode( $fields );
		
		$headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,
 
		);
		
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html( $error_message );
			exit();
		}
		
		return wp_remote_retrieve_body($response);
	}

	function validate_otp_token($transactionId,$otpToken) {
		$url = get_option('host_name') . '/moas/api/auth/validate';
			

		$customerKey =  $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$username = get_option('mo_api_authentication_admin_email');

		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$currentTimeInMillis = self::get_timestamp();

		/* Creating the Hash using SHA-512 algorithm */
		$stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . $currentTimeInMillis;
		$authorizationHeader = "Authorization: " . $hashValue;

		$fields = '';

		//*check for otp over sms/email
		$fields = array(
			'txId' => $transactionId,
			'token' => $otpToken,
		);

		$field_string = json_encode($fields);

		$headers = array( 'Content-Type' => 'application/json');
		$headers['Customer-Key'] = $customerKey;
		$headers['Timestamp'] = $currentTimeInMillis;
		$headers['Authorization'] = $hashValue;
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,	
		);
			
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html( $error_message );
			exit();
		}
			
		return wp_remote_retrieve_body($response);
	}

	function submit_contact_us( $email, $phone, $query, $send_config = true ) {
		global $current_user;
		$plugin_config          = mo_api_authentication_export_plugin_config();
		$config_to_send         = json_encode( $plugin_config, JSON_UNESCAPED_SLASHES );
		$last_requested_api     = get_option( 'mo_api_authentication_last_requested_api' );
		$site_url               = site_url();
		$apis                   = "";
		$version = get_option("mo_api_authentication_current_plugin_version");
		wp_get_current_user();
		$query = '[WP REST API Authentication] version ' . $version . " - " . $query;
		if ( ! empty( $last_requested_api ) ) {
			foreach( $last_requested_api as $api => $method ){
				$apis .= $method." ".$api.'<br>';
			}
		}
		if( $send_config ) {
			$query .= "<br><br>Config String:<br><pre style=\"border:1px solid #444;padding:10px;\"><code>" . $config_to_send . "<br><br>APIs : <br>". $apis . "</code></pre>";
		}

		$fields = array(
			'firstName'			=> $current_user->user_firstname,
			'lastName'	 		=> $current_user->user_lastname,
			'company' 			=> sanitize_text_field($_SERVER['SERVER_NAME']),
			'email' 			=> $email,
			'ccEmail'           => 'apisupport@xecurify.com',
			'phone'				=> $phone,
			'query'				=> $query
		);
		$field_string = json_encode( $fields );

		$url = get_option('host_name') . '/moas/rest/customer/contact-us';
		$headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '15',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,
 
		);
		
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html( $error_message );
			exit();
		}
		
		return wp_remote_retrieve_body($response);
	}

	function mo_api_authentication_send_email_alert($email,$phone,$reply,$message,$subject,$plugin_config){

		$url = get_option( 'host_name' ) . '/moas/api/notify/send';
		
		$config_to_send         = json_encode( $plugin_config, JSON_UNESCAPED_SLASHES );
		$last_requested_api     = get_option( 'mo_api_authentication_last_requested_api' );
		$customerKey = $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$currentTimeInMillis = self::get_timestamp();
		$stringToHash 		= $customerKey .  $currentTimeInMillis . $apiKey;
		$hashValue 			= hash("sha512", $stringToHash);
		$customerKeyHeader 	= "Customer-Key: " . $customerKey;
		$timestampHeader 	= "Timestamp: " .  $currentTimeInMillis;
		$authorizationHeader= "Authorization: " . $hashValue;
		$fromEmail 			= $email;
		$site_url           = site_url();
		$apis               = "";
		$plugin_version     = MINIORANGE_API_AUTHENTICATION_VERSION;


		if ( ! empty( $last_requested_api ) ) {
			foreach( $last_requested_api as $api => $method ){
				$apis .= $method." ".$api.'<br>';
			}
		}
		global $user;
		$user         = wp_get_current_user();
		$query        = '[WP REST API Authentication: '.$plugin_version .'] : ' . $message;

		$content='<div >Hello, <br><br>First Name :'.$user->user_firstname.'<br><br>Last  Name :'.$user->user_lastname.'   <br><br>Company :<a href="'.$site_url.'" target="_blank" >'.$site_url.'</a><br><br>Phone Number :'.$phone.'<br><br>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>'.$reply.'<br><br>Query :'.$query.'</div>';
		$content .= "<br><br>Config String:<br><pre style=\"border:1px solid #444;padding:10px;\"><code>" . $config_to_send . "<br><br>APIs :<br>". $apis . "</code></pre>";

		$fields = array(
			'customerKey'	=> $customerKey,
			'sendEmail' 	=> true,
			'email' 		=> array(
				'customerKey' 	=> $customerKey,
				'fromEmail' 	=> $fromEmail,
				'bccEmail' 		=> 'apisupport@xecurify.com',
				'fromName' 		=> 'miniOrange',
				'toEmail' 		=> 'apisupport@xecurify.com',
				'toName' 		=> 'apisupport@xecurify.com',
				'subject' 		=> $subject,
				'content' 		=> $content
			),
		);
		$field_string = json_encode($fields);
		$headers = array( 'Content-Type' => 'application/json');
		$headers['Customer-Key'] = $customerKey;
		$headers['Timestamp'] = $currentTimeInMillis;
		$headers['Authorization'] = $hashValue;
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,

		);
		
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html( $error_message );
			exit();
		}

		return true;
	}

	function mo_api_auth_send_demo_alert($email,$demo_plan,$message,$subject) {

		if(!$this->mo_api_authentication_check_internet_connection())
			return;
		$url = get_option( 'host_name' ) . '/moas/api/notify/send';
		
		$customerKey = $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$currentTimeInMillis = self::get_timestamp();
		$stringToHash 		= $customerKey .  $currentTimeInMillis . $apiKey;
		$hashValue 			= hash("sha512", $stringToHash);
		$customerKeyHeader 	= "Customer-Key: " . $customerKey;
		$timestampHeader 	= "Timestamp: " .  $currentTimeInMillis;
		$authorizationHeader= "Authorization: " . $hashValue;
		$fromEmail 			= $email;
		$site_url=site_url();

		global $user;
		$user         = wp_get_current_user();

		$content='<div >Hello, </a><br><br><b>Email :</b><a href="mailto:'. $fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br><b>Requested Demo for :</b> ' . $demo_plan . '<br><br><b>Requirements (Usecase) :</b> ' . $message.'</div>';

		$fields = array(
			'customerKey'	=> $customerKey,
			'sendEmail' 	=> true,
			'email' 		=> array(
				'customerKey' 	=> $customerKey,
				'fromEmail' 	=> $fromEmail,
				'bccEmail' 		=> 'apisupport@xecurify.com',
				'fromName' 		=> 'miniOrange',
				'toEmail' 		=> 'apisupport@xecurify.com',
				'toName' 		=> 'apisupport@xecurify.com',
				'subject' 		=> $subject,
				'content' 		=> $content
			),
		);
		$field_string = json_encode($fields);
		$headers = array( 'Content-Type' => 'application/json');
		$headers['Customer-Key'] = $customerKey;
		$headers['Timestamp'] = $currentTimeInMillis;
		$headers['Authorization'] = $hashValue;
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => $headers,

		);
		
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html( $error_message );
			exit();
		}
	}

	function mo_api_authentication_check_internet_connection() {
		return (bool) @fsockopen('login.xecurify.com', 443, $iErrno, $sErrStr, 5);
	}
}