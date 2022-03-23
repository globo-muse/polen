<label id="<?php echo $id; ?>" class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea mdc-text-field--no-label">
  <span class="mdc-notched-outline">
    <span class="mdc-notched-outline__leading"></span>
    <span class="mdc-notched-outline__notch">
      <?php if ($label) : ?>
        <span class="mdc-floating-label" id="label-<?php echo $id; ?>"><?php echo $label; ?></span>
      <?php endif; ?>
    </span>
    <span class="mdc-notched-outline__trailing"></span>
  </span>
  <textarea class="mdc-text-field__input" rows="6" cols="40" aria-labelledby="label-<?php echo $id; ?>" name="<?php echo $name; ?>" <?php echo $required ? " required" : ""; ?> <?php foreach ($params as $key => $value) {
                                                                                                                                                                                  echo " {$key}='{$value}'";
                                                                                                                                                                                } ?>></textarea>
</label>
