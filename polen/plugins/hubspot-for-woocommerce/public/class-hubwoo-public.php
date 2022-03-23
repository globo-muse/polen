<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/public
 */
class Hubwoo_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Update key as soon as user data is updated.
	 *
	 * @since    1.0.0
	 * @param      string $user_id       User Id.
	 */
	public function hubwoo_woocommerce_save_account_details( $user_id ) {

		update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
	}

	/**
	 * Enqueuing the HubSpot Tracking Script
	 * in the footer file
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_add_hs_script() {
		if ( ! in_array( 'leadin/leadin.php', get_option( 'active_plugins' ), true ) ) {
			$portal_id = get_option( 'hubwoo_pro_hubspot_id', '' );
			if ( ! empty( $portal_id ) ) {
				wp_enqueue_script( 'hs-script-loader', '//js.hs-scripts.com/' . $portal_id . '.js', array( 'jquery' ), WC_VERSION, true );
			}
		}
	}

	/**
	 * Update key as soon as guest order is done
	 *
	 * @since    1.0.0
	 * @param    string $order_id       Order Id.
	 */
	public function hubwoo_pro_woocommerce_guest_orders( $order_id ) {

		if ( ! empty( $order_id ) ) {

			$customer_id = get_post_meta( $order_id, '_customer_user', true );

			if ( empty( $customer_id ) || 0 == $customer_id ) {

				update_post_meta( $order_id, 'hubwoo_pro_guest_order', 'yes' );
			} else {
				update_user_meta( $customer_id, 'hubwoo_pro_user_data_change', 'yes' );
			}
		}
	}

	/**
	 * Update key as soon as order is renewed
	 *
	 * @since    1.0.0
	 * @param      string $order_id       Order Id.
	 */
	public function hubwoo_pro_save_renewal_orders( $order_id ) {

		if ( ! empty( $order_id ) ) {

			$user_id = (int) get_post_meta( $order_id, '_customer_user', true );

			if ( 0 !== $user_id && 0 < $user_id ) {

				update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
			}
		}
	}

	/**
	 * Update key as soon as customer make changes in his/her subscription orders
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_save_changes_in_subs() {

		$user_id = get_current_user_id();

		if ( $user_id ) {

			update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
		}
	}

	/**
	 * Update key as soon as customer make changes in his/her subscription orders
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_subscription_switch() {

		if ( isset( $_GET['switch-subscription'] ) && isset( $_GET['item'] ) ) {

			$user_id = get_current_user_id();

			if ( $user_id ) {

				update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
			}
		}
	}

	/**
	 * Update key as soon as subscriptions order status changes.
	 *
	 * @since 1.0.0
	 * @param object $subs subscription order object.
	 */
	public function hubwoo_pro_update_subs_changes( $subs ) {

		if ( ! empty( $subs ) && ( $subs instanceof WC_Subscription ) ) {

			$order_id = $subs->get_id();

			if ( ! empty( $order_id ) ) {

				$user_id = (int) get_post_meta( $order_id, '_customer_user', true );

				if ( 0 !== $user_id && 0 < $user_id ) {

					update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'yes' );
				}
			}
		}
	}


	/**
	 * Add checkout optin checkbox at woocommerce checkout
	 *
	 * @since    1.0.0
	 * @param object $checkout woocommerce checkut object.
	 */
	public function hubwoo_pro_checkout_field( $checkout ) {

		if ( is_user_logged_in() ) {
			$subscribe_status    = get_user_meta( get_current_user_id(), 'hubwoo_checkout_marketing_optin', true );
			$registeration_optin = get_user_meta( get_current_user_id(), 'hubwoo_registeration_marketing_optin', true );
		}
		if ( ! empty( $subscribe_status ) && 'yes' === $subscribe_status ) {
			return;
		} elseif ( ! empty( $registeration_optin ) && 'yes' === $registeration_optin ) {
			return;
		}
		$label = get_option( 'hubwoo_checkout_optin_label', __( 'Subscribe', 'hubspot-for-woocommerce' ) );
		echo '<div class="form-row form-row-wide hubwoo_checkout_marketing_optin">';
		woocommerce_form_field(
			'hubwoo_checkout_marketing_optin',
			array(
				'type'  => 'checkbox',
				'class' => array( 'hubwoo-input-checkbox', 'woocommerce-form__input', 'woocommerce-form__input-checkbox' ),
				'label' => $label,
			),
			WC()->checkout->get_value( 'hubwoo_checkout_marketing_optin' )
		);
		echo '</div>';
	}

	/**
	 * Optin checkbox on My Account page.
	 *
	 * @since    1.0.0
	 */
	public function hubwoo_pro_register_field() {

		$label = get_option( 'hubwoo_registeration_optin_label', __( 'Subscribe', 'hubspot-for-woocommerce' ) );
		echo '<div class="form-row form-row-wide hubwoo_registeration_marketing_optin">';
		woocommerce_form_field(
			'hubwoo_registeration_marketing_optin',
			array(
				'type'    => 'checkbox',
				'class'   => array( 'hubwoo-input-checkbox', 'woocommerce-form__input', 'woocommerce-form__input-checkbox' ),
				'label'   => $label,
				'default' => 'yes',
			),
			'yes'
		);
		echo '</div>';
	}

	/**
	 * Save order meta when any user optin through checkout.
	 *
	 * @since 1.0.0
	 * @param int $order_id order ID.
	 */
	public function hubwoo_pro_process_checkout_optin( $order_id ) {

		if ( ! empty( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) {

			$request = sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-process-checkout-nonce'] ) );

			$nonce_value = wc_get_var( $request );

			if ( ! empty( $nonce_value ) && wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
				if ( ! empty( $_POST['hubwoo_checkout_marketing_optin'] ) ) {

					if ( ! empty( $order_id ) ) {

						if ( is_user_logged_in() ) {

							update_user_meta( get_current_user_id(), 'hubwoo_checkout_marketing_optin', 'yes' );
						} else {

							update_post_meta( $order_id, 'hubwoo_checkout_marketing_optin', 'yes' );
						}
					}
				}
			}
		}
	}

	/**
	 * Save user meta when they optin via woocommerce registeration form.
	 *
	 * @since 1.0.0
	 * @param int $user_id user ID.
	 */
	public function hubwoo_save_register_optin( $user_id ) {

		if ( empty( $user_id ) ) {
			return;
		}
		$nonce_value = isset( $_POST['woocommerce-register-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-register-nonce'] ) ) : '';
		if ( isset( $_POST['email'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ) {
			if ( isset( $_POST['hubwoo_registeration_marketing_optin'] ) ) {
				update_user_meta( $user_id, 'hubwoo_registeration_marketing_optin', 'yes' );
			}
		}
	}

	/**
	 * Create a new ecomm deal.
	 *
	 * @since 1.0.0
	 * @param int $order_id order id to be updated.
	 */
	public function hubwoo_ecomm_deal_on_new_order( $order_id ) {

		if ( ! empty( $order_id ) ) {

			$post_type = get_post_type( $order_id );

			if ( 'shop_subscription' == $post_type ) {
				return;
			}
			as_schedule_single_action( time() + 10, 'hubwoo_ecomm_deal_upsert', array( $order_id ) );
		}
	}

	/**
	 * Update a ecomm deal.
	 *
	 * @since 1.0.0
	 * @param int $order_id order id to be updated.
	 */
	public function hubwoo_ecomm_deal_update_order( $order_id ) {

		if ( ! empty( $order_id ) ) {

			$post_type = get_post_type( $order_id );

			if ( 'shop_subscription' == $post_type ) {
				return;
			}
			as_schedule_single_action( time() + 10, 'hubwoo_ecomm_deal_upsert', array( $order_id ) );
		}
	}

	/**
	 * Start session for abandonec carts.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_abncart_start_session() {
		if ( WC()->is_rest_api_request() ) {
			return;
		}

		if ( function_exists( 'WC' ) && ! empty( WC()->session ) && ! is_admin() ) {

			WC()->session;
			self::hubwoo_abncart_set_locale();
		}
	}

	/**
	 * Save cart of the user if captured by HubSpot.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_track_cart_for_formuser() {

		if ( ! empty( WC()->session ) ) {
			if ( ! empty( WC()->session->get( 'mwb_guest_user_email' ) ) && empty( WC()->session->get( 'hs_form_user_tracked' ) ) ) {

				$guest_user_cart = array();

				if ( function_exists( 'WC' ) ) {

					$guest_user_cart['cart'] = WC()->session->cart;
				} else {

					$guest_user_cart['cart'] = $woocommerce->session->cart;
				}

				if ( empty( $guest_user_cart['cart'] ) ) {
					return;
				}

				$get_cookie = WC()->session->get_session_cookie();

				$session_id = $get_cookie[0];

				$locale = ! empty( WC()->session->get( 'locale' ) ) ? WC()->session->get( 'locale' ) : '';

				$user_data = array(
					'email'     => WC()->session->get( 'mwb_guest_user_email' ),
					'cartData'  => $guest_user_cart,
					'timeStamp' => time(),
					'sessionID' => $session_id,
					'locale'    => $locale,
					'sent'      => 'no',
				);

				self::hubwoo_abncart_update_new_data( WC()->session->get( 'mwb_guest_user_email' ), $user_data, $session_id );

				WC()->session->set( 'hs_form_user_tracked', true );
			}
		}
	}

	/**
	 * Save cart when billing email is entered on checkout.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_save_guest_user_cart() {

		check_ajax_referer( 'hubwoo_cart_security', 'nonce' );

		if ( ! empty( $_POST['email'] ) ) {

			$posted_email = sanitize_email( wp_unslash( $_POST['email'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$guest_user_cart = array();

			if ( function_exists( 'WC' ) ) {

				$guest_user_cart['cart'] = WC()->session->cart;
			} else {

				$guest_user_cart['cart'] = $woocommerce->session->cart;
			}

			$get_cookie = WC()->session->get_session_cookie();

			$session_id = $get_cookie[0];

			if ( ! empty( $session_id ) ) {

				$locale = ! empty( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : '';

				if ( empty( WC()->session->get( 'mwb_guest_user_email' ) ) ) {

					WC()->session->set( 'mwb_guest_user_email', ! empty( $posted_email ) ? $posted_email : '' );
					$user_data = array(
						'email'     => WC()->session->get( 'mwb_guest_user_email' ),
						'cartData'  => $guest_user_cart,
						'timeStamp' => time(),
						'sessionID' => $session_id,
						'locale'    => $locale,
						'sent'      => 'no',
					);

					self::hubwoo_abncart_update_new_data( WC()->session->get( 'mwb_guest_user_email' ), $user_data, $session_id );
				} else {

					$new_email_entered = ! empty( $posted_email ) ? $posted_email : '';

					$before_entered_email = WC()->session->get( 'mwb_guest_user_email' );

					WC()->session->set( 'mwb_guest_user_email', $new_email_entered );

					$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );

					if ( ! empty( $existing_cart_data ) ) {

						if ( $new_email_entered === $before_entered_email ) {

							foreach ( $existing_cart_data as $key => &$single_cart_data ) {

								if ( array_key_exists( 'sessionID', $single_cart_data ) && $single_cart_data['sessionID'] == $session_id ) {

									$single_cart_data['cartData']  = $guest_user_cart;
									$single_cart_data['timeStamp'] = time();
									$single_cart_data['locale']    = $locale;
									$single_cart_data['sent']      = 'no';
									break;
								} elseif ( array_key_exists( 'email', $single_cart_data ) && $single_cart_data['email'] == $before_entered_email ) {

									$single_cart_data['cartData']  = $guest_user_cart;
									$single_cart_data['timeStamp'] = time();
									$single_cart_data['locale']    = $locale;
									$single_cart_data['sent']      = 'no';
									break;
								}
							}
						} else {

							foreach ( $existing_cart_data as $key => &$single_cart_data ) {

								if ( array_key_exists( 'sessionID', $single_cart_data ) && $single_cart_data['sessionID'] == $session_id ) {

									$single_cart_data['cartData']  = $guest_user_cart;
									$single_cart_data['timeStamp'] = time();
									$single_cart_data['email']     = $new_email_entered;
									$single_cart_data['locale']    = $locale;
									$single_cart_data['sent']      = 'no';

									$user_data            = array(
										'email'     => $before_entered_email,
										'cartData'  => '',
										'timeStamp' => time(),
										'sessionID' => $session_id,
										'locale'    => $locale,
										'sent'      => 'no',
									);
									$existing_cart_data[] = $user_data;
									break;
								}
							}
						}
					} else {

						WC()->session->set( 'mwb_guest_user_email', ! empty( $posted_email ) ? $posted_email : '' );
						$user_data = array(
							'email'     => WC()->session->get( 'mwb_guest_user_email' ),
							'cartData'  => $guest_user_cart,
							'timeStamp' => time(),
							'sessionID' => $session_id,
							'locale'    => $locale,
							'sent'      => 'no',
						);
						self::hubwoo_abncart_update_new_data( WC()->session->get( 'mwb_guest_user_email' ), $user_data, $session_id );
					}

					update_option( 'mwb_hubwoo_guest_user_cart', $existing_cart_data );
				}
			}

			wp_die();
		}
	}

	/**
	 * Set site local in session.
	 *
	 * @since 1.0.0
	 */
	public static function hubwoo_abncart_set_locale() {

		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {

			$locale = ICL_LANGUAGE_CODE;
		} else {

			$locale = get_locale();
		}

		if ( ! empty( $locale ) ) {

			WC()->session->set( 'locale', $locale );
		}
	}

	/**
	 * Track billing form for email on checkout.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_track_email_for_guest_users() {

		if ( ! is_user_logged_in() ) {

			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {

				$locale = ICL_LANGUAGE_CODE;
			} else {

				$locale = get_locale();
			}
			?>
			<script type="text/javascript">
				jQuery( 'input#billing_email' ).on( 'change', function() {
					var guest_user_email = jQuery( 'input#billing_email' ).val();
					var ajaxUrl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
					var locale = "<?php echo esc_html( $locale ); ?>";
					var nonce = "<?php echo esc_html( wp_create_nonce( 'hubwoo_cart_security' ) ); ?>";
					jQuery.post( ajaxUrl, { 'action' : 'hubwoo_save_guest_user_cart', 'email' : guest_user_email, 'locale' : locale, 'nonce' : nonce }, function( status ) {});
				});
			</script>
			<?php
		}
	}

	/**
	 * Clear saved cart on new order.
	 *
	 * @since 1.0.0
	 * @param int $order_id id of new order.
	 */
	public function hubwoo_abncart_woocommerce_new_orders( $order_id ) {

		if ( empty( WC()->session ) ) {
			return; }

		$get_cookie = WC()->session->get_session_cookie();

		$session_id = $get_cookie[0];

		if ( ! empty( $order_id ) ) {

			$order = new WC_Order( $order_id );

			$order_status = $order->get_status();

			$order_email = get_post_meta( $order_id, '_billing_email', true );

			$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );

			if ( 'failed' !== $order_status ) {

				if ( ! empty( $existing_cart_data ) ) {

					foreach ( $existing_cart_data as $key => &$single_cart_data ) {

						if ( array_key_exists( 'sessionID', $single_cart_data ) && $single_cart_data['sessionID'] == $session_id ) {

							if ( isset( $single_cart_data['cartData']['cart'] ) ) {

								$single_cart_data['cartData']['cart'] = '';
								$single_cart_data['sent']             = 'no';
							}
						}

						if ( array_key_exists( 'email', $single_cart_data ) && $single_cart_data['email'] == $order_email ) {

							if ( isset( $single_cart_data['cartData']['cart'] ) ) {

								$single_cart_data['cartData']['cart'] = '';
								$single_cart_data['sent']             = 'no';
							}
						}

						if ( array_key_exists( 'email', $single_cart_data ) && null !== WC()->session->get( 'mwb_guest_user_email' ) && WC()->session->get( 'mwb_guest_user_email' ) == $single_cart_data['email'] ) {

							if ( isset( $single_cart_data['cartData']['cart'] ) ) {

								$single_cart_data['cartData']['cart'] = '';
								$single_cart_data['sent']             = 'no';
							}
						}
					}

					update_option( 'mwb_hubwoo_guest_user_cart', $existing_cart_data );
				}
			}

			if ( is_user_logged_in() ) {

				update_user_meta( get_current_user_id(), 'hubwoo_pro_user_cart_sent', 'no' );
			}
		}
	}

	/**
	 * Track changes on cart being updated.
	 *
	 * @since 1.0.0
	 */
	public static function hubwoo_abncart_track_guest_cart() {

		if ( ! is_user_logged_in() ) {

			$get_cookie = WC()->session->get_session_cookie();

			if ( ! empty( $get_cookie ) ) {

				$session_id = $get_cookie[0];

				if ( ! empty( $session_id ) ) {

					if ( null !== WC()->session->get( 'mwb_guest_user_email' ) ) {

						$guest_user_email = WC()->session->get( 'mwb_guest_user_email' );

						if ( ! empty( $guest_user_email ) ) {

							$guest_user_cart = array();

							$locale = ! empty( WC()->session->get( 'locale' ) ) ? WC()->session->get( 'locale' ) : '';

							if ( function_exists( 'WC' ) ) {

								$guest_user_cart['cart'] = WC()->session->cart;
							} else {

								$guest_user_cart['cart'] = $woocommerce->session->cart;
							}

							$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );

							$saved_cart = array();

							if ( ! empty( $existing_cart_data ) ) {

								foreach ( $existing_cart_data as $single_cart_data ) {

									if ( array_key_exists( 'email', $single_cart_data ) && WC()->session->get( 'mwb_guest_user_email' ) == $single_cart_data['email'] ) {

										if ( array_key_exists( 'cartData', $single_cart_data ) ) {

											if ( ! empty( $single_cart_data['cartData']['cart'] ) ) {
												$saved_cart = $single_cart_data['cartData']['cart'];
											}
										}
										break;
									}
								}
							}

							if ( $saved_cart === $guest_user_cart['cart'] ) {

								return;
							}

							$user_data = array(
								'email'     => WC()->session->get( 'mwb_guest_user_email' ),
								'cartData'  => $guest_user_cart,
								'timeStamp' => time(),
								'sessionID' => $session_id,
								'locale'    => $locale,
								'sent'      => 'no',
							);

							self::hubwoo_abncart_update_new_data( WC()->session->get( 'mwb_guest_user_email' ), $user_data, $session_id );
						}
					}
				}
			}
		}
	}

	/**
	 * Callback to update cart data in DB.
	 *
	 * @since 1.0.0
	 * @param string $email email of the contact.
	 * @param array  $user_data formatted data for cart.
	 * @param string $session session id for the cart activity.
	 */
	public static function hubwoo_abncart_update_new_data( $email, $user_data, $session ) {

		$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );
		$update_flag        = false;

		if ( ! empty( $existing_cart_data ) ) {

			foreach ( $existing_cart_data as $key => &$single_cart_data ) {

				if ( ! empty( $single_cart_data['email'] ) && $single_cart_data['email'] == $email ) {

					$single_cart_data = $user_data;
					$update_flag      = true;
					break;
				} elseif ( ! empty( $single_cart_data['sessionID'] ) && $single_cart_data['sessionID'] == $session ) {

					$single_cart_data = $user_data;
					$update_flag      = true;
					break;
				}
			}
		}

		if ( ! $update_flag ) {

			$existing_cart_data[] = $user_data;
		}

		update_option( 'mwb_hubwoo_guest_user_cart', $existing_cart_data );
	}

	/**
	 * Transfer guest cart to user on account registeration.
	 *
	 * @since 1.0.0
	 * @param int $user_id user ID.
	 */
	public function hubwoo_abncart_user_registeration( $user_id ) {

		$user  = get_user_by( 'id', $user_id );
		$email = ! empty( $user->data->user_email ) ? $user->data->user_email : '';
		if ( empty( $email ) || null == WC()->session ) {
			return;
		}
		$get_cookie = WC()->session->get_session_cookie();

		if ( ! empty( $get_cookie ) ) {

			$session_id         = $get_cookie[0];
			$existing_cart_data = get_option( 'mwb_hubwoo_guest_user_cart', array() );
			foreach ( $existing_cart_data as $key => &$single_cart_data ) {
				if ( array_key_exists( 'sessionID', $single_cart_data ) && $single_cart_data['sessionID'] === $session_id ) {
					if ( ! empty( $single_cart_data['sent'] ) && 'no' === $single_cart_data['sent'] ) {
						unset( $existing_cart_data[ $key ] );
					} else {
						$single_cart_data['cartData'] = '';
						$single_cart_data['sent']     = 'no';
					}
				} elseif ( array_key_exists( 'email', $single_cart_data ) && $single_cart_data['email'] === $email ) {
					if ( ! empty( $single_cart_data['sent'] ) && 'no' === $single_cart_data['sent'] ) {
						unset( $existing_cart_data[ $key ] );
					} else {
						$single_cart_data['cartData'] = '';
						$single_cart_data['sent']     = 'no';
					}
				}
			}

			update_option( 'mwb_hubwoo_guest_user_cart', $existing_cart_data );

			$locale = ! empty( WC()->session->get( 'locale' ) ) ? WC()->session->get( 'locale' ) : '';

			update_user_meta( $user_id, 'hubwoo_pro_user_left_cart', 'yes' );
			update_user_meta( $user_id, 'hubwoo_pro_last_addtocart', time() );
			update_user_meta( $user_id, 'hubwoo_pro_user_cart_sent', 'no' );
			update_user_meta( $user_id, 'hubwoo_pro_cart_locale', $locale );
		}
	}

	/**
	 * Clear session on logout.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_clear_session() {

		if ( null !== WC()->session->get( 'locale' ) ) {
			WC()->session->set( 'locale', null );
		}
		if ( null !== WC()->session->get( 'mwb_guest_user_email' ) ) {
			WC()->session->set( 'mwb_guest_user_email', null );
		}
	}

	/**
	 * Handles the cart data when the guest user updates the cart.
	 *
	 * @since 1.0.0
	 * @param bool $cart_updated true/false.
	 * @return bool $cart_updated true/false.
	 */
	public function hubwoo_guest_cart_updated( $cart_updated ) {

		if ( is_user_logged_in() ) {

			$user_id = get_current_user_id();
			//phpcs:disable
			$locale  = ! empty( WC()->session->get( 'locale' ) ) ? WC()->session->get( 'locale' ) : '';
			//phpcs:enable
			if ( ! empty( $user_id ) && $user_id ) {

				update_user_meta( $user_id, 'hubwoo_pro_user_left_cart', 'yes' );
				update_user_meta( $user_id, 'hubwoo_pro_last_addtocart', time() );
				update_user_meta( $user_id, 'hubwoo_pro_cart_locale', $locale );
				update_user_meta( $user_id, 'hubwoo_pro_user_cart_sent', 'no' );
			}
		} else {

			self::hubwoo_abncart_track_guest_cart();
		}
		return $cart_updated;
	}

	/**
	 * Update key as soon as user makes a addtocart action
	 *
	 * @since       1.0.0
	 */
	public function hubwoo_abncart_woocommerce_add_to_cart() {

		if ( is_user_logged_in() ) {

			$user_id = get_current_user_id();
			//phpcs:disable
			$locale = ! empty( WC()->session->get( 'locale' ) ) ? WC()->session->get( 'locale' ) : '';
			//phpcs:enable
			if ( ! empty( $user_id ) && $user_id ) {

				update_user_meta( $user_id, 'hubwoo_pro_user_left_cart', 'yes' );

				update_user_meta( $user_id, 'hubwoo_pro_last_addtocart', time() );

				update_user_meta( $user_id, 'hubwoo_pro_cart_locale', $locale );

				update_user_meta( $user_id, 'hubwoo_pro_user_cart_sent', 'no' );
			}
		} else {

			self::hubwoo_abncart_track_guest_cart();
		}
	}

	/**
	 * Tracking the abandonded cart products.
	 *
	 * @since       1.0.4
	 */
	public function hubwoo_add_abncart_products() {
		$product_string = ! empty( $_GET['hubwoo-abncart-retrieve'] ) ? sanitize_text_field( wp_unslash( $_GET['hubwoo-abncart-retrieve'] ) ) : '';
		if ( ! empty( $product_string ) ) {
			$seperated_products = explode( ',', $product_string );
			if ( ! empty( $seperated_products ) ) {
				global $woocommerce;
				$woocommerce->cart->empty_cart();
				foreach ( $seperated_products as $product ) {
					$pro_qty    = array();
					$pro_qty    = explode( ':', $product );
					$pro_qty[1] = ! empty( $pro_qty[1] ) ? $pro_qty[1] : 1;
					$woocommerce->cart->add_to_cart( $pro_qty[0], $pro_qty[1] );
				}
				wp_safe_redirect( wc_get_cart_url(), 301 );
				exit;
			}
		}
	}

	/**
	 * Tracking subscribe box for guest users
	 *
	 * @since 1.2.3
	 */
	public function get_email_checkout_page() {
		if ( ! is_user_logged_in() ) {
			?>
			<script type="text/javascript">
				jQuery( 'input#billing_email' ).on( 'change', function() {
					var guestuser_email = jQuery( 'input#billing_email' ).val();
					var ajaxUrl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
					var nonce = "<?php echo esc_html( wp_create_nonce( 'hubwoo_cart_security' ) ); ?>";
					jQuery.post( ajaxUrl, { 
							'action' : 'get_order_detail', 
							'email'  : guestuser_email, 
							'nonce' : nonce 
						}, function( response ) {
							if ( response == '"success"' ) {
								jQuery('#hubwoo_checkout_marketing_optin').prop('checked', true);
							} 

							if ( response == '"failure"' ) {
								jQuery('#hubwoo_checkout_marketing_optin').prop('checked', false);
							} 
						}
					);
				});
			</script>
			<style>
				.hubwoo-input-checkbox label.checkbox {
					display: inline-block;
				}
			</style>
			<?php
		}
	}


	/**
	 * Getting orders of guest user
	 *
	 * @since 1.2.3
	 */
	public function get_order_detail() {

		check_ajax_referer( 'hubwoo_cart_security', 'nonce' );

		if ( ! empty( $_POST['email'] ) ) {

			$order_statuses = array_keys( wc_get_order_statuses() );

			$fetched_email  = sanitize_email( wp_unslash( $_POST['email'] ) );

			$query = new WP_Query();

			$customer_orders = $query->query( 
				array(
					'post_type'           => 'shop_order',
					'posts_per_page'      => 1,
					'post_status'         => $order_statuses,
					'orderby'             => 'id',
					'order'               => 'desc',
					'fields'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
					'meta_query'          => array(
						array(
							'key'   => '_billing_email',
							'value' => $fetched_email,
						),
					),
				)
			);

			$value = null;

			foreach ( $customer_orders as $single_order ) {
				$orders    = wc_get_order( $single_order );
				$meta_data = $orders->get_meta_data();
				foreach ( $meta_data as $data ) {
					$key = $data->key;
					if ( 'hubwoo_checkout_marketing_optin' == $key ) {
						$value = 'success';
						break;
					} else {
						$value = 'failure';
					}
				}
			}

			echo wp_json_encode( $value );
		}

		wp_die();
	}
}
