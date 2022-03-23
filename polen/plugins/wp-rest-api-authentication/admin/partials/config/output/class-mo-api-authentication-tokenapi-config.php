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

class Mo_API_Authentication_Admin_TokenAPI_Config {

	public static function mo_api_auth_configuration_output() {

		$mo_api_key_enable = (get_option('mo_api_authentication_selected_authentication_method') == 'tokenapi') ? 1 : 0;

	?>
	<div id="mo_api_key_authentication_support_layout" class="mo_api_authentication_support_layout">
		<form method="post" id="mo-apikey-authentication-method-form" action="">
						

		<input type="hidden" name="action" id="mo_api_apikeyauth_save_config_input" value="Save APIKey Auth">
		<div id="mo_api_authentication_support_basicoauth" class="mo_api_authentication_common_div_css">

			<button type="button" onclick="moAPIKeyAuthenticationMethodSave('save_apikey_auth')" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;" <?php if(!$mo_api_key_enable){echo 'disabled';} ?> >Next</button>
			<a href="admin.php?page=mo_api_authentication_settings"><button type="button" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;margin-right: 15px;">Back</button></a>
			<h4><a href="admin.php?page=mo_api_authentication_settings&tab=config" style="text-decoration: none">Configure Methods</a> > API Key Authentication Method</h4>
			
			<div style="display: flex;">
				<div style="float: left"><h2 class="mo_api_authentication_method_head">API Key Authentication Method</h2></div>
				<div class="mo_api_authentication_premium_methods">
				<div class="mo_api_auth_inner_premium_label"><p>Premium</p></div>
			 	</div>
			</div>	
			<p class="mo_api_authentication_method_description">WordPress REST API - API Key Authentication Method involves the REST APIs access on validation against the API key/token.</p>
			<br>
			<div class="mo_api_auth_setup_guide2">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/youtube.png" height="25px" width="25px"></div>
				<a href="https://www.youtube.com/watch?v=HdWvlkAdXgA" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Video guide</b></p></div></a>
			</div>
			<div class="mo_api_auth_setup_guide2">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/user-guide.png" height="25px" width="25px"></div>
				<a href="https://plugins.miniorange.com/rest-api-key-authentication-method#step_1" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Setup Guide</p></div></a>
			</div>
			<div class="mo_api_auth_setup_guide2">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/document.png" height="25px" width="25px"></div>
				<a href="https://developers.miniorange.com/docs/rest-api-authentication/wordpress/api-key-authentication" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Developer Doc</b></p></div></a>
			</div>
			<br><br><br>
			<div class="mo_api_authentication_support_layout" style="margin-left: 5px; margin-top: 40px;width: 90%">
				<br>
					<table width="100%">
						<tr>
							<td>
								<p style="font-size: 15px;font-weight: 500"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/universal-key.png" height="25px" width="25px;margin-top:10px;margin-right:10px">&nbsp;Universal API Key : </p>
							</td>
							<td>
								<p><input type="text" name="" style="width: 80%" value="<?php if(get_option('mo_api_auth_bearer_token')){echo get_option('mo_api_auth_bearer_token');}else{echo 'xxxxxxxxxxxxxxxxxxxxxx';} ?>" disabled>
									<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/write.png" height="25px" width="25px;margin-top:10px;">
								</p>
							</td>
						</tr>
					</table>
				<br>
				<p><b>Tip:</b> Universal key can be used to unlock all the WordPress REST API access which does not involves user permissions. You can create the key for any user from the above dropdown.</p>
				<br>
			</div>
			<br>
			<div class="mo_api_authentication_support_layout" style="margin-left: 5px;margin-top:20px; width: 90%">
				<br>
					<table width="100%">
						<tr>
							<td style="width: 42%">
								<p style="font-size: 15px;font-weight: 500"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/user-based-login.png" height="25px" width="25px;margin-top:10px;"> &nbsp; User-specific API Key : </p>
							</td>
							<td style="width: 58%">
								<?php $users = get_users();?>
								<p>
						<select readonly style="width:80%;margin-top:5px">
						$count = 0;
						<?php foreach($users as $user){
							$count++;
							if($count==11){
								break;
							}
						?>
							<option><?php echo esc_attr( $user->user_login ); ?></option>
						<?php
						} ?>
						</select>
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/write.png" height="25px" width="25px;"></p>
							</td>
						</tr>
					</table>
				<br>
				<p><b>Tip:</b> User specific key can be used to unlock all the WordPress REST API access including the ones that involves user permissions.</p>
				<br>
			</div>
			<br>
		</div>
	</form>
	</div>
<div class="mo_api_authentication_support_layout" id="mo_api_keyauth_finish" style="display: none;margin-left: 20px;">

	<form method="post" id="mo-api-key-authentication-method-form" action="">
					<input required type="hidden" name="option" value="mo_api_key_authentication_config_form" />
					<?php wp_nonce_field("mo_api_key_authentication_method_config","mo_api_key_authentication_method_config_fields"); ?>	

			<div id="mo_api_basicauth_client_creds" style="margin-left: 20px;">
				<button type="button" onclick="moAPIKeyAuthenticationMethodFinish()" class="mo_api_authentication_method_save_button2 button button-primary button-large" style="background: #473970;margin-right: 10px;">Finish</button>
				<a href="admin.php?page=mo_api_authentication_settings"><button type="button" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;margin-right: 15px;">Back</button></a>
				<b><p><a href="admin.php?page=mo_api_authentication_settings&tab=config" style="text-decoration: none">Configure Methods</a> > API Key Authentication Method</p></b>
			<h2 style="font-size: 22px;">Configuration Overview</h2>
				<br>
				<div class="mo_api_authentication_support_layout" style="width: 80%;">
					<br>
				
					<table width="80%">
						<tr>
							<td>
								<p style="font-size: 15px;"> Universal API Key :</p>
							</td>
							<td>
								<p style="font-size: 15px;font-weight: 500"><?php if(get_option('mo_api_auth_bearer_token')){echo get_option('mo_api_auth_bearer_token');}else{echo 'universal-api-key';} ?>
								</p>
							</td>
						</tr>
					</table>
					<p style="padding-right: 10px;"><b>Tip : </b>Please keep this API key securely and do not share it. In case if it is compromised, you can always regenerate it.</p>
				</div>
			</div>
			<br><br>
		</div>
	
		<script>
			function moAPIKeyAuthenticationMethodSave(action){
				
				div = document.getElementById('mo_api_key_authentication_support_layout');
				div.style.display = "none";
				div2 = document.getElementById('mo_api_keyauth_finish');
				div2.style.display = "block";
			}

			function moAPIKeyAuthenticationMethodFinish(){
				document.getElementById("mo-api-key-authentication-method-form").submit();
			}

		</script>
	<?php }
}