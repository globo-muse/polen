<label id="<?php echo $id; ?>" class="mdc-text-field mdc-text-field--outlined<?php echo $classes ? " " . $classes : ""; ?>">
  <span class="mdc-notched-outline">
    <span class="mdc-notched-outline__leading"></span>
    <span class="mdc-notched-outline__notch">
      <span class="mdc-floating-label" id="label-<?php echo $id; ?>"><?php echo $label; ?></span>
    </span>
    <span class="mdc-notched-outline__trailing"></span>
  </span>
  <input pattern=".{3,}" type="<?php echo $type; ?>" name="<?php echo $name; ?>" class="mdc-text-field__input" aria-labelledby="label-<?php echo $id; ?>" <?php if (isset($helper)) : ?> aria-controls="helper-<?php echo $id; ?>" aria-describedby="helper-<?php echo $id; ?>" <?php endif; ?> <?php echo $required ? " required" : ""; ?><?php foreach ($params as $key => $value) {
                                                                                                                                                                                                                                                                                                                        echo " {$key}='{$value}'";
                                                                                                                                                                                                                                                                                                                      } ?> />
</label>
