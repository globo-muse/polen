<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       miniorange
 * @since      1.0.0

 * @package    Miniorange_api_authentication
 * @subpackage Miniorange_api_authentication/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Miniorange_api_authentication
 * @subpackage Miniorange_api_authentication/admin
 * @author     miniOrange
 */
// require plugin_dir_path( dirname( __FILE__ ) ) .'miniorange-api-authentication.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/class-miniorange-api-authentication-deactivator.php';
require plugin_dir_path( __FILE__ ) .'/class-miniorange-api-authentication-customer.php';
require( 'partials/class-mo-api-authentication-admin-menu.php' );
require( 'partials/flow/mo-api-authentication-flow.php' );
require( 'partials/flow/mo-token-api-flow.php' );
require( 'partials/support/class-mo-api-authentication-feedback.php' );

class Miniorange_API_Authentication_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$mo_path = (dirname(dirname(plugin_basename(__FILE__))));
		$mo_path = $mo_path.'/miniorange-api-authentication.php';
		add_filter( 'plugin_action_links_' . $mo_path, array( $this, 'add_action_links') );
		add_action( 'admin_menu',  array( $this, 'miniorange_api_authentication_save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_script' ) );
		
    }

	//Function to add the Premium settings in Plugin's section

    function add_action_links ( $actions ) {

		   $url = esc_url( add_query_arg(
			'page',
			'mo_api_authentication_settings',
			get_admin_url() . 'admin.php'
		) );	
		   $url2 =  $url.'&tab=licensing';
		   $settings_link = "<a href='$url'>" . esc_attr( 'Configure' ) . '</a>';
		   $settings_link2 = "<a href='$url2' style=><b>" . esc_attr( 'Upgrade to Premium' ) . '</b></a>';
		   array_push($actions, $settings_link2);
		   array_push($actions, $settings_link);
		   return array_reverse($actions);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */

	// function mo_api_authentication_feedback_request() {
	// 	Mo_API_Authentication_Admin_Feedback::mo_api_authentication_display_feedback_form();
	// }
	
	function plugin_settings_style() {
		wp_enqueue_style( 'mo_api_authentication_admin_settings_style', plugins_url( 'css/style_settings.css', __FILE__ ) );
		wp_enqueue_style( 'mo_api_authentication_admin_settings_phone_style', plugins_url( 'css/phone.css', __FILE__ ) );
	}

	function plugin_settings_script() {
		wp_enqueue_script( 'mo_api_authentication_admin_settings_phone_script', plugins_url('js/phone.js', __FILE__ ) );
	}

	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Miniorange_api_authentication_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Miniorange_api_authentication_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( 'mo_rest_api_material_icon', plugin_dir_url( __FILE__ ) . 'css/materialdesignicons.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/miniorange-api-authentication-admin.css', array(), $this->version, 'all' );
		if(isset($_REQUEST['tab']) && sanitize_text_field( $_REQUEST['tab'] ) == 'licensing'){
            wp_enqueue_style( 'mo_api_authentication_bootstrap_css', plugins_url( 'css/bootstrap/bootstrap.min.css', __FILE__ ) );
        }
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Miniorange_api_authentication_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Miniorange_api_authentication_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
	}

	private function mo_api_authentication_show_curl_error() {
		if( $this->mo_api_authentication_is_curl_installed() == 0 ) {
			update_option( 'mo_api_auth_message', '<a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled. Please enable it to continue.');
			mo_api_auth_show_error_message();
			return;
		}
	}

	private function mo_api_authentication_is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else {
			return 0;
		}
	}

	public function mo_api_auth_admin_menu() {

		$page = add_menu_page( 'API Authentication Settings ' . __( 'Configure Authentication', 'mo_api_authentication_settings' ), 'miniOrange API Authentication', 'administrator', 'mo_api_authentication_settings', array( $this, 'mo_api_auth_menu_options' ) ,plugin_dir_url(__FILE__) . 'images/miniorange.png');	
	}

	function mo_api_auth_menu_options () {
		global $wpdb;
		$customerRegistered = mo_api_authentication_is_customer_registered();
		mo_api_authentication_main_menu();

	}

    public static function whitelist_routes( $access ) {

        // Return current value of $access and skip all plugin functionality
        if ( self::allow_rest_api() ) {
            return $access;
        }
       
		$current_route = self::get_current_route();
	

        if ( self::is_whitelisted( $current_route ) ) {
			return false;
        }
        
        return $access;

    }

    public static function is_whitelisted( $currentRoute ) {
        return array_reduce( self::get_route_whitelist_option(), function ( $isMatched, $pattern ) use ( $currentRoute ) {
            return $isMatched || (bool) preg_match( '@^' . htmlspecialchars_decode( $pattern ) . '$@i', $currentRoute );
        }, false );
    }

    public static function get_route_whitelist_option() {
        return (array) get_option( 'mo_api_authentication_protectedrestapi_route_whitelist', array() );
    }

    public static function mo_api_auth_else()
    {	
    	self::mo_api_shortlist();
    }

    public static function get_current_route() {
        $rest_route = $GLOBALS['wp']->query_vars['rest_route'];

        return ( empty( $rest_route ) || '/' == $rest_route ) ?
            $rest_route :
            untrailingslashit( $rest_route );
    }

    public static function allow_rest_api() {
        return (bool) apply_filters( 'dra_allow_rest_api', is_user_logged_in() );
    }

	public function mo_api_authentication_config_settings() {
		mo_api_authentication_config_app_settings();
	}

	public function mo_api_authentication_export_plugin_configuration() {
		mo_api_authentication_export_plugin_config();
	}

	 public static function mo_api_shortlist()
    {
		self::convergence();
    }


	public function mo_api_auth_initialize_api_flow() {

		if ( !mo_api_auth_user_has_capability() ) {
			if(strpos(sanitize_text_field($_SERVER['REQUEST_URI']), '/api/v1/token') !== false  && get_option( 'mo_api_authentication_selected_authentication_method' ) === 'jwt_auth' ) {
				$json = file_get_contents('php://input');
				$json = json_decode( $json, true );
				if( json_last_error() !== JSON_ERROR_NONE ) {
					$json = array_map( 'esc_attr', $_POST );
				}
	        	mo_api_auth_token_endpoint_flow($json);
			} else {
	        	mo_api_auth_restrict_rest_api_for_invalid_users();
	        } 
	    }
	}

	function regenerate_token() {	
		if (sanitize_text_field($_SERVER['REQUEST_METHOD']) === 'POST' &&  current_user_can('administrator') ) {
			$bearer_token = stripslashes( wp_generate_password( 32, false, false ) );
			update_option( 'mo_api_auth_bearer_token ', $bearer_token );
			echo esc_attr( $bearer_token );
			wp_die(); 
		}
	}

	function regenerate_client_credentials(){
		if (sanitize_text_field($_SERVER['REQUEST_METHOD']) === 'POST' &&  current_user_can('administrator') ) {
			mo_api_authentication_create_client();
			$response = [
				'client_id' => get_option( 'mo_api_auth_clientid' ),
				'client_secret' => get_option( 'mo_api_auth_clientsecret' )
			];
			wp_send_json( $response, 200 );
		}
	}

	function save_temporary_data(){
		
		if (sanitize_text_field($_SERVER['REQUEST_METHOD']) === 'POST' &&  current_user_can('administrator') ) {
			if(isset($_POST['auth_method']) && sanitize_text_field($_POST['auth_method']) == 'basic_auth'){

				$api_temp = array();
				$api_temp['algo'] = sanitize_text_field($_POST['algo']);
				$api_temp['token_type'] = sanitize_text_field($_POST['token_type']);
				update_option('mo_rest_api_ajax_method_data',$api_temp);
			}
			$response = [
				'success' => 'true'
			];
			wp_send_json( $response, 200 );
		}
	}

	function mo_api_authentication_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}

	public static function convergence()
 	{	
 		
		if (!mo_api_auth_is_valid_request()) {
            $response = array(
				'status' => "error",
                'error' => 'UNAUTHORIZED',
               	'error_description' => 'Sorry, you are not allowed to access REST API.'
			);
            wp_send_json($response, 401);
        }
       
 	}


	function miniorange_api_authentication_remove_registered_user(){
		delete_option( 'mo_api_authentication_new_registration' );
		delete_option( 'mo_api_authentication_admin_email');
		delete_option( 'mo_api_authentication_admin_phone');
		delete_option( 'mo_api_authentication_admin_fname');
		delete_option( 'mo_api_authentication_admin_lname');
		delete_option( 'mo_api_authentication_admin_company');
		delete_option( 'mo_api_authentication_verify_customer' );
		delete_option( 'mo_api_authentication_admin_customer_key' );
		delete_option( 'mo_api_authentication_admin_api_key' );
		delete_option( 'mo_api_authentication_new_customer' );
		delete_option( 'mo_api_authentication_registration_status' );
		delete_option( 'mo_api_authentication_customer_token' );
	}

	function miniorange_api_authentication_save_settings() {

		if (sanitize_text_field($_SERVER['REQUEST_METHOD']) === 'POST' &&  current_user_can('administrator') ) {

			if ( isset( $_POST['option'] ) and sanitize_text_field( $_POST['option'] ) == "mo_api_authentication_change_email_address" && isset($_REQUEST['mo_api_authentication_change_email_address_form_fields']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_api_authentication_change_email_address_form_fields'])), 'mo_api_authentication_change_email_address_form') ) {
				$this->miniorange_api_authentication_remove_registered_user();
				return;
			} else if( isset( $_POST['option'] ) and sanitize_text_field( $_POST['option'] ) == "mo_api_authentication_register_customer" && isset($_REQUEST['mo_api_authentication_register_form_fields']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_api_authentication_register_form_fields'])), 'mo_api_authentication_register_form')  ) {	//register the admin to miniOrange
				//validation and sanitization
				$email = '';
				$phone = '';
				$password = '';
				$confirmPassword = '';
				$fname = '';
				$lname = '';
				$company = '';
				if( $this->mo_api_authentication_check_empty_or_null( sanitize_email($_POST['email']) ) || $this->mo_api_authentication_check_empty_or_null( sanitize_text_field($_POST['password']) ) || $this->mo_api_authentication_check_empty_or_null( sanitize_text_field($_POST['confirmPassword']) ) ) {
					update_option( 'mo_api_auth_message', 'All the fields are required. Please enter valid entries.');
					update_option('mo_api_auth_message_flag', 2);
					return;
				} else if( strlen( sanitize_text_field($_POST['password']) ) < 8 || strlen( sanitize_text_field($_POST['confirmPassword']) ) < 8){
					update_option( 'mo_api_auth_message', 'Choose a password with minimum length 8.');
					update_option('mo_api_auth_message_flag', 2);
					return;
				} else {
					$email = sanitize_email( $_POST['email'] );
					$phone = stripslashes( sanitize_text_field($_POST['phone']) );
					$password = stripslashes( sanitize_text_field($_POST['password']) );
					$confirmPassword = stripslashes( sanitize_text_field($_POST['confirmPassword']) );
					$fname = stripslashes( sanitize_text_field($_POST['fname']) );
					$lname = stripslashes( sanitize_text_field($_POST['lname' ]) );
					$company = stripslashes( sanitize_text_field($_POST['company']) );
				}
				
				update_option( 'mo_api_authentication_admin_email', $email );
				update_option( 'mo_api_authentication_admin_phone', $phone );
				update_option( 'mo_api_authentication_admin_fname', $fname );
				update_option( 'mo_api_authentication_admin_lname', $lname );
				update_option( 'mo_api_authentication_admin_company', $company );
				
				if( strcmp( $password, $confirmPassword) == 0 ) {
					update_option( 'password', $password );
					$customer = new Miniorange_API_Authentication_Customer();
					$email = get_option('mo_api_authentication_admin_email');
					$content = json_decode( $customer->check_customer(), true );
					
					if( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND') == 0 ) {
						$response = json_decode( $customer->create_customer(), true );

						if(strcasecmp($response['status'], 'SUCCESS') != 0) {
							update_option( 'mo_api_auth_message', 'Failed to create customer. Try again.');
							update_option('mo_api_auth_message_flag', 2);
						} else {
							update_option( 'mo_api_authentication_verify_customer', 'true' );
							update_option( 'mo_api_auth_message', sanitize_text_field( $response['message'] ) );
							update_option('mo_api_auth_message_flag', 1);
						}
					} elseif(strcasecmp( $content['status'], 'SUCCESS') == 0 ) {
						update_option( 'mo_api_authentication_verify_customer', 'true' );
						update_option( 'mo_api_auth_message', 'Account already exist. Please Login.');
						update_option('mo_api_auth_message_flag', 2);
					} else if(is_null($content)) {
						update_option( 'mo_api_auth_message', 'Failed to create customer. Try again.');
						update_option('mo_api_auth_message_flag', 2);
					} else {
						update_option( 'mo_api_auth_message', sanitize_text_field( $content['message'] ) );
						update_option('mo_api_auth_message_flag', 1);
					}
					
				} else {
					update_option( 'mo_api_auth_message', 'Passwords do not match.');
					delete_option('mo_api_authentication_verify_customer');
					update_option('mo_api_auth_message_flag', 2);
				}
			} else if( isset( $_POST['option'] ) and sanitize_text_field( $_POST['option'] ) == "mo_api_authentication_goto_login" && isset($_REQUEST['mo_api_authentication_goto_login_fields']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_api_authentication_goto_login_fields'])), 'mo_api_authentication_goto_login') ) {
				delete_option( 'mo_api_authentication_new_registration' );
				update_option( 'mo_api_authentication_verify_customer', 'true' );
			} else if( isset( $_POST['option'] ) and sanitize_text_field( $_POST['option'] ) == "mo_api_authentication_verify_customer" && isset($_REQUEST['mo_api_authentication_verify_customer_form_fields']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_api_authentication_verify_customer_form_fields'])), 'mo_api_authentication_verify_customer_form') ) {	//login the admin to miniOrange
				//validation and sanitization
				$email = '';
				$password = '';
				if( $this->mo_api_authentication_check_empty_or_null( $_POST['email'] ) || $this->mo_api_authentication_check_empty_or_null( $_POST['password'] ) ) {
					update_option( 'mo_api_auth_message', 'All the fields are required. Please enter valid entries.');
					mo_api_auth_show_error_message();
					return;
				} else{
					$email = sanitize_email( $_POST['email'] );
					$password = sanitize_text_field( $_POST['password'] );
				}

				update_option( 'mo_api_authentication_admin_email', $email );
				update_option( 'password', $password );
				$customer = new Miniorange_API_Authentication_Customer();
				$content = $customer->get_customer_key();
				$customerKey = json_decode( $content, true );
				if( json_last_error() == JSON_ERROR_NONE && isset($customerKey['status']) && $customerKey['status'] === "SUCCESS" ) {
					update_option( 'mo_api_authentication_admin_customer_key', sanitize_text_field( $customerKey['id'] ) );
					update_option( 'mo_api_authentication_admin_api_key', sanitize_text_field( $customerKey['apiKey'] ) );
					update_option( 'mo_api_authentication_customer_token', sanitize_text_field( $customerKey['token'] ) );
					if( isset( $customerKey['phone'] ) )
						update_option( 'mo_api_authentication_admin_phone', sanitize_text_field( $customerKey['phone'] ) );
					delete_option( 'password' );
					update_option( 'mo_api_auth_message', 'Customer retrieved successfully');
					delete_option( 'mo_api_authentication_verify_customer' );
					update_option('mo_api_auth_message_flag', 1);
				} else {
					update_option( 'mo_api_auth_message', 'Invalid username or password. Please try again.');
					update_option('mo_api_auth_message_flag', 2);
				}
			} else if ( isset( $_POST['option'] ) and sanitize_text_field( $_POST['option'] ) == 'mo_api_authentication_skip_feedback' && isset($_REQUEST['mo_api_authentication_skip_feedback_form_fields']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_api_authentication_skip_feedback_form_fields'])), 'mo_api_authentication_skip_feedback_form') ) {
				$path = plugin_dir_path( dirname( __FILE__ ) ) .'miniorange-api-authentication.php';
				deactivate_plugins( $path );
				update_option( 'mo_api_auth_message', 'Plugin deactivated successfully' );
				mo_api_auth_show_success_message();
			} else if(isset($_POST['mo_api_authentication_feedback']) and sanitize_text_field ( $_POST['mo_api_authentication_feedback'] ) == 'true' && isset($_REQUEST['mo_api_authentication_feedback_fields']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_api_authentication_feedback_fields'])), 'mo_api_authentication_feedback_form')) {
				
				if( current_user_can( 'administrator' ) ) {
				$user = wp_get_current_user();

				$message = 'Plugin Deactivated:';
				if(isset($_POST['deactivate_reason_select'])){
					$deactivate_reason = sanitize_text_field( $_POST['deactivate_reason_select']);
				}
				
				$deactivate_reason_message = array_key_exists( 'query_feedback', $_POST ) ? sanitize_text_field( wp_unslash( $_POST['query_feedback'] ) ) : false;

				if ( $deactivate_reason ) {
					$message .= $deactivate_reason;
					if ( isset( $deactivate_reason_message ) ) {
						$message .= ': ' . $deactivate_reason_message;
					}

					if(isset($_POST['rate'])){
					$rate_value = sanitize_text_field($_POST['rate']);
					}

					$rating = "[Rating: ".$rate_value."]";

					$email = sanitize_email( $_POST['query_mail'] );
					if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
						$email = get_option("mo_api_authentication_admin_email");
						if(empty($email)){
							$email = $user->user_email;
						}
					}

					$reply = $rating;

					$phone = get_option( 'mo_api_authentication_admin_phone' );
					$plugin_config = mo_api_authentication_export_plugin_config();

					//only reason
					$feedback_reasons = new Miniorange_API_Authentication_Customer();
					$submited = $feedback_reasons->mo_api_authentication_send_email_alert( $email,$phone,$reply, $message, "WordPress REST API Authentication by miniOrange", $plugin_config );
					
					$path = plugin_dir_path( dirname( __FILE__ ) ) .'miniorange-api-authentication.php';
					deactivate_plugins( $path );
					if ( $submited == false ) {
						update_option('mo_api_auth_message', 'Your query could not be submitted. Please try again.');
						update_option('mo_api_auth_message_flag', 2);
					} else {
						update_option('mo_api_auth_message', 'Thanks for getting in touch! We shall get back to you shortly.');
						update_option('mo_api_auth_message_flag', 1);
					}
				} else {
					update_option( 'message', 'Please Select one of the reasons ,if your reason is not mentioned please select Other Reasons' );
					update_option('mo_api_auth_message_flag', 2);
				}
			}

			} else if( isset( $_POST['option'] ) and sanitize_text_field( $_POST['option'] ) == "mo_api_authentication_contact_us_query_option" && isset($_REQUEST['mo_api_authentication_contact_us_query_form_fields']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_api_authentication_contact_us_query_form_fields'])), 'mo_api_authentication_contact_us_query_form') ) {

				// Contact Us query
				$email = sanitize_email( $_POST['mo_api_authentication_contact_us_email']);
				$phone = sanitize_text_field( $_POST['mo_api_authentication_contact_us_phone']);
				$query = sanitize_text_field( $_POST['mo_api_authentication_contact_us_query']);
				$send_config = isset( $_POST['mo_api_authentication_send_plugin_config'] );
				$customer = new Miniorange_API_Authentication_Customer();
				if ( $this->mo_api_authentication_check_empty_or_null( $email ) || $this->mo_api_authentication_check_empty_or_null( $query ) ) {
					update_option('mo_api_auth_message', 'Please fill up Email and Query fields to submit your query.');
					mo_api_auth_show_error_message();
				} else {
					$submited = $customer->submit_contact_us( $email, $phone, $query, $send_config );
					if ( $submited == false ) {
						update_option('mo_api_auth_message', 'Your query could not be submitted. Please try again.');
						update_option('mo_api_auth_message_flag', 2);
						return;
					} else {
						update_option('mo_api_auth_message', 'Thanks for getting in touch! We shall get back to you shortly.');
						update_option('mo_api_auth_message_flag', 1);
						return;
					}
				}
			} else if( isset( $_POST['option'] ) and sanitize_text_field( $_POST['option']) == "mo_api_authentication_license_contact_form" && isset($_REQUEST['mo_api_authentication_license_contact_fields']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_api_authentication_license_contact_fields'])), 'mo_api_authentication_license_contact_form') ) {	
				$email = isset(($_POST['email'])) ? sanitize_email($_POST['email']) : '';
				$phone = isset(($_POST['phone'])) ? sanitize_text_field($_POST['phone']) : '';
				$query = isset(($_POST['query'])) ? sanitize_text_field($_POST['query']) : '';
				$plugin_config = mo_api_authentication_export_plugin_config();
				//only reason
				$payment_plan = new Miniorange_API_Authentication_Customer();
				if ( $this->mo_api_authentication_check_empty_or_null( $email ) || $this->mo_api_authentication_check_empty_or_null( $query ) ) {
					update_option('mo_api_auth_message', 'Please fill up Email and Query fields to submit your query.');
					update_option('mo_api_auth_message_flag', 2);
				} else {
					$submited = $payment_plan->mo_api_authentication_send_email_alert( $email, $phone,'', $query, "Payment Plan Information: WordPress REST API Authentication", $plugin_config );
					if ( $submited == false ) {
						update_option('mo_api_auth_message', 'Your query could not be submitted. Please try again.');
						update_option('mo_api_auth_message_flag', 2);
					} else {
						update_option('mo_api_auth_message', 'Thanks for getting in touch! We shall get back to you shortly.');
						update_option('mo_api_auth_message_flag', 1);
					}
				}
			} else if( isset( $_POST['option'] ) and sanitize_text_field( $_POST['option'] ) == "mo_api_authentication_demo_request_form" && isset($_REQUEST['mo_api_authentication_demo_request_field']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_api_authentication_demo_request_field'])), 'mo_api_authentication_demo_request_form') ) {
				// Demo Request 
				if( $this->mo_api_authentication_is_curl_installed() == 0 ) {
					return $this->mo_api_authentication_show_curl_error();
				}

				$email = sanitize_email( $_POST['mo_api_authentication_demo_email'] );
				$demo_plan = sanitize_text_field( $_POST['mo_api_authentication_demo_plan']);
				$query = sanitize_text_field( $_POST['mo_api_authentication_demo_usecase']);

				if ( $this->mo_api_authentication_check_empty_or_null( $email ) || $this->mo_api_authentication_check_empty_or_null( $demo_plan ) || $this->mo_api_authentication_check_empty_or_null( $query ) ) {
					update_option('message', 'Please fill up Usecase, Email field and Requested demo plan to submit your query.');
					update_option('mo_api_auth_message_flag', 2);
				} else {
					$url = 'https://demo.miniorange.com/wordpress-oauth/';

					$headers = array( 'Content-Type' => 'application/x-www-form-urlencoded', 'charset' => 'UTF - 8');
					$args = array(
						'method' =>'POST',
						'body' => array(
							'option' => 'mo_auto_create_demosite',
							'mo_auto_create_demosite_email' => $email,
							'mo_auto_create_demosite_usecase' => $query,
							'mo_auto_create_demosite_demo_plan' => $demo_plan,
							'mo_auto_create_demosite_plugin_name' => 'mo-rest-api-authentication'
						),
						'timeout' => '20',
						'redirection' => '5',
						'httpversion' => '1.0',
						'blocking' => true,
						'headers' => $headers,

					);

					$response = wp_remote_post( $url, $args );

					if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						echo "Something went wrong: " . esc_html( $error_message );
						exit();
					}
					$output = wp_remote_retrieve_body($response);
					$output = json_decode($output);

					if(is_null($output)){
						update_option('mo_api_auth_message', 'Something went wrong! contact to your administrator');
						mo_api_auth_show_error_message();
					}

					if($output->status == 'SUCCESS'){
						update_option('mo_api_auth_message', sanitize_text_field( $output->message ));
						update_option('mo_api_auth_message_flag', 1);
					}else{
						update_option('mo_api_auth_message', sanitize_text_field( $output->message ));
						update_option('mo_api_auth_message_flag', 2);
					}

				}

			}
			
		}
	}
}
