<?php
/**
 * The admin-facing file for contacts sync.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/setup
 */

if ( isset( $_GET['action'] ) && 'hubwoo-osc-schedule-sync' == $_GET['action'] ) {
	update_option( 'hubwoo_greeting_displayed_setup', 'yes' );
	Hubwoo_Admin::hubwoo_schedule_sync_listener();
	wp_safe_redirect( admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-overview&hubwoo_key=sync' ) );
}
	$total_registered_users          = Hubwoo_Admin::hubwoo_get_all_users_count();
	$sync_process['display_sync']    = 'block';
	$sync_process['display_greet']   = 'none';
	$sync_process['display_onboard'] = 'none';

if ( 'yes' == get_option( 'hubwoo_greeting_displayed_setup', 'no' ) ) {
	$sync_process['display_sync']    = 'none';
	$sync_process['display_greet']   = 'none';
	$sync_process['display_onboard'] = 'block';
}

if ( 'yes' == get_option( 'hubwoo_onboard_user', 'no' ) ) {
	$sync_process['display_sync']    = 'none';
	$sync_process['display_onboard'] = 'none';
	$sync_process['display_greet']   = 'block';
}

if ( empty( get_option( 'hubwoo_customers_role_settings', array() ) ) ) {
	update_option( 'hubwoo_customers_role_settings', array_keys( Hubwoo_Admin::get_all_user_roles() ) );
}

$onboarding_data = Hubwoo::hubwoo_onboarding_questionaire();

?>

<div class="mwb-heb-welcome sync-page" style="display: <?php echo esc_html( $sync_process['display_sync'] ); ?>">
	<div class="hubwoo-box">
		<div class="mwb-heb-wlcm__title">			
			<h2>
				<?php esc_html_e( 'Sync WooCommerce data with HubSpot', 'hubspot-for-woocommerce' ); ?>
			</h2>
		</div>
		<div class="mwb-heb-wlcm__content">
			<div class="hubwoo-content__para">
				<p>
					<?php esc_html_e( 'You’re almost done! The last step is to sync your existing WooCommerce data to HubSpot. This will sync all your Contacts, Deals, and Products data to HubSpot.', 'hubspot-for-woocommerce' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Once you sync your data, you’ll see all your WooCommerce information on HubSpot so you can start engaging with them right away.', 'hubspot-for-woocommerce' ); ?>
				</p>			
			</div>				
			<div class="mwb-heb-wlcm__btn-wrap">

				<a href="javascript:void(0);" id = "hubwoo-osc-instant-sync-historical" class="hubwoo-osc-instant-sync hubwoo-btn--primary" data-total_users= "<?php echo esc_attr( $total_registered_users ); ?>"><?php esc_html_e( 'Sync Now', 'hubspot-for-woocommerce' ); ?></a>		

				<a href="?page=hubwoo&hubwoo_tab=hubwoo-sync-contacts&action=hubwoo-osc-schedule-sync" id = "hubwoo-osc-schedule-sync" id="hubwoo-osc-schedule-sync" class="hubwoo-osc-schedule-sync hubwoo__btn"><?php esc_html_e( 'Skip Historical Data Sync', 'hubspot-for-woocommerce' ); ?></a>

			</div>
		</div>
		<div class="hubwoo-progress-container" >
			<div class="hubwoo-progress-wrap" style="display: none;">
				<p>
					<strong><?php esc_html_e( '1. Syncing Your Contacts to HubSpot. This should only take a few moments. Thanks for your patience!', 'hubspot-for-woocommerce' ); ?></strong>
				</p>					
				<div class="hubwoo-progress">
					<div class="hubwoo-progress-bar" role="progressbar" style="width:0"></div>
				</div>
			</div>
			<div class="hubwoo-progress-wrap-import" style="display: none;">
				<p>
					<strong><?php esc_html_e( '2. Syncing your Products to HubSpot. This should only take a few moments. Thanks for your patience!', 'hubspot-for-woocommerce' ); ?></strong>
				</p>					
				<div class="hubwoo-progress">
					<div class="hubwoo-progress-bar" role="progressbar" style="width:0"></div>
				</div>
			</div>
			<div class="hubwoo-progress-wrap-import-deals" style="display: none;">
				<p>
					<strong><?php esc_html_e( '3. Syncing your Deals to Hubspot. This should only take a few moments. Thanks for your patience!', 'hubspot-for-woocommerce' ); ?></strong>
				</p>					
				<div class="hubwoo-progress">
					<div class="hubwoo-progress-bar" role="progressbar" style="width:0"></div>
				</div>
			</div>					
		</div>
	</div>
</div>

<div id="hubwoo-visit-dashboard" class="acc-connected mwb-heb-welcome" style="display: <?php echo esc_attr( $sync_process['display_greet'] ); ?>">
	<div class="hubwoo-box">
		<div class="mwb-heb-wlcm__title">			
			<h2>
				<?php esc_html_e( 'Congrats! You’ve successfully set up the HubSpot for WooCommerce plugin', 'hubspot-for-woocommerce' ); ?>
			</h2>
		</div>
		<div class="mwb-heb-wlcm__content">
			<div class="hubwoo-content__para hubwoo-content__para--greeting">
				<div class="hubwoo-content__para--greeting-img" >
					<p>
						<?php esc_html_e( "What's next? Go to your dashboard to learn more about the integration.", 'hubspot-for-woocommerce' ); ?>
					</p>
					<div class="mwb-heb-wlcm__btn-wrap">
						<a href="javascript:void(0);" class="hubwoo__btn hubwoo_manage_screen" data-process="greet-to-dashboard" data-tab="hubwoo_tab" ><?php esc_html_e( 'Visit DashBoard', 'hubspot-for-woocommerce' ); ?></a>
					</div>														
				</div>
				<div class="hubwoo-content__para--greeting-content" >
					<img height="150px" width="150px" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/congo.jpg' ); ?>">
				</div>
			</div>
		</div>
	</div>
</div>

<div id="hubwoo-onboard-user" class="acc-connected mwb-heb-welcome" style="display: <?php echo esc_attr( $sync_process['display_onboard'] ); ?>">
	<div class="hubwoo-box">
		<div class="mwb-heb-wlcm__title">			
			<h2>
				<?php esc_html_e( 'Get tailored onboarding emails straight to your inbox', 'hubspot-for-woocommerce' ); ?>
			</h2>
		</div>
		<div class="hubwoo-onboard-suburb">
			<p><?php esc_html_e( 'Help us make your experience even better by telling us:', 'hubspot-for-woocommerce' ); ?></p>
		</div>		
		<div class="hubwoo-onboarding-email__body mwb-heb-wlcm__content">
			<form action="#" method="POST" id="hubwoo-onboarding-form">			
				<div class="hubwoo-onboarding-email__body-content">
					<div class="hubwoo-onboard-notice">
						<span><?php esc_html_e( 'Please fill all of the below fields before submission', 'hubspot-for-woocommerce' ); ?></span>
					</div>					
					<?php
					foreach ( $onboarding_data as $name => $data ) {
						?>
					<div class="hubwoo-onboarding-email__items">
						<label class="hubwoo-onboard-suburb-label"><?php echo esc_textarea( $data['label'] ); ?></label>
						<select name="<?php echo esc_attr( $name ); ?>[]" <?php echo esc_attr( $data['allow'] ); ?> class="hubwoo-form-control hubwoo-onquest <?php echo esc_attr( $name ); ?>">
							<?php foreach ( $data['options'] as $option ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_attr( $option ); ?></option>							
							<?php endforeach ?>
						</select>
						<img class="hubwoo-onboard-img" name="<?php echo esc_attr( $name ); ?>" height="20px" width="20px" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/checked.png' ); ?>">
					</div>
						<?php
					}
					?>

					<span class="hubwoo-onboarding-message hidefield hubwoo_register">
						<?php esc_html_e( 'Choosing the ideal plan for your company might be confusing. ', 'hubspot-for-woocommerce' ); ?>
						<a target="_blank" href="https://meetings.hubspot.com/makewebbetter/free-hubspot-consultation"><?php esc_html_e( 'Connect Us ', 'hubspot-for-woocommerce' ); ?></a>
						<?php esc_html_e( 'and we can assist you for free.', 'hubspot-for-woocommerce' ); ?>
					</span>
					<!-- code for adding first name field in form --> 
					<div class="hubwoo-onboarding-email__items flname hidefield hubwoo_register">
						<label class="hubwoo-onboard-suburb-label"><?php esc_html_e( 'First Name', 'hubspot-for-woocommerce' ); ?></label>
						<input type="esc_textarea" placeholder="First Name" name="firstname" class="hubwoo-form-control-flname" >
						<img class="hubwoo-onboard-img" name="firstname" height="20px" width="20px" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/checked.png' ); ?>">
					</div>

					<!-- code for adding last name field in form -->
					<div class="hubwoo-onboarding-email__items flname hidefield hubwoo_register">
						<label class="hubwoo-onboard-suburb-label"><?php esc_html_e( 'Last Name', 'hubspot-for-woocommerce' ); ?></label>
						<input type="esc_textarea" placeholder="Last Name" name="lastname" class="hubwoo-form-control-flname" >
						<img class="hubwoo-onboard-img" name="lastname" height="20px" width="20px" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/checked.png' ); ?>">
					</div>

					<!-- code for adding company name field in form -->
					<div class="hubwoo-onboarding-email__items hidefield hubwoo_register">
						<label class="hubwoo-onboard-suburb-label"><?php esc_html_e( 'Company Name', 'hubspot-for-woocommerce' ); ?></label>
						<input type="esc_textarea" placeholder="Company Name" name="company" class="hubwoo-form-control" >
						<img class="hubwoo-onboard-img" name="company" height="20px" width="20px" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/checked.png' ); ?>">
					</div>

					<!-- code for adding company url field in form -->
					<div class="hubwoo-onboarding-email__items hidefield hubwoo_register">
						<label class="hubwoo-onboard-suburb-label"><?php esc_html_e( 'Website URL', 'hubspot-for-woocommerce' ); ?></label>
						<input type="esc_textarea" placeholder="URl where you'll install HubSpot" name="website" class="hubwoo-form-control" >
						<img class="hubwoo-onboard-img" name="website" height="20px" width="20px" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/checked.png' ); ?>">
					</div>

					<div class="hubwoo-onboarding-email__items">
						<label class="hubwoo-onboard-suburb-label"><?php esc_html_e( 'Email Address', 'hubspot-for-woocommerce' ); ?></label>
						<input type="email" placeholder="Email Address" name="email" class="hubwoo-form-control" value="<?php echo esc_textarea( get_user_by( 'id', get_current_user_id() )->data->user_email ); ?>">
						<img class="hubwoo-onboard-img" name="email" height="20px" width="20px" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/checked.png' ); ?>">
					</div>
					<!-- code for adding phone nuber field in form --> 
					<div class="hubwoo-onboarding-email__items">
						<label class="hubwoo-onboard-suburb-label"><?php esc_html_e( 'Phone Number', 'hubspot-for-woocommerce' ); ?></label>
						<input type="esc_textarea" placeholder="Phone number" name="phone" class="hubwoo-form-control" >
						<img class="hubwoo-onboard-img" name="phone" height="20px" width="20px" src="<?php echo esc_url( HUBWOO_URL . 'admin/images/checked.png' ); ?>">
					</div>					
					<div class="onboard-spinner"><span class="fa fa-spinner fa-spin"></span></div>
					<div class="hubwoo-onboarding-email__butttons">
						<a href="javascript:void" data-type='sync' id= "hubwoo-complete-onboarding" class="hubwoo-onboard-manage hubwoo-btn--dashboard hubwoo-btn--primary"><?php esc_html_e( 'Complete Onboarding', 'hubspot-for-woocommerce' ); ?></a>
						<div class="hubwoo-onboard-manage hubwoo-onboarding-skip--link">
							<a href="javascript:void" data-type='skip' class="hubwoo-onboard-manage" ><?php esc_html_e( 'Skip for now', 'hubspot-for-woocommerce' ); ?></a>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
