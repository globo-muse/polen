<div class="wrap">
    <?php if ( in_array( $order->get_status(), array( 'payment-approved', 'talent-rejected', 'talent-accepted', 'order-expired', 'processing', 'completed', 'refunded' ), true ) ) : ?>
    <table class="wc-order-totals">
        <tr>
            <td>
                <strong>Valor:</strong>
            </td>
            <td>
                <?php echo wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ); ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Parcelas:</strong>
            </td>
            <td>
                <?php echo get_post_meta( $order->get_id(), 'braspag_order_installments', true ); ?>x
            </td>
        </tr>
        <tr>
            <td>
                <strong>Pago em:</strong>
            </td>
            <td>
                <?php echo esc_html( sprintf( __( '%1$s', 'woocommerce' ), date_i18n( get_option( 'date_format' ) . ' \à\s H:i:s' ) ) ); ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Transaction id:</strong>
            </td>
            <td>
                <?php echo get_post_meta( $order->get_id(), '_transaction_id', true );  ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>NSU:</strong>
            </td>
            <td>
                <?php echo get_post_meta( $order->get_id(), 'braspag_order_nsu', true );  ?>
            </td>
        </tr>  
        <tr>
            <td>
                <strong>Authorization Code:</strong> 
            </td>
            <td>
                <?php echo get_post_meta( $order->get_id(), 'braspag_order_authorizationCode', true );  ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Link(s):</strong>
            </td>
            <td>
                <?php 
                    $link_array = get_post_meta( $order->get_id(), 'braspag_order_links', true );
                    if( $link_array && ! is_null( $link_array ) && is_array( $link_array ) && ! empty( $link_array ) ) {
                        foreach( $link_array as $k => $link ) {
                            if( $link->Method == 'GET' ) {
                                echo '<a href="' . $link->Href . '" target="_blank">' . $link->Href . '</a><br>';
                            }
                        }
                    }    
                ?>
            </td>
        </tr>
    </table>
    <div class="clear"></div>
    <?php endif; ?>
</div>