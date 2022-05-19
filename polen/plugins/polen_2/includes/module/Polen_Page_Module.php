<?php
namespace Polen\Includes\Module;

use Exception;

defined('ABSPATH') ?? die;

class Polen_Page_Module
{
    protected $object;

    public function __construct($object)
    {
        if(is_page($object->ID ?? '')) {
            throw new Exception('Page inválida', 401);
        }

        $this->object = $object;
    }

    public function get_id()
    {
        return $this->object->ID;
    }


    public function get_title()
    {
        return $this->object->post_title;
    }


    public function get_excerpt()
    {
        return $this->object->post_excerpt;
    }


    public function get_name()
    {
        return $this->object->post_name;
    }


    public function get_slug()
    {
        return $this->object->post_name;
    }

    public function get_content()
    {
        return $this->object->post_content;
    }


    public function get_seo_title()
    {
        return get_field('seo_title', $this->get_id());
    }


    public function get_seo_meta_title()
    {
        return get_field('meta_title', $this->get_id());
    }


    public function get_seo_meta_description()
    {
        return get_field('meta_description', $this->get_id());
    }


    public function get_seo_image()
    {
        return get_field('seo_image', $this->get_id());
    }
}