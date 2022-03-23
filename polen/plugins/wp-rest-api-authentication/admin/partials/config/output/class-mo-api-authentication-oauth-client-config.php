<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       miniorange
 * @since      1.0.0
 *
 * @package    Miniorange_api_authentication
 * @subpackage Miniorange_api_authentication/admin/partials
 */

class Mo_API_Authentication_Admin_OAuth_Client_Config {

	public static function mo_api_auth_configuration_output() {
		?>
	<div id="mo_api_oauth_authentication_support_layout" class="mo_api_authentication_support_layout">
		
		<form>
				
		<input type="hidden" name="action" id="mo_api_oauth2auth_save_config_input" value="Save OAuth2 Auth">
		<div id="mo_api_authentication_support_basicoauth" class="mo_api_authentication_common_div_css">

			<button type="button" disabled class="mo_api_authentication_method_save_button button button-primary button-large">Next</button>
			<a href="admin.php?page=mo_api_authentication_settings"><button type="button" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;margin-right: 15px;">Back</button></a>
			<h4><a href="admin.php?page=mo_api_authentication_settings&tab=config" style="text-decoration: none">Configure Methods</a> > OAuth 2.0 Authentication Method</h4>
			<div style="display: flex;">
				<div style="float: left"><h2 class="mo_api_authentication_method_head">OAuth 2.0 Authentication Method</h2></div>
				<div class="mo_api_authentication_premium_methods">
					<div class="mo_api_auth_inner_premium_label"><p>Premium</p></div>
			 	</div>
			 	<div class="mo_api_authentication_premium_methods">
					<div class="mo_api_auth_inner_premium_label_special"><p>Most Secure</p></div>
			 	</div>
			</div>	
			<p class="mo_api_authentication_method_description">WordPress REST API OAuth 2.0 Authentication Method involves the REST APIs access on validation against the access token/JWT token (JSON Web Token) generated based on the user or client credentials using highly secure encryption algorithm. It follows the standards of OAuth 2.0 protocol.</p>
			<br>
			<div class="mo_api_auth_setup_guide">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/user-guide.png" height="25px" width="25px"></div>
				<a href="https://plugins.miniorange.com/wordpress-rest-api-oauth-2-0-authentication-method#step_1" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Setup Guide</p></div></a>
			</div>
			<div class="mo_api_auth_setup_guide2">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/document.png" height="25px" width="25px"></div>
				<a href="https://developers.miniorange.com/docs/rest-api-authentication/wordpress/oauth-authentication" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Developer Doc</b></p></div></a>
			</div>
			<br><br>
			<div class="mo_api_authentication_support_layout" style="border-width: 0px;padding-left: 2px;">
				<br>
				<h3 style="margin-top: 40px">Select OAuth 2.0 Grant Type -</h3>
				<br>
				<div class="mo_api_authentication_card_layout_internal" style="width: 100%">
					<div class="mo_api_flex_child1" id="mo_api_config_bauth" style="cursor:no-drop;">
						
						<div style="height: 30%">
							<div class="mo_api_oauth_internal1">
								<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/guarantee.png" height="30px" width="30px">
							</div>
						</div>
						<div class="mo_api_oauth_internal2">
							<p class="mo_api_oauth_internal_text">Password Grant with Access Token</p>
						</div>
						
					</div>
					<div class="mo_api_flex_child1" style="cursor:no-drop;">
						<div style="height: 30%">
					
						<div class="mo_api_oauth_internal1">
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/user-authentication.png" height="30px" width="30px"></div>
						</div>
						<div class="mo_api_oauth_internal2">
							<p class="mo_api_oauth_internal_text">Password Grant with JWT Token</p>
						</div>
					</div>
					<div class="mo_api_flex_child1" style="cursor:no-drop;">
						<div style="height: 30%">
					
						<div class="mo_api_oauth_internal1">
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/secure.png" height="30px" width="30px"></div>
						</div>
						<div class="mo_api_oauth_internal2">
							<p class="mo_api_oauth_internal_text">Client Credentials with Access Token</p></div>
						</div>
					</div>
				</div>
				<br>
				Note: The custom/3rd party plugin endpoints access can still be blocked or allowed to be accessed publicly with this plan of the plugin.				<br>
				<div class="mo_api_authentication_support_layout" style="width:95%; margin-top: 0px;">
					<p style="font-weight: 500;font-size: 16px;color: black">Additonal Configurations to control OAuth 2.0 - </p>
					<br>
						<input type="checkbox" name="mo_api_oauth_refresh_token" disabled> <b>Refresh Token </b> <small>(Refresh tokens are the credentials to be used to acquire new access tokens to increase session timeout)</small><br><br>
			            <input type="checkbox" name="mo_api_oauth_revoke_token" disabled > <b>Revoke Token </b> <small>(Revoke token request causes the removal of the client permissions associated with the specified token)</small><br><br>
						<input type="checkbox" name="mo_api_oauth_client_secret_validation" disabled> <b>Client Secret Validation </b> <small></small><br><br>
						<input type="checkbox" name="mo_api_oauth_client_cred_validation_refresh_token" disabled> <b>Client Credentials Validation on refresh token </b> <small></small>
					<br>
					<br>
				</div>
				<br>
				<br>
			</div>
			<br>

		</div>
	</form>
</div>
		
	<?php }
}