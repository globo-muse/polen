const formName = "form#talent-suggestion";
const form = document.querySelector(formName);

form.addEventListener("submit", function (evt) {
	evt.preventDefault();
	polAjaxForm(
		formName,
		function () {
			polMessages.message(
				"Sugestão enviada",
				"Obrigado por nos enviar sua sugestão"
			);
		},
		function (error) {
			polMessages.error(error);
		}
	);
});
