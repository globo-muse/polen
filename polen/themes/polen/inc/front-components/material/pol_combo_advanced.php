<div id="<?php echo $id; ?>" class="combo-advanced">
  <?php foreach ($items as $item) : ?>
    <label class="item<?php echo $item["checked"] ? ' -checked' : ''; ?><?php echo $item["disabled"] ? ' -disabled' : ''; ?>">
      <div class="input">
        <input type="radio" name="<?php echo $name; ?>" id="<?php echo $item['id']; ?>" class="custom-check custom-check--small" value="<?php echo $item['value']; ?>"<?php echo $item["checked"] ? " checked" : ""; ?><?php echo $item["disabled"] ? " disabled" : ""; ?> />
      </div>
      <div class="content">
        <header class="header">
          <span class="title"><?php echo $item['title']; ?></span>
          <span class="price"><?php echo $item['price']; ?></span>
        </header>
        <footer>
          <p class="text"><?php echo $item['text']; ?></p>
        </footer>
      </div>
    </label>
  <?php endforeach; ?>
</div>
<script>
  const component = document.querySelector("#<?php echo $id; ?>");
  const radio = document.querySelectorAll("input[name=<?php echo $name; ?>]");
  function removeChecked() {
    const items = document.querySelectorAll(".combo-advanced .item");
    [...items].map(item => item.classList.remove("-checked"));
  }
  [...radio].map(item => {
    item.addEventListener("click", function(e) {
      removeChecked();
      this.parentNode.parentNode.classList.add("-checked");
      component.dispatchEvent(new CustomEvent("polcombochange", {
        detail: e.target.value
      }));
    });
  });
</script>
