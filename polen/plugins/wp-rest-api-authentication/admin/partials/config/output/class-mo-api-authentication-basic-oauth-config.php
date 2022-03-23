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

class Mo_API_Authentication_Admin_Basic_Auth_Config {
	
	public static function mo_api_auth_configuration_output() {

		?>
		<div id="mo_api_basic_authentication_support_layout" class="mo_api_authentication_support_layout">				

		<input type="hidden" name="action" id="mo_api_basicauth_save_config_input" value="Save Basic Auth">
		<div id="mo_api_authentication_support_basicoauth" class="mo_api_authentication_common_div_css">

			<button type="button" onclick="moBasicAuthenticationMethodSave('save_basic_auth')" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;">Next</button>
			<a href="admin.php?page=mo_api_authentication_settings"><button type="button" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;margin-right: 15px;">Back</button></a>

			<h4><a href="admin.php?page=mo_api_authentication_settings&tab=config" style="text-decoration: none">Configure Methods</a> > Basic Authentication Method</h4>
			<h2 class="mo_api_authentication_method_head">Basic Authentication Method</h2>
			
			<p class="mo_api_authentication_method_description">WordPress REST API - Basic Authentication Method involves the REST APIs access on validation against the API token generated based on the user’s username, password and on basis of client credentials.</p>
			<br>
			<div class="mo_api_auth_setup_guide2">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/youtube.png" height="25px" width="25px"></div>
				<a href="https://www.youtube.com/watch?v=vwxkpuj7LCw" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Video guide</b></p></div></a>
			</div>
			<div class="mo_api_auth_setup_guide">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/user-guide.png" height="25px" width="25px"></div>
				<a href="https://plugins.miniorange.com/wordpress-rest-api-basic-authentication-method#step_1" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Setup Guide</p></div></a>
			</div>
			<div class="mo_api_auth_setup_guide2">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/document.png" height="25px" width="25px"></div>
				<a href="https://developers.miniorange.com/docs/rest-api-authentication/wordpress/basic-authentication" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Developer Doc</b></p></div></a>
			</div>
			<br><br>
			<div class="mo_api_authentication_support_layout" style="border-width: 0px;padding-left: 2px">
				<br>
				<h3 style="margin-top: 40px">Select One of the below Basic Token generation types - </h3>
				<br>
				<div class="mo_api_authentication_card_layout_internal">

					<div class="mo_api_flex_child1" id="mo_api_config_bauth1" onclick="moBasicAuthenticationClienCreds('uname_pass')">
						<div style="height: 30%">
						<div id="mo_api_basicauth_select_type1" style="<?php if(!get_option('mo_api_authentication_selected_authentication_method') || ( get_option('mo_api_authentication_selected_authentication_method') == 'basic_auth' && get_option('mo_api_authentication_authentication_key') == 'uname_pass') ){ echo 'display:block'; }else{echo 'display:none';}?>"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/select-all.png" height="25px" width="25px" style="float: right;padding-top: 0px;padding-right: 5px;">
						</div>
						<div class="mo_api_bauth_internal_div">
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/guarantee.png" height="30px" width="30px"></div>
						</div>
						<div style="height: 50%;width: 90%;text-align: center;padding-top: 10%">
							<p style="font-size: 15px;font-weight: 500">Username & Password with Base64 Encoding</p>
						</div>
					</div>
					<div class="mo_api_flex_child1" style="cursor:no-drop;">
						<div style="height: 30%">
							<div class="mo_api_auth_premium_label_jwt">
								<div class="mo_api_auth_premium_label_internal_jwt">
									<div class="mo_api_auth_premium_label_text_jwt">Premium</div>
								</div>
							</div>
						<div class="mo_api_bauth_internal_div">
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/user-authentication.png" height="30px" width="30px"></div>
						</div>
						<div style="height: 50%;width: 90%;text-align: center;padding-top: 10%;">
						<p style="font-size: 15px;font-weight: 500">Username & Password with HMAC Validation</p>
						</div>
					
				</div>
			</div>
				<br>

				<?php 

					$basic_auth_client_creds_enable = 0;

					if(get_option('mo_api_authentication_selected_authentication_method') == 'basic_auth' && get_option('mo_api_authentication_authentication_key') == 'cid_secret' ){
						$basic_auth_client_creds_enable = 1;
					 }
					 else{
					 	$basic_auth_client_creds_enable = 0;
					 }
				?>

				<div class="mo_api_authentication_card_layout_internal">
					<div class="mo_api_flex_child1" id="mo_api_config_bauth2" onclick="moBasicAuthenticationClienCreds('cid_secret')" style="<?php if(!$basic_auth_client_creds_enable){echo 'cursor:no-drop';} ?>" >
						<div style="height: 30%">
							<div class="mo_api_auth_premium_label_jwt">
								<div class="mo_api_auth_premium_label_internal_jwt">
									<div class="mo_api_auth_premium_label_text_jwt">Premium</div>
								</div>
							</div>
							<?php if($basic_auth_client_creds_enable){ ?>
						<div id="mo_api_basicauth_select_type2" style=""><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/select-all.png" height="25px" width="25px" style="float: right;padding-top: 0px;padding-right: 5px;"></div>
					<?php } ?>
						<div class="mo_api_bauth_internal_div" >
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/key.png" height="30px" width="30px"></div>
						</div>
						<div class="mo_api_bauth_internal" >
						<p style="font-size: 15px;font-weight: 500">Client ID & Secret with Base64 Encoding</p></div>
						
					</div>
					<div class="mo_api_flex_child1" style="cursor:no-drop;">
						<div style="height: 30%">
							<div class="mo_api_auth_premium_label_jwt">
								<div class="mo_api_auth_premium_label_internal_jwt">
									<div class="mo_api_auth_premium_label_text_jwt">Premium</div>
								</div>
							</div>
						<div class="mo_api_bauth_internal_div">
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/secure.png" height="30px" width="30px"></div>
						</div>
						<div class="mo_api_bauth_internal" style="cursor:no-drop;">
							<p style="font-size: 15px;font-weight: 500">Client ID & Secret with HMAC Validation</p>
						</div>
					</div>
				</div>
				<br>
			</div>
			<br>
			
		</div>
		</div>
	</form>
</div>

<div class="mo_api_authentication_support_layout" id="mo_api_basicauth_finish" style="display: none;margin-left: 20px;">
		<form method="post" id="mo-api-basic-authentication-method-form" action="">
					<input required type="hidden" name="option" value="mo_api_basic_authentication_config_form" />
					<?php wp_nonce_field("mo_api_basic_authentication_method_config","mo_api_basic_authentication_method_config_fields"); ?>	

			<div id="mo_api_basicauth_client_creds" style="margin-left: 20px;">
				<input type="hidden" name="action" id="mo_api_auth_save_config_input" value="Save Configuration">
				<button type="submit" onclick="moBasicAuthenticationMethodFinish()" class="mo_api_authentication_method_save_button2 button button-primary button-large" style="background: #473970;margin-right: 10px;">Finish</button>
				<a href="admin.php?page=mo_api_authentication_settings"><button type="button" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;margin-right: 15px;">Back</button></a>
				<b><p><a href="admin.php?page=mo_api_authentication_settings&tab=config" style="text-decoration: none">Configure Methods</a> > Basic Authentication Method</p></b>
			<h2 style="font-size: 22px;">Configuration Overview</h2>
				<br>
				<div class="mo_api_authentication_support_layout" style="width: 80%;">
					<br>
					
					<table width="80%">
						<tr>
							<td>
								<p style="font-size: 15px;">Token credentials Type :</p>
							</td>
							<td>
								<p id="mo_api_basicauth_token_type" class="mo_api_basicauth_creds_type"></p>
							</td>
						</tr>
						<tr>
							<td>
								<p style="font-size: 15px;">Token Encryption Method :</p>
							</td>
							<td>
								<p class="mo_api_basicauth_creds_type" >Base64 Encoding</p>
							</td>
						</tr>
					</table>

						<div id="mo_api_basic_auth_show_client_creds" style="display: none;">

						<table width="80%">
							<tr>
								<td style="width: 60%">
									<p style="font-size: 15px;">Client ID :</p>
								</td>
								<td >
									<p><input id="mo_api_basicauth_clientid" type="text" value="" disabled></p>
								</td>
							</tr>
							<tr>
								<td>
									<p style="font-size: 15px;">Client Secret :</p>
								</td>
								<td>
									<p><input id="mo_api_basicauth_clientsecret" type="password" value="" disabled></p>
								</td>
							</tr>
							</table>
						</div>
				</div>
			</div>
			<br><br>
		</form>
		</div>
		
		<script>
			function moBasicAuthenticationMethodSave(action){
				
				var data = {
					'action': 'save_temporary_data',
					'auth_method' : 'basic_auth',
					'algo' : 'base64',
					'token_type' : localStorage.getItem('mo_api_basic_token_type')
				};			

				jQuery.post(ajaxurl, data);

				div = document.getElementById('mo_api_basic_authentication_support_layout');
				div.style.display = "none";
				div2 = document.getElementById('mo_api_basicauth_finish');
				div2.style.display = "block";

				if(localStorage.getItem('mo_api_basic_token_type') == 'uname_pass'){
					document.getElementById('mo_api_basicauth_token_type').innerHTML = 'WordPress Username & Password';
				}
				else{
					document.getElementById('mo_api_basicauth_token_type').innerHTML = 'Client ID & Secret';
					div3 = document.getElementById('mo_api_basic_auth_show_client_creds');
					div3.style.display = "block";
				}
			}

			function moBasicAuthenticationMethodFinish(){
				document.getElementById("mo-api-basic-authentication-method-form").submit();
			}

			function moBasicAuthenticationClienCreds(type){
				div = document.getElementById('mo_api_basicauth_select_type1');
				div2 = document.getElementById('mo_api_basicauth_select_type2');

				if(type == 'cid_secret'){
					div.style.display = "none";
					div2.style.display = "block";
				}
				else{
					div.style.display = "block";
					if(div2 != null){
						div2.style.display = "none";
					}
				}

				localStorage.setItem('mo_api_basic_token_type', type);
			}

		</script>
		<?php
	}
}