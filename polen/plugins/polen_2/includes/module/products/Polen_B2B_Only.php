<?php
namespace Polen\Includes\Module\Products;

use Polen\Includes\Module\Polen_Product_Module;

class Polen_B2B_Only extends Polen_Product_Module
{
    /**
     * Cria uma opcao no Combobox Advanced de adicionar ao carrinho (B2C)
     * no frontend para o arquivo content-single-product.php
     * @param Input classe do templete polen
     * @return string
     */
    public function b2c_combobox_advanced_item_html($inputs)
    {
        $disabled = true;
        $checked = false;
        return $inputs->pol_combo_advanced_item(
            "Vídeo para uso pessoal",
            $this->get_price_html(),
            "Compre um vídeo personalizado para você ou para presentar outra pessoa",
            "check-pessoal", "pessoal",
            $checked,
            $disabled
        );
    }


    /**
     * Cria uma opcao no Combobox Advanced para ir para a Pagina B2B
     * no frontend para o arquivo content-single-product.php
     * @param Input classe do templete polen
     * @return string
     */
    public function b2b_combobox_advanced_item_html($inputs)
    {
        $disabled = false;

        $range = get_post_meta(get_the_ID(), 'polen_price_range_b2b', false);
        $price_range = $range[0] ? "À partir de R$ {$range[0]}" : 'Valor sob consulta';

        $checked = true;
        return $inputs->pol_combo_advanced_item(
            "Vídeo para meu negócio", 
            "{$price_range}",
            "Compre um Vídeo Polen para usar no seu negócio",
            "check-b2b",
            "b2b",
            $checked,
            $disabled
        );
    }


    public function template_buy_buttons($inputs)
    {
        ob_start();
        polen_buy_button_b2b($inputs, $this);
        $result_html = ob_get_contents();
        ob_end_clean();
        return $result_html;
    }


    public function get_price()
    {
        return $this->object->get_meta('polen_price_range_b2b');
    }
}