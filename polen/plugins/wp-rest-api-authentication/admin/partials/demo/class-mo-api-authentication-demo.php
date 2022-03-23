<?php
	
	class Mo_API_Authentication_Admin_RFD {
	
		public static function mo_api_authentication_requestfordemo() {
			self::demo_request();
		}

		public static function demo_request(){
		?>
		<div id="mo_api_authentication_password_setting_layout" class="mo_api_authentication_support_layout">
		<h2 style="font-size: 20px;font-weight: 700">Demo/Trial Request for Premium plans</h2>
		<p style="font-size: 14px;font-weight: 400">Make a request for the demo/trial of the Premium plans of the plugin to try all the features.</p>
		<br>
		<form method="post" action="">
		<div class="mo_api_authentication_support_layout" style="padding-left: 5px;width: 90%">
		<br>
		
		<input type="hidden" name="option" value="mo_api_authentication_demo_request_form" />
		<?php wp_nonce_field('mo_api_authentication_demo_request_form', 'mo_api_authentication_demo_request_field'); ?>

		<table width="90%">
			<tr>
				<td>
					<p style="font-size: 15px;font-weight: 500;margin-left: 20px">Email : </p>
				</td>
				<td>
					<p><input required type="text" style="width: 80%" name="mo_api_authentication_demo_email" placeholder="person@example.com" value="<?php echo esc_attr( get_option("mo_api_authentication_admin_email") ); ?>">
				</td>
			</tr>
			<tr>
				<td>
					<p style="font-size: 15px;font-weight: 500;margin-left: 20px">Select Premium Plan : </p>
				</td>
				<td>
					<p><select required style="width: 80%" name="mo_api_authentication_demo_plan" id="mo_api_authentication_demo_plan_id">
									<option disabled selected>------------------ Select ------------------</option>
									<option value="rest-api-authentication-enterprise@31.0.2">WP API Authentication Enterprise Plugin</option>
									<option value="rest-api-authentication-premium@21.0.2">WP API Authentication Premium Plugin</option>
									<option value="Not Sure">Not Sure</option>
					</select></p>
				</td>
			</tr>
			<tr>
				<td>
					<p style="font-size: 15px;font-weight: 500;margin-left: 20px">Use Case and Requirements : </p>
				</td>
				<td>
					<p>
						<textarea type="text" minlength="15" name="mo_api_authentication_demo_usecase" style="resize: vertical; width:80%; height:100px;" rows="4" placeholder="Write us about your usecase" required value=""></textarea>
					</p>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" name="submit" value="Submit Request" class="button button-primary button-large" style="width:120px;background: #473970" /></td>
			</tr>
		</table>
		<br>
	</div>
	
	<p style="padding-left: 10px;padding-right: 10px;user-select: none"><b>Tip:</b> You will receive the email shortly with the demo details once you successfully make the demo/trial request. If not received, please check out your spam folder or contact us at <a href="mailto:apisupport@xecurify.com?subject=WP REST API Authentication Plugin - Enquiry">apisupport@xecurify.com</a>.</p><br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</form>
		
		</div>
		
		<?php
		}
	}