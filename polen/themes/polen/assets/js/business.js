const form = "#bus-form";
const url_success = document.getElementById("url-success").value;

const bus_form = new Vue({
  el: form,
  data: {
    phone: "",
  },
  methods: {
    handleChange: function (e) {
      // this.phone = mtel(e.target.value);
    },
    handleSubmit: function () {
      polAjaxForm(
        form,
        function (e) {
          window.location.href = url_success;
        },
        function (e) {
          polMessages.error(e);
        }
      );
    },
  },
});
