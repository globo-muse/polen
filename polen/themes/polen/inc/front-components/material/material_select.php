<div class="mdc-select mdc-select--outlined<?php echo $required ? " required" : ""; ?><?php echo $classes ? " " . $classes : ""; ?>">
  <input id="<?php echo $id; ?>" type="hidden" name="<?php echo $name; ?>"<?php foreach ($params as $key => $value) {
                                                                              echo " {$key}='{$value}'";
                                                                            } ?> />
  <div class="mdc-select__anchor" aria-labelledby="outlined-select-label">
    <span class="mdc-notched-outline">
      <span class="mdc-notched-outline__leading"></span>
      <span class="mdc-notched-outline__notch">
        <span id="outlined-select-label" class="mdc-floating-label"><?php echo $label; ?></span>
      </span>
      <span class="mdc-notched-outline__trailing"></span>
    </span>
    <span class="mdc-select__selected-text-container">
      <span id="demo-selected-text" class="mdc-select__selected-text"></span>
    </span>
    <span class="mdc-select__dropdown-icon">
      <svg class="mdc-select__dropdown-icon-graphic" viewBox="7 10 10 5" focusable="false">
        <polygon class="mdc-select__dropdown-icon-inactive" stroke="none" fill-rule="evenodd" points="7 10 12 15 17 10">
        </polygon>
        <polygon class="mdc-select__dropdown-icon-active" stroke="none" fill-rule="evenodd" points="7 15 12 10 17 15">
        </polygon>
      </svg>
    </span>
  </div>

  <div class="mdc-select__menu mdc-menu mdc-menu-surface mdc-menu-surface--fullwidth">
    <ul class="mdc-list" role="listbox" aria-label="Food picker listbox">
      <?php foreach ($items as $key => $value) : ?>
        <li class="mdc-list-item" aria-selected="false" data-value="<?php echo $key; ?>" role="option">
          <span class="mdc-list-item__ripple"></span>
          <span class="mdc-list-item__text">
            <?php echo $value; ?>
          </span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
