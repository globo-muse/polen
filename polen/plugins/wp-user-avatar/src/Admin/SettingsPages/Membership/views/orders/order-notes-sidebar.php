<?php /** @var array $order_notes */ ?>
<div class="ppress-order-notes-wrap">
    <ul class="ppress-notes">
        <?php foreach ($order_notes as $order_note) : $explode = explode('|', $order_note['meta_value']); ?>
            <li class="ppress-note ppress-note-system ">
                <div class="ppress-note-content">
                    <p><?= $explode[1] ?></p>
                </div>
                <p class="ppress-note-meta">
                    <abbr class="ppress-note-date"><?= $explode[0] ?></abbr>
                    <a data-note-id="<?= $order_note['meta_id'] ?>" href="#" class="ppress-delete-note" role="button"><?php esc_html_e('Delete', 'wp-user-avatar') ?></a>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
</div>