<?php
namespace Polen\Tributes;

class Tributes_API_Router extends \WP_REST_Controller
{

    public function __construct( bool $static = false )
    {
        if( $static ) {
            $this->create_routes_create_tributes();
            $this->create_routes_create_tributes_invites();
        }
    }

    /**
     * Cria as rotas para tratamento dos Tributos no /wp-admin/admin-ajax.php
     */
    public function create_routes_create_tributes()
    {
        $controller = new Tributes_Controller();
        add_action( 'wp_ajax_create_tribute',        [ $controller, 'create_tribute' ] );
        add_action( 'wp_ajax_nopriv_create_tribute', [ $controller, 'create_tribute' ] );

        add_action( 'wp_ajax_check_tribute_slug_exists',        [ $controller, 'check_slug_exists' ] );
        add_action( 'wp_ajax_nopriv_check_tribute_slug_exists', [ $controller, 'check_slug_exists' ] );

        add_action( 'wp_ajax_check_tribute_hash_exists',        [ $controller, 'check_hash_exists' ] );
        add_action( 'wp_ajax_nopriv_check_tribute_hash_exists', [ $controller, 'check_hash_exists' ] );

        add_action( 'wp_ajax_tribute_get_links_downloads', [ $controller, 'get_links_downloads' ] );
    }

    /**
     * Cria as rotas para tratamento dos Invites no /wp-admin/admin-ajax.php
     */
    public function create_routes_create_tributes_invites()
    {
        $controller = new Tributes_Invites_Controller();
        add_action( 'wp_ajax_create_tribute_invites',        [ $controller, 'create_tribute_invites' ] );
        add_action( 'wp_ajax_nopriv_create_tribute_invites', [ $controller, 'create_tribute_invites' ] );

        add_action( 'wp_ajax_get_invites_by_tribute',        [ $controller, 'get_all_invite_by_tribute_id' ] );
        add_action( 'wp_ajax_nopriv_get_invites_by_tribute', [ $controller, 'get_all_invite_by_tribute_id' ] );

        add_action( 'wp_ajax_tribute_resend_email',        [ $controller, 'resend_email' ] );
        add_action( 'wp_ajax_nopriv_tribute_resend_email', [ $controller, 'resend_email' ] );

        add_action( 'wp_ajax_tribute_create_vimeo_slot',        [ $controller, 'make_video_slot_vimeo' ] );
        add_action( 'wp_ajax_nopriv_tribute_create_vimeo_slot', [ $controller, 'make_video_slot_vimeo' ] );

        add_action( 'wp_ajax_tribute_delete_invite',        [ $controller, 'delete_invite' ] );
        add_action( 'wp_ajax_nopriv_tribute_delete_invite', [ $controller, 'delete_invite' ] );
    }
}
