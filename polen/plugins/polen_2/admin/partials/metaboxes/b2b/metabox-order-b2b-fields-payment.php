<?php

use Polen\Includes\Module\Polen_Order_Module;

$payments = [
    "Transferência",
    "Pix",
];

$form_of_payment = $payday = $value_payment_talent = $video_url_b2b = $qty_employees = $company_size = '';

if(!empty($_GET['post'])) {
    global $post;
    if (!empty($post)) {
        $order                 = wc_get_order($post->ID);
        $polen_order           = new Polen_Order_Module($order);

        $form_of_payment = $polen_order->get_form_of_payment(); // forma de pagamento escolhida
        $value_payment_talent = $polen_order->get_value_payment_talent();
        $payday = $polen_order->get_payday();

        $video_url_b2b = $polen_order->get_video_url_b2b();
        $qty_employees = $polen_order->get_qty_employees();
        $company_size = $polen_order->get_company_size();
    }
}
?>
<div class="wrap">
    <hr>
    <b>Informações para o pedido concluído</b>
    <div>
        <p class="form-field form-field-wide">
            <label for="payday">Data do pagamento</label>
            <?php echo $form_of_payment; ?>
            <input type="date" id="payday" name="payday" value="<?= $payday; ?>" />
        </p>
    </div>

    <div>
        <p class="form-field form-field-wide">
            <label for="video_category">Forma de pagamento</label>
            <select id="form_of_payment" name="form_of_payment">
                <?php foreach($payments as $payment) : ?>
                    <option value="<?= $payment; ?>" <?= $form_of_payment == $payment?'selected':''; ?>><?= $payment; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
    </div>

    <div>
        <p class="form-field form-field-wide">
            <label for="value_payment_talent">Valor pago para o talento</label>
            <input type="text" id="value_payment_talent" name="value_payment_talent" value="<?= $value_payment_talent; ?>" />
        </p>
    </div>

    <!--video-->
    <div>
        <p class="form-field form-field-wide">
            <label for="video_url_b2b">url do vídeo</label>
            <input type="text" id="video_url_b2b" name="video_url_b2b" value="<?= $video_url_b2b; ?>" />
        </p>
    </div>
    <div>
        <p class="form-field form-field-wide">
            <label for="qty_employees">Número de funcionários</label>
            <input type="number" id="qty_employees" name="qty_employees" value="<?= $qty_employees; ?>" />
        </p>
    </div>

    <div>
        <p class="form-field form-field-wide">
            <label for="company_size">Tamanho da empresa</label>
            <input type="text" id="company_size" name="company_size" value="<?= $company_size; ?>" />
        </p>
    </div>
    <div class="clear"></div>
</div>