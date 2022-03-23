<?php


function mo_api_authentication_register_ui() {
	update_option ( 'mo_api_authentication_new_registration', 'true' );
	$current_user = wp_get_current_user();
	?>
			<!--Register with miniOrange-->
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_api_authentication_register_customer" />
			<?php wp_nonce_field('mo_api_authentication_register_form','mo_api_authentication_register_form_fields'); ?>

			<div id="mo_api_authentication_advanced_setting_layout" class="mo_api_authentication_support_layout">

			<h2 style="font-size: 20px;font-weight: 700">Account Setup With miniOrange</h2>
			
			<p style="font-size: 14px;font-weight: 400;padding-right: 10px">You should register so that in case you need help, we can help you with step by step instructions.
	                        <b>You will also need a miniOrange account to upgrade to the premium version of the plugins.</b> We do not store any information except the email that you will use to register with us.</p>
			<br>

			<div class="mo_api_authentication_support_layout" style="padding-left: 5px;width: 90%">

			<table width="80%">
				
				<tr class="hidden">
						<td><b><font color="#FF0000">*</font>Website/Company Name:</b></td>
						<td><input class="" type="text" name="company"
						required placeholder="Enter website or company name"
						value="<?php echo esc_attr( $_SERVER['SERVER_NAME'] ); ?>"/></td>
					</tr>
					<tr  class="hidden">
						<td><b>&nbsp;&nbsp;First Name:</b></td>
						<td><input class="" type="text" name="fname"
						placeholder="Enter first name" value="<?php echo esc_attr( $current_user->user_firstname );?>" /></td>
					</tr>
					<tr class="hidden">
						<td><b>&nbsp;&nbsp;Last Name:</b></td>
						<td><input class="" type="text" name="lname"
						placeholder="Enter last name" value="<?php echo esc_attr( $current_user->user_lastname );?>" /></td>
					</tr>

					<tr  class="hidden">
						<td><b>&nbsp;&nbsp;Phone number :</b></td>
						 <td><input class="" type="text" name="phone" pattern="[\+]?([0-9]{1,4})?\s?([0-9]{7,12})?" id="phone" title="Phone with country code eg. +1xxxxxxxxxx" placeholder="Phone with country code eg. +1xxxxxxxxxx" value="<?php echo esc_attr( get_option('mo_api_authentication_admin_phone') );?>" />
						 This is an optional field. We will contact you only if you need support.</td>
						</tr>
					</tr>
					<tr  class="hidden">
						<td></td>
						<td>We will call only if you need support.</td>
					</tr>

					<tr>
						<td>
							<p style="font-size: 15px;font-weight: 500;margin-left: 20px;"><b><font color="#FF0000">*</font></b>Email : </p>
						</td>
						<td>
							<p><input required type="text" style="width: 80%" name="email" placeholder="person@example.com" value="<?php echo esc_attr( get_option('mo_api_authentication_admin_email'));?>">
								
							</p>
						</td>
					</tr>

					<tr>
						<td>
							<p style="font-size: 15px;font-weight: 500;margin-left: 20px;"><b><font color="#FF0000">*</font></b>Password : </p>
						</td>
						<td>
							<p><input style="width: 80%" required type="password"
									name="password" style="width:100%" placeholder="Choose your password (Min. length 8)" />
								
							</p>
						</td>
					</tr>
					<tr>
						<td>
							<p style="font-size: 15px;font-weight: 500;margin-left: 20px"><b><font color="#FF0000">*</font></b>Confirm Password : </p>
						</td>
						<td>
							<p><input style="width: 80%" required type="password"
									name="confirmPassword" style="width:100%" placeholder="Confirm your password">
								
							</p>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="submit" name="submit" value="Register" class="button button-primary button-large" style="width:80px;background: #473970" />&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="button" name="mo_api_authentication_goto_login" id="mo_api_authentication_goto_login" value="Already have an account?" class="button button-primary button-large" style="width:180px;background: #473970" />
						</td>
					</tr>
				</table>
				<br>
			</div>
			<br>&nbsp;&nbsp;
			<br>
			
			<br><br>
		</div>
	</form>
	<form name="f1" method="post" action="" id="mo_api_authentication_goto_login_form">
				<?php wp_nonce_field('mo_api_authentication_goto_login','mo_api_authentication_goto_login_fields'); ?>			
                <input type="hidden" name="option" value="mo_api_authentication_goto_login"/>
            </form>
	</div>
            <script>
            	jQuery("#phone").intlTelInput();
                jQuery('#mo_api_authentication_goto_login').click(function () {
                    jQuery('#mo_api_authentication_goto_login_form').submit();
                } );
            </script>
		<?php
}

function mo_api_authentication_show_customer_info() {
	?>

	<div id="mo_api_authentication_advanced_setting_layout" class="mo_api_authentication_support_layout">

		<h2 style="font-size: 20px;font-weight: 700">miniOrange Account Information</h2>
		<br>
		<div class="mo_api_authentication_support_layout" style="padding-left: 5px;width: 90%">
			<table width="50%">
				<tr>
					<td>
						<p style="font-size: 15px;font-weight: 500;margin-left: 20px">Account Email : </p>
					</td>
					<td>
						<p><?php echo esc_html( get_option( 'mo_api_authentication_admin_email' ) ); ?></p>
					</td>
				</tr>
				<tr>
					<td>
						<p style="font-size: 15px;font-weight: 500;margin-left: 20px">Customer ID : </p>
					</td>
					<td>
						<p><?php echo esc_html( get_option( 'mo_api_authentication_admin_customer_key' ) ); ?></p>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><form name="f1" method="post" action="">
				<?php wp_nonce_field('mo_api_authentication_change_email_address_form','mo_api_authentication_change_email_address_form_fields'); ?>
				<input type="hidden" value="mo_api_authentication_change_email_address" name="option"/>
				<input type="submit" value="Change Account" class="button button-primary button-large" style="width:120px;background: #473970" />
			</form></td>
				</tr>
			</table>
			<br>

		</div>
		<br>

		<br>
		<br>
	</div>

	<?php
}