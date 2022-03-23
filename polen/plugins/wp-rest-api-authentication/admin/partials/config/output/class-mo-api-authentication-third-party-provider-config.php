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

class Mo_API_Authentication_Admin_Thirdparty_Auth_Config {
	
	public static function mo_api_auth_configuration_output() {

		?>
		<div id="mo_api_basic_authentication_support_layout" class="mo_api_authentication_support_layout">
		
		<form method="post" id="mo-thirdparty-authentication-method-form" action="">
							

		<input type="hidden" name="action" id="mo_api_thirdpartyauth_save_config_input" value="Save Third-party Auth">
		<div id="mo_api_authentication_support_basicoauth" class="mo_api_authentication_common_div_css">

			<button type="button" style="width:70px;float: right;background: linear-gradient(45deg, #54B6F6, #B608D8)" disabled class="button button-primary button-large">Next</button>
			<a href="admin.php?page=mo_api_authentication_settings"><button type="button" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;margin-right: 15px;">Back</button></a>
			<h4><a href="admin.php?page=mo_api_authentication_settings&tab=config" style="text-decoration: none">Configure Methods</a> > Third-party provider Authentication Method</h4>
		
			<div style="display: flex;">
				<div style="float: left"><h2 class="mo_api_authentication_method_head">Third-party provider Authentication Method</h2></div>
				<div class="mo_api_authentication_premium_methods">
				<div class="mo_api_auth_inner_premium_label"><p>Premium</p></div>
			 	</div>
			</div>	
			<p class="mo_api_authentication_method_description">WordPress REST API Third-party provider Authentication Method involves the REST APIs access on validation against the token provided by Third-party providers like OAuth 2.0, OpenIDConnect, SAML 2.0 etc. The plugin directly validates the token with these providers and based on the response, APIs are allowed to access.</p>
			<br>
			<div class="mo_api_auth_setup_guide">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/user-guide.png" height="25px" width="25px"></div>
				<a href="https://plugins.miniorange.com/wordpress-rest-api-authentication-using-third-party-provider#step_1" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Setup Guide</p></div></a>
			</div>
			<div class="mo_api_auth_setup_guide2">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/document.png" height="25px" width="25px"></div>
				<a href="https://developers.miniorange.com/docs/rest-api-authentication/wordpress/third-party-provider-authentication" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Developer Doc</b></p></div></a>
			</div>

			<br><br>
			<div class="mo_api_authentication_support_layout" style="border-width: 0px;padding-left: 2px">
				<br>
				<h3 style="margin-top: 40px">Select Third-party provider / Federated Identity -</h3>
				<br>
				<div class="mo_api_authentication_card_layout_internal" style="width: 100%">

					<div class="mo_api_flex_child1" style="cursor:no-drop;">
						<div class="mo_rest_tpp_auth_box">	
							<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/oauth.png" height="40px" width="40px">
						</div>
						<div class="mo_rest_tpp_auth_text">
							<p class="mo_rest_tpp_auth_text_p">OAuth 2.0 Provider</p></div>
					</div>
					<div class="mo_api_flex_child1" style="cursor:no-drop;">
						<div class="mo_rest_tpp_auth_box">	
							<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/oidc.png" height="40px" width="40px">
						</div>
						<div class="mo_rest_tpp_auth_text">
							<p class="mo_rest_tpp_auth_text_p">OpenID Connect Provider</p></div>
					</div>
					
					<div class="mo_api_flex_child1" style="cursor:no-drop;">
						<div class="mo_rest_tpp_auth_box">	
							<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/saml.png" height="40px" width="40px">
						</div>
						<div class="mo_rest_tpp_auth_text">
							<p class="mo_rest_tpp_auth_text_p">SAML 2.0 Provider</p></div>
					</div>
					<div class="mo_api_flex_child1" style="cursor:no-drop;">
						<div class="mo_rest_tpp_auth_box">	
							<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/api.png" height="40px" width="40px">
						</div>
						<div class="mo_rest_tpp_auth_text">
							<p class="mo_rest_tpp_auth_text_p">Token via Custom API</p></div>
					</div>
				</div>
				<br>
				
			</div>
			<br>
		

		</div>
	</form>
</div>
		<?php
	}
}