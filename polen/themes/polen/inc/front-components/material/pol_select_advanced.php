<div id="<?php echo $name; ?>" class="row select-advanced" <?php foreach ($params as $key => $value) {
                                                              echo " {$key}='{$value}'";
                                                            } ?>>
  <?php foreach ($items as $item) : ?>
    <div class="col-6 col-lg-4">
      <label class="item<?php echo $item["checked"] ? ' -checked' : ''; ?><?php echo $item["disabled"] ? ' -disabled' : ''; ?>">
        <input type="radio" name="<?php echo $name; ?>" value="<?php echo $item['value']; ?>" <?php echo $item["checked"] ? " checked" : ""; ?><?php echo $item["disabled"] ? " disabled" : ""; ?> />
        <?php if ($item["icon"]) : ?>
          <figure class="icon">
            <img src="<?php echo $item["icon"]; ?>" alt="">
          </figure>
        <?php endif; ?>
        <span><?php echo $item["title"]; ?></span>
      </label>
    </div>
  <?php endforeach; ?>
</div>
<?php /*
<script>
  const component = document.querySelector("#<?php echo $name; ?>");
  const radio = document.querySelectorAll("#<?php echo $name; ?> input[name=<?php echo $name; ?>]");

  function removeChecked() {
    const items = document.querySelectorAll("#<?php echo $name; ?> .item");
    [...items].map(item => item.classList.remove("-checked"));
  }
  [...radio].map(item => {
    item.addEventListener("click", function(e) {
      removeChecked();
      this.parentNode.classList.add("-checked");
      component.dispatchEvent(new CustomEvent("polselectchange", {
        detail: e.target.value
      }));
    });
  });
</script>
*/
