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

require( 'output/class-mo-api-authentication-basic-oauth-config.php' );
require( 'output/class-mo-api-authentication-tokenapi-config.php' );
require( 'output/class-mo-api-authentication-jwt-auth-config.php' );
require( 'output/class-mo-api-authentication-oauth-client-config.php' );
require( 'output/class-mo-api-authentication-third-party-provider-config.php' );

	

class Mo_API_Authentication_Admin_Config {
	
	public static function mo_api_authentication_config() {
		?>
		<div id='mo_api_section_method'>
		<div class="mo_api_authentication_support_layout">
			<p style="font-size: 20px;font-weight: 900">API Authentication Methods Configuration</p>
			<p style="font-size: 15px;font-weight: 500">Select any of the below authentication methods to get started - </p>
			<br><br>
			<div class="mo_api_authentication_card_layout">
				<div class="mo_api_flex_child1" id=mo_api_config_bauth onclick="api_ajax_redir('basic auth')" style="<?php if(get_option('mo_api_authentication_selected_authentication_method') == 'basic_auth'){echo 'box-shadow: 4px 4px 4px 2px rgba(0,0,0,0.1);border: 3px solid #473970';} ?> ">
					
					<div class="mo_api_auth_method_img">
					<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/basic-key.png" height="55em" width="55em"></div>
					<div class="mo_api_auth_div_internal">
					<p class="mo_api_auth_div_nested_internal">BASIC AUTHENTICATION</p></div>
					
				</div>
				<div class="mo_api_flex_child1" onclick="api_ajax_redir('jwt auth')" style="<?php if(get_option('mo_api_authentication_selected_authentication_method') == 'jwt_auth'){echo 'box-shadow: 4px 4px 4px 2px rgba(0,0,0,0.1);border: 3px solid #473970';} ?> ">
					<div class="mo_api_auth_method_img">
					<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/jwt_authentication.png" height="55em" width="55em"></div>
					<div class="mo_api_auth_div_internal">
					<p class="mo_api_auth_div_nested_internal">JWT AUTHENTICATION</p></div>
					
				</div>
			</div>
			<br>
			<div class="mo_api_authentication_card_layout">
				
				<div class="mo_api_flex_child1" onclick="api_ajax_redir('apikey auth')" style="<?php if(get_option('mo_api_authentication_selected_authentication_method') == 'tokenapi'){echo 'box-shadow: 4px 4px 4px 2px rgba(0,0,0,0.1);border: 3px solid #473970';} ?> ">
					<div class="mo_api_auth_method_img">
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/api-key.png" height="55em" width="55em">
					</div>
					<div class="mo_api_auth_div_internal">
						<p class="mo_api_auth_div_nested_internal">API KEY AUTHENTICATION</p>
					</div>
					<div class="mo_api_auth_premium_label_main">
						<div class="mo_api_auth_premium_label_internal">
							<div class="mo_api_auth_premium_label_text">Premium</div>
						</div>
					</div>
				</div>
				<div class="mo_api_flex_child1" onclick="api_ajax_redir('oauth2 auth')">
					<div class="mo_api_auth_method_img">
					<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/oauth_2.png" height="55em" width="55em"></div>
					<div class="mo_api_auth_div_internal">
					<p class="mo_api_auth_div_nested_internal">OAUTH 2.0 AUTHENTICATION</p></div>
					<div class="mo_api_auth_premium_label_main" style='margin-left:-85px'>
						<div class="mo_api_auth_premium_label_internal">
							<div class="mo_api_auth_premium_label_text" >Premium</div>
						</div>
					</div>
					<div class="mo_api_auth_premium_label_main" >
						<div class="mo_api_auth_premium_label_internal">
							<div class="mo_api_auth_premium_label_text" style='margin-left:-16px; background-color: #ffa033'>Most Secure</div>
						</div>
					</div>
					
				</div>
			</div>
			<br>
			<div class="mo_api_authentication_card_layout" onclick="api_ajax_redir('thirdparty auth')">
				
				<div class="mo_api_flex_child1">
					<div class="mo_api_auth_method_img">
					<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/third_party.png" height="55em" width="55em"></div>
					<div class="mo_api_auth_div_internal">
					<p class="mo_api_auth_div_nested_internal">THIRD-PARTY AUTHENTICATION</p></div>
					<div class="mo_api_auth_premium_label_main">
						<div class="mo_api_auth_premium_label_internal">
							<div class="mo_api_auth_premium_label_text">Premium</div>
						</div>
					</div>
				</div>
				<div class="mo_api_flex_child1" style="border: 0px">
					
				</div>
				
			</div>
			<br>
		</div>
		</div>
	
		<div id='mo_api_section_basicauth_method' style="display: none">
			<?php
			Mo_API_Authentication_Admin_Basic_Auth_Config::mo_api_auth_configuration_output();	
			?>
		</div>
		<div id='mo_api_section_jwtauth_method' style="display: none">
			<?php
			Mo_API_Authentication_Admin_Jwt_Auth_Config::mo_api_auth_configuration_output();
			?>
		</div>
		<div id='mo_api_section_apikeyauth_method' style="display: none">
			<?php
			Mo_API_Authentication_Admin_TokenAPI_Config::mo_api_auth_configuration_output();
			?>
		</div>
		<div id='mo_api_section_oauth2auth_method' style="display: none">
			<?php
			Mo_API_Authentication_Admin_OAuth_Client_Config::mo_api_auth_configuration_output();
			?>
		</div>
		<div id='mo_api_section_thirdpartyauth_method' style="display: none">
			<?php
			Mo_API_Authentication_Admin_Thirdparty_Auth_Config::mo_api_auth_configuration_output();
			?>
		</div>
		<div id="mo_api_auth_step_container" style="display: none;padding-top: 200px;">
			<h2 style="text-align: center;color: white;padding-top: 25px;font-size: 20px"><span>&#9751;</span> Configuration Tracker</h2>
			<div class="mo_step_container" style="padding-top: 25px;">
		  <!-- completed -->
		    <div class="step completed">
		      <div class="v-stepper">
		        <div class="circle"></div>
		        <div class="line"></div>
		      </div>

		      <div class="content">
		        Configure Authentication Method
		      </div>
		  </div>
		  <div class="step completed">
		      <div class="v-stepper">
		        <div class="circle"></div>
		        <div class="line"></div>
		      </div>

		      <div id="mo_api_auth_flow_method_name" class="content">
		        Basic Authentication Method Configurations (Pre-Configured)
		      </div>
		  </div>
		  <!-- active -->
		  <div class="step active">
		    <div class="v-stepper">
		      <div class="circle"></div> 
		    </div>

		    <div class="content">
		      Save Configuration and get started
		    </div>
		  </div>
		  
		</div>
		</div>	

		<script>

			function api_ajax_redir(auth_method){

				div = document.getElementById('mo_api_section_method');
				div.style.display = "none";
				if(auth_method == "basic auth"){
					div2 = document.getElementById('mo_api_section_basicauth_method');
					div2.style.display = "block";
					document.getElementById('mo_api_side_bar_content').innerHTML = document.getElementById('mo_api_auth_step_container').innerHTML;
					document.getElementById('mo_api_auth_flow_method_name').innerHTML = 'Basic Authentication Method Configurations (Pre-Configured)';
				}
				else if(auth_method == "jwt auth"){
					div2 = document.getElementById('mo_api_section_jwtauth_method');
					div2.style.display = "block";
					document.getElementById('mo_api_side_bar_content').innerHTML = document.getElementById('mo_api_auth_step_container').innerHTML;
					document.getElementById('mo_api_auth_flow_method_name').innerHTML = 'JWT Authentication Method Configurations (Pre-Configured)';
				}
				else if(auth_method == "apikey auth"){
					div2 = document.getElementById('mo_api_section_apikeyauth_method');
					div2.style.display = "block";
					document.getElementById('mo_api_side_bar_content').innerHTML = document.getElementById('mo_api_auth_step_container').innerHTML;
					document.getElementById('mo_api_auth_flow_method_name').innerHTML = 'API Key Authentication Method Configurations (Pre-Configured)';
				}
				else if(auth_method == "oauth2 auth"){
					div2 = document.getElementById('mo_api_section_oauth2auth_method');
					div2.style.display = "block";
					document.getElementById('mo_api_side_bar_content').innerHTML = document.getElementById('mo_api_auth_step_container').innerHTML;
					document.getElementById('mo_api_auth_flow_method_name').innerHTML = 'OAuth 2.0 Authentication Method Configurations (Pre-Configured)';
				}
				else if(auth_method == "thirdparty auth"){
					div2 = document.getElementById('mo_api_section_thirdpartyauth_method');
					div2.style.display = "block";
					document.getElementById('mo_api_side_bar_content').innerHTML = document.getElementById('mo_api_auth_step_container').innerHTML;
					document.getElementById('mo_api_auth_flow_method_name').innerHTML = '3rd Party Authentication Method Configurations (Pre-Configured)';
				}
			}

		</script>

		<?php
	}

}