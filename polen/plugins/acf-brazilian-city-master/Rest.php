<?php

class Rest {

    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'https://servicodados.ibge.gov.br/api/v1/localidades/';
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Request a API do IBGE
     *
     * @param string $path
     * @return mixed
     */
    public function request(string $path)
    {
        $args = array(
            'timeout' => 45,
        );

        $response = wp_remote_get($this->apiUrl . $path, $args);

        return json_decode(wp_remote_retrieve_body($response));
    }

    /**
     * Retornar estado através da API do IBGE
     *
     * @return string[]
     */
    public function get_states(): array
    {
        $response = $this->request('estados');

        $states = ['__' => ''];
        foreach ($response as $value) {
            $states[$value->sigla] = $value->nome;
        }

        return $states;
    }

    /**
     * Retornar cidades de acordo com o estado selecionado
     *
     * @return string[]
     */
    public function get_cities_by_state(string $state): array
    {
        $response = $this->request("estados/{$state}/municipios");

        $cities = [];
        foreach ($response as $value) {
            $cities[$value->id] = $value->nome;
        }

        return $cities;
    }
}