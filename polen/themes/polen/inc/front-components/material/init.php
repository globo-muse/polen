<script>
  function polMaterialComponents() {
    const inputs    = document.querySelectorAll(".mdc-text-field");
    const buttons   = document.querySelectorAll(".mdc-button");
    const selects   = document.querySelectorAll('.mdc-select');

    [...inputs].map(el => mdc.textField.MDCTextField.attachTo(el));
    [...buttons].map(el => mdc.ripple.MDCRipple.attachTo(el));

    [...selects].map(el => {
      const select = mdc.select.MDCSelect.attachTo(el);
      if (el.classList.contains("required")) {
        select.required = true;
      }
      return select;
    });
  }
  docReady(function() {
    polMaterialComponents();
  });
</script>
