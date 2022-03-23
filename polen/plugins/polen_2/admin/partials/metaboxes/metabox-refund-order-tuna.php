<?php
defined( 'ABSPATH' ) || die;
?>
<div class="wrap">
    <div>
        <a href="#" id="polen_refund_order_tuna">Reembolsar valor</a>
    </div>
    <div class="clear"></div>
    <form id="form-refund-order-tuna" action="./" method="post">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
    </form>
</div>
<script>
    document.getElementById('polen_refund_order_tuna').addEventListener('click', function(evt){
        evt.preventDefault();
        if (!window.confirm('Tem certeza que você quer reembolsar o pedido ?')) {
            return;
        }
        let url = '<?= admin_url('admin-ajax.php'); ?>';
        const formCreate = document.querySelector('#form-refund-order-tuna');
        jQuery.ajax({
            type: "POST",
            url: url,
            data: {
                action: "create_refund_order_tuna",
                product_id: <?php echo $product_id; ?>
            },
            success: function (response) {
                alert('Pedido Reembolsado');
                window.location.reload();
            },
            error: function (jqXHR) {
                alert(jqXHR.responseJSON.data);
            },
        });
    });
</script>