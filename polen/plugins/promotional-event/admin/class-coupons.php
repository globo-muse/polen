<?php

class Coupons{
    /**
     * Criar tabela promocional de eventos
     */

    private $wpdb;
    public $table_name;

    function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $table_name = $wpdb->prefix . 'promotional_event';
    }

    public function create_table_promotinal_event()
    {
        $sql = "CREATE TABLE $this->table_name (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `code` VARCHAR(255) NOT NULL,
			  `is_used` bit NOT NULL DEFAULT (0),
			  `order_id` INT(11),
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `id_UNIQUE` (`id` ASC));";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Inserir Cupons na tabela
     *
     * @throws Exception
     */
    public function insert_coupons($qty)
    {
        for ($i = 0; $i <= $qty; $i++) {
            $this->wpdb->insert(
                $this->table_name,
                array(
                    'code' => Promotional_Event_Generate_Coupon::generate(8),
                )
            );
        }
    }
}


