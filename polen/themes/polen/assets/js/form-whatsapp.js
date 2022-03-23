const phone = document.querySelector("#phone_cache").value;

const form_whatsapp = new Vue({
	el: "#add-whatsapp",
	data: {
		phone: phone,
		savedPhone: phone,
		edit: phone ? false : true,
	},
	methods: {
		handleChange: function (e) {
			this.phone = mtel(e.target.value);
		},
		handleEdit: function () {
			this.edit = true;
		},
		handleSubmit: function () {
			let _this = this;
			polAjaxForm(
				"#form-add-whatsapp",
				function (e) {
					_this.edit = false;
					_this.savedPhone = _this.phone;
					polMessages.message(
						"Enviado!",
						"Seu número foi adicionado com sucesso"
					);
				},
				function (e) {
					polMessages.error(e);
				}
			);
		},
	},
});
