"use strict";

function polen_onSubmit(token) {
	const formName = "form.register";
	polRequestZapier(
		formName
	);
	document.querySelector('form.register').submit();
}
