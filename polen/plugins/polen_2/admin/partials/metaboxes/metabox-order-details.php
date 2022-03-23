<?php
use Polen\Admin\Polen_Admin_Order_Custom_Fields;
use Polen\Includes\Polen_Cart;
use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Utils;
use Polen\Includes\Polen_WooCommerce;
?>
<div class="wrap">
    <?php
    $Polen_WooCommerce = new Polen_WooCommerce();
    $details = $Polen_WooCommerce->get_order_items( $order_id );
    ?>
    <table class="wc-order-totals">
        <?php
        if( $details && ! is_null( $details ) && is_array( $details ) && ! empty( $details) ) {
            foreach( $details as $k => $v ) {
                foreach( $v as $j => $info ) {
                    if( $j != 'id' && $info != 'other_one' ) {
        ?>
        <?php
            if ($j == 'Instruções do vídeo') {
        ?>
        <tr>
            <td>
                <strong><?php echo $j?>:</strong>
            </td>
            <td>
                <table>
                    <tr>
                        <?php $info = Polen_Utils::remove_sanitize_xss_br_escape($info, 'edit'); ?>
                        <td style="width: 400px;"><textarea id="video-instruction" style="margin-top: 0px; margin-bottom: 0px; height: 231px;width:100%;" data-action="polen_edit_order_custom_fields" disabled="disabled"><?= $info; ?></textarea></td>
                        <td><a href="#" class="edit-custom-field" field-edit="video-instruction" data-field="<?= Polen_Cart::ITEM_INSTRUCTION_TO_VIDEO; ?>" data-action="polen_edit_order_custom_fields">Editar</a></td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php
            } else {
        ?>
        <tr>
            <td>
                <strong><?php echo $j?>:</strong>
            </td>
            <td>
                <?php echo $info ?>
            </td>
        </tr>
        <?php
            }
        ?>
        <?php
                    }
                }
            }
        }
        ?>
        <tr>
            <td>
                <strong>Expira em:</strong>
            </td>
            <td>
                <?php
                try {
                    $_order = wc_get_order( $order_id );
                    $deadline = $_order->get_meta( Polen_Order::META_KEY_DEADLINE, true );
                    $field = Polen_Order::META_KEY_DEADLINE;
                    $deadline_datetime = \WC_DateTime::createFromFormat( 'U', $deadline );
                    if( empty( $deadline_datetime ) ) {
                        throw new \Exception( 'Problema com a data de expiração, entre em contato com o Admin', 500 );
                    }
                    $deadline_date = $deadline_datetime->format( 'd/m/Y' );
                    echo <<<HTML
                        <input type="text" id="deadline-field" disabled="disabled" value="{$deadline_date}"/>
                    HTML;

                } catch ( \Exception $e ) {
                    echo <<<HTML_ERROR
                        <input type="text" id="deadline-field" disabled="disabled" value=""/>
                        {$e->getMessage()}
                    HTML_ERROR;
                }
                    echo <<<HTML
                        <a href="#" class="edit-custom-field" field-edit="deadline-field" data-field="{$field}" data-action="polen_edit_order_custom_fields_deadline">Editar</a>
                    HTML;
                ?>
            </td>
        </tr>
    </table>
    <div class="clear"></div>
</div>
<script>
    jQuery(function(){
        jQuery('.edit-custom-field').click( function( evt ){
            evt.preventDefault();
            let input = jQuery(evt.currentTarget).attr('field-edit');
            isDisabled = jQuery( '#' + input ).is( ':disabled' );
            if( isDisabled ) {
                jQuery( '#' + input ).removeAttr( 'disabled' );
                jQuery( evt.currentTarget ).html( "Salvar" );
                return false;
            }
            let new_value = jQuery( '#' + input ).val();
            let data_update = {
                action : jQuery(evt.currentTarget).attr('data-action'),
                field : jQuery(evt.currentTarget).attr('data-field'),
                security : '<?= wp_create_nonce( Polen_Admin_Order_Custom_Fields::NONCE_ACTION ); ?>',
                value : new_value,
                order_id: <?= $_GET['post']; ?>
            };
            jQuery.post(ajaxurl, data_update, function(data,a,b){
                alert(data.data);
                jQuery( '#' + input ).attr( 'disabled', 'disabled' );
                jQuery( evt.currentTarget ).html( "Editar" );
            }).fail( (xhr, textStatus, errorThrown) => {
                alert(xhr.responseJSON.data);
                jQuery( '#' + input ).attr( 'disabled', 'disabled' );
            });
        });
    });
</script>
