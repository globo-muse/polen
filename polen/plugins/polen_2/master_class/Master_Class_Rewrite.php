<?php

namespace Polen\Master_class;

class Master_Class_Rewrite
{
    const BASE_URL = 'masterclass';
    const QUERY_VARS_MASTER_CLASS_APP     = 'master_class_app';

    const QUERY_VARS_MASTER_CLASS_IS_HOME = 'master_class_is_home';
    const QUERY_VARS_MASTER_CLASS_SUCCESS = 'master_class_success';

    public function __construct($static = false)
    {
        if ($static) {
            $this->init();
        }

    }

    public function init()
    {
        add_action('init', array($this, 'rewrites'));
        add_filter('query_vars', array($this, 'query_vars'), 10, 1);
        add_action('template_include', array($this, 'template_include'));
    }


    /**
     *
     */
    public function rewrites()
    {
        add_rewrite_rule( self::BASE_URL . '/ronnie-von/beaba-do-vinho/sucesso[/]?$', 'index.php?'.self::QUERY_VARS_MASTER_CLASS_APP.'=1&'.self::QUERY_VARS_MASTER_CLASS_SUCCESS.'=1', 'top' );
        add_rewrite_rule( self::BASE_URL . '/ronnie-von/beaba-do-vinho[/]?$', 'index.php?'.self::QUERY_VARS_MASTER_CLASS_APP.'=1&'.self::QUERY_VARS_MASTER_CLASS_IS_HOME.'=1', 'top' );
    }


    /**
     *
     */
    public function query_vars($query_vars)
    {
        $query_vars[] = self::QUERY_VARS_MASTER_CLASS_APP;
        $query_vars[] = self::QUERY_VARS_MASTER_CLASS_IS_HOME;
        $query_vars[] = self::QUERY_VARS_MASTER_CLASS_SUCCESS;
        return $query_vars;
    }


    /**
     *
     */
    public function template_include($template)
    {
        $app = get_query_var( self::QUERY_VARS_MASTER_CLASS_APP );
        if( empty( $app ) || $app !== '1' ) {
            return $template;
        }

        $GLOBALS[ self::QUERY_VARS_MASTER_CLASS_APP ]     = '1';

        if ($this->is_home()) {
            $GLOBALS[self::QUERY_VARS_MASTER_CLASS_IS_HOME] = '1';
            return get_template_directory() . '/master_class/index.php';
        }

        if( $this->is_page_success() ) {
            $GLOBALS[self::QUERY_VARS_MASTER_CLASS_SUCCESS] = '1';
            return get_template_directory() . '/master_class/success.php';
        }

    }


    /**
     *
     */
    private function is_home()
    {
        $is_home = get_query_var(self::QUERY_VARS_MASTER_CLASS_IS_HOME);
        if (!empty($is_home) || $is_home == '1') {
            return true;
        }

        return false;
    }


    /**
     *
     */
    private function is_page_success()
    {
        $page = get_query_var(self::QUERY_VARS_MASTER_CLASS_SUCCESS);
        if (!empty($page) || $page == '1') {
            return true;
        }

        return false;
    }

}
