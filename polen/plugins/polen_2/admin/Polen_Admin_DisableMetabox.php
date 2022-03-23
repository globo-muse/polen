<?php

namespace Polen\Admin;

/**
 * Plugin Name: Polen
 * Description: Plugin plataforma Polen.
 * Version: 1.0.0
 * Author: Cubo9
 * Developer: Cubo9
 * Text Domain: polen
 *
 * WC requires at least: 4.0
 * WC tested up to: 4.0
 *
 * Copyright: © 2021 by Polen.
 * License: Proprietary
 */

if( ! defined( 'ABSPATH' ) ) {
    die( 'Silence is golden.');
}

/*
 * Check if WooCommerce exists and is active.
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    class Polen_Admin_DisableMetabox
    {

        public function __construct( $static = false ) {
            if( $static ) {
                /**
                 * Load translations
                 */
                add_action( 'init', array( $this, 'Load_Text_Domain' ) );

                /**
                 * Make default settings for WooCommerce
                 */
                $this->Set_WooCommerce_Settings();

                /**
                 * Load styles and scripts
                 */
                add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

                /**
                 * Remove genertor metas
                 */
                remove_action( 'wp_head', 'wp_generator' );
                remove_action( 'wp_head', 'wc_generator' );
                remove_action( 'wp_head', array( 'Redux_Functions_Ex', 'meta_tag' ) );

                /**
                 * Remove wp-admin dashboard widgets and branding
                 */
                remove_action( 'welcome_panel', 'wp_welcome_panel' );
                add_action( 'wp_dashboard_setup', array( $this, 'Remove_Dashboard_Metaboxes' ) );
                add_action( 'admin_init', array( $this, 'Remove_Footer_Text' ) );
                add_filter( 'update_footer', '__return_false', 11 );
                add_action( 'wp_before_admin_bar_render', array( $this, 'Remove_Admin_Bar_Logo' ), 0, 0 );

                /**
                 * Remove wp-admin dashboard widgets and branding for multisite
                 */
                if( defined( 'MULTISITE' ) && is_network_admin() ) {
                    add_action( 'wp_network_dashboard_setup', array( 'Remove_Dashboard_Metaboxes_Multisite' ) );
                }
            }
        }

        public function Load_Text_Domain() {
            if( file_exists( PLUGIN_POLEN_DIR . 'languages/' ) ) {
                $path = PLUGIN_POLEN_DIR . 'languages/';
                load_plugin_textdomain( 'polen', false, $path );
            }
        }

        public function Set_WooCommerce_Settings() {
            $polen_woocommerce_setup_finish = get_option( 'polen_woocommerce_setup_finish' );
            if( ! $polen_woocommerce_setup_finish || is_null( $polen_woocommerce_setup_finish ) || empty( $polen_woocommerce_setup_finish ) ) {
                if( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
                    update_option( 'woocommerce_registration_generate_username', 'yes' );
                }

                if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) ) {
                    update_option( 'woocommerce_registration_generate_password', 'no' );
                }

                update_option( 'polen_woocommerce_setup_finish', 'true' );
            }
        }

        public function Remove_Dashboard_Metaboxes() {
            /**
             * Remove widgets da tela inicial do wp-admin
             */
            if( is_admin() ) {
                remove_meta_box( 'welcome_panel', 'dashboard', 'normal' );
                remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );     // Status do Diagnóstico
                remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );        // Atividade
                remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );       // Agora
                remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' ); // Comentários Recentes
                remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );  // Links
                remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );         // Plugins

                remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );       // Quick Press
                remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );     // Rascunhos
                remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );           // Novidades e eventos do Wordpress
                remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );         // Outras novidades do Wordpress

                remove_meta_box( 'woocommerce_dashboard_recent_reviews', 'dashboard', 'normal' );
                remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal' );
                remove_meta_box( 'wc_admin_dashboard_setup', 'dashboard', 'normal' );
            }
        }

        public function Remove_Dashboard_Metaboxes_Multisite() {
            remove_meta_box('network_dashboard_right_now', 'dashboard-network', 'normal'); // Agora
            remove_meta_box('dashboard_primary', 'dashboard-network', 'side');             // Novidades e eventos do Wordpress    
        }

        public function Remove_Footer_Text() {
            add_filter( 'admin_footer_text', array( $this, 'Footer_Text' ), 11, 1 );
        }
        
        public function Footer_Text( $content ) {
            return '';
        }

        public function Remove_Admin_Bar_Logo() {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu( 'wp-logo' );
        }

        public function scripts() {
            wp_enqueue_script('jquery-maskedinput', PLUGIN_POLEN_URL . 'assets/scripts/vendor/jquery.maskedinput.min.js', array( 'jquery' ), null, true );
            //wp_enqueue_script('polen-script', PLUGIN_POLEN_URL . 'assets/scripts/scripts.js', array( 'jquery' ), null, true );
        }

    }

    // new Polen( true );

}
