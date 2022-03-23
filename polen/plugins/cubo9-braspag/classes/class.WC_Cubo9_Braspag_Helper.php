<?php

class WC_Cubo9_Braspag_Helper {

    public function __construct( $static = false ) {
        global $WC_Cubo9_BraspagReduxSettings;
        $this->braspag_settings = $WC_Cubo9_BraspagReduxSettings;
        if( $static ) {
            // add_action( 'wp_enqueue_scripts', array( $this, 'scripts') );
        } else {
            $this->active_credit_card_brands = $this->credit_card_brands();
        }
    }
    
    /**
     * Front Scripts
     */
    public function scripts() {
        
    }

    /**
     * Listagem das bandeiras de cartões cadastradas
     */
    public function credit_card_brands( $args = array() ) {
        global $wpdb;
        $sql = "SELECT `brand`, `slug` FROM `" . $wpdb->base_prefix . "c9_braspag_cards`";
        if( isset( $args['active'] ) ) {
            $sql .= ' WHERE `active`=' . intval( $args['active'] );
        } else { 
            $sql .= ' WHERE `active`=1';
         }
        $orderby = ( isset( $args['orderby'] ) ) ? $args['orderby'] : 'brand';
        $order = ( isset( $args['order'] ) ) ? $args['order'] : 'ASC';
        $sql .= " ORDER BY `" . $orderby ."` " . $order;

        $res = $wpdb->get_results( $sql );
        
        if( ! is_wp_error ( $res ) ) {
            return $res;
        }
    }

    public function calculate_installments( $amount ) {
        $max_installments      = (int) $this->braspag_settings['max_installments'];
        $min_installment_value = (float) $this->braspag_settings['min_installment_value'];

        if( ! empty( $min_installment_value ) && $min_installment_value > (float) 0 ) {
            $max_installments_count = floor( ($amount/$min_installment_value) );
            if( $max_installments_count > $max_installments ) {
                $max_installments_count = $max_installments;
            }
        } else {
            $max_installments_count = $max_installments;
        }

        $return = array();
        for( $i=0; $i<$max_installments_count; $i++ ) {
            $installment = ($i+1);
            $value = (float) ($amount/$installment);
            $value = number_format( $value, 2, ',', '.' );
            $return[ $installment ] = $value;
        }
        return $return;
    }

    public function get_installment_rates_range( $installment ) {
        global $wpdb;
        if( $installment >= 1 && $installment <= 12 ) {
            $field = 'installment_' . $installment;
            $sql = "SELECT MIN(`" . $field . "`) AS `min`, MAX(`" . $field . "`) AS `max` FROM `" . $wpdb->base_prefix . "c9_braspag_cards`";
            $res = $wpdb->get_results( $sql );
            if( count( $res ) > 0 ) {
                return $res[0];
            }
        }
    }

    public function get_installment_rates_by_brand( $installment, $brand_slug ) {
        global $wpdb;
        if( $installment >= 1 && $installment <= 12 ) {
            $field = 'installment_' . $installment;
            $sql = "SELECT `" . $field . "` FROM `" . $wpdb->base_prefix . "c9_braspag_cards` WHERE `slug`='" . $brand_slug . "'";
            $res = $wpdb->get_results( $sql );
            if( count( $res ) > 0 ) {
                return (float) $res[0]->$field;
            }
        }
    }

}

new WC_Cubo9_Braspag_Helper( true );
