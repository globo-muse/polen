<?php
class Mo_API_Authentication_Basic_OAuth {

	static function mo_api_auth_is_valid_request($headers) {

		if(  ( isset( $headers['AUTHORIZATION'] ) && $headers['AUTHORIZATION'] !== "" ) || ( isset( $headers['AUTHORISATION'] ) && $headers['AUTHORISATION'] !== "" ) ) {

			if ( isset( $headers['AUTHORIZATION'] ) && $headers['AUTHORIZATION'] !== "" ) {
				$authorization_header = explode( " ", $headers['AUTHORIZATION'] );
			} elseif ( isset( $headers['AUTHORISATION'] ) && $headers['AUTHORISATION'] !== "" ) {
				$authorization_header = explode( " ", $headers['AUTHORISATION'] );
			}
			
			if( isset( $authorization_header[0] ) && (strcasecmp( $authorization_header[0], 'Basic' ) == 0 ) && isset( $authorization_header[1] ) && $authorization_header[1] !== "" ) {
				$encoded_creds = $authorization_header[1];
				$decoded_cred_string = base64_decode($encoded_creds);
				$creds = explode(":", $decoded_cred_string);
				if( isset($creds[0]) && isset($creds[1]) ) {
					if( get_option( 'mo_api_authentication_authentication_key' ) == 'uname_pass' ) {
						// username and password
						$uname = $creds[0];
						$pword = $creds[1];
						$user = get_user_by('login', $uname);
						if( $user ) {
							if(wp_check_password( $pword, $user->user_pass, $user->ID )){
								wp_set_current_user($user->ID);
								return true;
							} else {
								$response = array(
									'status' => "error",
									'error' => 'INVALID_PASSWORD',
									'code'  => '400',
									'error_description' => 'Incorrect password.'
								);
								wp_send_json( $response, 400 );
							}
						} else {
							$response = array(
								'status' => "error",
								'error' => 'INVALID_USERNAME',
								'code'  => '400',
								'error_description' => 'Username Does not exist.'
							);
							wp_send_json( $response, 400 );
						}

					} elseif( get_option( 'mo_api_authentication_authentication_key' ) == 'cid_secret' ) {
						// client id and client secret
						if ( get_option( 'mo_api_auth_clientid' ) === $creds[0] && get_option( 'mo_api_auth_clientsecret' ) === $creds[1] ) {
							return true;
						} else {
							$response = array(
								'status' => "error",
								'error'  => 'INVALID_CLIENT_CREDENTIALS',
								'code'   => '400',
								'error_description' => 'Invalid client ID or client sercret.'
							);
							wp_send_json( $response, 400 );
						}
					}
				} else {
					$response = array(
						'status' => "error",
						'error' => 'INVALID_TOKEN_FORMAT',
						'code'  => '401',
						'error_description' => 'Sorry, you are not using correct format to encode string.'
					);
					wp_send_json( $response, 401 );
				}
			} else {
				$response = array(
					'status' => "error",
					'error' => 'INVALID_AUTHORIZATION_HEADER_TOKEN_TYPE',
					'code'  => '401',
					'error_description' => 'Authorization header must be type of Basic Token.'
				);
				wp_send_json( $response, 401 );
			}
		}
		$response = array(
			'status' => "error",
			'error' => 'MISSING_AUTHORIZATION_HEADER',
			'code'  => '401',
			'error_description' => 'Authorization header not received. Either authorization header was not sent or it was removed by your server due to security reasons.'
		);
		wp_send_json( $response, 401 );
	}
}