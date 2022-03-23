<?php
	
	class Mo_API_Authentication_Admin_AdvancedSettings {
	
		public static function mo_api_authentication_advancedsettings() {
			?>

		<div id="mo_api_authentication_advanced_setting_layout" class="mo_api_authentication_support_layout">

			<div style="display: flex;">
					<div style="float: left"><h2 style="font-size: 20px;font-weight: 700">Advanced Security Settings</h2></div>
					<div style="float: left;margin: 10px;">
					<div class="mo_api_auth_inner_premium_label"><p>Premium</p></div>
				 	</div>
				</div>	
			<p style="font-size: 14px;font-weight: 400;margin-right: 10px">This section consists of advanced settings that can be used on the top of the authentication method configuration to provide more control over security.</p>
			<br>
			<div class="mo_api_authentication_support_layout" id="mo_api_jwtauth_client_creds" style="margin-left: 5px; margin-top: 5px; width: 90%">
				<br>
				<h2 style="font-size: 18px;">Custom Header Configuration</h2>
					<table width="100%">
						<tr>
							<td>
								<p style="font-size: 15px;font-weight: 500"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/heading.png" height="20px" width="20px;margin-top:10px;margin-right:10px;">&nbsp; Custom Header : </p>
							</td>
							<td>
								<p><input type="text" name="" style="width: 80%" value="Authorization" disabled>
									<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/write.png" height="25px" width="25px;margin-top:10px;">
								</p>
							</td>
						</tr>
					</table>
				<br>
				<p><b>Tip:</b> If you want to authenticate the WordPress REST APIs in a more secure way, you can set a custom Header.</p>
				<br>
			</div>
			<br>
			<div class="mo_api_authentication_support_layout" id="mo_api_jwtauth_client_creds" style="margin-left: 5px; margin-top: 5px; width: 90%">
				<br>
				<h2 style="font-size: 18px;">Custom Token Expiration Configuration</h2>
					<table width="100%">
						<tr>
							<td style="width: 40%">
								<p style="font-size: 14px;font-weight: 500"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/hourglass.png" height="20px" width="20px;margin-top:10px;margin-right:10px">&nbsp;Access Token Expiry Time <span style="font-size: 12px">(in minutes)</span> : </p>
							</td>
							<td>
								<p><input type="text" name="" style="width: 80%" value="60" disabled>
									<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/write.png" height="25px" width="25px;margin-top:10px;">
								</p>
							</td>
						</tr>
						<tr>
							<td style="width: 40%">
								<p style="font-size: 14px;font-weight: 500"><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/hourglass.png" height="20px" width="20px;margin-top:10px;margin-right:10px">&nbsp;Refresh Token Expiry Time <span style="font-size: 12px"><select readonly style="width: 24%"><option selected>days</option><option>hours</option></select></span> : </p>
							</td>
							<td>
								<p><input type="text" name="" style="width: 80%" value="14" disabled>
									<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/write.png" height="25px" width="25px;margin-top:10px;">
								</p>
							</td>
						</tr>
					</table>
				<br>
				<p><b>Tip:</b> JWT Token and the OAuth 2.0 Access Token will be expired on the given time.</p>
				<br>
			</div>
			<br>
			<div class="mo_api_authentication_support_layout" id="mo_api_jwtauth_client_creds" style="margin-left: 5px; margin-top: 5px; width: 90%">
				<br>
				<h2 style="font-size: 18px;">Role Based Access Restriction Configuration</h2>
				<table cellpadding="4" cellspacing="4">
				<?php
					$base_roles = array_keys(get_editable_roles());
					foreach ($base_roles as $key ) {
						echo '<tr><td><input type="checkbox" disabled checked></td><td>'.ucfirst($key).'</td></tr>';
					}
				 ?>
				</table>
				<br>
				<p><b>Tip:</b> User having below roles can access REST APIs of <b><?php echo esc_url( site_url() );?></b> site.</p>
				<br>
			</div>
			<br>
			<div class="mo_api_authentication_support_layout" id="mo_api_jwtauth_client_creds" style="margin-left: 5px; margin-top: 5px; width: 90%">
				<br>
				<h2 style="font-size: 18px;">Exclude REST API Configuration</h2>
					<p><input type="text" name="" style="width: 50%" placeholder="Enter the REST API patterns here" disabled>
						<img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(__FILE__))));?>/images/more.png" height="22px" width="22px" style="align-items: center;">
					</p>
				<br>
				<p><b>Tip:</b> Given APIs will be publicly accessible from the all users.</p>
				<br>
			</div>
			<br><br>
		</div>
	</form>
	</div>
	<?php
			
		}

	}