<?php 

function mo_api_authentication_verify_password_ui() {
		?>
	<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_api_authentication_verify_customer" />
			<?php wp_nonce_field('mo_api_authentication_verify_customer_form','mo_api_authentication_verify_customer_form_fields'); ?>
	<div id="mo_api_authentication_password_setting_layout" class="mo_api_authentication_support_layout">
		<h2 style="font-size: 20px;font-weight: 700">Login With miniOrange</h2>
		<p style="font-size: 14px;font-weight: 400">Enter your miniOrange login credentials to log into the plugin.</p>
		<br>
		
		<div class="mo_api_authentication_support_layout" style="padding-left: 5px;width: 90%">
			<br>
		<table width="90%">
			<tr>
				<td>
					<p style="font-size: 15px;font-weight: 500;margin-left: 20px"><b><font color="#FF0000">*</font></b>Email : </p>
				</td>
				<td>
					<p><input required type="text" style="width: 70%" name="email" placeholder="person@example.com" value="<?php echo esc_attr( get_option('mo_api_authentication_admin_email'));?>">
				</td>
			</tr>
			<tr>
				<td>
					<p style="font-size: 15px;font-weight: 500;margin-left: 20px"><b><font color="#FF0000">*</font></b>Password : </p>
				</td>
				<td>
					<p><input style="width: 70%" required type="password" name="password" placeholder="Choose your password (Min. length 8)" /></p>
				</td>
			</tr>
			<tr>
				<td>
					
				</td>
				<td>
					<p> <a href="#mo_api_authentication_forgot_password_link">Click here if you forgot your password?</a></p>
				</td>
			</tr>
			<tr>
							<td>&nbsp;</td>
							<td><input type="submit" name="submit" value="Login"
								class="button button-primary button-large" style="background: #473970" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</form>

								<input type="button" name="back-button" id="mo_api_authentication_back_button" onclick="document.getElementById('mo_api_authentication_change_email_form').submit();" value="Back" class="button button-primary button-large" style="background: #473970;" />

								<form id="mo_api_authentication_change_email_form" method="post" action="">
									<?php wp_nonce_field('mo_api_authentication_change_email_address_form','mo_api_authentication_change_email_address_form_fields'); ?>
									<input type="hidden" name="option" value="mo_api_authentication_change_email_address" />
								</form></td>
							</td>
						</tr>
		</table>
		<br>
		
		</div>
	</form>
		<br />
		</div>

		<script>
			jQuery("a[href=\"#mo_api_authentication_forgot_password_link\"]").click(function(){
				window.open('https://login.xecurify.com/moas/idp/resetpassword');
				//jQuery("#mo_api_authentication_forgotpassword_form").submit();
			});
		</script>
		<?php
	}