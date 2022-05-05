<?php

use Polen\Includes\Module\Factory\Polen_Product_Module_Factory;

global $post;
$product = wc_get_product($post->ID);
$product_module = Polen_Product_Module_Factory::create_product_from_campaing($product);
$vimeo_videos = $product_module->get_vimeo_videos_page_details();
// var_dump($vimeo_videos);
?>
<div class="wrap">
    <div>
        <p class="form-field form-field-wide">
            <label for="vimeo_id_1">Video para a empresa</label>
            <input type="text" id="vimeo_id_1" name="vimeo_id[]" class="vimeo_id" data="1" value="<?= $vimeo_videos[0]['vimeo_id'] ?? ''; ?>" />
            <img src="" id="vimeo_img_1" height="50px" />
        </p>
    </div>
    <div>
        <p class="form-field form-field-wide">
            <label for="vimeo_id_2">Video para a empresa</label>
            <input type="text" id="vimeo_id_2" name="vimeo_id[]" class="vimeo_id" data="2" value="<?= $vimeo_videos[1]['vimeo_id'] ?? ''; ?>" />
            <img src="" id="vimeo_img_2" height="50px" />
        </p>
    </div>
    <div>
        <p class="form-field form-field-wide">
            <label for="vimeo_id_3">Video para a empresa</label>
            <input type="text" id="vimeo_id_3" name="vimeo_id[]" class="vimeo_id" data="3" value="<?= $vimeo_videos[2]['vimeo_id'] ?? ''; ?>" />
            <img src="" id="vimeo_img_3" height="50px" />
        </p>
    </div>
    <div>
        <p class="form-field form-field-wide">
            <label for="vimeo_id_4">Video para a empresa</label>
            <input type="text" id="vimeo_id_4" name="vimeo_id[]" class="vimeo_id" data="4" value="<?= $vimeo_videos[3]['vimeo_id'] ?? ''; ?>" />
            <img src="" id="vimeo_img_4" height="50px" />
        </p>
    </div>
</div>
<script>
jQuery(function(){
    jQuery('.vimeo_id').change(function(evt){
        let indexIten = jQuery(evt.currentTarget).attr('data');
        jQuery.post(WordfenceAdminVars.ajaxURL, {action:'polen_vimeo_info',vimeo_id:jQuery('#vimeo_id_' + indexIten).val()}, function(result){
            console.log(result);
            if(result.is_landscape == false) {
                alert('esse video não é landscape');
            }
            jQuery('#vimeo_img_' + indexIten).attr('src', result.thumb);
        });
    });
});
</script>