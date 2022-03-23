<?php
	
	class Mo_API_Authentication_Admin_CustomAPIIntegration {
	
		public static function mo_api_authentication_customintegration() {
			self::custom_api_integration();
		}

		public static function custom_api_integration(){

		?>
			<div id="mo_api_authentication_password_setting_layout" class="mo_api_authentication_support_layout">
				
				<div style="display: flex;">
					<div style="float: left"><h2 style="font-size: 20px;font-weight: 700">Custom/Third Party Plugin API Authentication/Integrations</h2></div>
					<div style="float: left;margin: 10px;">
					<div class="mo_api_auth_inner_premium_label"><p>Premium</p></div>
				 	</div>
				</div>	

				<p style="font-size: 14px;font-weight: 400;padding-right: 10px;">The REST APIs of third-party plugin can be authenticated with the premium plan. Also any third-party application can also be integrated using the plugin via APIs. Contact us at <a href="mailto:apisupport@xecurify.com?subject=WP REST API Authentication Plugin - Enquiry">apisupport@xecurify.com</a> to know more.</p>
				<br>

				<div class="mo_api_authentication_support_layout" style="padding-left: 5px;width: 90%">
					<br>
					<table cellpadding="4" cellspacing="4">
						
                        <tr>
						  	<td><input type="checkbox" disabled></td>
							<td> <img src="<?php echo plugin_dir_url( __FILE__ ).'../../images/woocommerce-circle.png'; ?>" width="50px"> </td>
							<td><h2> WooCommerce  </h2></td>
						</tr>
						<tr>
						  	<td><input type="checkbox" disabled></td>
							<td> <img src="<?php echo plugin_dir_url( __FILE__ ).'../../images/buddypress.png'; ?>" width="50px"> </td>
							<td><h2> BuddyPress </h2></td>
						</tr>
						<tr>
						  	<td><input type="checkbox" disabled></td>
							<td> <img src="<?php echo plugin_dir_url( __FILE__ ).'../../images/gravityform.jpg'; ?>" width="50px"> </td>
							<td><h2> Gravity Form </h2></td>
						</tr>
						<tr>
						  	<td><input type="checkbox" disabled></td>
							<td> <img src="<?php echo plugin_dir_url( __FILE__ ).'../../images/learndash.png'; ?>" width="50px"> </td>
							<td><h2> Learndash API Endpoints </h2></td>
						</tr>
						<tr>
						  	<td><input type="checkbox" disabled></td>
							<td> <img src="<?php echo plugin_dir_url( __FILE__ ).'../../images/cocart-icon.png'; ?>" width="50px"> </td>
							<td><h2> Cocart API Endpoints </h2></td>
						</tr>
						<tr>
						  	<td><input type="checkbox" disabled></td>
							<td> <img src="<?php echo plugin_dir_url( __FILE__ ).'../../images/logo.png'; ?>" width="50px"> </td>
							<td><h2> Custom Built REST Endpoints in WordPress</h2></td>
						</tr>
			    	</table>
			    	<br>
				</div>
				<br>
			</div>
			
		<?php
		}
	}