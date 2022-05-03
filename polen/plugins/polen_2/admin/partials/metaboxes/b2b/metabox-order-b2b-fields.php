<?php

use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Utils;

$categories = [
    "Divulgação da marca em redes sociais",
    "Marketing interno",
    "Eventos e Convenções",
    "Inauguração",
    "Outro",
];

$cnpj_cpf = '';
$installments = '';
$corporate_name = '';
$company_name = '';
$instructions_to_video = '';
$licence_in_days = '';
$video_category = '';
if(!empty($_GET['post'])) {
    global $post;
    if(!empty($post)) {
        $order                 = wc_get_order($post->ID);
        $polen_order           = new Polen_Order_Module($order);

        $installments          = $polen_order->get_installments();
        $cnpj_cpf              = $polen_order->get_billing_cnpj_cpf();
        $corporate_name        = $polen_order->get_corporate_name();
        $company_name          = $polen_order->get_company_name();
        $instructions_to_video = $polen_order->get_instructions_to_video();
        $licence_in_days       = $polen_order->get_licence_in_days();
        $video_category        = $polen_order->get_video_category();
    }
}
?>
<div class="wrap">
    <div>
        <p class="form-field form-field-wide">
            <label for="company_name">Video para a empresa</label>
            <input type="text" id="company_name" name="company_name" value="<?= $company_name; ?>" />
        </p>
    </div>
    <div>
        <p class="form-field form-field-wide">
            <label for="corporate_name">Razão Social</label>
            <input type="text" id="corporate_name" name="corporate_name" value="<?= $corporate_name; ?>" />
        </p>
    </div>
    <div>
        <p class="form-field form-field-wide">
            <label for="cnpj">CNPJ da empresa ou CPF do representante</label>
            <input type="text" id="cnpj" name="cnpj" value="<?= $cnpj_cpf; ?>" />
        </p>
    </div>
    <div>
        <p class="form-field form-field-wide">
            <label for="cnpj">Número máximo de parcelamento</label>
            <input type="text" id="installments" name="installments" value="<?= $installments; ?>" />
        </p>
    </div>
    <div class="clear"></div>
    <div>
        <p class="form-field form-field-wide">
            <label for="instructions_to_video">Instruções para o video</label>
            <textarea id="instructions_to_video" name="instructions_to_video" rows="6"><?= Polen_Utils::remove_sanitize_xss_br_escape($instructions_to_video, 'edit'); ?></textarea>
        </p>
    </div>
    <div class="clear"></div>
    <div>
        <p class="form-field form-field-wide">
            <label for="video_category">Finalidade do Video</label>
            <select id="video_category" name="video_category">
             <?php foreach($categories as $category) : ?>
                <option value="<?= $category; ?>" <?= $video_category == $category?'selected':''; ?>><?= $category; ?></option>
            <?php endforeach; ?>
            </select>
        </p>
    </div>
    <div>
        <p class="form-field form-field-wide">
            <label for="licence_in_days">Prazo da Licença</label>
            <select id="licence_in_days" name="licence_in_days">
                <option value="30" <?= $licence_in_days == '30'?'selected':'';?>>30</option>
                <option value="60" <?= $licence_in_days == '60'?'selected':'';?>>60</option>
                <option value="90" <?= $licence_in_days == '90'?'selected':'';?>>90</option>
            </select>
        </p>
    </div>
    <div class="clear"></div>
</div>