<?php

require( 'login/register.php' );
require( 'login/verify-password.php' );

class Mo_API_Authentication_Admin_Account {

	public static function verify_password() { 
		mo_api_authentication_verify_password_ui(); 	
	}

	public static function register() {
		if(!mo_api_authentication_is_customer_registered()){
			mo_api_authentication_register_ui();
		} else {
			mo_api_authentication_show_customer_info();
		}
	}	
}