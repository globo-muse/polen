const formName = "form#landpage-form";
const form_landpage = document.querySelector(formName);

form_landpage.addEventListener("submit", function (evt) {
	evt.preventDefault();
	polAjaxForm(
		formName,
		function () {
			polMessages.message(
				"Seu e-mail foi adicionado a lista",
				"Aguarde nossas novidades!"
			);
		},
		function (error) {
			polMessages.error(error);
		}
	);
	// Zapier request
	polRequestZapier(
		formName
	);
});
