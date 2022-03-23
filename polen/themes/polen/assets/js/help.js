const form = "#help-form";

const bus_form = new Vue({
  el: form,
  data: {
    phone: ""
  },
  methods: {
    handleChange: function (e) {
      //this.phone = mtel(e.target.value);
    },
    handleEdit: function () {
      this.edit = true;
    },
    handleSubmit: function () {
      polAjaxForm(
        form,
        function (e) {
          polMessages.message(
						"Enviado!",
						"Sua mensagem foi enviada com sucesso!"
					);
        },
        function (e) {
          polMessages.error(e);
        }
      );
    },
  },
});

(function ($) {
  // Open collapse url
  const regexExp = /^#[^ !@#$%^&*(),.?":{}|<>]*$/gi;
  let currentURL = window.location.href;
  var hash = currentURL.substring(currentURL.indexOf('#'));
  if (regexExp.test(hash)) {
    $('.panel-button[aria-controls='+hash.substring(1)+']').removeClass("collapsed").attr("aria-expanded","true");
    $(hash).addClass('show');
  }
})(jQuery);
