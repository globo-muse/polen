<?php
/**
 * Polen functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Polen
 */

use Polen\Includes\Polen_Talent;

define('TEMPLATE_URI', get_template_directory_uri());
define('TEMPLATE_DIR', get_template_directory());
define('DEVELOPER', defined('ENV_DEV') && ENV_DEV);
define('POL_COOKIES', array(
	'POLICIES' => 'pol_policies',
	'CRIESP_BANNER_HOME' => 'criesp-banner-home',
));

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', DEVELOPER ? time() : '2.0.0' );
}

add_action('init', 'handle_preflight');
function handle_preflight() {
        header("Access-Control-Allow-Origin: " . "*");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
        if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) {
            status_header(200);
            exit();
        }
}

if ( ! function_exists( 'polen_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function polen_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Polen, use a find and replace
		 * to change 'polen' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'polen', TEMPLATE_DIR . '/languages' );

		// Add default posts and comments RSS feed links to head.
		//add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );
		$fat = 1.5;

		add_image_size( 'polen-thumb-sm', 156*$fat, 190*$fat, false );
		add_image_size( 'polen-thumb-md', 163*$fat, 190*$fat, false );
		add_image_size( 'polen-thumb-lg', 200*$fat, 290*$fat, false );
		add_image_size( 'polen-thumb-xl', 316*$fat, 371*$fat, false );

		add_image_size( 'polen-square-crop-sm', 32*$fat, 32*$fat, true );
		add_image_size( 'polen-square-crop-md', 40*$fat, 40*$fat, true );
		add_image_size( 'polen-square-crop-lg', 48*$fat, 48*$fat, true );
		add_image_size( 'polen-square-crop-xl', 120*$fat, 120*$fat, true );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'polen' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'polen_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'polen_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function polen_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'polen_content_width', 640 );
}
add_action( 'after_setup_theme', 'polen_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function polen_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'polen' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'polen' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'polen_widgets_init' );

function get_assets_folder() {
	$min = "min/";
	if (DEVELOPER) {
		$min = "";
	}
	return $min;
}

function polen_remove_all()
{
	global $wp_styles;
	global $wp_scripts;

	if(
		is_front_page() ||
		is_page_template( 'inc/landpage.php' ) ||
		(is_singular() && is_product()) ||
		polen_is_landingpage() ||
		is_tribute_app()
		) {
		foreach( $wp_styles->queue as $style ) {
			wp_dequeue_style( $wp_styles->registered[$style]->handle );
		}
		foreach( $wp_scripts->queue as $scripts ) {
			wp_dequeue_script( $wp_scripts->registered[$scripts]->handle );
		}
	}
}

/**
 * Enqueue scripts and styles.
 */
function polen_scripts() {
	$min = get_assets_folder();
	polen_remove_all();

	if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
		remove_theme_support( 'wc-product-gallery-zoom' );
		remove_theme_support( 'wc-product-gallery-lightbox' );
		remove_theme_support( 'wc-product-gallery-slider' );
	}

	wp_enqueue_script( 'global-js', TEMPLATE_URI . '/assets/js/' . $min . 'global.js', array("jquery"), _S_VERSION, false );

	if(is_front_page() || social_is_in_social_app()) {
		wp_enqueue_script( 'home-scripts', TEMPLATE_URI . '/assets/js/' . $min . 'front-page.js', array('owl-carousel'), _S_VERSION, true );
	}

	// Registrando Scripts ------------------------------------------------------------------------------
	wp_register_script( 'vimeo', 'https://player.vimeo.com/api/player.js', array(), '', true );
	wp_register_script('polen-upload-video-tus', TEMPLATE_URI . '/assets/js/' . $min . 'tus.js', array(), _S_VERSION, true);
	wp_register_script('vuejs', TEMPLATE_URI . '/assets/vuejs/' . $min . 'vue.js', array(), '', false);
	wp_register_script( 'comment-scripts', TEMPLATE_URI . '/assets/js/' . $min . 'comment.js', array("vuejs"), _S_VERSION, true );
	wp_register_script( 'suggestion-scripts', TEMPLATE_URI . '/assets/js/' . $min . 'suggestion.js', array("jquery"), _S_VERSION, true );
	wp_register_script( 'owl-carousel', TEMPLATE_URI . '/assets/js/vendor/owl.carousel.min.js', array(), _S_VERSION, true );
	wp_register_script( 'zuck', TEMPLATE_URI . '/assets/js/' . $min . 'zuck.js', array(), _S_VERSION, true );
	wp_register_script( 'form-whatsapp', TEMPLATE_URI . '/assets/js/' . $min . 'form-whatsapp.js', array("vuejs"), _S_VERSION, true );
	wp_register_script( 'polen-business', TEMPLATE_URI . '/assets/js/' . $min . 'business.js', array("vuejs"), _S_VERSION, true );
  wp_register_script( 'polen-help', TEMPLATE_URI . '/assets/js/' . $min . 'help.js', array("jquery", "vuejs"), _S_VERSION, true );
  wp_register_script( 'material-js', TEMPLATE_URI . '/assets/js/vendor/material-components-web.min.js', array(), _S_VERSION, false );
  wp_register_script('popper-js', TEMPLATE_URI . '/assets/js/vendor/popper.min.js', array(), _S_VERSION, true);
	// --------------------------------------------------------------------------------------------------

	if (polen_is_landingpage()) {
		wp_enqueue_script( 'landpage-scripts', TEMPLATE_URI . '/assets/js/' . $min . 'landpage.js', array("jquery"), _S_VERSION, true );
	}

	if (is_tribute_app()) {
		wp_enqueue_script( 'tributes-scripts', TEMPLATE_URI . '/assets/js/' . $min . 'tributes.js', array("jquery", "vuejs"), _S_VERSION, true );
		wp_enqueue_script( 'tributes-scripts-video', TEMPLATE_URI . '/assets/js/' . $min . 'upload-video-tributes.js', array("jquery", "polen-upload-video-tus"), _S_VERSION, true );
	}

	if(social_is_in_social_app())
	{
		wp_enqueue_script( 'polen-cart', TEMPLATE_URI . '/assets/js/' . $min . 'criesp.js', array(), _S_VERSION, true );
	}

	wp_enqueue_style('polen-custom-styles', TEMPLATE_URI . '/assets/css/style.css', array(), filemtime(TEMPLATE_DIR . '/assets/css/style.css'));

	if((is_singular() && is_product()) || event_promotional_is_detail_product()) {
		// wp_enqueue_script( 'slick-slider', TEMPLATE_URI . '/assets/slick/slick.min.js', array("jquery"), '', true );
		wp_enqueue_script( 'vimeo');
		wp_enqueue_script( 'talent-scripts', TEMPLATE_URI . '/assets/js/' . $min . 'talent.js', array("vimeo", "zuck"), _S_VERSION, true );
	}

	if( is_cart() ) {
		wp_enqueue_script( 'polen-cart', TEMPLATE_URI . '/assets/js/' . $min . 'cart.js', array("jquery", "vuejs"), _S_VERSION, true );
	}

	if( is_checkout() ) {
		wp_enqueue_script( 'polen-checkout', TEMPLATE_URI . '/assets/js/' . $min . 'checkout.js', array("jquery"), _S_VERSION, true );
	}

	wp_enqueue_script( 'bootstrap-js', TEMPLATE_URI . '/assets/js/vendor/bootstrap.min.js', array("jquery", "popper-js"), _S_VERSION, true );
  wp_enqueue_script('material-js');

	// if(is_user_logged_in()) {
		wp_enqueue_script( 'header-scripts', TEMPLATE_URI . '/assets/js/' . $min . 'navigation.js', array("jquery"), _S_VERSION, true );
	// }
}
add_action( 'wp_enqueue_scripts', 'polen_scripts' );

/**
 * File responsible to utils functions
 */
require_once TEMPLATE_DIR . '/inc/utils.php';

/**
 * Implement the Custom Header feature.
 */
require TEMPLATE_DIR . '/inc/custom-header.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require TEMPLATE_DIR . '/inc/template-functions.php';

/**
 * SEO functions
 */
require TEMPLATE_DIR . '/inc/seo.php';

/**
 * Customizer additions.
 */
require TEMPLATE_DIR . '/inc/customizer.php';


/**
 * Components.
 */
require TEMPLATE_DIR . '/inc/components.php';

/**
 * File responsible to get all collection for front
 */
require_once TEMPLATE_DIR . '/inc/collection-front.php';

/**
 * File responsible to analitics functions
 */
require_once TEMPLATE_DIR . '/inc/analitics_function.php';

/**
 * File responsible to search only products in /?s=
 */
require_once TEMPLATE_DIR . '/inc/search.php';

/**
 * Arquivo responsavel por retornos HTML e icones
 */
require_once TEMPLATE_DIR . '/classes/Icon_Class.php';

/**
 * Arquivo responsavel por retornos da tela de acompanhamento de pedidos
 */
require_once TEMPLATE_DIR . '/classes/Order_Class.php';

/**
 * Funcoes do Tributes APP
 */
require_once TEMPLATE_DIR . '/tributes/tributes_functions.php';


/**
 * Funcoes do Social APP
 */
require_once TEMPLATE_DIR . '/social/social_function.php';

require_once TEMPLATE_DIR . '/event_promotional/function_event_promotional.php';

require_once TEMPLATE_DIR . '/social_base/function.php';;

/**
* Funções para master-class
*/
require_once TEMPLATE_DIR . '/master_class/function_master_class.php';

/**
 * Funções para polen empresas
 */

// flush_rewrite_rules();

require_once TEMPLATE_DIR . '/enterprise/function_enterprise.php';

/**
 * Função para retornar categorias destacadas
 */
require_once TEMPLATE_DIR . '/inc/highlight_categories.php';

/**
 * Funcoes responsáveis pelo B2B da polen
 */
require_once TEMPLATE_DIR . '/inc/b2b_functions.php';

/**
 * Funções para REST API
 */
require_once TEMPLATE_DIR . '/api/api_function.php';

/**
 * Funções para página natal lacta
 */
require_once TEMPLATE_DIR . '/lacta/function_natal_lacta.php';

/**
 * Funções para modificar a página padrão thankyou page
 */
require_once TEMPLATE_DIR . '/inc/function_thankyoupage.php';

add_action('wc_gateway_stripe_process_response', function($response, $order) {
	// $response
	// $order
	if( $response->status == 'succeeded' ) {
		$order->update_status( 'payment-approved', 'Pago com Sucesso' );
	}

	if ( $response->status == 'failed') {
		$order->update_status( 'payment-rejected', 'Erro no Pagamento' );
	}
}, 10, 3);

add_action('wc_gateway_stripe_process_webhook_payment_error', function($order, $notification){
	$order->update_status( 'payment-rejected', 'Erro no Pagamento' );
}, 10, 2);

add_filter('wc_stripe_save_to_account_text', function(){
	return 'Salvar os dados do cartão de credito para proximas compras.';
});

add_action('woocommerce_before_checkout_process', function(){
	WC_Emails::instance();
});

function filter_woocommerce_coupon_error($err, $err_code, $instance)
{
  WC()->cart->remove_coupons();
  return $err;
};

add_filter('woocommerce_coupon_error', 'filter_woocommerce_coupon_error', 10, 3);

/**
 * Funções para disparado de zapier
 */
require_once TEMPLATE_DIR . '/inc/zapier.php';

/**
 * Customiza a paginação de todos os tipos de posts
 *
 * @param array $args
 * @return string
 */
function show_pagination(array $args)
{
  $query = new WP_Query($args);

  $maxPage = 99999;
  $pages = paginate_links(array(
    'base' => str_replace($maxPage, '%#%', esc_url(get_pagenum_link($maxPage))),
    'format' => '?paged=%#%',
    'current' => max(1, get_query_var('paged')),
    'total' => $query->max_num_pages,
    'type' => 'array',
    'prev_next' => true,
    'prev_text' => __('<i aria-hidden="true" class="fas fa-fw fa-chevron-left"><</i>'),
    'next_text' => __('<i aria-hidden="true" class="fas fa-fw fa-chevron-right">></i>'),
  ));

  $output = '';
  if (is_array($pages)) {
    $output .= '<ul class="pagination">';
    foreach ($pages as $page) {
      $output .= "<li class=\"pagination__number\">{$page}</li>";
    }
    $output .= '</ul>';
  }
  wp_reset_query();

  return $output;
}

//Remover plugins do menu para Sobhan
function polen_remove_menus() {
	remove_menu_page( 'plugins.php' );
}

if( 37 == get_current_user_id() ) {
	add_action( 'admin_menu', 'polen_remove_menus' );
}

add_action('woocommerce_account_content', function(){
	if(Polen_Talent::static_is_user_talent(wp_get_current_user())) {
		polen_alert('Para melhorar a experiencia dos nossos ídolos, criamos um novo Dashboard. Para acessar <a href="https://idolo.polen.me" target="_blank">clique aqui</a>');
	}
}, 9);

// Redirect polen
//REMOVENDO O REDIRECT
/*
add_action('template_redirect', 'redirect_to_polen');
function redirect_to_polen()
{
    $path = $_SERVER['REQUEST_URI'];
    if (stripos($path, 'my-account') && is_page()) {
        return null;
    }

    exit(wp_redirect('https://polen.me'));
}

function redirect_users_by_role()
{
    $current_user   = wp_get_current_user();
    if (in_array('customer', $current_user->roles)) {
        exit(wp_redirect('https://polen.me'));
    }
}
add_action('init', 'redirect_users_by_role');
*/