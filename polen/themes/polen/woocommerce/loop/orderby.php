<?php

/**
 * Show options for ordering
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/orderby.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.6.0
 */

if (!defined('ABSPATH')) {
  exit;
}
$inputs = new Material_Inputs();
$items = array();
foreach ( $catalog_orderby_options as $id => $name ) {
  $items[$id] = $name;
}
?>
<form class="woocommerce-ordering" method="get">
  <?php $inputs->material_select("orderby", "orderby", "Ordenar", $items, false, "orderby", array()); ?>
  <?php $inputs->input_hidden("paged", "1"); ?>
  <?php wc_query_string_form_fields(null, array('orderby', 'submit', 'paged', 'product-page')); ?>
</form>
<script>
  jQuery(document).ready(function() {
    var select = mdc.select.MDCSelect.attachTo(document.querySelector(".orderby"));
    select.setValue("<?php echo $orderby; ?>");
    select.listen('MDCSelect:change', () => {
      document.querySelector("form.woocommerce-ordering").submit();
    });
  });
</script>
