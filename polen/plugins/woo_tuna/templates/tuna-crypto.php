<?php
if (!defined('ABSPATH')) {
  exit;
}
?>
<h3><?php echo __('Pagar com Bitcoin') ?></h3>
<p><?php echo __('A partir da sua wallet de preferência, efetue o pagamento de acordo com a cotação do momento.') ?></p>
<div style="height: 651px;">
  <div style="width:300px; float:left">
    <img src="<?php echo esc_html($crypto_coin_qrcode_url); ?>" width="250px" />
    <input style="width:250px;margin-top:5px" id="tuna-qr-code" value="<?php echo esc_html($crypto_coin_addr); ?>"></input>
    <button style="width:250px;margin-top:5px" onclick="copy_qr_code()" onclick="true"><?php echo esc_html('Copiar Hash'); ?></button>
    <script>
      function copy_qr_code() {
        var copyText = document.getElementById("tuna-qr-code");
        copyText.select();
        copyText.setSelectionRange(0, 99999)
        document.execCommand("copy");
      }
    </script>
  </div>
  <div style="float:left">
    <table>
      <tr>
        <th style="width:20px"> </th>
        <th> </th>
      </tr>
      <tr>
        <td> <?php echo __('1)'); ?></td>
        <td> <?php echo __('Abra a sua wallet de preferência'); ?> </td>
      </tr>
      <tr>
        <td> <?php echo __('2)'); ?></td>
        <td> <?php echo __('Escolha a opção pagar com qr code e escaneie o código ao lado'); ?> </td>
      </tr>
      <tr>
        <td> <?php echo __('3)'); ?></td>
        <td> <?php echo __('Confirme as informações e finalize a compra'); ?> </td>
      </tr>
    </table>

    <hr>
    <table>
      <tr>
        <td> <b> <?php echo __('Total:'); ?> </b> </td>
        <td align="right">
          <b>
            <?php
            $value = $crypto_coin_value;
            $arrayOfValues = preg_split('/\./', $value);
            $formattedValue = ($arrayOfValues[0] == '0' ? '0' : preg_replace('/,/', '.', $arrayOfValues[0])) . ',' . $arrayOfValues[1];
            echo __('₿ ' . $formattedValue);
            ?>
          </b>
        </td>
      </tr>
      <tr>
        <td> </td>
        <td align="right" style="color:#999999">
          <?php
          $value = $crypto_coin_rate_currency;
          $arrayOfValues = preg_split('/\./', $value);
          $formattedValue = ($arrayOfValues[0] == '0' ? '0,' : preg_replace('/,/', '.', $arrayOfValues[0])) . ',' . $arrayOfValues[1];
          echo __('1 ₿ ≈ R$ ' . $formattedValue);
          ?>
        </td>
      </tr>
      <tr>
        <td> </td>
        <td align="right" style="color:#999999;font-size:90%"> <?php echo __('cotação expira em 10 minutos'); ?> </td>
      </tr>
    </table>
  </div>

</div>
<div style="float:left;width:100%"><br/></div>