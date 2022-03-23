<div class="wrap">
    <div class="clear">
        <label for="url_media">
            <p>Url da matéria</p>
            <input type="text" id="url_media"
                   name="url_media"
                   value="<?php echo esc_attr(get_post_meta($post->ID, 'url_media', true)); ?>"
                   style="width: 100%">
        </label>
        <label for="date_media">
            Data de publicação da matéria
            <input type="text" id="date_media"
                   name="date_media"
                   value="<?php echo esc_attr(get_post_meta($post->ID, 'date_media', true)); ?>"
                   style="width: 100%">
        </label>
    </div>
</div>