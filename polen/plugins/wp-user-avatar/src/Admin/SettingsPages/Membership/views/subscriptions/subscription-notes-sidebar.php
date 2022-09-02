<?php /** @var array $sub_notes */ ?>
<div class="ppress-subscription-notes-wrap">
    <ul class="ppress-notes">
        <?php foreach ($sub_notes as $sub_note) : $explode = explode('|', $sub_note); ?>
            <li class="ppress-note ppress-note-system ">
                <div class="ppress-note-content">
                    <p><?= $explode[1] ?></p>
                </div>
                <p class="ppress-note-meta">
                    <abbr class="ppress-note-date"><?= $explode[0] ?></abbr>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
</div>