<?php
defined('ABSPATH') || die;
?>

<?php

$title = get_post_meta( $post->ID, '_seo_title', true );
$description = get_post_meta( $post->ID, '_seo_description', true );

?>

<div class="wrap">
    <p class="form-field">
        <label for="meta-seo-title">Título</label><br />
        <input type="text" name="meta-seo-title" id="meta-seo-title" value="<?php echo $title; ?>" />
    </p>
    <p class="form-field">
        <label for="meta-seo-description">Descrição</label><br />
        <textarea name="meta-seo-description" id="meta-seo-description" rows="8" cols="50"><?php echo $description; ?></textarea>
    </p>
</div>