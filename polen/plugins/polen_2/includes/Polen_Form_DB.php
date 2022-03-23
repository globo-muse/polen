<?php

namespace Polen\Includes;

use Exception;

class Polen_Form_DB
{

    private $wpdb;
    private $table_name;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->base_prefix . 'polen_forms';
    }

    public function insert($args)
    {
        if (isset($args['action'])) {
            unset($args['action']);
        }
        $inserted = $this->wpdb->insert($this->table_name, $args);
        if(!empty($this->wpdb->last_error)) {
            throw new Exception($this->wpdb->last_error, 503);
        }

        return $inserted;
    }

    public function getLeads($form_id = 1)
    {
        return $this->wpdb->get_results("
            SELECT * FROM {$this->table_name} 
            WHERE `form_id` = {$form_id}
            ",
        );
    }
}


