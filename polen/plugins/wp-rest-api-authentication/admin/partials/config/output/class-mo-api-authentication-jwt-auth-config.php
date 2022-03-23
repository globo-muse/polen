<?php 

class Mo_API_Authentication_Admin_Jwt_Auth_Config {

	public static function mo_api_auth_configuration_output() {
		?>
	<div id="mo_api_jwt_authentication_support_layout" class="mo_api_authentication_support_layout">
	
	<form>					
		<input type="hidden" name="action" id="mo_api_jwtauth_save_config_input" value="Save JWT Auth">
		<div id="mo_api_authentication_support_basicoauth" class="mo_api_authentication_common_div_css">

			<button type="button" onclick="moJWTAuthenticationMethodSave('save_jwt_auth')" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;">Next</button>
			<a href="admin.php?page=mo_api_authentication_settings"><button type="button" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;margin-right: 15px;">Back</button></a>
			<h4><a href="admin.php?page=mo_api_authentication_settings&tab=config" style="text-decoration: none">Configure Methods</a> > JWT Authentication Method</h4>
			<h2 class="mo_api_authentication_method_head">JWT Authentication Method</h2>
			
			<p class="mo_api_authentication_method_description">WordPress REST API - JWT Authentication Method involves the REST APIs access on validation against the JWT token (JSON Web Token) generated based on the user’s username, password using highly secure encryption algorithm.</p>
			<br>
			<div class="mo_api_auth_setup_guide2">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/youtube.png" height="25px" width="25px"></div>
				<a href="https://www.youtube.com/watch?v=XlbSVHR7ohQ" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Video guide</b></p></div></a>
			</div>
			<div class="mo_api_auth_setup_guide">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/user-guide.png" height="25px" width="25px"></div>
				<a href="https://plugins.miniorange.com/wordpress-rest-api-jwt-authentication-method#step_1" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Setup Guide</p></div></a>
			</div>
			<div class="mo_api_auth_setup_guide2">
				<div class="mo_api_auth_setup_guide1"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/document.png" height="25px" width="25px"></div>
				<a href="https://developers.miniorange.com/docs/rest-api-authentication/wordpress/jwt-authentication" target="_blank"><div class="mo_api_authentication_guide1"><p style="font-weight: 700;">Developer Doc</b></p></div></a>
			</div>
			<br><br>	
			<div class="mo_api_authentication_support_layout" style="border-width: 0px;padding-left: 2px">
				<br>
				<h3 style="margin-top: 40px">Select JWT Token generation types -</h3>
				<p><b>Tip: </b>With the current plan of the plugin, by default HS256 Encryption algorithm is configured.</p>
				<br>
				<div class="mo_api_authentication_card_layout_internal">

					<div class="mo_api_flex_child1" id=mo_api_config_bauth>
						
						<div style="height: 30%">
							<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/select-all.png" height="25px" width="25px" style="float: right;padding-top: 0px;padding-right: 5px;">
						<div style="width: 20%;float: left;padding-top: 25px;padding-left: 80px;">
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/key.png" height="30px" width="30px"></div>
						</div>
						<div style="height: 60%;width: 80%;text-align: center;padding-top: 10%">
							<p style="font-size: 15px;font-weight: 500">JWT generation using HS256 Encryption</p>
						</div>
						
					</div>
					<div class="mo_api_flex_child1" style="cursor:no-drop;">
						<div style="height: 30%">
							<div class="mo_api_auth_premium_label_jwt">
								<div class="mo_api_auth_premium_label_internal_jwt">
									<div class="mo_api_auth_premium_label_text_jwt">Premium</div>
								</div>
							</div>
							<div style="width: 20%;float: left;padding-top: 25px;padding-left: 80px;">
								<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>/images/secure.png" height="30px" width="30px">
							</div>
						</div>
						<div style="height: 60%;width: 80%;text-align: center;padding-top: 10%">
							<p style="font-size: 15px;font-weight: 500">JWT generation using RS256 Encryption</p>
						</div>
					</div>
				</div>
				<br>
				
				<br>
				
				<div style="display: flex;">
					<div style="float: left"><h3>Signing Key/Certificate Configuration - </h3></div>
					<div style="float: left;margin: 10px;">
					<div class="mo_api_auth_inner_premium_label_jwt"><p style="font-size: 0.8em;">Premium</p></div>
				 	</div>
				 </div>
			 <p><b>Tip: </b>With the current plan of the plugin, by default a randomly generated secret key will be used.</p>
				<br>
				<div style="cursor:no-drop;">
					<textarea type="textbox" placeholder="Configure your certificate or secret key" disabled style="width: 70%;height: 100px;"></textarea>
				</div>
		
				<br>
				
			</div>
			<br>

		</div>
	</form>
</div>
<div class="mo_api_authentication_support_layout" id="mo_api_jwtauth_finish" style="display: none;margin-left: 20px;">

	<form method="post" id="mo-api-jwt-authentication-method-form" action="">
					<input required type="hidden" name="option" value="mo_api_jwt_authentication_config_form" />
					<?php wp_nonce_field("mo_api_jwt_authentication_method_config","mo_api_jwt_authentication_method_config_fields"); ?>	

			<div id="mo_api_basicauth_client_creds" style="margin-left: 20px;">
				<button type="button" onclick="moJWTAuthenticationMethodFinish()" class="mo_api_authentication_method_save_button2 button button-primary button-large" style="background: #473970;margin-right: 10px;">Finish</button>
				<a href="admin.php?page=mo_api_authentication_settings"><button type="button" class="mo_api_authentication_method_save_button button button-primary button-large" style="background: #473970;margin-right: 15px;">Back</button></a>
				<b><p><a href="admin.php?page=mo_api_authentication_settings&tab=config" style="text-decoration: none">Configure Methods</a> > JWT Authentication Method</p></b>
			<h2 style="font-size: 22px;">Configuration Overview</h2>
				<br>
				<div class="mo_api_authentication_support_layout" style="width: 80%;">
					<br>
				
					<table width="80%">
						<tr>
							<td>
								<p style="font-size: 15px;">JWT Token Generation Algorithm :</p>
							</td>
							<td>
								<p style="font-size: 15px;font-weight: 500">HS256
								</p>
							</td>
						</tr>
						<tr>
							<td>
								<p style="font-size: 15px;">JWT Token Signing key/certificate :</p>
							</td>
							<td>
								<p style="font-size: 15px;font-weight: 500"><?php
								if(get_option('mo_api_authentication_jwt_client_secret')){
									echo get_option('mo_api_authentication_jwt_client_secret');
								}
								else{
									echo 'sample-certificate';
								}
								?></p>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<br><br>
		</form>
		</div>
	
		<script>
			function moJWTAuthenticationMethodSave(action){
				
				div = document.getElementById('mo_api_jwt_authentication_support_layout');
				div.style.display = "none";
				div2 = document.getElementById('mo_api_jwtauth_finish');
				div2.style.display = "block";
			}

			function moJWTAuthenticationMethodFinish(){
				document.getElementById("mo-api-jwt-authentication-method-form").submit();
			}

		</script>
	<?php }
}