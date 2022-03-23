=== WordPress REST API Authentication ===
Contributors: miniOrange
Tags: api, rest-api, jwt auth, REST, jwt, secure api, token, endpoints, json web token, oauth, api key auth, basic auth
Requires at least: 3.0.1
Tested up to: 5.9.1
Stable tag: 2.1.0
Requires PHP: 5.6
License: MIT/Expat
License URI: https://docs.miniorange.com/mit-license

Secure and protect your WP REST API endpoints from unauthorized access with our REST API Authentication using highly secure authentication methods. 


== Description ==
**Wordpress REST API’s** by default are **loose endpoints** through which a hacker can control your site remotely. You don’t want hackers to give access to your WordPress Login and Wordpress Register or any other endpoints. With our **[WordPress REST API Authentication plugin](https://plugins.miniorange.com/wordpress-rest-api-authentication)**, we promise to have the secure api from unauthorized users and **protects WP REST API endpoints** from public access using [API Key Authentication](https://developers.miniorange.com/docs/rest-api-authentication/wordpress/api-key-authentication) or [JWT Authentication](https://developers.miniorange.com/docs/rest-api-authentication/wordpress/jwt-authentication) or [Basic Authentication](https://developers.miniorange.com/docs/rest-api-authentication/wordpress/basic-authentication) or [OAuth 2.0 Authentication](https://developers.miniorange.com/docs/rest-api-authentication/wordpress/oauth-authentication) or third-party OAuth 2.0/OIDC/[Firebase](https://firebase.google.com/docs/auth/admin/create-custom-tokens) provider's token authentication methods. Our plugin is made in a way to make sure that we always have a secure api connection so that data isn’t compromised. JWT AUthentication is an industry approved method to secure communication between 2 parties and we also allow you to use that on your wordpress website.
It also allows you to access the WordPress REST APIs using above-mentioned authentication methods from Android / iOS and desktop applications.
This plugin will make sure that only after the successful authentication, the user is allowed to access your site's resources which adds to our motivation towards secure api. REST API Authentication will make your **WordPress login endpoints secure from unauthorized access.** You can protect api with ease and in a highly secure way using this plugin.
This plugin also provides features for authentication of custom-developed REST endpoints and third-party plugin REST API endpoints like that of [Woocommerce](https://wordpress.org/plugins/woocommerce/), [Learndash](https://www.learndash.com/), [Buddypress](https://wordpress.org/plugins/buddypress/), [Gravity forms](https://www.gravityforms.com/), [Cocart](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/) etc.

**_You can create the custom routes/REST endpoints in WordPress with our another GUI based plugin [Custom API for WordPress](https://wordpress.org/plugins/custom-api-for-wp/)_**. 

You will be able to securely login into rest api using the following endpoint:
`
https://<your-wordpress-base-url>/wp-json/api/v1/token
`
== Usecases ==

* _**The WordPress REST API makes CRUD (Create, Read, Update & Delete) operations available** from anywhere instead of being limited to just the admin dashboard. It provides a lightweight form of communication between the client and the server making it a great solution for exchanging data._
* _It can be used to **create iOS/Android, etc native apps.** We can use any language we want as long as the language can make HTTP requests and interpret JSON such as Node, js, Express.js, Ruby, Python etc._
* _**Wordpress Login and Wordpress Registration** become secure with REST API Authentication._
* _**Block unauthorized public access** to your WordPress and protect api endpoints like /pages, /posts to secure your website from hackers._
* _Only users authorized using our plugin’s authentication methods will be allowed to access the secure api._
* _Login APIs will be protected from unauthorized access._
* _[Woocommerce](https://woocommerce.com/), [Learndash](https://www.learndash.com/), [Buddypress](https://buddypress.org/), [Gravity forms](https://www.gravityforms.com/), [Cocoart](https://cococart.in/) etc. like plugins will be secured with our plugin’s authentication methods._
* _jwt token (JSON Web tokens) from other Identity Providers (OAuth/OIDC providers)Authenticate/Protect/Secure WordPress REST API endpoints with the access token ._
* _Securely Login and register into Mobile or other client applications using REST APIs._
* _Obtain **user based JWT token** to use as authentication source to login and register in other platforms._
* _Authenticate Woocommerce REST API endpoints by bypassing WooCommerce consumers' credentials security and instead of using own authentication methods to control the data access and thus improving security and removing chances for exposing the WC credentials._
* _**Authenticate/secure WordPress REST APIs** access using Firebase JWT token, any external JWT token, any OAuth 2.0/OpenID Connect(OIDC) provider access/id-token like Azure AD, Azure B2C, Okta, Keycloak, ADFS, AWS Cognito etc or that provided by Social login providers like Google, Facebook, Apple.
The plugin provides an interface for applications to interact with your WordPress REST API endpoints by sending and receiving data as JSON (JavaScript Object Notation) objects. Also, It provides a user-friendly user interface of the plugin to configure the methods and implement them very easily. You can easily secure api/protect your WordPress REST API endpoints with ease._
 
With our plugin, the user credentials are not stored as cookies but with every API call, user credentials or JWT (JSON Web tokens) or API key are passed so that we have secure api transactions.
 
==WordPress REST API Authentication Methods in our WordPress plugin==

* [Basic Authentication](https://plugins.miniorange.com/wordpress-rest-api-basic-authentication-method): _This is a basic authentication method to protect your wordpress endpoints by following methods:_
           	- 1. **Username: Password**: _This method for Basic Authentication authenticates the REST APIs by using username and passwords in the authorization header with the form of base64 encoded or with highly secure HMAC encryption._
           	- 2. **Client-ID: Client-Secret**: _This method for Basic Authentication authenticates/protects the REST APIs by using client credentials provided by the plugin in the authorization header with the form of base64 encoded or highly secure HMAC encryption._
* [API Key Authentication](https://plugins.miniorange.com/rest-api-key-authentication-method#step_a): _It allows you to secure wordpress endpoints without exposing user credentials as the plugin generates an API key for accessing any resource which can also be regenerated in the plugin UI._
* [JWT Authentication](https://plugins.miniorange.com/wordpress-rest-api-jwt-authentication-method#step_a1): _WordPress REST API Authentication itself issues the JWT token and works as an API Authenticator to protect your REST APIs. The plugin itself provides the REST API endpoint through which you can generate the JWT token very easily by passing the valid WordPress user credentials._
* [OAuth 2.0 Authentication](https://plugins.miniorange.com/wordpress-rest-api-oauth-2-0-authentication-method#step_a): _If you don’t have a third-party identity provider, then, in this case, **WordPress REST API Authentication** works as both **OAuth Server(Provider)** and **API Authenticator** to protect your REST APIs. It is the most secure method to authenticate the WordPress REST API endpoints._
           	- **1. Password Grant**: _If you want to fetch user-specific content from the database then this method is useful_
           	- **2. Client Credentials Grant**: _This method uses the OAuth 2.0 protocol with Client Credentials grant to authenticate the WP REST API endpoints to use services that call APIs without users._
* [Third Party Provider Authentication](https://plugins.miniorange.com/wordpress-rest-api-authentication-using-third-party-provider#step_a): _if you are already using an external OAuth/OpenID Connect (Identity provider) which provides you with an access token/id token or a JWT token, then that token can be used to authenticate the WordPress REST APIs and the plugin will validate the token directly from these token providers and only on successful validation, API endpoints are allowed to access._

 
 Following are some of the integrations that are possible with REST API Authentication:
== WooCommerce API ==
* **Wordpress Rest API authentication** allows integration of WP Rest API with **WooCommerce Rest APIs**. You can authenticate the WooCommerce store APIs with your mobile or desktop application & extend the features and functionality of your eCommerce store.
== BuddyPress API ( BP REST API ) ==
* With this plugin you can **access BP REST API endpoints** and also authenticate those from different Authentication methods like JWT token (JSON Web Token), API Keys etc.
== Gravity Form API ==
* This plugin supports interaction with Gravity Forms from an external client application which can be your android/iOS application. WP REST API Authentication also allows WordPress users to create, read, update and delete forms, entries, and results over HTTP based on their roles.
== Learndash API ==
* This plugin allows you to securely access to Learndash user profiles, courses, groups & many more third party APIs.
== Custom Built REST API Endpoints ==
* The plugin **supports authentication for your own built custom REST API routes/endpoints**. You can secure these API endpoints using the plugin’s highly secured authentication methods.
== External/Third-party plugin API endpoints integration in WordPress ==
* These integrations can be used to fetch/update the data from the third-party side into the WordPress that can be used to display it in WordPress site as well as this data can be processed further to use with any other plugin or WordPress events.
 
== FEATURES ==
 
FREE PLAN
 
* Supports Basic Authentication with username and password along with base64 encoding of the API token to have a secure api.
* JWT Token-based Authentication.
* Authentication for all the Standard WordPress REST API endpoints like WordPress Login, WordPress Register, etc..
* Allow or Deny public access to your WordPress standard REST APIs as per your requirement.
* Token endpoint to retrieve user-specific JWT (JSON Web tokens).
* Restrict non-logged-in users to access REST API endpoints.
* Postman Samples for each Authentication method to test the APIs access with the Postman application
 
PREMIUM PLANS
 
* Authentication (Protection) for all WordPress REST API endpoints including standard WP REST APIs and custom/third-party plugin's REST API endpoints.
* Supports Basic Authentication (both WP User credentials and Client credentials), JWT Token Authentication, API Key Authentication, OAuth 2.0 Authentication, Third-Party OAuth 2.0/OIDC Provider's Token Authentication methods.
* HMAC encryption & User-specific Client credentials with Basic authentication.
* Token endpoint to retrieve user-specific JWT (JSON Web tokens).
* Allow or Deny public access to all the WordPress standard REST APIs as well as custom/third-party plugin's REST API endpoints as per requirement.
* Universal (Global) API key as well as User-specific API key for authentication.
* Supports JWT Authentication with signature validation using highly secured HSA & RSA Signing.
* Custom Token Expiry feature for JWT token to further increase security.
* Provides the Time-based Access token or JWT token.
* WordPress Login using Access token or JWT token.
* Authenticate WordPress REST APIs with the token (access token / JWT) provided by your OAuth/OIDC Provider (Third Party Provider) like Azure, AWS Cognito, ADFS, Keycloak, Google, Facebook, ADFS, Firebase or any external JWT (JSON Web tokens) provider etc.
* User's WordPress Role & Capability-based access to all the WordPress REST API endpoints like posts, pages etc.
* Allow or Deny public access to your WordPress REST APIs as per requirement.
* Custom Header support rather than just 'Authorization' to increase security.
* Create users in WordPress based on third-party provider's access tokens (JWT tokens).
* Feature to exclude any selected REST API endpoints.
* Restrict non-logged-in users to access REST API endpoints.
== Installation ==
 
This section describes how to install the WordPress REST API Authentication and get it working.
 
= From your WordPress dashboard =
 
1. Visit `Plugins > Add New`
2. Search for `REST API Authentication`. Find and Install `api authentication` plugin by miniOrange
3. Activate the plugin
 
= From WordPress.org =
 
1. Download WordPress REST API Authentication.
2. Unzip and upload the `wp-rest-api-authentication` directory to your `/wp-content/plugins/` directory.
3. Activate WordPress REST API Authentication from your Plugins page.
 
 
== Privacy ==
 
This plugin does not store any user data.

== Frequently Asked Questions ==

= What is the use of API Authentication =
    The REST API authentication prevents unauthorized access to your WordPress APIs. It reduces potential attack factors.
	
= How to enable API access in WooCommerce?
    You can enable API access in WooCommerce using our WP REST API Authentication plugin. Please reach out to us at oauthsupport@xecurify.com.

= How does the REST API Authentication plugin work? =
	You just have to select your Authentication Method.
	Based on the method you have selected you will get the authorization code/token after sending the token request.
	Access your REST API with the code/token you received in the previous step. 

= How to access draft posts? =
	You can access draft posts using Basic Auth, OAuth 2.0(using Username: Password), JWT authentication, API Key auth(using Universal Key) methods. Pages/posts need to access with the status. Default status used in the request is 'Publish' and any user can access the Published post. 
	To access the pages/posts stored in the draft, you need to append the ?status=draft to the page/post request.
	For Example:
	You need to use below URL format while sending request to access different type of posts
	1. Access draft posts only
		https://<domain>/wp-json/wp/v2/posts?status=draft
	2. Access all type of posts
		https://<domain>/wp-json/wp/v2/posts?status=any
	You just have to change the status(draft, pending, any, publish) as per your requirement. You do not have to pass status parameter to access Published posts.

= How can I authenticate the REST APIs using this plugin? =
	This plugin supports 5 methods: i) authentication through API key or token, ii) authentication through user credentials passed as an encrypted token, iii) authentication through JWT (JSON Web token), iv) authentication through OAuth 2.0 protocol and v) authentication via JWT token obtained from the external OAuth/OpenId providers which include Google, Facebook, Azure, AWS Cognito, Apple etc and also from Firebase. 


= Does this plugin allows authentication through JWT (JSON Web Tokens)? =
	Yes, this plugin supports the REST API authentication through JWT (JSON Web token). The JWT be validated every time an API request is made, only the requested resource for the API call be allowed to access if the JWT validation is successful.

= How do I authenticate WordPress REST API endpoints using external JWT token or access token provided by OAuth/OIDC/Social Login providers? = 
     This plugin provides you with an authentication method called the 'Third Party Provider' authentication method in which the JWT token or access token obtained from external identities(OAuth/OIDC providers) like Firebase, Okta, Azure, Keycloak, ADFS, AWS Cognito, Google, Facebook, Apple etc. can be passed along with API request in the header and the plugin validates that JWT / access token directly from these external sources/providers. 

= How do I access user-specific data for Woocommerce REST API without the need to pass actual Woocommerce API credentials? =
	This plugin provides a way to bypass Woocommerce security and instead authenticate APIs using the authentication methods, hence improvising the security and hence no chance of Woocommerce credentials getting compromised. The authentication token passed in the API request will validate the user and results into user-specific data only. For more information, please contact us at oauthsupport@xecurify.com


== Screenshots ==

1. List of API Authentication Methods
2. List of Protected WP REST APIs
3. Basic Authentication method configuration
4. JWT Authentication method configuration
5. Advanced Settings
6. Custom API Integration
7. Postman Sample Settings

== Changelog ==

= 2.1.0 =
* Major UI updates
* Usability improvements and bug fixes
* Compatibility with WordPress 5.9.1
* Compatibility with PHP 8+

= 1.6.7 = 
* Compatiblity with WordPress 5.9

= 1.6.6 = 
* UI Updates

= 1.6.5 =
* WordPress 5.8.2 compatiblity
* UI Changes

= 1.6.4 =
* Security Improvements

= 1.6.3 =
* WordsPress 5.8.1 compatability
* Readme Updates 

= 1.6.2 =
* WordPress 5.8 compatiblity
* Bug Fixes
* Usability Improvements
* UI Updates

= 1.6.1 =
* Bug Fixes
* Modifications for Custom API auth capabilities

= 1.6.0 =
* Minor fixes
* UI updates
* Usability improvements

= 1.5.2 =
* Minor fixes
* Remove extra code

= 1.5.1 =
* Minor fixes
* Security fixes

= 1.5.0 =
* Minor fixes
* Security fixes

= 1.4.2 =
* UI updates

= 1.4.1 =
* UI updates
* Minor fixes

= 1.4.0 =
* WordPress 5.6 compatibility

= 1.3.10 =
* Allow all REST APIs to authenticate
* Added postman samples
* Minor Bugfix

= 1.3.9 =
* Minor Bugfix

= 1.3.8 =
* Added compatibility for WP 5.5

= 1.3.7 =
* Bundle plan release
* Minor Bugfix

= 1.3.6 =
* Added compatibility for WP 5.4

= 1.3.5 =
* Minor Bugfix

= 1.3.4 =
* Minor Bugfix

= 1.3.2 =
* Minor Bugfix

= 1.3.1 =
* Minor Fixes

= 1.3.0 =
* Added UI Changes
* Updated plugin licensing
* Added New features
* Added compatibility for WP 5.3 & PHP7.4
* Minor UI & feature fixes

= 1.2.1 =
* Added fixes for undefined getallheaders()

= 1.2.0 =
* Added UI changes for Signing Algorithms and Role-Based Access
* Added Signature Validation
* Minor fixes

= 1.1.2 =
* Added JWT Authentication
* Fixed role-based access to REST APIs
* Fixed common class conflicts

= 1.1.1 =
* Fixes to Create, Posts, Update Publish Posts

= 1.1.0 =
* Updated UI and features
* Added compatibility for WordPress version 5.2.2
* Added support for accessing draft posts as per User's WordPress Role Capability
* Allowed Logged In Users to access posts through /wp-admin Dashboard

= 1.0.2 =
* Added Bug fixes  

= 1.0.0 =
* Updated UI and features
* Added compatibility for WordPress version 5.2.2

== Upgrade Notice ==

= 1.1.1 =
* Fixes to Create, Posts, Update Publish Posts

= 1.1.0 =
* Updated UI and features
* Added compatibility for WordPress version 5.2.2
* Added support for accessing draft posts as per User's WordPress Role Capability
* Allowed Logged In Users to access posts through /wp-admin Dashboard

= 1.0.2 =
* Added Bug fixes  

= 1.0.0 =
* Updated UI and features
* Added compatibility for WordPress version 5.2.2

