<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://polen.me
 * @since      1.0.0
 *
 * @package    Promotional_Event
 * @subpackage Promotional_Event/admin/partials
 */
?>

<div class="container-fluid">
    <div class="panel panel-primary">
        <div class="panel-heading clearfix">
            <div class="col-md-10"><h3>Lista de Cupons</h3></div>
            <!--            <div class="col-md-2"><a href="#" class="btn btn-success pull-right" id="export">Exportar CSV</a></div>-->
        </div>
        <div class="panel-body">
            <table id="list-table" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Código</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">ID do pedido</th>
                </tr>
                </thead>
                <tfoot>
                <tr class="text-center">
                    <th class="text-center">#</th>
                    <th class="text-center">Código</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">ID do pedido</th>
                </tr>
                </tfoot>
                <tbody>
                <?php foreach ($values_code as $code) : ?>
                    <?php
                    $status = "Não Utilizado";
                    $order_id = $code->order_id;
                    ?>

                    <tr class="text-center">
                        <td style="text-align: center;"><?php echo $code->ID; ?></td>
                        <td style="text-align: center;"><?php echo $code->code; ?></td>

                        <?php if ($code->is_used == 1) : ?>
                            <?php $status =  "Utilizado"; ?>
                        <?php endif; ?>

                        <td style="text-align: center;"><?php echo $status; ?></td>

                        <?php if ($order_id) : ?>
                            <td style="text-align: center;">
                                <a href="<?php echo admin_url("/post.php?post={$order_id}&action=edit") ?>">
                                    #<?php echo !empty($order_id) ? $order_id : '--'; ?>
                                </a>
                            </td>
                        <?php else : ?>
                            <td style="text-align: center;"> -- </td>
                        <?php endif ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready( function ($) {
        $('#list-table').DataTable({
            // "pageLength": 5,
            // "pagingType": "full_numbers"
        });
        $('#list-table').removeClass( 'display' ).addClass('table table-striped table-bordered');
    });

</script>