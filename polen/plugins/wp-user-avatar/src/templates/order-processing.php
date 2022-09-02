<?php /** @global $order_success_page */ ?>
<div id="ppress-payment-processing">
    <p><?php printf(__('Your order is processing. This page will reload automatically in 8 seconds. If it does not, click <a href="%s">here</a>.', 'wp-user-avatar'), $order_success_page); ?>
        <script type="text/javascript">setTimeout(function () {
                window.location = '<?php echo $order_success_page; ?>';
            }, 8000);
        </script>
</div>
