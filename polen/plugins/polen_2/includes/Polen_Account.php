<?php

namespace Polen\Includes;

use \Polen\includes\Polen_Talent;
use \Polen\Includes\Polen_Video_Info;

class Polen_Account
{

    public function __construct( $static = false ) {
        if( $static ) {
            add_filter( 'wp_pre_insert_user_data', array( $this, 'set_user_login' ), 10, 3 );
            add_filter( 'woocommerce_endpoint_orders_title', array( $this,  'my_account_custom' ), 20, 2 );
            add_filter( 'woocommerce_account_menu_items', array( $this, 'my_account_menu_title' ) );
            add_filter( 'woocommerce_endpoint_view-order_title', array( $this,  'view_order_custom' ), 20, 2 );
            add_filter( 'woocommerce_before_account_orders', array( $this, 'my_orders_title' ));
            add_action( 'template_redirect', array( $this, 'my_account_redirect' ) );
            add_action( 'woocommerce_account_watch-video_endpoint', array( $this, 'my_account_watch_video' ) );
            add_action( 'woocommerce_account_create-review_endpoint', array( $this, 'my_account_create_review' ) );
            add_action( 'init', function() {
                add_rewrite_endpoint('watch-video', EP_PAGES, 'watch-video' );
                add_rewrite_endpoint('create-review', EP_PAGES, 'create-review' );
            });
        }
    }

    public function set_user_login( $data, $update, $id ) {
        if( isset( $_REQUEST['talent_alias'] ) && ! empty( $_REQUEST['talent_alias'] ) ) {
            $data['user_nicename'] = $_REQUEST['talent_alias'];
        }
        $data['user_login']    = $data['user_email'];
        return $data;
    }

    public function my_account_custom( $title, $endpoint ) {
        $title = __( " ", "polen" );
        return $title;
    }

    public function my_orders_title(){
        $logged_user = wp_get_current_user();
		if( in_array( 'user_talent',  $logged_user->roles ) )
		{ 
            echo '<h1 class="entry-title">Suas solicitações</h1>';
        }else{
            echo '<h1 class="entry-title">Meus pedidos</h1>';
        }    
    }

    public function view_order_custom( $title, $endpoint ) {
        $title = ' ';
        return $title;
    }

    public function my_account_menu_title( $items ) {
        $logged_user = wp_get_current_user();
        if( in_array( 'user_talent',  $logged_user->roles ) )
        { 
            $menu_items = array(
                // 'dashboard'       => 'Início',
                'orders'          => 'Meus pedidos',
                'payment-options' => 'Pagamento',
                'customer-logout' => __( 'Logout', 'woocommerce' ),
            );
        }else{
            $menu_items = array(
                'orders'          => 'Meus pedidos',
                // 'payment-options' => 'Pagamento',
                'edit-account'    => 'Meus dados',
                'customer-logout' => __( 'Logout', 'woocommerce' ),
            );           
        }    
        return $menu_items;
    }

    /**
     * Faz my-account redirecionar para a lista de pedidos ao invés do dashboard
     */
    public function my_account_redirect() {
        if( is_user_logged_in() ){
            $logged_user = wp_get_current_user();
            if( !in_array( 'user_talent',  $logged_user->roles ) )
            { 
                $current_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";        
                $dashboard_url = get_permalink( get_option('woocommerce_myaccount_page_id'));
                if( is_user_logged_in() && $dashboard_url == $current_url ){
                    $url = get_home_url() . '/my-account/orders';
                    wp_redirect( $url );
                    exit;
                }
            } 
        }   
    }

    /**
     * Tela para visualizar o vídeo
    */
    public function my_account_watch_video()
    {
        if( is_user_logged_in() ){
            $user = wp_get_current_user();
            $polen_talent = new Polen_Talent;
            if( $polen_talent->is_user_talent( $user ) ) {
                wp_safe_redirect(site_url('my-account/orders'));
                exit;
            }
            $order_id = get_query_var('watch-video');
            if( isset( $order_id ) && !empty( $order_id) ){
                $video_info = Polen_Video_Info::get_by_order_id( $order_id );
                $video_hash = $video_info->hash;
                if( !empty( $video_hash ) ){
                    require_once PLUGIN_POLEN_DIR . '/publics/partials/polen_watch_video.php';
                } else {
                    $this->set_404();
                }    
            }
        }
    }


    /**
     * Tela para fã criar um review
    */
    public function my_account_create_review()
    {
        $order_id = get_query_var('create-review');
        $order = wc_get_order( $order_id );
        if( empty( $order) ) {
            $this->set_404();
        }
        $item_cart = \Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
        $talent_id = $item_cart->get_talent_id();
        
        require_once PLUGIN_POLEN_DIR . '/publics/partials/polen_create_review.php';
    }


    private function set_404()
    {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        get_template_part( 404 );
        exit();
    }

    public function polen_core_show_user_profile($user)
    {
        /** wp user avatar customized function **/
        global $blog_id, $current_user, $show_avatars, $wpdb, $wp_user_avatar, $wpua_edit_avatar, $wpua_functions, $wpua_upload_size_limit_with_units;

        $has_wp_user_avatar = has_wp_user_avatar(@$user->ID);
        // Get WPUA attachment ID
        $wpua = get_user_meta(@$user->ID, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', true);
        // Show remove button if WPUA is set
        $hide_remove = ! $has_wp_user_avatar ? 'wpua-hide' : "";
        // Hide image tags if show avatars is off
        $hide_images = ! $has_wp_user_avatar && (bool)$show_avatars == 0 ? 'wpua-no-avatars' : "";
        // If avatars are enabled, get original avatar image or show blank
        $avatar_medium_src = (bool)$show_avatars == 1 ? $wpua_functions->wpua_get_avatar_original(@$user->user_email, 'medium') : includes_url() . 'images/blank.gif';
        // Check if user has wp_user_avatar, if not show image from above
        $avatar_medium = $has_wp_user_avatar ? get_wp_user_avatar_src($user->ID, 'medium') : $avatar_medium_src;
        // Check if user has wp_user_avatar, if not show image from above
        $avatar_thumbnail     = $has_wp_user_avatar ? get_wp_user_avatar_src($user->ID, 96) : $avatar_medium_src;
        $edit_attachment_link = esc_url(add_query_arg(array('post' => $wpua, 'action' => 'edit'), admin_url('post.php')));
        // Chck if admin page
        ?>
        <input type="hidden" name="wp-user-avatar" id="<?php echo ($user == 'add-new-user') ? 'wp-user-avatar' : 'wp-user-avatar-existing' ?>" value="<?php echo $wpua; ?>"/>
        <?php if ($wp_user_avatar->wpua_is_author_or_above()) : // Button to launch Media Uploader ?>

        <p id="<?php echo ($user == 'add-new-user') ? 'wpua-add-button' : 'wpua-add-button-existing' ?>">
            <button type="button" class="button" id="<?php echo ($user == 'add-new-user') ? 'wpua-add' : 'wpua-add-existing' ?>" name="<?php echo ($user == 'add-new-user') ? 'wpua-add' : 'wpua-add-existing' ?>" data-title="<?php _e('Choose Image', 'wp-user-avatar'); ?>: <?php echo(! empty($user->display_name) ? $user->display_name : ''); ?>"><?php _e('Choose Image', 'wp-user-avatar'); ?></button>
        </p>

        <?php elseif ( ! $wp_user_avatar->wpua_is_author_or_above()) : // Upload button ?>
            <p style="display: none;" id="<?php echo ($user == 'add-new-user') ? 'wpua-upload-button' : 'wpua-upload-button-existing' ?>">
                <input name="wpua-file" id="<?php echo ($user == 'add-new-user') ? 'wpua-file' : 'wpua-file-existing' ?>" type="file" class= "wpua-file" />
                <button type="submit" class="btn btn-outline-light" id="<?php echo ($user == 'add-new-user') ? 'wpua-upload' : 'wpua-upload-existing' ?>" name="submit" value="<?php _e('Upload', 'wp-user-avatar'); ?>"><?php _e('Upload', 'wp-user-avatar'); ?></button>
            </p>
        <?php endif; ?>
        <div style="display: none;" id="<?php echo ($user == 'add-new-user') ? 'wpua-images' : 'wpua-images-existing' ?>" class="text-center <?php echo $hide_images; ?>">
            <p id="<?php echo ($user == 'add-new-user') ? 'wpua-preview' : 'wpua-preview-existing' ?>" class="image-cropper large">
                <img src="<?php echo $avatar_medium; ?>" alt=""/>
            </p>
            <a href="javascript:document.querySelector('.wpua-file').click()" class="btn btn-outline-light btn-lg btn-block mb-3 d-none">Trocar imagem</a>
        </div>
        <?php
    }
}
