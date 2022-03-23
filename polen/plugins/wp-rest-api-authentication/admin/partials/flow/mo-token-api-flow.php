<?php
require( 'class-mo-api-authentication-basic-oauth.php' );
require( 'class-mo-api-authentication-tokenapi.php' );
require( 'class-mo-api-authentication-jwt-auth.php' );

function mo_api_auth_user_has_capability() {
	$found = false;
	$user = wp_get_current_user();
	$user_roles = array('author','editor','contributor','subscriber','administrator');
	foreach($user->caps as $caps) 
		$found[$caps] = in_array($caps, $user_roles) ? true : false;
	return $found;
}

function mo_api_auth_token_endpoint_flow( $request ) {
	mo_api_auth_method_get_token( $request );		
}

function mo_api_auth_method_get_token($request) {
	if( isset( $request['username'] ) && isset( $request['password'] ) ) {
		$username   = sanitize_text_field( $request['username'] );
		$password   = sanitize_text_field( $request['password'] );
		$client_secret = sanitize_text_field( get_option('mo_api_authentication_jwt_client_secret') );

		if ( $client_secret === false || $client_secret === "" ) {
			$response = array(
				'status' => "error",
				'error' => 'BAD_REQUEST',
				'code'  => '401',
				'error_description' => 'Sorry, client secret is required to make a request. Contact to your administrator.'
			);
			wp_send_json( $response, 401 );
		}

		$user = get_user_by('login', $username);

		if( $user ) {
			wp_set_current_user($user->ID);
			$valid_pass = wp_check_password( $password, $user->user_pass, $user->ID );
		}

		if( isset($valid_pass) && $valid_pass) {
			$token_data = '';
			$token_data = mo_api_auth_create_jwt_token( $client_secret, $user ); 
			$response   = rest_ensure_response( $token_data ); 
			echo json_encode($token_data); exit;  //return $response;
		} else {
			$response = array(
				'status' => "error",
				'error' => 'INVALID_CREDENTIALS',
				'code'  => '400',
				'error_description' => 'Invalid username or password.'
			);
			wp_send_json( $response, 400 );
		}
	} else {
		$response = array(
			'status' => "error",
			'error' => 'FORBIDDEN',
			'code'  => '403',
			'error_description' => 'Username and password are required.'
		);
		wp_send_json( $response, 403 );
	}
}

function mo_api_auth_create_jwt_token($client_secret, $user) {
	
	$iat          = time();
	$exp          = time() + 157680000; 

	// Create the token header
	$header = json_encode([
	    'alg' => 'HS256',
	    'typ' => 'JWT'
	]);

	// Create the token payload
	$payload = json_encode([
		'sub' => $user->ID,
		'name' => $user->user_login,
		'iat' => $iat,
		'exp' => $exp
	]);

	// Encode Header
	$base64UrlHeader = mo_api_authentication_base64UrlEncode($header);

	// Encode Payload
	$base64UrlPayload = mo_api_authentication_base64UrlEncode($payload);

	// Create Signature Hash
	$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $client_secret, true);

	// Encode Signature to Base64Url String
	$base64UrlSignature = mo_api_authentication_base64UrlEncode($signature);

	// Create JWT
	$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
	
	$token_data = array(
	  'token_type' => 'Bearer',
	  'iat' => $iat,
	  'expires_in' => $exp, 	
	  'jwt_token' => $jwt,	
	);

	return $token_data;
	
}

function mo_api_authentication_base64UrlEncode($text)
{
	return rtrim(strtr(base64_encode($text), '+/', '-_'), '='); 
}

function mo_api_auth_strpos_arr($haystack, $needle) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $what) {
        if(($pos = strpos($haystack, $what))!==false) return $pos;
    }
    return false;
}

function mo_api_auth_restrict_rest_api_for_invalid_users()
{		
		/*if(!get_option( 'mo_api_authentication_protectedrestapi_route_whitelist'))
		{
			mo_api_authentication_reset_api_protection();
		}*/

		
		if( get_option( 'mo_api_authentication_protectedrestapi_route_whitelist') && Miniorange_API_Authentication_Admin::whitelist_routes(true) === true){
			return true;
		}
		Miniorange_API_Authentication_Admin::mo_api_auth_else();		
}


function mo_api_auth_is_valid_request() {
	$response = '';

	$headers = mo_api_auth_getallheaders();
	$headers = array_change_key_case($headers, CASE_UPPER);
	
	// if (stripos(explode('?', sanitize_text_field($_SERVER['REQUEST_URI']), 2)[0], '/wp/v2') === false){
	// 	if(get_option('mo_rest_api_protect_migrate')){
	// 		$response = array(
	// 			'status'	=>	'error',
	// 			'error'		=>  'Restricted',
	// 			'error_description'	=>	'Sorry, you are not allowed to access REST API.'
	// 		);
	// 		wp_send_json($response, 403);
	// 	}
	// 	return true;
	// }

	if( get_option( 'mo_api_authentication_selected_authentication_method' ) == 'basic_auth' ) {
		$response = Mo_API_Authentication_Basic_OAuth::mo_api_auth_is_valid_request($headers);
	} elseif( get_option( 'mo_api_authentication_selected_authentication_method') == 'tokenapi' ) {
		$response = Mo_API_Authentication_TokenAPI::mo_api_auth_is_valid_request($headers);
	} elseif( get_option( 'mo_api_authentication_selected_authentication_method') == 'jwt_auth' ) {	
		$response = Mo_API_Authentication_JWT_Auth::mo_api_auth_is_valid_request($headers);
	}

	return $response;
}

if (!function_exists('mo_api_auth_getallheaders'))
{
    function mo_api_auth_getallheaders()
    {
        $headers = [];
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
}