<?php

use Polen\Includes\Module\Polen_Order_Module;

$payments = [
    "Transferência",
    "Pix",
];

$form_of_payment = $payday = $value_payment_talent = '';

if(!empty($_GET['post'])) {
    global $post;
    if(!empty($post)) {
        $order                 = wc_get_order($post->ID);
        $polen_order           = new Polen_Order_Module($order);

        $form_of_payment = $polen_order->get_form_of_payment(); // forma de pagamento escolhida
        $value_payment_talent = $polen_order->get_value_payment_talent();
        $payday = $polen_order->get_payday();
    }
}
?>
<div class="wrap">
    <div>
        <p class="form-field form-field-wide">
            <label for="payday">Data do pagamento</label>
            <input type="date" id="payday" name="payday" value="<?= $payday; ?>" />
        </p>
    </div>
    <div>
        <p class="form-field form-field-wide">
            <label for="value_payment_talent">Valor pago para o talento</label>
            <input type="text" id="value_payment_talent" name="value_payment_talent" value="<?= $value_payment_talent; ?>" />
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
    <div class="clear"></div>
</div>