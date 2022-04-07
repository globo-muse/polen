<?php
/*
Plugin Name: Advanced Custom Fields: Brazilian City
Description: Adiciona ao ACF a opção de campo de cidade considerando a seleção de Estado/Cidade.
Plugin URI: #
Version: 1.0.0
Author: Polen
License: GPL
*/
load_plugin_textdomain( 'acf-brazilian-city-field', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
// ACF Version 4.*
function register_fields_brazilian_city()
{
    include_once('Rest.php');
    include_once('acf-brazilian-city-field.php');
}
add_action('acf/include_field_types', 'register_fields_brazilian_city');