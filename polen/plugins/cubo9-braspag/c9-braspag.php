<?php

/**
 * Plugin Name: Cubo9 - Braspag
 * Description: Add-on de pagamentos através da Braspag para WooCommerce
 * Version: 1.0.0
 * Author: Cubo9
 * Developer: Cubo9
 * Text Domain: cubo9
 *
 * WC requires at least: 3.0
 * WC tested up to: 3.0
 *
 * Copyright: © 2020 by Cubo9.
 * License: Proprietary
 */

if( ! defined( 'ABSPATH' ) ) {
    die( 'Silence is golden.');
}

/**
 * Checa se o plugin WooCommerce está ativo.
 */
if ( ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) ) {

    /**
     * Define as constantes utilizadas pelo Plugin.
     */
    define( 'PLUGIN_CUBO9_BRASPAG_DIR', plugin_dir_path( __FILE__ ) );
    define( 'PLUGIN_CUBO9_BRASPAG_URL', plugin_dir_url( __FILE__ ) );

    /**
     * URL de retorno para a Braspag
     */
    add_query_arg( 'wc-api', 'wc-cubo9-braspag', home_url('/') );

    /**
     * Inclusão das classes auxiliares ao plugin.
     */
    require_once 'classes/class.WC_Cubo9_BraspagReduxSettings.php';
    require_once 'classes/class.WC_Cubo9_Braspag_Helper.php';
    require_once 'classes/class.Cubo9_Braspag.php';
    require_once 'classes/class.WC_Cubo9_WooCommerce.php';
    require_once 'redux-components/credit-card-installments/credit-card-installments.php';

    /**
     * Adiciona o método de pagamento Braspag a lista de métodos disponíveis.
     */
    add_filter( 'woocommerce_payment_gateways', 'WC_Cubo9_Braspag_Add' );
    function WC_Cubo9_Braspag_Add( $gateways ) {
        $gateways[] = 'WC_Cubo9_Braspag';
        return $gateways;
    }

    /**
     * Adiciona o botão de configurações do gateway de pagamento na listagem de plugins.
     */
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'WC_Cubo9_Braspag_Links' );
    function WC_Cubo9_Braspag_Links( $links ) {
        $settings = array(
            '<a href="' . admin_url( 'admin.php?page=cubo9-braspag-settings' ) . '">' . __( 'Configurações', 'cubo9' )
        );
        return array_merge( $settings, $links );
    }

    /**
     * Adiciona a classe do método de pagamento WC_Cubo9_Braspag.
     */
    add_action( 'plugins_loaded', 'WC_Cubo9_Braspag_Init', 11 );
    function WC_Cubo9_Braspag_Init() {
        require_once 'classes/class.WC_Cubo9_Braspag.php';
    }

    /**
     * Criação da(s) tabela(s) no banco de dados
     */
    function WC_Cubo9_Braspag_Create_Tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "
            CREATE TABLE `" . $wpdb->base_prefix . "c9_braspag_cards` (
                `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `active` INT(1) NULL DEFAULT NULL,
                `brand` varchar(50) NOT NULL,
                `slug` varchar(50) NOT NULL,
                `icon` text DEFAULT NULL,
                `debit_tax` float(5,2) DEFAULT NULL,
                `installment_1` float(5,2) DEFAULT NULL,
                `installment_2` float(5,2) DEFAULT NULL,
                `installment_3` float(5,2) DEFAULT NULL,
                `installment_4` float(5,2) DEFAULT NULL,
                `installment_5` float(5,2) DEFAULT NULL,
                `installment_6` float(5,2) DEFAULT NULL,
                `installment_7` float(5,2) DEFAULT NULL,
                `installment_8` float(5,2) DEFAULT NULL,
                `installment_9` float(5,2) DEFAULT NULL,
                `installment_10` float(5,2) DEFAULT NULL,
                `installment_11` float(5,2) DEFAULT NULL,
                `installment_12` float(5,2) DEFAULT NULL,
                PRIMARY KEY (`ID`),
                UNIQUE KEY `slug` (`slug`)
            ) " . $charset_collate . ";";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    register_activation_hook( __FILE__, 'WC_Cubo9_Braspag_Create_Tables' );


    function addPaymentInfoMetabox() {
        if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
            add_meta_box( 'PaymentInfoMetabox', 'Dados do Pagamento', 'paymentInfoMetabox', 'shop_order', 'normal', 'low' );
        }
    }
    add_action( 'add_meta_boxes', 'addPaymentInfoMetabox' );

    function paymentInfoMetabox() {
        global $post;
        $order = wc_get_order( $post->ID );
        if( file_exists( TEMPLATEPATH . '/braspag/admin/metabox-payment-info.php' ) ) {
            require_once TEMPLATEPATH . '/braspag/admin/metabox-payment-info.php';
        } else {
            require_once PLUGIN_CUBO9_BRASPAG_DIR . '/assets/php/metabox-payment-info.php';
        }
    }
}