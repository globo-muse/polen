<?php
use Polen\Includes\Polen_Video_Info;
$video_info = Polen_Video_Info::get_by_order_id( $order_id );

if( !empty( $video_info ) ) :
?>
<div class="wrap">
    <table class="wc-order-totals table table">
        <tr>
            <td><strong>Hash:</strong></td>
            <td><?= $video_info->hash; ?></td>
        </tr>
        <tr>
            <td><strong>Link:</strong></td>
            <td><a href="<?= site_url( "v/{$video_info->hash}" ); ?>" target="_blank"><?= site_url( "v/{$video_info->hash}" ); ?></a></td>
        </tr>
        <tr>
            <td><strong>Thumbnail"</strong></td>
            <td><a href="<?= $video_info->vimeo_thumbnail; ?>" target="_blank"><?= $video_info->vimeo_thumbnail; ?></a></td>
        </tr>
        <tr>
            <td><strong>Processamento do Vimeo:</strong></td>
            <td><?= $video_info->vimeo_process_complete == '1' ? 'Concluído' : 'Ainda em processamento'; ?></td>
        </tr>
        <tr>
            <td><strong>Público:</strong></td>
            <td><?= $video_info->is_public == '1' ? 'Publico' : 'Privado'; ?></td>
        </tr>
    </table>
    <div class="clear"></div>
</div>

<?php endif;
