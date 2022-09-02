<div class="widget" data-field-id="{{data.field_key}}">
    <div class="widget-top">
        <div class="widget-title-action">
            <button type="button" class="widget-action">
                <span class="toggle-indicator" aria-hidden="true"></span>
            </button>
        </div>
        <div class="widget-title ui-sortable-handle"><h3>{{data.label}}</h3></div>
    </div>
    <div class="widget-inside">
        <div class="widget-content">
            <p>
                <label for="{{data.fieldGroup}}fieldLabel"><?php esc_html_e('Field Label', 'wp-user-avatar') ?>:</label>
                <input name="{{data.fieldGroup}}[{{data.field_key}}][label]" id="{{data.fieldGroup}}fieldLabel" type="text" value="{{data.label}}">
            </p>
            <p>
                <label for="{{data.fieldGroup}}fieldWidth"><?php esc_html_e('Field Width', 'wp-user-avatar') ?>:</label>
                <select id="{{data.fieldGroup}}fieldWidth" name="{{data.fieldGroup}}[{{data.field_key}}][width]">
                    <option value="full"<?php echo "<# if(data.width == 'full') { #> selected <# } #>"; ?>><?php esc_html_e('Full', 'wp-user-avatar') ?></option>
                    <option value="half"<?php echo "<# if(data.width == 'half') { #> selected <# } #>"; ?>><?php esc_html_e('Half', 'wp-user-avatar') ?></option>
                    <option value="one-third"<?php echo "<# if(data.width == 'one-third') { #> selected <# } #>"; ?>><?php esc_html_e('One Third', 'wp-user-avatar') ?></option>
                </select>
            </p>
            <p>
                <label for="{{data.fieldGroup}}fieldWidth"><?php esc_html_e('Required Field', 'wp-user-avatar') ?>:</label>
                <select id="{{data.fieldGroup}}fieldWidth" name="{{data.fieldGroup}}[{{data.field_key}}][required]">
                    <option value="true"<?php echo "<# if(data.required == 'true') { #> selected <# } #>"; ?>><?php esc_html_e('Yes', 'wp-user-avatar') ?></option>
                    <option value="false"<?php echo "<# if(data.required == 'false') { #> selected <# } #>"; ?>><?php esc_html_e('No', 'wp-user-avatar') ?></option>
                </select>
            </p>

            <# if(_.indexOf(ppress_logged_in_hidden_fields, data.field_key) === -1) { #>
            <p>
                <label for="{{data.fieldGroup}}fieldLoggedInHide"><?php esc_html_e('Hide for Logged-in Users', 'wp-user-avatar') ?>:</label>
                <select id="{{data.fieldGroup}}fieldLoggedInHide" name="{{data.fieldGroup}}[{{data.field_key}}][logged_in_hide]">
                    <option value="true"<?php echo "<# if(data.logged_in_hide == 'true') { #> selected <# } #>"; ?>><?php esc_html_e('Yes', 'wp-user-avatar') ?></option>
                    <option value="false"<?php echo "<# if(data.logged_in_hide == 'false') { #> selected <# } #>"; ?>><?php esc_html_e('No', 'wp-user-avatar') ?></option>
                </select>
            </p>
            <# } #>

            <# if(data.deletable == 'true') { #>
            <div class="widget-control-actions">
                <div class="alignleft">
                    <button type="button" class="button-link button-link-delete widget-control-remove"><?php esc_html_e('Delete', 'wp-user-avatar') ?></button>
                </div>
                <br class="clear">
            </div>
            <# } #>
        </div>
    </div>
</div>