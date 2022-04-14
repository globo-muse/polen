<?php

class acf_brazilian_city_field extends acf_field {

    public function __construct()
    {
        $this->name = 'COUNTRY_FIELD';
        $this->label = 'Cidade Brasileira';
        $this->category = __('Basic','acf');
        $this->defaults = array(
            'city_name' => '',
            'state_name' => '',
            'city_id' => 0,
            'state_id' => '',
        );
        parent::__construct();
        $this->settings = array(
            'version' => '1.1.0',
            'url'  => plugin_dir_url(__FILE__),
            'path' => plugin_dir_path(__FILE__)
        );

        add_action('wp_ajax_get_list_state_cities', [$this, 'get_list_state_cities']);
    }

    function render_field($field)
    {
        $ibge = new Rest();
        $field['value'] = $field['value'] ?? '';
        $field_name = $field['name'];
        $city_id = $field['value']['city_id'] ??  0;
        $state_id = $field['value']['state_id'] ?? 0;
        $states = $ibge->get_states();
        $cities = $ibge->get_cities_by_state($state_id);

        ?>

            <ul class="country-selector-list">
                <li id="field-<?php echo $field_name; ?>[state_id]">
                        <strong><?php _e("Selecione o estado", 'acf'); ?></strong><br />
                        <?php

                        $state_field = $field['name'] . '[state_id]';
                        acf_render_field(array(
                            'type' => 'select',
                            'name' => $state_field,
                            'value' => $state_id,
                            'choices' => $states,
                        ));
                        ?>
                </li>
                <li id="field-<?php echo $field_name; ?>[city_id]">
                        <strong><?php _e("Selecione a cidade", 'acf'); ?></strong><br />
                        <?php
                        $city_field = $field['name'] . '[city_id]';
                        acf_render_field(array(
                            'type' => 'select',
                            'name' => $city_field,
                            'value' => $city_id,
                            'choices' => $cities,
                        ));
                        ?>
                </li>
            </ul>

        <?php
    }

    function input_admin_enqueue_scripts()
    {
        wp_register_script('acf-brazilian-city', $this->settings['url'] . 'js/brazilian-city.js', array('acf-input'), $this->settings['version']);
        wp_register_script('acf-input-chosen', $this->settings['url'] . 'js/chosen.jquery.min.js', array('jquery'), $this->settings['version']);
        wp_register_style('acf-input-chosen', $this->settings['url'] . 'css/chosen.min.css', array(), $this->settings['version']);
        wp_localize_script( 'acf-brazilian-city', "AcfBrazilianCity", array(
            "ajaxurl" => admin_url("admin-ajax.php"),
        ) );
        // scripts
        wp_enqueue_script(array(
            'acf-brazilian-city',
            'acf-input-chosen',
        ));
        // styles
        wp_enqueue_style(array(
            'acf-input-chosen',
        ));
    }

    /**
     * Disponibiliza via ajax a lista de cidades para um determinado estado
     */
    function get_list_state_cities()
    {
        $ibge = new Rest();
        $state_id = trim($_REQUEST['stateId']);
        $cities_results = $ibge->get_cities_by_state($state_id);
        if (!$cities_results) {
            return null;
        }

        ob_end_clean();
        header("Content-Type: application/json");
        echo json_encode($cities_results);
        wp_die();

    }
}

new acf_brazilian_city_field();