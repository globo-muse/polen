<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Polen
 */

use Polen\Includes\Cart\Polen_Cart_Item_Factory;

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function polen_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'polen_body_classes' );


function polen_get_header_objects()
{
	?>
	<script>
		var polenObj = {
			base_url: '<?= site_url(); ?>',
			template_uri: '<?php echo TEMPLATE_URI; ?>',
			developer: <?php echo DEVELOPER ? 1 : 0; ?>,
			ajax_url: "/wp-admin/admin-ajax.php",
			COOKIES: <?php echo json_encode(POL_COOKIES); ?>
		};
		if (!polenObj.developer) {
			console = {
				debug: function() {},
				error: function() {},
				info: function() {},
				log: function() {},
				warn: function() {},
			};
		}
	</script>
	<?php
}

/**
 * Responsible to return a link for all talents
 *
 * @return string link
 */
function polen_get_all_new_talents_url()
{
	return polen_get_all_talents_url() . '?orderby=date';
}

/**
 * Retorna a URL de todos os talentos
 *
 * @return string link
 */
function polen_get_all_talents_url()
{
	return get_permalink( wc_get_page_id( 'shop' ) );
}


/**
 * Responsible to return a link for all categories
 *
 * @return string link
 */
function polen_get_all_categories_url()
{
	return site_url( get_option( 'category_base', null ) );
}


/**
 * Get a URL para assistir video passando a $order_id
 * @param int $order_id
 */
function polen_get_link_watch_video_by_order_id( $order_id )
{
	return wc_get_account_endpoint_url('watch-video') . "{$order_id}";
}

/**
 * Get a URL para acompanhar o pedido passando a $order_id
 * @param int $order_id
 */
function polen_get_link_order_status( $order_id )
{
	return polen_get_url_my_account() . "view-order/" . "{$order_id}";
}


/**
 * Funcao para pegar a URL do My-Account
 */
function polen_get_url_my_account()
{
	return get_permalink( get_option('woocommerce_myaccount_page_id') );
}

/**
 * Funcao para pegar a URL dos Pedidos (Talento)
 */
function polen_get_url_my_orders()
{
	return polen_get_url_my_account() . "orders";
}


/**
 * Pegar a URL da categoria pela CategoriaID
 */
function polen_get_url_category_by_term_id( $term_id )
{
	return get_term_link( $term_id, 'product_cat' );
}


/**
 * Pegar o nome da categoria pelo WC_Product
 * @param WC_Product
 * @return string
 */
function polen_get_title_category_by_product( $product )
{
	$ids = $product->get_category_ids();
	$category = _polen_get_first_category_object( $ids );
	return $category->name;
}


/**
 * Pegar a URL da categoria pelo ProductID
 */
function polen_get_url_category_by_product_id ( $product_id )
{
	$cat_terms = wp_get_object_terms( $product_id, 'product_cat' );
    $cat_link = '';
	$cat = array_pop( $cat_terms );
    if ( !empty($cat) ) {
        $cat_link = get_term_link($cat->term_id);
    }
	return $cat_link;
}


/**
 * Pegar a URL da categoria pelo OrderID
 */
function polen_get_url_category_by_order_id ( $order_id )
{
	$order = wc_get_order( $order_id );
	$car_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
	$category_ids = $car_item->get_product()->get_category_ids();
	$category_id = array_pop( $category_ids );
	$cat_terms = wp_get_object_terms( $category_id, 'product_cat' );
    $cat_link = '';
	$cat = array_pop( $cat_terms );
    if ( !empty($cat) ) {
        $cat_link = get_term_link($cat->term_id);
    }
	return $cat_link;
}

/**
 *
 */
function polen_get_url_review_page()
{
	return './reviews/';
}

/**
 *
 */
function polen_get_url_create_review( $order_id )
{
	return polen_get_url_my_account() . 'create-review/' . $order_id;
}

/**
 * Pegar a URL da Custom Logo
 */
function polen_get_custom_logo_url() {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if( $custom_logo_id && ! is_null( $custom_logo_id ) && ! empty( $custom_logo_id ) ) {
		$image_url = wp_get_attachment_image_url( $custom_logo_id, 'full', true );
		$protocol = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ) ? 'https:' : 'http:';
		return $protocol . $image_url;
	}
}


/**
 * Pegar a URL da Custom Logo
 * a unica funcao dessa function é corrigir um erro que está dando em producao
 * tem que ser removido e corrigido.
 * o problema é que está apresentado https:https://polen.m
 */
function polen_get_custom_logo_url_() {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if( $custom_logo_id && ! is_null( $custom_logo_id ) && ! empty( $custom_logo_id ) ) {
		$image_url = wp_get_attachment_image_url( $custom_logo_id, 'full', true );
		$protocol = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ) ? 'https:' : 'http:';
		$protocol = '';
		return $protocol . $image_url;
	}
}


/**
 * Pegar as duas logos para thema claro e escuro
 */
function polen_get_theme_logos() {
	$logo_dark = get_theme_mod( 'custom_logo' );
	$logo_dark = wp_get_attachment_image_url( $logo_dark, 'full' );
	$logo_light = get_theme_mod( 'logo_theme_white' );

	// Provisório CRIESP
	$logo_criesp_dark = TEMPLATE_URI . '/assets/img/criesp/logo-criesp.png';
	$logo_criesp_light = TEMPLATE_URI . '/assets/img/criesp/logo-criesp-color.png';

	// Masterclass
	$logo_masterclass = TEMPLATE_URI . "/assets/img/masterclass/logo-masterclass.svg";

	$html =  '<a href="https://polen.me/" class="custom-logo-link" rel="home" aria-current="page">';

	if(social_is_in_social_app()) {
		$html .= 	'<img width="67" height="40" src="'. $logo_dark . '" class="custom-logo" alt="Polen">';
	} elseif(master_class_is_app()) {
		$html .= 	'<img width="208" height="36" src="'. $logo_masterclass . '" class="dark" alt="Polen">';
	}
	 else {
		$html .= 	'<img width="67" height="40" src="'. $logo_dark . '" class="custom-logo dark" alt="Polen">';
		$html .= 	'<img width="67" height="40" src="'. $logo_light . '" class="custom-logo light" alt="Polen">';
	}
	$html .= '</a>';

	if(is_tribute_app())
	{
		return $html;
	}

	// Provisório CRIESP
	// $html .= '<a href="' . social_get_criesp_url() . '">';
	// if(is_front_page() || social_is_in_social_app()) {
	// 	$html .= 	'<img width="106" height="31" src="'. $logo_criesp_dark . '" class="custom-logo custom-logo-criesp" alt="Logo Criança Esperança">';
	// } else {
	// 	$html .= 	'<img width="106" height="31" src="'. $logo_criesp_dark . '" class="custom-logo custom-logo-criesp dark" alt="Logo Criança Esperança">';
	// 	$html .= 	'<img width="106" height="31" src="'. $logo_criesp_light . '" class="custom-logo custom-logo-criesp light" alt="Logo Criança Esperança">';
	// }
	// $html .= '</a>';

	return $html;
}

function polen_the_theme_logos() {
	echo polen_get_theme_logos();
}

/**
 * Funcao que pegar a URL de login e completa com ?redirect= se estiver no cart ou checkout
 */
function polen_get_login_url() {
	$complement = '';
	if( is_cart() || is_checkout() ) {
		$url_complement = is_cart() ? urlencode( wc_get_cart_url() ) : urlencode( wc_get_checkout_url() );
		$complement = '?redirect_to=' . $url_complement;
	}
	return polen_get_url_my_account() . $complement;
}


/**
 *
 */
function polen_get_querystring_redirect()
{
	$redirect_to = urlencode( filter_input( INPUT_GET, 'redirect_to' ) );
	if( !empty( $redirect_to ) ) {
		return "?redirect_to={$redirect_to}";
	}
	return null;
}


/**
 * Se o email que será enviado for para um Talento
 * será mostrado o Valor Total sem desconto só é tratado nessa funcao
 * emails Polen\Includes\Polen_WC_Payment_Approved
 *
 * @param WC_Order
 * @param \WC_Email
 */
function polen_get_total_order_email_detail_to_talent( $order, $email )
{
	if ( ( 'Polen\Includes\Polen_WC_Payment_Approved' === get_class( $email ) )
		&& !empty( $email->get_recipient_talent())
	) {
		$total_order = floatval( $order->get_total() );
		$discount = floatval( $order->get_discount_total() );
		$order_is_social = social_order_is_social( $order );
		return polen_apply_polen_part_price( ( $total_order + $discount ), $order_is_social );
	}
	return $order->get_total();
}


function polen_get_videos_by_talent($talent, $json = false)
{
	$items = array();
	$items_raw = Polen\Includes\Polen_Video_Info::select_by_talent_id($talent->user_id);
	foreach ($items_raw as $item) {
		$order = wc_get_order($item->order_id);
		$cart_item = \Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
		$items[] = [
			'title' => '',
			'name' => $talent->nome,
			'thumb' => polen_get_avatar($talent->user_id, 'polen-square-crop-lg'),
			'cover' =>  $item->vimeo_thumbnail,
			'video' => $item->vimeo_file_play,
			'hash' => $item->hash,
			'first_order' => $item->first_order,
			'initials' => $item->order_id == "4874"?'':polen_get_initials_name($cart_item->get_name_to_video()),
		];
	}

	return $json ? json_encode($items) : $items;
}


/**
 * Aplica a parte da polen no valor de entrada (valor produto)
 * 25%
 *
 * @param float $full_price
 */
function polen_apply_polen_part_price( $full_price, $social = false )
{
	if( $social ) {
		return $full_price;
	} else {
		return ( floatval( $full_price ) * 0.75 );
	}
}

function polen_zapier_thankyou( $order )
{
	if(empty($order)) {
		return;
	}
	$order_item_cart = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
	$product = $order_item_cart->get_product();
	?>
		<form id="zapier-purchase-data">
			<input type="hidden" name="order_id" value="<?php echo $order->get_id(); ?>" />
			<input type="hidden" name="nome" value="<?php echo $order_item_cart->get_name_to_video(); ?>" />
			<input type="hidden" name="email" value="<?php echo $order_item_cart->get_email_to_video(); ?>" />
			<input type="hidden" name="artista" value="<?php echo $product->get_name(); ?>" />
			<input type="hidden" name="data_compra" value="<?php echo $order->order_date; ?>" />
			<input type="hidden" name="data_atual" value="<?php echo date("Y-m-d H:i:s"); ?>" />
			<input type="hidden" name="url_source" value="<?php echo site_url( $_SERVER[ 'REDIRECT_URL' ] ); ?>" />
      <input type="hidden" name="zapier" value="4" />
		</form>
		<script>
			polRequestZapier("#zapier-purchase-data");
		</script>
	<?php
}
