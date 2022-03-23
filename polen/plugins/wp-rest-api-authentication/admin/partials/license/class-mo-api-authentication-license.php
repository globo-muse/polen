<?php

class Mo_API_Authentication_Admin_License {
    public static function emit_css() {
        $tick_img = plugin_dir_url(dirname(dirname(__FILE__))) . "images/tick.png";
        ?>
        <style>
            body{
                background-color: #f1f1f1;
            }

            .container-customize{ 
                margin-left: 10% ;
                margin-right: 10% ;
            }

            .card {
            border: 0;
            border-radius: 0px;
            -webkit-box-shadow: 0 3px 0px 0 rgba(0, 0, 0, 0.08);
            box-shadow: 0 3px 0px 0 rgba(0, 0, 0, 0.08);
            transition: all .3s ease-in-out;
            padding: 2.25rem 1rem;
            position: relative;
            text-align:center;
            will-change: transform;
            border-radius: 8px;
            box-shadow: 0 20px 35px 0 rgba(0, 0, 0, 0.08);
            max-width: 100%;
            }
            .card:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 5px;
            background-color: #3473b4;
            transition: 0.5s;
            }
            .card:hover {
            transform: scale(1.05);
            -webkit-box-shadow: 0 20px 35px 0 rgba(0, 0, 0, 0.08);
            box-shadow: 0 20px 35px 0 rgba(0, 0, 0, 0.18);
            }
            .card:hover:after {
            width: 100%;
            }
            .card .card-header {
            background-color: white;
            padding-left: 2rem;
            border-bottom: 0px;
            }
            .card .card-title {
            margin-bottom: 1rem;
            font-size: 1.3rem;
            }
            .card .card-block {
            padding-top: 0;
            }
            .card .list-group-item {
            border: 0px;
            padding: .25rem;
            color: #808080;
            font-weight: 300;
            }

            .card .card-text{
                font-size: 0.8rem;
                color: #808080;
                font-weight: 800;
            }

            .popup-text{
                font-size: 20px;
                color: #808080;
                text-align: center;
            }

            .display-2 {
            font-size: 3rem;
            letter-spacing: -.1rem;
            }
            .display-2 .currency {
            font-size: 1.5rem;
            position: relative;
            font-weight: 400;
            top: -20px;
            letter-spacing: 0px;
            }
            .display-2 .period {
            font-size: 0.5rem;
            color: #b3b3b3;
            letter-spacing: 0px;
            }

            .btn {
            text-transform: uppercase;
            font-size: .65rem;
            font-weight: bold;
            color: #fff;
            border-radius: 0;
            padding: .65rem 1.15rem;
            letter-spacing: 1px;
            border-radius: 2px;
            }

            .btn-gradient {
            background: #473970
            transition: background .3s ease-in-out;
            }
            .btn-gradient:hover {
            color: white;
            background-color: #3473b4cc;
            }

            .view-plan-btn {
            text-transform: uppercase;
            font-size: .75rem;
            font-weight: 500;
            color: #fff;
            border-radius: 0;
            padding: .75rem 1.25rem;
            letter-spacing: 1px;
            border-radius: 2px;
            }

            .btn-gradient {
            background: #473970;
            transition: background .3s ease-in-out;
            }
            .btn-gradient:hover {
            color: white;
            background-color: #3473b4cc;
            }

            .overlay {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            transition: opacity 500ms;
            visibility: hidden;
            opacity: 0;
            z-index: 100;
            transition: .3s ease-in-out;
            }
            /*End of overlay*/

            /*=== Popup Styles ===*/

            .popup {
            border: 0px solid #000000;
            width: 800px;
            padding-top: 10px;
            margin: 20px;
            padding-bottom: 20px;
            box-shadow: 1px 4px 15px 1px rgba(0,0,0,0.3);
            border-radius: 10px;
            background: #ffffff;
            position: absolute; /*These styles make center align for x and y*/
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%); /*End of center align */
            }
            /*== End of Left part of popup ==*/

            /* X Close Button */
            .close-btn::before, .close-btn::after {
            position: absolute;
            left: auto;
            content: "";
            height: 23px;
            width: 5px;
            right: 5%;
            background-color: #A8A399;
            margin-left: 320px;
            margin-top: 15px;
            }

            .close-btn::before {
            transform: rotate(45deg);
            }

            .close-btn::after {
            transform: rotate(-45deg);
            }

            .popup-title{
                font-size: 18px;
                text-align: center;
            }


            .popup-divider{
                width: 15%;
                margin-top: 25px;
                border: 1px solid #d8d8d8;
            }

            .mo-api-license-li {
                color:black;
                list-style:none;
                position: relative;
                padding-left:10%;
                line-height: 2;
                font-size:13px;
            }

            .mo-api-license-li:before {
                position: absolute;
                left: 5%;
                color:#007c00;
                font-size:15px;
            }

            .mo-api-license-li.feature-item:before {
                /* content:"\f00c"; */
                display: block;
                margin-top: 8px;
                background-image: url("<?php echo esc_url( $tick_img ); ?>");
                background-repeat: no-repeat;
                background-size: contain;
                width: 12px;
                height: 12px;
                content: "";
            }

            .mo-api-license-li.unsupported-item:before {
                content:"X";
                color: #ff0000;
                font-weight: bold;
            }

            .btn-view-plan{
                background: white;
                color: #3473b4;
                border: 2px solid #3473b4;
                transition: .3s ease-in-out;
            }

            .btn-view-plan:hover{
                background: white;
                color: #3473b4;
                border: 2px solid #3473b4;
                transition: .3s ease-in-out;
            }
            
            form {
            max-width: 100%;
            text-align: center;
            margin: 20px auto;
            }
            form input, form textarea {
            border: 1;
            outline: 0;
            padding: 1em;
            -moz-border-radius: 8px;
            -webkit-border-radius: 8px;
            border-radius: 8px;
            display: block;
            width: 500px;
            margin-top: 1em;
            font-family: 'Merriweather', sans-serif;
            -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            resize: none;
            }
            form #input-submit {
            color: white;
            background: #3473b4;
            cursor: pointer;
            }
            form #input-submit:hover {
            -moz-box-shadow: 0 1px 1px 1px rgba(170, 170, 170, 0.6);
            -webkit-box-shadow: 0 1px 1px 1px rgba(170, 170, 170, 0.6);
            box-shadow: 0 1px 1px 1px rgba(170, 170, 170, 0.6);
            }

            .half {
            float: left;
            width: 48%;
            margin-bottom: 1em;
            }

            .right {
            width: 50%;
            }

            .left {
            margin-right: 2%;
            }

            /* Clearfix */
            .cf:before,
            .cf:after {
            content: " ";
            /* 1 */
            display: table;
            /* 2 */
            }

            .cf:after {
            clear: both;
            }

            


        </style>
        <?php
    }
    public static function mo_api_authentication_licensing_page(){
        self::emit_css();
        ?>
        <!-- Important JSForms -->
        <input type="hidden" value="<?php echo esc_attr( mo_api_authentication_is_customer_registered() );?>" id="mo_customer_registered">
        <form style="display:none;" id="loginform"
              action="<?php echo esc_attr( get_option( 'host_name' ) ) . '/moas/login'; ?>"
              target="_blank" method="post">
			<?php wp_nonce_field('mo_api_authentication_goto_login_xecurify','mo_api_authentication_goto_login_fields_xecurify'); ?>			
            <input type="email" name="username" value="<?php echo esc_attr( get_option( 'mo_api_authentication_admin_email' ) ); ?>"/>
            <input type="text" name="redirectUrl"
                   value="<?php echo esc_attr( get_option( 'host_name' ) ) . '/moas/initializepayment'; ?>"/>
            <input type="text" name="requestOrigin" id="requestOrigin"/>
        </form>
        <form style="display:none;" id="viewlicensekeys"
              action="<?php echo esc_attr( get_option( 'host_name' ) ) . '/moas/login'; ?>"
              target="_blank" method="post">
			<?php wp_nonce_field('mo_api_authentication_goto_license_keys','mo_api_authentication_goto_license_keys_fields_xecurify'); ?>			
            <input type="email" name="username" value="<?php echo esc_attr( get_option( 'mo_api_authentication_admin_email' ) ); ?>"/>
            <input type="text" name="redirectUrl"
                   value="<?php echo esc_attr( get_option( 'host_name' ) ) . '/moas/viewlicensekeys'; ?>"/>
        </form>
        <!-- End Important JSForms -->
        <script>
        function mo_show_popup_feature(popup_id){
            document.getElementById(popup_id).style.visibility = "visible";
            document.getElementById(popup_id).style.opacity = "1";
        }
        function mo_hide_popup_feature(popup_id){
            document.getElementById(popup_id).style.opacity = "0";
            document.getElementById(popup_id).style.visibility = "hidden";
        }
        </script>

        <section class="popup-overlay">

        <!-- API Key  -->
          <div id="api-key-authentication" class="overlay">
            <div class="popup">
              <a href="#" onclick="mo_hide_popup_feature('api-key-authentication')" class="close-btn"></a>
              <br>
              <div class="container">
                  <h2 class="popup-title">API Key Authentication Method</h2>
                  <hr class="popup-divider">
                  <ul>
                    <li class="mo-api-license-li feature-item">
                        API Key Authentication Method
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Unlimited API Authentication
                    </li>
                    <li class="mo-api-license-li feature-item">
                        User specific API keys (Access to only specific user data)
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Restrict Public Access to WP REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Support for GET, POST, PUT & DELETE methods
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Role based Access to APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Header
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Exclude REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        24/7* Basic Email Support System
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Basic Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        JWT Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        OAuth 2.0 Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Authentication from external OAuth 2.0 providers
                    </li>
                </ul>
              </div>
          </div>
        </div>

          <!-- Basic Auth  -->
          <div id="basic-authentication" class="overlay">
            <div class="popup">
              <a href="#" onclick="mo_hide_popup_feature('basic-authentication')" class="close-btn"></a>
              <br>
              <div class="container">
                  <h2 class="popup-title">Basic Authentication Method</h2>
                  <hr class="popup-divider">
                  <ul>
                    <li class="mo-api-license-li feature-item">
                        Advanced Basic Authentication Method<br>
                        1. Username : Password <br>
                        2. Client ID : Client Secret <br>
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Unlimited API Authentication
                    </li>
                    <li class="mo-api-license-li feature-item">
                        User specific Client Credentials (Access to only specific user data)
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Authentication using Client Credentials (Without involving original user login credentials)
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Restrict Public Access to WP REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Support for GET, POST, PUT & DELETE methods
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Role based Access to APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Encryption through highly secure HMAC
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Header
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Exclude REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        24/7* Basic Email Support System
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        API Key Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        JWT Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        OAuth 2.0 Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Authentication from external OAuth 2.0 providers
                    </li>
                </ul>
              </div>
          </div>
        </div>

          <!-- JWT Auth  -->
          <div id="jwt-authentication" class="overlay">
            <div class="popup">
              <a href="#" onclick="mo_hide_popup_feature('jwt-authentication')" class="close-btn"></a>
              <br>
              <div class="container">
                  <h2 class="popup-title">JWT Authentication Method</h2>
                  <hr class="popup-divider">
                  <ul>
                    <li class="mo-api-license-li feature-item">
                        JWT Authentication Method
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Unlimited API Authentication
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Signature Validation : HSA & RSA Signing (Very High Security)
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Token Expiry (<span style="font-weight: bold;"><small>It will help you to make JWT token available to limited time period to improve security</small></span>)
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Certificate / Secret upload
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Restrict Public Access to WP REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Support for GET, POST, PUT & DELETE methods
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Role based Access to APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Header
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Exclude REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        24/7* Basic Email Support System
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        API Key Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Basic Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        OAuth 2.0 Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Authentication from external OAuth 2.0 providers
                    </li>
                </ul>
              </div>
          </div>
        </div>

        <!-- Oauth 2.0  -->
        <div id="oauth-authentication" class="overlay">
            <div class="popup">
              <a href="#" onclick="mo_hide_popup_feature('oauth-authentication')" class="close-btn"></a>
              <br>
              <div class="container">
                  <h2 class="popup-title">OAuth 2.0 Authentication Method</h2>
                  <hr class="popup-divider">
                  <ul>
                    <li class="mo-api-license-li feature-item">
                        OAuth 2.0 Authentication Method<br>
                        1. Password Grant <br>
                        2. Client Credentials Grant <br>
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Unlimited API Authentication
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Access Token & JWT Token support
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Refresh & Revoke Tokens
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Restrict Public Access to WP REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Support for GET, POST, PUT & DELETE methods
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Role based Access to APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Token Expiry 
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Header
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Exclude REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        24/7* Basic Email Support System
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        API Key Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Basic Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        JWT Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Authentication from external OAuth 2.0 providers
                    </li>
                </ul>
              </div>
          </div>
        </div>

        <!-- Authentication from an external providers  -->
        <div id="oauth-external-providers-authentication" class="overlay">
            <div class="popup">
              <a href="#" onclick="mo_hide_popup_feature('oauth-external-providers-authentication')" class="close-btn"></a>
              <br>
              <div class="container">
                  <h2 class="popup-title">Authentication From External OAuth 2.0 Providers</h2>
                  <hr class="popup-divider">
                  <ul>
                    <li class="mo-api-license-li feature-item">
                        Authentication from external OAuth 2.0 providers <br>(Like Azure, Cognito, Firebase Access Token, Google, Facebook, Keycloak, ADFS etc.)
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Restrict Public Access to WP REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Support for GET, POST, PUT & DELETE methods
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Auto Create Users into WordPress on basis of external OAuth/OIDC providers token
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Role based Access to APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Header
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Exclude REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        24/7* Basic Email Support System
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        API Key Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Basic Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        JWT Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        OAuth 2.0 Authentication Method
                    </li>
                </ul>
              </div>
          </div>
        </div>

        <!-- OAuth 2.0 + Third Party Auth  -->
        <div id="external-authentication" class="overlay">
            <div class="popup">
              <a href="#" onclick="mo_hide_popup_feature('external-authentication')" class="close-btn"></a>
              <br>
              <div class="container">
                  <h2 class="popup-title">Authentication From External Providers</h2>
                  <hr class="popup-divider">
                  <ul>
                    <li class="mo-api-license-li feature-item">
                        Authentication from External Providers which doesn't support any standard (Like Validate from SAML response)
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Unlimited API Authentication
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Restrict Public Access to WP REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Support for GET, POST, PUT & DELETE methods
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Role based Access to APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Token Expiry
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Header
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Exclude REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        24/7* Basic Email Support System
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        API Key Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Basic Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        JWT Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        OAuth 2.0 Authentication Method
                    </li>
                </ul>
              </div>
          </div>
        </div>

        <!-- Protecting 3rd party plugin or custom APIs  -->
        <div id="protecting-third-party-plugin-authentication" class="overlay">
            <div class="popup">
              <a href="#" onclick="mo_hide_popup_feature('protecting-third-party-plugin-authentication')" class="close-btn"></a>
              <br>
              <div class="container">
                  <h2 class="popup-title">Protecting 3rd Party Plugin or Custom APIs</h2>
                  <hr class="popup-divider">
                  <ul>
                    <li class="mo-api-license-li feature-item">
                        Protecting 3rd party plugin or custom APIs <br>
                        1. WooCommerce<br>
                        2. BuddyPress<br>
                        3. Gravity Form<br>
                        4. Learndash API Endpoints<br>
                        5. Custom built REST Endpoints in WordPress
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Unlimited API Authentication
                    </li>
                    <li class="mo-api-license-li feature-item">
                        API Key Authentication Method
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Basic Authentication Method
                    </li>
                    <li class="mo-api-license-li feature-item">
                        JWT Authentication Method
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Restrict Public Access to WP REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Support for GET, POST, PUT & DELETE methods
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Role based Access to APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Token Expiry
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Header
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Exclude REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        24/7* Basic Email Support System
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        OAuth 2.0 Authentication Method
                    </li>
                    <li class="mo-api-license-li unsupported-item">
                        Authentication from external OAuth 2.0 providers
                    </li>
                </ul>
              </div>
          </div>
        </div>

        <!-- All Inclusive  -->
        <div id="all-inclusive" class="overlay">
            <div class="popup">
            <a href="#" onclick="mo_hide_popup_feature('all-inclusive')" class="close-btn"></a>
              <br>
              <div class="container">
                  <h2 class="popup-title">All Inclusive Plan</h2>
                  <hr class="popup-divider">
                  <ul>
                    <li class="mo-api-license-li feature-item">
                        All Authentication method (One at a time)<br>
                        1. API Key Authentication<br>
                        2. Basic Authentication<br>
                        3. JWT Authentication<br>
                        4. OAuth 2.0 Authentication<br>
                        5. Authentication from external OAuth 2.0 providers<br>
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Protecting 3rd party plugin or custom APIs <br>
                        1. WooCommerce<br>
                        2. BuddyPress<br>
                        3. Gravity Form<br>
                        4. Learndash API Endpoints<br>
                        5. Custom built REST Endpoints in WordPress
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Unlimited API Authentication
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Restrict Public Access to WP REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Support for GET, POST, PUT & DELETE methods
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Role based Access to APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Token Expiry
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Custom Header
                    </li>
                    <li class="mo-api-license-li feature-item">
                        Exclude REST APIs
                    </li>
                    <li class="mo-api-license-li feature-item">
                        24/7* Basic Email Support System
                    </li>
                </ul>
              </div>
          </div>
        </div>

        <!-- Contact Form  -->
        <div id="contact-form" class="overlay">
            <div class="popup">
            <a href="#" onclick="mo_hide_popup_feature('contact-form')" class="close-btn"></a>
              <br>
              <div class="container">
                  <h2 class="popup-title">Request a quote</h2>
                  <hr class="popup-divider">
                  <p class="popup-text">Drop your requirements here, we'd be happy to help you!!</p>
                  <div class="row">
                    <form method="post" action="">
                        <input required type="hidden" name="option" value="mo_api_authentication_license_contact_form" />
                        <?php wp_nonce_field('mo_api_authentication_license_contact_form','mo_api_authentication_license_contact_fields'); ?>
                        <input type="hidden">
                        <input name="email" type="email" id="input-email" placeholder="Email address" required>
                        <input type="tel" name="phone" id="input-phone" placeholder="Phone no." required>
                        <textarea name="query" type="text" id="input-message" placeholder="Enter your external provider with you use-case" required></textarea>
                        <input type="submit" value="Submit" id="input-submit">
                    </form>
                  </div>
              </div>
          </div>
        </div>

          </section>

        <!-- Licensing Table -->
        <div class="container-customize">
            <div class="row">

                <!-- API Key  -->
                <div class="col-xs-12 col-lg-4">
                <div class="card text-xs-center">
                    <div class="card-header">
                    <h3 class="display-2"><span class="currency">$</span>149<sup style="color: #e0d8d7">*</sup></h3>
                    </div>
                    <div class="card-block">
                    <h4 class="card-title"> 
                        API Key Authentication <br> Method
                    </h4>
                    <!-- <ul class="list-group">
                        <li class="list-group-item">Ultimate Features</li>
                        <li class="list-group-item">Responsive Ready</li>
                        <li class="list-group-item">Visual Composer Included</li>
                        <li class="list-group-item">24/7 Support System</li>
                    </ul> -->
                    <button onclick="mo_show_popup_feature('api-key-authentication')" class="btn btn-gradient mt-2 btn-view-plan">View Plan</button>
                    <a href="#" onclick="upgradeform('wp_rest_api_authentication_custom_api_key_plan')" class="btn btn-gradient mt-2">Upgrade now</a>
                    </div>
                </div>
                </div>

                <!-- Basic Auth  -->
                <div class="col-xs-12 col-lg-4">
                <div class="card text-xs-center">
                    <div class="card-header">
                    <h3 class="display-2"><span class="currency">$</span>149<sup style="color: #e0d8d7">*</sup></h3>
                    </div>
                    <div class="card-block">
                    <h4 class="card-title"> 
                        Basic Authentication <br> Method
                    </h4>
                    <button onclick="mo_show_popup_feature('basic-authentication')" class="btn btn-gradient mt-2 btn-view-plan">View Plan</button>
                    <a href="#" onclick="upgradeform('wp_rest_api_authentication_custom_basic_auth_plan')" class="btn btn-gradient mt-2">Upgrade now</a>
                    </div>
                </div>
                </div>

                <!-- JWT Auth  -->
                <div class="col-xs-12 col-lg-4">
                <div class="card text-xs-center">
                    <div class="card-header">
                    <h3 class="display-2"><span class="currency">$</span>199<sup style="color: #e0d8d7">*</sup></h3>
                    </div>
                    <div class="card-block">
                    <h4 class="card-title"> 
                        JWT Authentication <br> Method
                    </h4>
                    
                    <button onclick="mo_show_popup_feature('jwt-authentication')" class="btn btn-gradient mt-2 btn-view-plan">View Plan</button>
                    <a href="#" onclick="upgradeform('wp_rest_api_authentication_custom_jwt_plan')" class="btn btn-gradient mt-2">Upgrade now</a>
                    </div>
                </div>
                </div>

            </div>
            <br>
            <div class="row">

                <!-- OAuth 2.0 Auth  -->
                <div class="col-xs-12 col-lg-4">

                <div class="card text-xs-center" >
                <div><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>admin/images/mostsecure.png" height="120px" width="120px" style="margin-left: -49px; margin-top: -82px; z-index: 1; position: absolute"></div>


                    <!-- <div class="mo_api_auth_premium_label_main" > -->
						<!-- <div class="mo_api_auth_premium_label_internal"> -->
						<!-- <div class="mo_api_auth_premium_label_text" style=' background-color: #ffa033'>Most Secure</div> -->
						<!-- </div> -->
					<!-- </div> -->
                    <div class="card-header" >
                    <h3 class="display-2"><span class="currency">$</span>249<sup style="color: #e0d8d7">*</sup></h3>
                    </div>
                    <div class="card-block">
                    <h4 class="card-title"> 
                        OAuth 2.0 Authentication <br> Method 
                    </h4>
                    
                    <button onclick="mo_show_popup_feature('oauth-authentication')" class="btn btn-gradient mt-2 btn-view-plan">View Plan</button>
                    <a href="#" onclick="upgradeform('wp_rest_api_authentication_custom_oauth_auth_plan')" class="btn btn-gradient mt-2">Upgrade now</a>
                    </div>
                </div>
                </div>

                <!-- Third Party Auth  -->
                <div class="col-xs-12 col-lg-4">
                <div class="card text-xs-center" >
                    <!-- <div class="" style="color:white">asd</div> -->
                  
                    <div class="card-header">
                    <h3 class="display-2"><span class="currency">$</span>349<sup style="color: #e0d8d7">*</sup></h3>
                    </div>
                    <div class="card-block">
                    <h4 class="card-title"> 
                        Authentication From External<br> OAuth 2.0 Providers
                    </h4>

                    <button onclick="mo_show_popup_feature('oauth-external-providers-authentication')" class="btn btn-gradient mt-2 btn-view-plan">View Plan</button>
                    <button onclick="upgradeform('wp_rest_api_authentication_from_external_oauth_provider_plan')" class="btn btn-gradient mt-2">Upgrade now</button>
                    </div>
                </div>
                </div>

                <!-- OAuth 2.0 + Third Party Auth  -->
                <div class="col-xs-12 col-lg-4">
                <div class="card text-xs-center">
            
                    <div class="card-header">
                    <h3 class="display-2"><span class="currency">$</span>399<sup style="color: #e0d8d7">*</sup></h3>
                    </div>
                    <div class="card-block">
                    <h4 class="card-title"> 
                        Protecting 3rd Party Plugin or <br> Custom APIs 
                    </h4>
                    
                    <button onclick="mo_show_popup_feature('protecting-third-party-plugin-authentication')" class="btn btn-gradient mt-2 btn-view-plan">View Plan</button>
                    <a href="#" onclick="upgradeform('wp_rest_api_authentication_custom_apis_plan')" class="btn btn-gradient mt-2">Upgrade now</a>
                    </div>
                </div>
                </div>
            </div>
            <br>
            <div class="row">

                <!-- Protecting 3rd party plugin or custom APIs  -->

                <!-- All Inclusive  -->
                <div class="col-xs-12 col-lg-12">
                <div class="card text-xs-center">
                    <div><img src="<?php echo esc_attr(plugin_dir_url(dirname(dirname(dirname(__FILE__)))));?>admin/images/mostpopular.png" height="180px" width="180px" style="margin-left: -85px; margin-top: -110px; z-index: 1; position: absolute"></div>
                    <div class="card-header">
                    <h3 class="display-2"><span class="currency">$</span>449<sup style="color: #e0d8d7">*</sup></h3>
                    </div>
                    <div class="card-block">
                    <h4 class="card-title"> 
                        All Inclusive Plan
                    </h4>
                    
                    <br>
                    <button onclick="mo_show_popup_feature('all-inclusive')" class="btn btn-gradient mt-2 btn-view-plan">View Plan</button>
                    <a href="#" onclick="upgradeform('wp_rest_api_authentication_enterprise_plan')" class="btn btn-gradient mt-2">Upgrade now</a>
                    </div>
                </div>
                </div>

            </div>
            <br><br>
            <div class="row">
                <div class="mo_api_authentication_support_layout" style="padding-left: 20px;">
                    <br>
                <h4 class="mo-oauth-h2" style="text-align: center;">LICENSING POLICY</h4>
                       <!--  <hr style="background-color:#17a2b8; width: 10%;height: 3px;border-width: 3px;"> -->
                        <br>
                        <p style="font-size: 0.9em;"><span style="color: red;">*</span>Cost applicable for one instance only. Licenses are perpetual and the Support Plan includes 12 months of maintenance (support and version updates). You can renew maintenance after 12 months at 50% of the current license cost.<br></p>

                        <p style="font-size: 0.9em;"><span style="color: red;">*</span>We provide deep discounts on bulk license purchases and pre-production environment licenses. As the no. of licenses increases, the discount percentage also increases. Contact us at <a href="mailto:apisupport@xecurify.com?subject=WP REST API Authentication Plugin - Enquiry">apisupport@xecurify.com</a> for more information.</p>

                        <p style="font-size: 0.9em;"><span style="color: red;">*</span><strong>MultiSite Network Support : </strong>
                            There is an additional cost for the number of subsites in Multisite Network. The Multisite licenses are based on the <b>total number of subsites</b> in your WordPress Network.
                            <br>
                            <br>
                            <strong>Note</strong> : All the data remains within your premises/server. We do not provide the developer license for our paid plugins and the source code is protected. It is strictly prohibited to make any changes in the code without having written permission from miniOrange. There are hooks provided in the plugin which can be used by the developers to extend the plugin's functionality.
                            <br>
                            <br>
                        At miniOrange, we want to ensure you are 100% happy with your purchase. If the premium plugin you purchased is not working as advertised and you've attempted to resolve any issues with our support team, which couldn't get resolved. Please email us at <a href="mailto:info@xecurify.com" target="_blank">info@xecurify.com</a> for any queries regarding the return policy.</p>
           <br>
        </div>
        <br>
            </div>
            
        </div>
        <!-- End Licensing Table -->
        <a  id="mobacktoaccountsetup" style="display:none;" href="<?php echo esc_html( add_query_arg( array( 'tab' => 'account' ), htmlentities( $_SERVER['REQUEST_URI'] ) ) ); ?>">Back</a>
        
        <!-- JSForms Controllers -->
        <script>
            function customplanupgrade() {
                planType = document.getElementById('wp-rest-api-custom-plan-select').value;
                upgradeform(planType);
            }

            function upgradeform(planType) {
                if(planType === "") {
                    location.href = "https://wordpress.org/plugins/wp-rest-api-authentication/";
                    return;
                } else {
                    jQuery('#requestOrigin').val(planType);
                    if(jQuery('#mo_customer_registered').val()==1)
                        jQuery('#loginform').submit();
                    else{
                        location.href = jQuery('#mobacktoaccountsetup').attr('href');
                    }
                }

            }

            function getlicensekeys() {
                // if(jQuery('#mo_customer_registered').val()==1)
                jQuery('#viewlicensekeys').submit();
            }
        </script>
        <!-- End JSForms Controllers -->
        <?php
    }
}