<?php
/**
 * All api GET/POST functionalities.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

/**
 * Handles all hubspot api reqests/response related functionalities of the plugin.
 *
 * Provide a list of functions to manage all the requests
 * that needs in our integration to get/fetch data
 * from/to hubspot.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */
class HubWooConnectionMananager {

	/**
	 * The single instance of the class.
	 *
	 * @since   1.0.0
	 * @var HubWooConnectionMananager   The single instance of the HubWooConnectionMananager
	 */
	protected static $_instance = null;

	/**
	 * Base url of hubspot api.
	 *
	 * @since 1.0.0
	 * @var string base url of API.
	 */
	private $base_url = 'https://api.hubapi.com';


	/**
	 * Main HubWooConnectionMananager Instance.
	 *
	 * Ensures only one instance of HubWooConnectionMananager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return HubWooConnectionMananager - Main instance.
	 */
	public static function get_instance() {

		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Refreshing access token from refresh token.
	 *
	 * @since 1.0.0
	 * @param string $hapikey client id for app.
	 * @param string $hseckey secret if for app.
	 */
	public function hubwoo_refresh_token( $hapikey, $hseckey ) {

		$endpoint = '/oauth/v1/token';

		$refresh_token = get_option( 'hubwoo_pro_refresh_token', false );

		$data = array(
			'grant_type'    => 'refresh_token',
			'client_id'     => $hapikey,
			'client_secret' => $hseckey,
			'refresh_token' => $refresh_token,
			'redirect_uri'  => admin_url() . 'admin.php',
		);

		$body = http_build_query( $data );

		return $this->hubwoo_oauth_post_api( $endpoint, $body, 'refresh' );
	}

	/**
	 * Fetching access token from code.
	 *
	 * @since 1.0.0
	 * @param string $hapikey client id for app.
	 * @param string $hseckey secret if for app.
	 */
	public function hubwoo_fetch_access_token_from_code( $hapikey, $hseckey ) {

		if ( isset( $_GET['type'] ) && 'hs-auth' == $_GET['type'] && isset( $_GET['code'] ) ) {
			$code     = sanitize_key( $_GET['code'] );
			$endpoint = '/oauth/v1/token';
			$data     = array(
				'grant_type'    => 'authorization_code',
				'client_id'     => $hapikey,
				'client_secret' => $hseckey,
				'code'          => $code,
				'redirect_uri'  => admin_url() . 'admin.php?type=hs-auth',
			);
			$body     = http_build_query( $data );
			return $this->hubwoo_oauth_post_api( $endpoint, $body, 'access' );
		}
	}

	/**
	 * Post api for oauth access and refresh token.
	 *
	 * @since 1.0.0
	 * @param string $endpoint API endpoint to call.
	 * @param array  $body formatted data to be sent in hubspot api request.
	 * @param string $action refresh/access.
	 */
	public function hubwoo_oauth_post_api( $endpoint, $body, $action ) {

		$headers = array(
			'Content-Type: application/x-www-form-urlencoded;charset=utf-8',
		);

		$response = wp_remote_post(
			$this->base_url . $endpoint,
			array(
				'body'    => $body,
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}

		$parsed_response = array(
			'status_code' => 400,
			'response'    => 'error',
		);

		if ( 200 === $status_code ) {

			$api_body = wp_remote_retrieve_body( $response );

			if ( $api_body ) {
				$api_body = json_decode( $api_body );
			}

			if ( ! empty( $api_body->refresh_token ) && ! empty( $api_body->access_token ) && ! empty( $api_body->expires_in ) ) {

				update_option( 'hubwoo_pro_access_token', $api_body->access_token );
				update_option( 'hubwoo_pro_refresh_token', $api_body->refresh_token );
				update_option( 'hubwoo_pro_token_expiry', time() + $api_body->expires_in );
				update_option( 'hubwoo_pro_valid_client_ids_stored', true );
				$message         = esc_html__( 'Fetching and refreshing access token', 'hubspot-for-woocommerce' );
				$parsed_response = array(
					'status_code' => $status_code,
					'response'    => $res_message,
				);
				$this->create_log( $message, $endpoint, $parsed_response );

				if ( 'access' === $action ) {

					$this->hubwoo_pro_get_access_token_info();
				}

				update_option( 'hubwoo_pro_send_suggestions', true );
				update_option( 'hubwoo_pro_oauth_success', true );
				return true;
			}
		} elseif ( 400 === $status_code ) {
			$message = ! empty( $api_body['message'] ) ? $api_body['message'] : '';
		} elseif ( 403 === $status_code ) {
			$message = esc_html__( 'You are forbidden to use this scope', 'hubspot-for-woocommerce' );
		} else {
			$message = esc_html__( 'Something went wrong.', 'hubspot-for-woocommerce' );
		}

		update_option( 'hubwoo_pro_send_suggestions', false );
		update_option( 'hubwoo_pro_api_validation_error_message', $message );
		update_option( 'hubwoo_pro_valid_client_ids_stored', false );
		$this->create_log( $message, $endpoint, $parsed_response );
		return false;
	}

	/**
	 * Fetch access token info for automation enabling.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_pro_get_access_token_info() {

		$access_token = Hubwoo::hubwoo_get_access_token();
		$endpoint     = '/oauth/v1/access-tokens/' . $access_token;
		$headers      = $this->get_token_headers();

		$response = wp_remote_get( $this->base_url . $endpoint, array( 'headers' => $headers ) );
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}

		if ( 200 === $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body, true );
			}
			if ( ! empty( $api_body['scopes'] ) ) {
				update_option( 'hubwoo_pro_account_scopes', $api_body['scopes'] );
			}
		}

		$message         = esc_html__( 'Getting access token information', 'hubspot-for-woocommerce' );
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		$this->create_log( $message, $endpoint, $parsed_response );
	}

	/**
	 * Sending details of hubspot.
	 *
	 * @since 1.0.0
	 */
	public function send_clients_details() {

		$send_status = get_option( 'hubwoo_pro_send_suggestions', false );

		if ( $send_status ) {

			$url     = '/owners/v2/owners';
			$headers = $this->get_token_headers();

			$response = wp_remote_get( $this->base_url . $url, array( 'headers' => $headers ) );

			if ( is_wp_error( $response ) ) {
				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
			}

			if ( 200 == $status_code ) {

				$api_body = wp_remote_retrieve_body( $response );

				if ( $api_body ) {
					$api_body = json_decode( $api_body );
				}

				$message = '';

				if ( ! empty( $api_body ) ) {

					foreach ( $api_body as $single_row ) {
						//phpcs:disable
						$message  = 'portalId: ' . $single_row->portalId . '<br/>';
						$message .= 'ownerId: ' . $single_row->ownerId . '<br/>';
						$message .= 'type: ' . $single_row->type . '<br/>';
						$message .= 'firstName: ' . $single_row->firstName . '<br/>';
						$message .= 'lastName: ' . $single_row->lastName . '<br/>';
						$message .= 'email: ' . $single_row->email . '<br/>';
						//phpcs:enable
						break;
					}

					$to      = 'integrations@makewebbetter.com';
					$subject = 'HubSpot PRO Customers Details';
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					$status  = wp_mail( $to, $subject, $message, $headers );
					return $status;
				}
			}
		}
		return false;
	}

	/**
	 * Get hubspot owner email.
	 *
	 * @since 1.0.0
	 * @return int $portalId portal id for hubspot account.
	 */
	public function hubwoo_get_owners_info() {

		$url     = '/integrations/v1/me';
		$headers = $this->get_token_headers();

		$response = wp_remote_get( $this->base_url . $url, array( 'headers' => $headers ) );

		$portal_id = '';

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}

		if ( 200 === $status_code ) {

			$api_body = wp_remote_retrieve_body( $response );

			if ( $api_body ) {
				$api_body = json_decode( $api_body );
			}
			//phpcs:disable
			if ( ! empty( $api_body ) && isset( $api_body->portalId ) ) {
				$portal_id = $api_body->portalId;
			}
			//phpcs:enable
		}

		return $portal_id;
	}

	/**
	 * Create group on hubspot.
	 *
	 * @since 1.0.0
	 * @param array $group_details formatted data to create new group.
	 * @return array $parsed_response formatted array with status code/response.
	 */
	public function create_group( $group_details ) {

		if ( is_array( $group_details ) ) {

			if ( isset( $group_details['name'] ) && isset( $group_details['displayName'] ) ) {

				$url     = '/properties/v1/contacts/groups';
				$headers = $this->get_token_headers();

				$group_details = wp_json_encode( $group_details );
				$response      = wp_remote_post(
					$this->base_url . $url,
					array(
						'body'    => $group_details,
						'headers' => $headers,
					)
				);
				$message       = esc_html__( 'Creating Groups', 'hubspot-for-woocommerce' );
				if ( is_wp_error( $response ) ) {
					$status_code = $response->get_error_code();
					$res_message = $response->get_error_message();
				} else {
					$status_code = wp_remote_retrieve_response_code( $response );
					$res_message = wp_remote_retrieve_response_message( $response );
				}
				$parsed_response = array(
					'status_code' => $status_code,
					'response'    => $res_message,
				);
				$this->create_log( $message, $url, $parsed_response );
				return $parsed_response;
			}
		}
	}

	/**
	 * Read a property from HubSpot.
	 *
	 * @since 1.0.0
	 * @param array $object_type Object name.
	 * @param array $property_name Property name.
	 * @return array $parsed_response Parsed Response.
	 */
	public function hubwoo_read_object_property( $object_type, $property_name ) {

		$url      = '/crm/v3/properties/' . $object_type . '/' . $property_name;
		$headers  = $this->get_token_headers();
		$res_body = '';
		$response = wp_remote_get( $this->base_url . $url, array( 'headers' => $headers ) );
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}

		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);

		$message = __( 'Reading object property', 'hubspot-for-woocommerce' );

		$this->create_log( $message, $url, $parsed_response );

		return $parsed_response;
	}

	/**
	 * Create property on hubspot.
	 *
	 * @since 1.0.0
	 * @param array $prop_details formatted data to create new property.
	 * @return array $parsed_response formatted array with status/message.
	 */
	public function create_property( $prop_details ) {

		if ( is_array( $prop_details ) ) {

			if ( isset( $prop_details['name'] ) && isset( $prop_details['groupName'] ) ) {

				$url          = '/properties/v1/contacts/properties';
				$headers      = $this->get_token_headers();
				$res_body     = '';
				$prop_details = wp_json_encode( $prop_details );
				$response     = wp_remote_post(
					$this->base_url . $url,
					array(
						'body'    => $prop_details,
						'headers' => $headers,
					)
				);
				$message      = esc_html__( 'Creating Properties', 'hubspot-for-woocommerce' );
				if ( is_wp_error( $response ) ) {
					$status_code = $response->get_error_code();
					$res_message = $response->get_error_message();
				} else {
					$status_code = wp_remote_retrieve_response_code( $response );
					$res_message = wp_remote_retrieve_response_message( $response );
					$res_body    = wp_remote_retrieve_body( $response );
				}

				$parsed_response = array(
					'status_code' => $status_code,
					'response'    => $res_message,
					'body'        => $res_body,
				);
				$this->create_log( $message, $url, $parsed_response );
				return $parsed_response;
			}
		}
	}

	/**
	 * Create Bulk Contact Property in HubSpot.
	 *
	 * @since 1.0.0
	 * @param array $properties_data all of the hs properties.
	 * @param array $object_type HubSpot Object type.
	 * @return array $parsed_response formatted array with status/message.
	 */
	public function create_batch_properties( $properties_data, $object_type ) {

		$request_body['inputs']     = $properties_data;
		$request_body['objectType'] = $object_type;

		if ( is_array( $request_body ) ) {

			$url          = '/crm/v3/properties/' . $object_type . '/batch/create';
			$headers      = $this->get_token_headers();
			$res_body     = '';
			$request_body = wp_json_encode( $request_body );
			$response     = wp_remote_post(
				$this->base_url . $url,
				array(
					'body'    => $request_body,
					'headers' => $headers,
				)
			);
			$message      = esc_html__( 'Creating Batch Properties', 'hubspot-for-woocommerce' );
			if ( is_wp_error( $response ) ) {
				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
				$res_body    = wp_remote_retrieve_body( $response );
			}

			$parsed_response = array(
				'status_code' => $status_code,
				'response'    => $res_message,
				'body'        => $res_body,
			);
			$this->create_log( $message, $url, $parsed_response );
			return $parsed_response;
		}
	}

	/**
	 * Update property on hubspot.
	 *
	 * @since 1.0.0
	 * @param array $prop_details formatted data to update hubspot property.
	 * @return array $parsed_response formatted array with status/message.
	 */
	public function update_property( $prop_details ) {
		// check if in the form of array.
		if ( is_array( $prop_details ) ) {
			// check for name and groupName.
			if ( isset( $prop_details['name'] ) && isset( $prop_details['groupName'] ) ) {

				// let's update.
				$url = '/properties/v1/contacts/properties/named/' . $prop_details['name'];

				$headers      = $this->get_token_headers();
				$prop_details = wp_json_encode( $prop_details );
				$response     = wp_remote_request(
					$this->base_url . $url,
					array(
						'method'  => 'PUT',
						'headers' => $headers,
						'body'    => $prop_details,
					)
				);

				$message = __( 'Updating Properties', 'hubspot-for-woocommerce' );

				if ( is_wp_error( $response ) ) {
					$status_code = $response->get_error_code();
					$res_message = $response->get_error_message();
				} else {
					$status_code = wp_remote_retrieve_response_code( $response );
					$res_message = wp_remote_retrieve_response_message( $response );
				}

				$parsed_response = array(
					'status_code' => $status_code,
					'response'    => $res_message,
				);

				$this->create_log( $message, $url, $parsed_response );

				return $parsed_response;
			}
		}
	}

	/**
	 * Create a single contact on HS.
	 *
	 * @since 1.0.0
	 * @param array $contact formatted data to create single contact on hubspot.
	 * @return array $parsed_response formatted array with status/message.
	 */
	public function create_single_contact( $contact ) {

		if ( is_array( $contact ) ) {

			$url      = '/contacts/v1/contact';
			$headers  = $this->get_token_headers();
			$contact  = wp_json_encode( $contact );
			$res_body = '';
			$response = wp_remote_post(
				$this->base_url . $url,
				array(
					'headers' => $headers,
					'body'    => $contact,
				)
			);
			$message  = __( 'Creating Single Contact', 'hubspot-for-woocommerce' );
			if ( is_wp_error( $response ) ) {
				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
				$res_body    = wp_remote_retrieve_body( $response );
			}

			$parsed_response = array(
				'status_code' => $status_code,
				'response'    => $res_message,
				'body'        => $res_body,
			);
			$this->create_log( $message, $url, $parsed_response );
			return $parsed_response;
		}
	}

	/**
	 * Create or update contacts.
	 *
	 * @param  array $contacts hubspot acceptable contacts array.
	 * @param  array $args ids of contacts or orders.
	 * @since 1.0.0
	 * @return array $parsed_response formatted array with status/message
	 */
	public function create_or_update_contacts( $contacts, $args = array() ) {

		if ( is_array( $contacts ) ) {

			$url      = '/contacts/v1/contact/batch/';
			$headers  = $this->get_token_headers();
			$res_body = '';

			$contacts = wp_json_encode( $contacts );
			$response = wp_remote_post(
				$this->base_url . $url,
				array(
					'body'    => $contacts,
					'headers' => $headers,
				)
			);
			$message  = esc_html__( 'Updating or Creating users data', 'hubspot-for-woocommerce' );

			if ( is_wp_error( $response ) ) {
				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
				$res_body    = wp_remote_retrieve_body( $response );
			}

			$parsed_response = array(
				'status_code' => $status_code,
				'response'    => $res_message,
				'body'        => $res_body,
			);

			if ( 202 == $status_code ) {
				update_option( 'hubwoo_last_sync_date', time() );
				if ( ! empty( $args ) && get_option( 'hubwoo_background_process_running', false ) ) {
					$hsocssynced  = get_option( 'hubwoo_ocs_contacts_synced', 0 );
					$hsocssynced += count( $args['ids'] );
					update_option( 'hubwoo_ocs_contacts_synced', $hsocssynced );
				}
				if ( isset( $args['ids'] ) && isset( $args['type'] ) ) {
					Hubwoo::hubwoo_marked_sync( $args['ids'], $args['type'] );
				}
			} elseif ( 400 === $status_code ) {

				$api_body = wp_remote_retrieve_body( $response );

				if ( $api_body ) {
					$api_body = json_decode( $api_body );
				}
				//phpcs:disable
				if ( ! empty( $api_body->invalidEmails ) ) {
					$savedinvalidemails = get_option( 'hubwoo_pro_invalid_emails', array() );
					foreach ( $api_body->invalidEmails as $single_email ) {
						//phpcs:enable
						if ( ! in_array( $single_email, $savedinvalidemails, true ) ) {
							$savedinvalidemails[] = $single_email;
						}
					}
				}
			}

			if ( ! empty( $savedinvalidemails ) ) {
				update_option( 'hubwoo_pro_invalid_emails', $savedinvalidemails );
			}
			$this->create_log( $message, $url, $parsed_response );
			return $parsed_response;
		}
	}

	/**
	 * Create or update contacts.
	 *
	 * @param  array $contacts hubspot acceptable contact properties array.
	 * @param  string $email email of contact.
	 * @since 1.2.6
	 * @return array $parsed_response formatted array with status/message
	 */
	public function create_or_update_single_contact( $contacts, $email ) {

		if ( is_array( $contacts ) ) {

			$url      = '/contacts/v1/contact/createOrUpdate/email/' . $email . '/';
			$headers  = $this->get_token_headers();
			$res_body = '';

			$contacts = wp_json_encode( $contacts );
			$response = wp_remote_post(
				$this->base_url . $url,
				array(
					'body'    => $contacts,
					'headers' => $headers,
				)
			);
			$message  = esc_html__( 'Updating or Creating single users data', 'hubspot-for-woocommerce' );

			if ( is_wp_error( $response ) ) {
				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
				$res_body    = wp_remote_retrieve_body( $response );
			}

			$parsed_response = array(
				'status_code' => $status_code,
				'response'    => $res_message,
				'body'        => $res_body,
			);

			$this->create_log( $message, $url, $parsed_response );
			return $parsed_response;
		}
	}

	/**
	 * Create list on hubspot.
	 *
	 * @since 1.0.0
	 * @param array $list_details formatted data to create new list on hubspot.
	 * @return array $parsed_response formatted array with status/message.
	 */
	public function create_list( $list_details ) {

		if ( is_array( $list_details ) ) {
			if ( isset( $list_details['name'] ) ) {
				$url          = '/contacts/v1/lists';
				$headers      = $this->get_token_headers();
				$res_body     = '';
				$list_details = wp_json_encode( $list_details );
				$response     = wp_remote_post(
					$this->base_url . $url,
					array(
						'body'    => $list_details,
						'headers' => $headers,
					)
				);
				$message      = __( 'Creating Lists', 'hubspot-for-woocommerce' );
				if ( is_wp_error( $response ) ) {
					$status_code = $response->get_error_code();
					$res_message = $response->get_error_message();
				} else {
					$status_code = wp_remote_retrieve_response_code( $response );
					$res_message = wp_remote_retrieve_response_message( $response );
					$res_body    = wp_remote_retrieve_body( $response );
				}

				$parsed_response = array(
					'status_code' => $status_code,
					'response'    => $res_message,
					'body'        => $res_body,
				);
				$this->create_log( $message, $url, $parsed_response );
				return $parsed_response;
			}
		}
	}

	/**
	 * Get all static lists from HS.
	 *
	 * @since 1.0.0
	 * @return array $lists formatted array with list id and name.
	 */
	public function get_static_list() {

		$lists           = array();
		$lists['select'] = __( '--Please Select a Static List--', 'hubspot-for-woocommerce' );

		$url      = '/contacts/v1/lists/static?count=250';
		$headers  = $this->get_token_headers();
		$response = wp_remote_get( $this->base_url . $url, array( 'headers' => $headers ) );
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}

		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);

		$message = __( 'Get Static Lists', 'hubspot-for-woocommerce' );

		$this->create_log( $message, $url, $parsed_response );

		if ( 200 == $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body, true );
			}
			if ( ! empty( $api_body->lists ) ) {

				foreach ( $api_body->lists as $single_list ) {
					//phpcs:disable
					if ( isset( $single_list->name ) && isset( $single_list->listId ) ) {

						$lists[ $single_list->listId ] = $single_list->name;
					}
					//phpcs:enable
				}
			}
		}

		return $lists;
	}

	/**
	 * Get all active lists from HS.
	 *
	 * @since 1.0.0
	 * @return array $lists formatted array with list id and name.
	 */
	public function get_dynamic_lists() {

		$lists = array();

		$url      = '/contacts/v1/lists/dynamic?count=250';
		$headers  = $this->get_token_headers();
		$response = wp_remote_get( $this->base_url . $url, array( 'headers' => $headers ) );
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}

		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);

		$message = __( 'Get Dynamic Lists', 'hubspot-for-woocommerce' );

		$this->create_log( $message, $url, $parsed_response );

		if ( 200 == $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body, true );
			}
			if ( ! empty( $api_body['lists'] ) ) {

				foreach ( $api_body['lists'] as $single_list ) {
					//phpcs:disable
					if ( isset( $single_list['name'] ) && isset( $single_list['listId'] ) ) {

						$lists[ $single_list['listId'] ] = $single_list['name'];
					}
					//phpcs:enable
				}
			}
		}

		return $lists;
	}

	/**
	 * Enroll a contact email in a list.
	 *
	 * @since 1.0.0
	 * @param string $email contact email to be enrolled.
	 * @param string $list_id id of the hubspot list.
	 * @return array  $parsed_response formatted array with status/response.
	 */
	public function list_enrollment( $email, $list_id ) {

		if ( ! empty( $email ) && ! empty( $list_id ) ) {

			$url      = '/contacts/v1/lists/' . $list_id . '/add';
			$headers  = $this->get_token_headers();
			$emails   = array();
			$emails[] = $email;
			$request  = array( 'emails' => $emails );
			$request  = wp_json_encode( $request );
			$response = wp_remote_post(
				$this->base_url . $url,
				array(
					'body'    => $request,
					'headers' => $headers,
				)
			);
			$message  = __( 'Enrolling in Static List', 'hubspot-for-woocommerce' );
			if ( is_wp_error( $response ) ) {
				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
			}
			$parsed_response = array(
				'status_code' => $status_code,
				'response'    => $res_message,
			);
			$this->create_log( $message, $url, $parsed_response );
			return $parsed_response;
		}
	}

	/**
	 * Create workflow on hubspot.
	 *
	 * @since 1.0.0
	 * @param array $workflow_details formatted array with workflow details.
	 * @return array $parsed_response formatted array with status/response.
	 */
	public function create_workflow( $workflow_details ) {

		if ( is_array( $workflow_details ) ) {

			if ( isset( $workflow_details['name'] ) ) {

				$url      = '/automation/v3/workflows';
				$headers  = $this->get_token_headers();
				$res_body = '';
				$workflow = wp_json_encode( $workflow_details );
				$response = wp_remote_post(
					$this->base_url . $url,
					array(
						'body'    => $workflow,
						'headers' => $headers,
					)
				);
				$message  = __( 'Creating Workflows', 'hubspot-for-woocommerce' );
				if ( is_wp_error( $response ) ) {
					$status_code = $response->get_error_code();
					$res_message = $response->get_error_message();
				} else {
					$status_code = wp_remote_retrieve_response_code( $response );
					$res_message = wp_remote_retrieve_response_message( $response );
					$res_body    = wp_remote_retrieve_body( $response );
				}
				$parsed_response = array(
					'status_code' => $status_code,
					'response'    => $res_message,
					'body'        => $res_body,
				);
				$this->create_log( $message, $url, $parsed_response );
				return $parsed_response;
			}
		}
	}

	/**
	 * Get all workflows from hubspot.
	 *
	 * @since 1.0.0
	 */
	public function get_workflows() {

		$workflows           = array();
		$workflows['select'] = esc_html__( '--Please Select a Workflow--', 'hubspot-for-woocommerce' );
		$url                 = '/automation/v3/workflows';
		$headers             = $this->get_token_headers();
		$response            = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}

		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);

		$message = __( 'Getting all Workflows', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );

		if ( 200 == $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body, true );
			}
		} else {
			$workflows = array();
		}

		if ( ! empty( $response->workflows ) ) {

			foreach ( $response->workflows as $single_workflow ) {

				if ( isset( $single_workflow->name ) && isset( $single_workflow->id ) ) {

					$workflows[ $single_workflow->id ] = $single_workflow->name;
				}
			}
		}

		return $workflows;
	}

	/**
	 * Enroll a contact email in a worlflow.
	 *
	 * @since 1.0.0
	 * @param string $email contact email.
	 * @param string $workflow_id id of hubspot workflow.
	 */
	public function workflow_enrollment( $email, $workflow_id ) {

		if ( ! empty( $email ) && ! empty( $workflow_id ) ) {

			$url      = '/automation/v2/workflows/' . $workflow_id . '/enrollments/contacts/' . $email;
			$headers  = $this->get_token_headers();
			$response = $this->_post( $url, array(), $headers );
			$message  = __( 'Enrolling in Workflow', 'hubspot-for-woocommerce' );
			$this->create_log( $message, $url, $response );
		}
	}

	/**
	 * Create log of requests.
	 *
	 * @param  string $message     hubspot log message.
	 * @param  string $url         hubspot acceptable url.
	 * @param  array  $response    hubspot response array.
	 * @since 1.0.0
	 */
	public function create_log( $message, $url, $response ) {

		if ( isset( $response['status_code'] ) ) {

			if ( 400 == $response['status_code'] || 401 == $response['status_code'] ) {

				update_option( 'hubwoo_pro_alert_param_set', true );
				$error_apis = get_option( 'hubwoo-error-api-calls', 0 );
				$error_apis ++;
				update_option( 'hubwoo-error-api-calls', $error_apis );
			} elseif ( 200 == $response['status_code'] || 202 == $response['status_code'] || 201 == $response['status_code'] || 204 == $response['status_code'] ) {

				$success_apis = get_option( 'hubwoo-success-api-calls', 0 );
				$success_apis ++;
				update_option( 'hubwoo-success-api-calls', $success_apis );
				update_option( 'hubwoo_pro_alert_param_set', false );
			} else {

				update_option( 'hubwoo_pro_alert_param_set', false );
			}

			if ( 200 == $response['status_code'] ) {

				$final_response['status_code'] = 200;
			} elseif ( 202 == $response['status_code'] ) {

				$final_response['status_code'] = 202;
			} else {

				$final_response = $response;
			}

			$log_enable = get_option( 'hubwoo_pro_log_enable', 'yes' );

			if ( 'yes' == $log_enable ) {

				if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
					$server = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
				}

				$log_dir = WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log';

				if ( ! is_dir( $log_dir ) ) {

					@fopen( WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log', 'a' );
				}

				$log = 'Website: ' . $server . PHP_EOL .
						'Time: ' . current_time( 'F j, Y  g:i a' ) . PHP_EOL .
						'Process: ' . $message . PHP_EOL .
						'URL: ' . $url . PHP_EOL .
						'Response: ' . json_encode( $final_response ) . PHP_EOL .
						'-----------------------------------' . PHP_EOL;

				file_put_contents( $log_dir, $log, FILE_APPEND );
			}
		}
	}

	/**
	 * Getting all hubspot properties.
	 *
	 * @since 1.0.0
	 */
	public function get_all_hubspot_properties() {

		$response = '';

		$url = '/properties/v1/contacts/properties';

		$headers = $this->get_token_headers();

		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);

		$message = esc_html__( 'Fetching all Contact Properties', 'hubspot-for-woocommerce' );

		$this->create_log( $message, $url, $response );

		if ( isset( $response['status_code'] ) && 200 == $response['status_code'] ) {

			if ( isset( $response['response'] ) ) {

				$response = json_decode( $response['response'] );
			}
		}

		return $response;
	}

	/**
	 * Get all contcat lists from hubspot.
	 *
	 * @since 1.0.0
	 * @param int $count count of list to get from HubSpot.
	 * @param int $offset offset for get call.
	 */
	public function get_all_contact_lists( $count, $offset ) {

		$response = array();

		$url = '/contacts/v1/lists?count=' . $count . '&offset=' . $offset;

		$headers = $this->get_token_headers();

		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);

		$message = __( 'Fetching Contact Lists', 'hubspot-for-woocommerce' );

		$this->create_log( $message, $url, $response );

		if ( isset( $response['status_code'] ) && 200 == $response['status_code'] ) {

			if ( isset( $response['response'] ) ) {

				$response = json_decode( $response['response'] );
			}
		}

		return $response;
	}

	/**
	 * Get all contacts in a list.
	 *
	 * @since 1.0.0
	 * @param int $list_id id of the list.
	 * @param int $offset list offset.
	 */
	public function get_contacts_in_list( $list_id, $offset ) {

		$response = array();

		$url = '/contacts/v1/lists/' . $list_id . '/contacts/all?count=50&vidOffset=' . $offset;

		$headers = $this->get_token_headers();

		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);

		$message = __( 'Fetching Contacts from List', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $response );

		if ( isset( $response['status_code'] ) && 200 == $response['status_code'] ) {

			if ( isset( $response['response'] ) ) {

				$response = json_decode( $response['response'] );
			}
		}

		return $response;
	}

	/**
	 * Remove deal associations on email change.
	 *
	 * @since 1.0.0
	 * @param string $deal_id id of the deal.
	 * @param string $vid id of the contact.
	 * @return array $response formatted aray for response.
	 */
	public function remove_deal_associations( $deal_id, $vid ) {

		$url      = '/crm-associations/v1/associations/delete';
		$headers  = $this->get_token_headers();
		$request  = array(
			'fromObjectId' => $vid,
			'toObjectId'   => $deal_id,
			'category'     => 'HUBSPOT_DEFINED',
			'definitionId' => '4',
		);
		$request  = json_encode( $request );
		$response = wp_remote_request(
			$this->base_url . $url,
			array(
				'body'    => $request,
				'headers' => $headers,
				'method'  => 'PUT',
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		$message         = __( 'Removing Deal Association With Contact', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $response;
	}

	/**
	 * Create deal associations.
	 *
	 * @since 1.0.0
	 * @param string $deal_id id of the deal.
	 * @param string $vid id of the contact.
	 * @return array $response formatted aray for response.
	 */
	public function create_deal_associations( $deal_id, $vid ) {

		$url     = '/crm-associations/v1/associations';
		$headers = $this->get_token_headers();
		$request = array(
			'fromObjectId' => $vid,
			'toObjectId'   => $deal_id,
			'category'     => 'HUBSPOT_DEFINED',
			'definitionId' => '4',
		);
		$request = json_encode( $request );

		$response = wp_remote_request(
			$this->base_url . $url,
			array(
				'body'    => $request,
				'headers' => $headers,
				'method'  => 'PUT',
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		$message         = __( 'Creating Deal Association With Contact', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Create deal and company associations.
	 *
	 * @since 1.2.7
	 * @param string $deal_id id of the deal.
	 * @param string $company_id id of the company.
	 * @return array $response formatted aray for response.
	 */
	public function create_deal_company_associations( $deal_id, $company_id ) {

		$url     = '/crm-associations/v1/associations';
		$headers = $this->get_token_headers();
		$request = array(
			'fromObjectId' => $company_id,
			'toObjectId'   => $deal_id,
			'category'     => 'HUBSPOT_DEFINED',
			'definitionId' => '6',
		);
		$request = json_encode( $request );

		$response = wp_remote_request(
			$this->base_url . $url,
			array(
				'body'    => $request,
				'headers' => $headers,
				'method'  => 'PUT',
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		$message         = __( 'Creating Deal Association With Company', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Updating deals.
	 *
	 * @since    1.0.0
	 * @param string $deal_id id of the deal.
	 * @param array  $deal_details details of the deal.
	 */
	public function update_existing_deal( $deal_id, $deal_details ) {

		$url          = '/deals/v1/deal/' . $deal_id;
		$headers      = $this->get_token_headers();
		$res_body     = '';
		$deal_details = json_encode( $deal_details );

		$response = wp_remote_request(
			$this->base_url . $url,
			array(
				'body'    => $deal_details,
				'headers' => $headers,
				'method'  => 'PUT',
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);
		$message         = __( 'Updating HubSpot Deals', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Creating groups for deals on HubSpot.
	 *
	 * @since 1.0.0
	 * @param array $deal_groups formatted array for deal groups.
	 * @return array $parsed_response formatted array for API response.
	 */
	public function create_deal_group( $deal_groups ) {

		$url             = '/properties/v1/deals/groups/';
		$headers         = $this->get_token_headers();
		$parsed_response = array(
			'status_code' => 400,
			'response'    => 'error',
		);
		if ( is_array( $deal_groups ) && count( $deal_groups ) ) {
			$deal_details = wp_json_encode( $deal_groups );
			$response     = wp_remote_post(
				$this->base_url . $url,
				array(
					'body'    => $deal_details,
					'headers' => $headers,
				)
			);
			$message      = __( 'Creating deal custom groups', 'hubspot-for-woocommerce' );
			if ( is_wp_error( $response ) ) {
				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
			}
			$parsed_response = array(
				'status_code' => $status_code,
				'response'    => $res_message,
			);
			$this->create_log( $message, $url, $parsed_response );
			return $parsed_response;
		}
	}

	/**
	 * Creating properties for deals.
	 *
	 * @since 1.0.0
	 * @param array $prop_details formatted array for creating deal property.
	 * @return array $parsed_response formatted response from API.
	 */
	public function create_deal_property( $prop_details ) {

		$url = '/properties/v1/deals/properties/';
		if ( is_array( $prop_details ) ) {
			if ( isset( $prop_details['name'] ) && isset( $prop_details['groupName'] ) ) {
				$url             = '/properties/v1/deals/properties/';
				$headers         = $this->get_token_headers();
				$parsed_response = array(
					'status_code' => 400,
					'response'    => 'error',
				);
				$prop_details    = wp_json_encode( $prop_details );
				$response        = wp_remote_post(
					$this->base_url . $url,
					array(
						'body'    => $prop_details,
						'headers' => $headers,
					)
				);
				$message         = __( 'Creating deal custom properties', 'hubspot-for-woocommerce' );
				if ( is_wp_error( $response ) ) {
					$status_code = $response->get_error_code();
					$res_message = $response->get_error_message();
				} else {
					$status_code = wp_remote_retrieve_response_code( $response );
					$res_message = wp_remote_retrieve_response_message( $response );
				}
				$parsed_response = array(
					'status_code' => $status_code,
					'response'    => $res_message,
				);
				$this->create_log( $message, $url, $parsed_response );
				return $parsed_response;
			}
		}
	}

	/**
	 * Updating deal properties.
	 *
	 * @since 1.0.0
	 * @param array $deal_property details to create new deal.
	 * @return array $response api response.
	 */
	public function update_deal_property( $deal_property ) {

		if ( is_array( $deal_property ) ) {
			if ( isset( $deal_property['name'] ) && isset( $deal_property['groupName'] ) ) {
				$url           = '/properties/v1/deals/properties/named/' . $deal_property['name'];
				$headers       = $this->get_token_headers();
				$deal_property = json_encode( $deal_property );
				$response      = wp_remote_request(
					$this->base_url . $url,
					array(
						'body'    => $deal_property,
						'headers' => $headers,
						'method'  => 'PUT',
					)
				);

				if ( is_wp_error( $response ) ) {
					$status_code = $response->get_error_code();
					$res_message = $response->get_error_message();
				} else {
					$status_code = wp_remote_retrieve_response_code( $response );
					$res_message = wp_remote_retrieve_response_message( $response );
				}
				$parsed_response = array(
					'status_code' => $status_code,
					'response'    => $res_message,
				);

				$message = __( 'Updating HubSpot Deal Properties', 'hubspot-for-woocommerce' );
				$this->create_log( $message, $url, $parsed_response );
				return $parsed_response;
			}
		}
	}

	/**
	 * Get customer vid using email.
	 *
	 * @since 1.0.0
	 * @param string $email contact email.
	 * @return string $vid hubspot vid of contact.
	 */
	public function get_customer_vid( $email ) {

		$vid      = '';
		$url      = '/contacts/v1/contact/email/' . $email . '/profile';
		$headers  = $this->get_token_headers();
		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		$message         = esc_html__( 'Fetching Contact VID by email', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $vid;
	}

	/**
	 * Get customer vid using email.
	 *
	 * @since 1.0.0
	 * @param string $email contact email.
	 * @return string $vid hubspot vid of contact.
	 */
	public function get_customer_vid_historical( $email ) {
		$vid      = '';
		$url      = '/contacts/v1/contact/email/' . $email . '/profile';
		$headers  = $this->get_token_headers();
		$res_body = '';

		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
			$parsed_response = array(
				'status_code' => $status_code,
				'response'    => $res_message,
				'body'        => $res_body,
			);
			$message         = esc_html__( 'Fetching Contact VID by email', 'hubspot-for-woocommerce' );
			$this->create_log( $message, $url, $parsed_response );
			return $parsed_response;
	}

	/**
	 * Creating deals on HubSpot.
	 *
	 * @since 1.0.0
	 * @param array $deal_details details to create new deal.
	 * @return array $parsed_response formatted array with status/response.
	 */
	public function create_new_deal( $deal_details ) {

		$url          = '/deals/v1/deal/';
		$headers      = $this->get_token_headers();
		$res_body     = '';
		$deal_details = wp_json_encode( $deal_details );
		$response     = wp_remote_post(
			$this->base_url . $url,
			array(
				'body'    => $deal_details,
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);
		$message         = esc_html__( 'Creating New deal', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Fetching all of the deal stages
	 *
	 * @since 1.0.0
	 * @return array $api_body formatted object with get request
	 */
	public function fetch_all_deal_pipelines() {

		$url     = '/crm-pipelines/v1/pipelines/deals';
		$headers = $this->get_token_headers();

		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}

		if ( 200 == $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body, true );
			}
		} else {
			$api_body = array();
		}

		return $api_body;
	}

	/**
	 * Get General Headers for API Calls.
	 *
	 * @since  1.0.0
	 * @param  array $additional_args (default: array()) Additional headers to be passed.
	 * @return array $headers All of the headers.
	 */
	private function get_token_headers( $additional_args = array() ) {

		$access_token = Hubwoo::hubwoo_get_access_token();
		$headers      = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $access_token,
		);

		if ( ! empty( $additional_args ) ) {
			$headers = array_merge( $headers, $additional_args );
		}

		return $headers;
	}

	/**
	 * Get General Headers for API Calls.
	 *
	 * @since  1.0.0
	 * @param  array $stores store details required for HubSpot.
	 * @return array $parsed_response Response from HubSpot.
	 */
	public function create_or_update_store( $stores ) {

		$headers  = self::get_token_headers();
		$url      = '/extensions/ecomm/v2/stores';
		$stores   = json_encode( $stores );
		$res_body = '';
		$response = wp_remote_request(
			$this->base_url . $url,
			array(
				'body'    => $stores,
				'headers' => $headers,
				'method'  => 'PUT',
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => json_decode( $res_body ),
		);
		$message         = __( 'Creating or Updating Store', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Sending sync messages to hubspot
	 *
	 * @since 1.0.0
	 * @param   array  $updates        array of properties showing the changes.
	 * @param   string $object_type    hubspot object name.
	 * @return  array response from HubSpot.
	 */
	public function ecomm_sync_messages( $updates, $object_type ) {

		$messages               = array();
		$res_body               = '';
		$messages['storeId']    = get_option( 'hubwoo_ecomm_store_id', '' );
		$messages['objectType'] = $object_type;
		$messages['messages']   = $updates;
		$url                    = '/extensions/ecomm/v2/sync/messages';
		$headers                = self::get_token_headers();
		$messages               = json_encode( $messages );

		$response = wp_remote_request(
			$this->base_url . $url,
			array(
				'body'    => $messages,
				'headers' => $headers,
				'method'  => 'PUT',
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);
		$message         = 'Updating/Creating Ecomm Bridge - ' . $object_type;
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Sending sync messages to hubspot
	 *
	 * @since 1.0.0
	 * @param   array  $object_id    external object Id.
	 * @param   string $object_type hubspot object name.
	 * @return  array response from HubSpot.
	 */
	public function ecomm_sync_status( $object_id, $object_type ) {

		$store_id = get_option( 'hubwoo_ecomm_store_id', '' );
		$res_body = '';
		$url      = '/extensions/ecomm/v2/sync/status/' . $store_id . '/' . $object_type . '/' . $object_id;
		$headers  = self::get_token_headers();
		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);
		$message         = 'Checking Sync Status of object id -' . $object_id . ' and type ' . $object_type;
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Updating the deal stages in eCommerce Pipeline
	 *
	 * @since 1.0.0
	 * @param string $pipeline_updates updated pipeline data.
	 * @return array $response formatted aray for response.
	 */
	public function update_deal_pipeline( $pipeline_updates ) {

		$url              = '/crm-pipelines/v1/pipelines/deals/' . $pipeline_updates['pipelineId'];
		$headers          = $this->get_token_headers();
		$pipeline_updates = json_encode( $pipeline_updates );
		$response         = wp_remote_request(
			$this->base_url . $url,
			array(
				'body'    => $pipeline_updates,
				'headers' => $headers,
				'method'  => 'PUT',
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		$message         = __( 'Updating the ecommerce pipeline deal stages', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Create a form data.
	 *
	 * @since 1.0.4
	 * @param array $form_data post form data.
	 * @return array $response formatted aray for response.
	 */
	public function create_form_data( $form_data ) {

		$url       = '/forms/v2/forms';
		$headers   = $this->get_token_headers();
		$res_body  = '';
		$form_data = wp_json_encode( $form_data );
		$response  = wp_remote_post(
			$this->base_url . $url,
			array(
				'body'    => $form_data,
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);
		$message         = __( 'Creating a form data', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Submit a form data to HubSpot.
	 *
	 * @since 1.0.0
	 * @param array  $form_data form fields.
	 * @param string $portal_id portal id.
	 * @param string $form_guid form id.
	 * @return array $response formatted aray for response.
	 */
	public function submit_form_data( $form_data, $portal_id, $form_guid ) {

		$url      = 'https://api.hsforms.com/submissions/v3/integration/submit/' . $portal_id . '/' . $form_guid;
		$res_body = '';
		$headers  = $this->get_token_headers();

		$form_data = json_encode( $form_data );

		$response = wp_remote_post(
			$url,
			array(
				'body'    => $form_data,
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);
		$message         = __( 'Submitting Form data', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Get Batch contacts from emails.
	 *
	 * @since 1.0.0
	 * @param array $batch_emails batch email string.
	 * @return array $response formatted aray for response.
	 */
	public function hubwoo_get_batch_vids( $batch_emails ) {

		$url      = '/contacts/v1/contact/emails/batch/?' . $batch_emails . 'property=email';
		$headers  = $this->get_token_headers();
		$res_body = '';

		$batch_emails = json_encode( $batch_emails );

		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);
		$message         = __( 'Fetching Batch Vids', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}


	/**
	 * Get All forms.
	 *
	 * @since 1.0.
	 * @return array $response formatted aray for response.
	 */
	public function hubwoo_get_all_forms() {

		$url      = '/forms/v2/forms';
		$res_body = '';
		$headers  = $this->get_token_headers();

		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);
		$message         = __( 'Fetching All Forms', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * Fetchig user by email.
	 *
	 * @since 1.0.0
	 * @param string $email email of user.
	 */
	public function get_customer_by_email( $email ) {

		$vid = '';
		$url = '/contacts/v1/contact/email/' . $email . '/profile';
		$headers = $this->get_token_headers();
		$response = wp_remote_get(
			$this->base_url . $url,
			array(
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response' => $res_message,
			'body' => $res_body,
		);

		if ( 200 == $parsed_response['status_code'] ) {
			$parsed_response['body'] = json_decode( $parsed_response['body'], true );
			if ( ! empty( $parsed_response['body'] ) && isset( $parsed_response['body']['vid'] ) ) {
				$vid = $parsed_response['body']['vid'];
			}
		}
		$message = esc_html__( 'Fetching Contact by email', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $vid;
	}

	/**
	 * Updating products.
	 *
	 * @since    1.2.7
	 * @param string $hubwoo_ecomm_pro_id hubspot product id.
	 * @param array  $properties product properties.
	 */
	public function update_existing_products( $hubwoo_ecomm_pro_id, $properties ) {

		$url          = '/crm-objects/v1/objects/products/' . $hubwoo_ecomm_pro_id;
		$headers      = $this->get_token_headers();
		$properties   = json_encode( $properties );
		$response     = wp_remote_request(
			$this->base_url . $url,
			array(
				'body'    => $properties,
				'headers' => $headers,
				'method'  => 'PUT',
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
			$res_body    = wp_remote_retrieve_body( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
			'body'        => $res_body,
		);
		$message         = __( 'Updating HubSpot Products', 'hubspot-for-woocommerce' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}
}
