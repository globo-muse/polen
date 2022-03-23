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

class Mo_API_Authentication_Admin_Support {
	
	public static function mo_api_authentication_support(){
	?>
		<div id="mo_api_authentication_support_layout" class="mo_api_authentication_support_layout" style="background: linear-gradient(to right, #09B9CE, #3C79DA, #7039E5)">
			<div style="padding: 0 5px 5px 5px 5px">
				<img src="<?php echo esc_url(plugin_dir_url(dirname(__DIR__)).'images/mologo.png');?>" height="45px" width="45px" style="float: right;padding-right: 10px;">
				<h2 class="mo_api_authentication_adv">Unlock More</h2>
				<h2 class="mo_api_authentication_adv">Security Features</h2>
				<p class="mo_api_authentication_adv_internal">Starting at  <span class="mo_api_authentication_adv_span">$149*</span></p>
				<hr class="mo_api_authentication_adv_hr">
				<p class="mo_api_authentication_adv_internal2"><a href="<?php echo site_url();?>/wp-admin/admin.php?page=mo_api_authentication_settings&tab=licensing"><button type="button" style="width:140px;height: 40px;background: #473970;color: white" class="button button-primary button-large">Go Premium Now</button></a></p>
			</div>
		</div>
	<?php
	}

	public static function mo_api_authentication_advertise(){
		?>
		<div id="mo_api_authentication_support_layout" class="mo_api_authentication_support_layout" style="padding-top: 20px; margin-top: 0px">
			<div style="padding: 0 5px 5px 5px 5px;">
				
				<div style="display: flex;width: 100%;height: 65px">
					<div style="flex: 1;width: 19%">
						<img src="<?php echo esc_url(plugin_dir_url(dirname(__DIR__)).'images/mologo.png');?>" height="65px" width="65px">
					</div>
					<div style="width: 75%">
						<p class="mo_api_authentication_adv_custom_api_heading">Custom API for WordPress</p>
					</div>
				</div>
					<p class="mo_api_authentication_adv_custom_api">Create your own REST API endpoints in WordPress to interact with WordPress database to fetch, insert, update, delete data. Also, any external APIs can be connected to WordPress for interaction between WordPress & External application.</p>
				<br><br>
				<div>
				<p class="mo_api_authentication_adv_custom_api_p"><a href="https://wordpress.org/plugins/custom-api-for-wp/" target="_blank" rel="noopener" ><button type="button" style="width:120px;height: 40px;background: #473970;color: white" class="button button-primary button-large">Install Plugin</button></a></p>
			</div>
			</div>
			<br>
		</div>
			<?php
	}

    public static function mo_oauth_client_setup_support(){
    	?>
	<div class="mo_rest_api_support-icon" style="display: block;">
			<div class="mo_rest_api_help-container" id="help-container" style="display: block;">
			  	<span class="mo_rest_api_span1">
					<div class="mo_rest_api_need">
					  <span class="mo_rest_api_span2"></span>
						<div id="mo-rest-api-support-msg">Need Help? We are right here!</div>
						<span class="fa fa-times fa-1x " id="mo-support-msg-hide" style="cursor:pointer;float:right;disply:inline;">
					</span>
					</div>
			  	</span>
			  	<div id="service-btn">
				<div class="mo-rest-api-service-icon">
					<img src="<?php echo esc_url(plugin_dir_url(dirname(__DIR__)).'images/mail.png'); ?>" class="mo-rest-api-service-img" alt="support">
				</div>
			</div>
			</div>
		</div>

	<div class="mo-rest-api-support-form-container" style="display: none;">
 			<div class="mo-rest-api-widget-header">
				<b>Contact miniOrange Support</b>
				<div class="mo-rest-api-widget-header-close-icon">
					<span style="cursor: pointer;float:right;" id="mo-rest-api-support-form-hide"><img src="<?php echo esc_url(plugin_dir_url(dirname(__DIR__)).'images/remove.png'); ?>" height="15px" width = "15px">
					</span>
				</div>
		  	</div>

			<div class="mo-rest-api-loading-inner" style="overflow:hidden;">
		      <div class="loading-icon">
		        <div class="loading-icon-inner">
		          <span class="icon-box">
		            <img class="icon-image" src="<?php echo esc_url(plugin_dir_url(dirname(__DIR__)).'images/tick.png'); ?>" alt="success" height="25px" width = "25px" >
		          </span>
		          <p class="loading-icon-text">
		              <p>Thanks for your inquiry.<br><br>If you dont hear from us within 24 hours, please feel free to send a follow up email to <a href="mailto:<?php echo 'apisupport@xecurify.com';?>"><?php echo 'apisupport@xecurify.com';?></a></p>
		          </p>
		        </div>
		      </div>
		    </div>

		    <div class="mo-rest-api-loading-inner-2" style="overflow:hidden;">
		      <div class="mo-rest-api-loading-icon-2">
		        <div class="loading-icon-inner-2">
		          <br>
		          <span class="icon-box-2">
		            <img class="icon-image-2" src="<?php echo esc_url(plugin_dir_url(dirname(__DIR__)).'images/mail.png'); ?>" alt="error" >
		          </span>
		          <p class="mo-rest-api-loading-icon-text-2">
		              <p>Unable to connect to Internet.<br>Please try again.</p>
		          </p>
		        </div>
		      </div>
		    </div>
		    
		    <div class="mo-rest-api-loading-inner-3" style="overflow:hidden;">
		      <div class="loading-icon-3">
		          <p class="loading-icon-text-3">
		              <p style="font-size:18px;">Please Wait...</p>
		          </p>
		          <div class="loader"></div>
		      </div>
		    </div>

		  	<br>
		  	<div class="support-form top-label" style="display: block;">
		  			<label for="email">
						Your Contact E-mail
		  			</label>
		  			<br><br>
		 	 		<input type="email" class="field-label-text" name="email" id="person_email" dir="auto" required="true" title="Enter a valid email address." placeholder="Enter valid email">
		 	 		<br><br>
		  			<label>
						How can we help you?
		  			</label>
		  			<br><br>
		  			<textarea rows="5" id="person_query" name="description" dir="auto" required="true" class="field-label-textarea" placeholder="You will get reply via email"></textarea>
		  			<br><br>
		  			<button id="mo-rest-api-submit-support" type="submit" class="button button-primary button-large" style="width:70px;margin-left:30%;border-radius: 2px;background: #473970;" value="Submit" aria-disabled="false">Submit</button>
	  		</div>
		</div>
	<script>

			jQuery('#mo-rest-api-support-form-hide').click(function(){
				jQuery(".mo-rest-api-support-form-container").css('display','none');
			});

			jQuery("#service-btn").click(function(){
					jQuery(".mo-rest-api-support-form-container").show();
					jQuery(".mo-rest-api-support-msg").hide();
				});
			jQuery("#mo-rest-api-submit-support").click(function(){

		        var email = jQuery("#person_email").val();
		        var query = jQuery("#person_query").val();
		        var fname = "<?php echo esc_attr((wp_get_current_user()->user_firstname)); ?>";
		        var lname = "<?php echo esc_attr((wp_get_current_user()->user_lastname)); ?>";
		    	var version = "<?php echo esc_attr(MINIORANGE_API_AUTHENTICATION_VERSION); ?>";
		        var query = "[WP REST API Authentication] "+version+" - "+query;

		        if(email == "" || query == ""){

		            jQuery('#login-error').show();
		            jQuery('#errorAlert').show();

		        }
		        else{
		            jQuery('input[type="text"], textarea').val('');
		            jQuery('select').val('Select Category');
		            jQuery(".support-form").css('display','none');
		            jQuery(".mo-rest-api-loading-inner-3").css('display','block');
		            var json = new Object();

		            json = {
		                "email" : email,
		                "query" : query,
		                "ccEmail" : "apisupport@xecurify.com",
		                "company" : "<?= sanitize_text_field( $_SERVER ['SERVER_NAME'] ) ?>",
		                "firstName" : fname,
		                "lastName" : lname,
		            }
		           
		            var jsonString = JSON.stringify(json);
		            jQuery.ajax({

	                  url: "https://login.xecurify.com/moas/rest/customer/contact-us",
	                  type : "POST",
	                  data : jsonString,
	                  crossDomain: true,
	                  dataType : "text",
	                  contentType : "application/json; charset=utf-8",
	               	  success: function (data, textStatus, xhr) { successFunction();},
              		  error: function (jqXHR, textStatus, errorThrown) { errorFunction(); }
	            });
		        }
			});

			function successFunction(){
        
		        jQuery(".mo-rest-api-loading-inner-3").css('display','none');
		        jQuery(".mo-rest-api-loading-inner").css('display','block');
		    }

		    function errorFunction(){
		        
		        jQuery(".mo-rest-api-loading-inner-3").css('display','none');
		        jQuery(".mo-rest-api-loading-inner-2").css('display','block');
		    }

	</script>
	<?php
}

}