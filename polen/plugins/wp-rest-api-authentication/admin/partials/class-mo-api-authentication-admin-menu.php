<?php

require( 'support/class-mo-api-authentication-support.php' );
require( 'support/class-mo-api-authentication-faq.php' );
require( 'config/class-mo-api-authentication-config.php' );
require( 'license/class-mo-api-authentication-license.php' );
require( 'account/class-mo-api-authentication-account.php' );
require( 'demo/class-mo-api-authentication-demo.php' );
require( 'postman/class-mo-api-authentication-postman.php' );
require( 'advanced/class-mo-api-authentication-advancedsettings.php' );
require ( 'advanced/class-mo-api-authentication-protectedrestapis.php' );
require( 'custom-api-integration/class-mo-api-authentication-custom-api-integration.php' );

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

function mo_api_authentication_main_menu() {

	$currenttab = "";
	if( isset( $_GET['tab'] ) )
		$currenttab = sanitize_text_field( $_GET['tab'] );

	if(!get_option('mo_save_settings'))
	{
		update_option('mo_save_settings',0);
	}
	?>

	<div>

	<div style="margin-left: -1.8%">
		<?php if(!isset($_GET['tab']) || (isset($_GET['tab']) && sanitize_text_field($_GET['tab'])!='licensing') ){ ?>
		<div class="mo_api_top_menu">
			<a href="https://plugins.miniorange.com/wordpress-rest-api-authentication" target="_blank" ><img style="float:left;width: 13.75em;height: 6em; margin-top: -10px;margin-left: -225px;" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/miniOrange-full-logo.png"></a>
			<a href="https://faq.miniorange.com/" target="_blank"><h2 class="mo_api_top_menu_h2"><img class="mo_api_top_menu_img" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/faq.png">&nbsp;FAQ</h2></a>
			<a href="https://plugins.miniorange.com/wordpress-rest-api-authentication#rest-api-methods" target="_blank"><h2 class="mo_api_top_menu_h2"><img class="mo_api_top_menu_img" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/know-how.png">&nbsp;Learn-More</h2></a>
			<a href="admin.php?page=mo_api_authentication_settings&tab=postman"><h2 class="mo_api_top_menu_h2"><img class="mo_api_top_menu_img" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/postman.png">&nbsp;Postman-Samples</h2></a>
			<a href="https://wordpress.org/support/plugin/wp-rest-api-authentication/" target="_blank"><h2 class="mo_api_top_menu_h2"><img class="mo_api_top_menu_img" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/wordpress-logo.png">&nbsp;WordPress Forum</h2></a>
		</div>
	</div>
		<div class="mo_api_side_bar">
			<!-- 
			<div style="background-color: white;min-height: 3.5rem;">
				<div style="padding-left: 3em;padding-top: 0.05em;">
					<a href="https://plugins.miniorange.com/wordpress-rest-api-authentication" target="_blank" ><img style="float:left;width: 13.75em;height: 6em; margin: -5px;" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/miniorange-full-logo.png"></a>
				</div>
			</div> -->
			<div id="mo_api_side_bar_content" style="padding-top: 3.5em;">
				<div class="<?php if( $currenttab == '' || $currenttab == 'config' ) {echo 'mo_api_side_bar_select';}  ?>" >
				<a href="admin.php?page=mo_api_authentication_settings&tab=config" style="text-decoration:none">
					<div style="text-align: center;padding-bottom: 1px;">
						<img style="width: 2em;height: 2em;display: block;padding-left: 8.5em;" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/setting.png">
						<p style="color: white;font-size: 1.3em;margin-top: 2px;text-align: center">Configure Methods</p>
					</div>
				</a>
			</div>
				<div class="<?php if( $currenttab == 'protectedrestapis' ) {echo 'mo_api_side_bar_select';}  ?>" >
				<a href="admin.php?page=mo_api_authentication_settings&tab=protectedrestapis" style="text-decoration:none">
					<div style="text-align: center;padding-bottom: 1px;">
						<img style="width: 1.8em;height: 1.8em;display: block;padding-left: 8.5em;" src="<?php echo esc_url (dirname( plugin_dir_url( __FILE__ ) ));?>/images/shield.png">
						<p style="color: white;font-size: 1.3em;margin-top: 2px;text-align: center">Protected REST APIs</p>
					</div>
				</a>
			</div>
				<div class="<?php if($currenttab == 'advancedsettings' ) {echo 'mo_api_side_bar_select';}  ?>" >
				<a href="admin.php?page=mo_api_authentication_settings&tab=advancedsettings" style="text-decoration:none">
					<div style="text-align: center;padding-bottom: 1px;">
						<img style="width: 1.8em;height: 1.8em;display: block;padding-left: 8.5em;" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/settings.png">
						<p style="color: white;font-size: 1.3em;margin-top: 2px;text-align: center">Advanced Settings</p>
					</div>
				</a>
			</div>

				<div class="<?php if( $currenttab == 'custom-integration' ) {echo 'mo_api_side_bar_select';}  ?>" >
				<a href="admin.php?page=mo_api_authentication_settings&tab=custom-integration" style="text-decoration:none">
					<div style="text-align: center;padding-bottom: 1px;">
						<img style="width: 1.8em;height: 1.8em;display: block;padding-left: 8.5em;" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/controller.png">
						<p style="color: white;font-size: 1.3em;margin-top: 2px;text-align: center">Custom APIs Auth</p>
					</div>
				</a>				
			</div>
				<div class="<?php if( $currenttab == 'requestfordemo' ) {echo 'mo_api_side_bar_select';}  ?>" >
				<a href="admin.php?page=mo_api_authentication_settings&tab=requestfordemo" style="text-decoration:none">
					<div style="text-align: center;padding-bottom: 1px;">
						<img style="width: 2em;height: 2em;display: block;padding-left: 8.5em;" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/trial.png">
						<p style="color: white;font-size: 1.3em;margin-top: 2px;text-align: center">Demo/Trials</p>
					</div>
				</a>
			</div>
			<div class="<?php if( $currenttab == 'account' ) {echo 'mo_api_side_bar_select';}  ?>" >
				<a href="admin.php?page=mo_api_authentication_settings&tab=account" style="text-decoration:none">
					<div style="text-align: center;padding-bottom: 1px;">
						<img style="width: 2em;height: 2em;display: block;padding-left: 8.5em;" src="<?php echo esc_url(dirname( plugin_dir_url( __FILE__ ) ));?>/images/account.png">
						<p style="color: white;font-size: 1.3em;margin-top: 2px;text-align: center">Account Setup</p>
					</div>
				</a>
			</div>
		</div>
		</div>

		

		<?php

	}else{?>

		<div style="background-color:#f9f9f9;  display: flex;justify-content: center; padding-bottom:7px;padding-top:20px;" id="nav-container">
            <div>
                <a style="font-size: 16px; color: #000;text-align: center;text-decoration: none;display: inline-block;" href="<?php echo add_query_arg( array( 'tab' => 'config' ), htmlentities( $_SERVER['REQUEST_URI'] ) ); ?>">
                    <button id="Back-To-Plugin-Configuration" type="button" value="Back-To-Plugin-Configuration" class="button button-primary button-large" style="position:absolute;left:10px;background: #473970">
                        <span class="dashicons dashicons-arrow-left-alt" style="vertical-align: middle;"></span> 
                        Plugin Configuration
                    </button> 
                </a> 
            </div>
            <div style="display:block;text-align:center;margin: 10px;">
                <p style="font-size: 20px;font-weight: 800">miniOrange REST API Authentication</p>
            </div>
        </div>
	<?php }
	
	$mo_licensing_width = '';
	$mo_api_main_dashboard_css = '';

	if(isset($_GET["tab"]) && sanitize_text_field($_GET["tab"]) == "licensing"){
		$mo_licensing_width = 'width:100%';
		$mo_api_main_dashboard_css = 'mo_api_main_dashboard2';
	}
	else{
		$mo_licensing_width = 'width:73%';
		$mo_api_main_dashboard_css = 'mo_api_main_dashboard';
	}

	?>
		<div class="<?php echo $mo_api_main_dashboard_css ?>">
	<?php 

	$mo_api_auth_message_flag = get_option('mo_api_auth_message_flag');
	$mo_api_auth_message_class = '';

	if($mo_api_auth_message_flag == 2){
		$mo_api_auth_message_class = 'mo_api_auth_admin_custom_notice_alert';
	}
	else if($mo_api_auth_message_flag == 1){
		$mo_api_auth_message_class = 'mo_api_auth_admin_custom_notice_success';
	}

	if($mo_api_auth_message_flag){
		update_option('mo_api_auth_message_flag', 0);
	?>		
	 	<div class="<?php echo $mo_api_auth_message_class; ?>" ><b><p style="font-size: 12px;"><?php echo get_option('mo_api_auth_message'); ?></p></b></div>
	 	<br>
	<?php
	}

	echo '
	<div id="mo_api_authentication_settings" style="padding-left:1em;">';
		echo '
		<div class="miniorange_container" style="padding-left:1em;">';
		echo '
		<table style="width:100%;">
			<tr>
				<td style="vertical-align:top;'.$mo_licensing_width.';" class="mo_api_authentication_content">';
					Mo_API_Authentication_Admin_Menu::mo_api_auth_show_tab( $currenttab );
				// echo '</td><td style="vertical-align:top;padding-left:1%;" class="mo_api_authentication_sidebar">';
					Mo_API_Authentication_Admin_Menu::mo_api_auth_show_support_sidebar( $currenttab );
				echo '</tr>
		</table>
		<div class="mo_api_authentication_tutorial_overlay" id="mo_api_authentication_tutorial_overlay" hidden></div>
		</div>'; ?>
		</div>
	</div>

<?php
}


class Mo_API_Authentication_Admin_Menu {
	
	public static function mo_api_auth_show_tab( $currenttab ) { 
		if($currenttab == 'account') {
			if (get_option ( 'mo_api_authentication_verify_customer' ) == 'true') {
				Mo_API_Authentication_Admin_Account::verify_password();
			} else if (trim ( get_option ( 'mo_api_authentication_email' ) ) != '' && trim ( get_option ( 'mo_api_authentication_admin_api_key' ) ) == '' && get_option ( 'mo_api_authentication_new_registration' ) != 'true') {
				Mo_API_Authentication_Admin_Account::verify_password();
			}
			else {
				Mo_API_Authentication_Admin_Account::register();
			}
		} 
		elseif( $currenttab == '' || $currenttab == 'config') 
    		Mo_API_Authentication_Admin_Config::mo_api_authentication_config();
		elseif( $currenttab == 'protectedrestapis')
            Mo_API_Authentication_Admin_ProtectedRestAPIs::mo_api_authentication_protectedrestapis();
    	elseif( $currenttab == 'advancedsettings') 
			Mo_API_Authentication_Admin_AdvancedSettings::mo_api_authentication_advancedsettings();
		elseif( $currenttab == 'custom-integration' )
			Mo_API_Authentication_Admin_CustomAPIIntegration::mo_api_authentication_customintegration();			
    	elseif( $currenttab == 'requestfordemo') 
    		Mo_API_Authentication_Admin_RFD::mo_api_authentication_requestfordemo();
    	elseif( $currenttab == 'faq') 
    		Mo_API_Authentication_Admin_FAQ::mo_api_authentication_faq();
    	elseif( $currenttab == 'licensing')
			Mo_API_Authentication_Admin_License::mo_api_authentication_licensing_page();
		elseif( $currenttab == 'postman')
			Mo_API_Authentication_Postman::mo_api_authentication_postman_page();
		
	} 

	public static function mo_api_auth_show_support_sidebar( $currenttab ) { 
		if( $currenttab != 'licensing' ) { 
			echo '<td style="vertical-align:top;padding-left:1%;" class="mo_api_authentication_sidebar">';
			echo Mo_API_Authentication_Admin_Support::mo_api_authentication_support();
			echo '<br>';
			echo Mo_API_Authentication_Admin_Support::mo_api_authentication_advertise();
			echo '<br>';
			echo Mo_API_Authentication_Admin_Support::mo_oauth_client_setup_support();
			echo '</td>';
		}
	}
		
}