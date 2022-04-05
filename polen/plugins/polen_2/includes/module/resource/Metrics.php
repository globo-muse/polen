<?php

namespace Polen\Includes\Module\Resource;

class Metrics{

    protected array $percentage_by_regions;

    public function __construct()
    {
        $this->percentage_by_regions = ['sul' => 0, 'sudeste' => 0, 'nordeste' => 0, 'norte' => 0, 'centro-oeste' => 0];
    }

    /**
     * Retornar porcentagem por região
     *
     * @param string $abbreviation
     * @param int $percentage
     */
    public function set_percentage_by_regions(string $abbreviation, int $percentage): void
    {
        $states = [
            'sul' => ['PR', 'RS', 'SC'],
            'sudeste' => ['SP', 'RJ', 'ES', 'MG'],
            'centro-oeste' => ['MT', 'MS', 'GO'],
            'norte' => ['AM', 'RR', 'AP', 'PA', 'TO', 'RO', 'AC'],
            'nordeste' => ['MA', 'PI', 'CE', 'RN', 'PE', 'PB', 'SE', 'AL', 'BA'],
        ];

        foreach ($states as $name_region => $state) {
            if (in_array($abbreviation, $state)) {
                $this->percentage_by_regions[$name_region] += $percentage;
            }
        }

    }

    /**
     * retornar propiedade percentage_by_regions
     *
     * @return array
     */
    public function get_percentage_by_regions(): array
    {
        return $this->percentage_by_regions;
    }

    /**
     * Retornar porcentagem total somando todas as regiões
     *
     * @return float|int
     */
    public function sum_percentage(): int
    {
        return array_sum($this->percentage_by_regions);
    }
}