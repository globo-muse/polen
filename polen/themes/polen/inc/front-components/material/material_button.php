<button type="<?php echo $type; ?>" id="<?php echo $id; ?>" class="mdc-button mdc-button--raised<?php echo $classes ? " " . $classes : ""; ?>" <?php foreach ($params as $key => $value) {
                                                                                                                                                  echo " {$key}='{$value}'";
                                                                                                                                                } ?>>
  <span class="mdc-button__ripple"></span>
  <?php if($icon) : ?>
    <i class="material-icons mdc-button__icon icon icon-<?php echo $icon; ?>" aria-hidden="true"
      ></i
    >
  <?php endif; ?>
  <span class="mdc-button__touch"></span>
  <span class="mdc-button__label"><?php echo $title; ?></span>
</button>
