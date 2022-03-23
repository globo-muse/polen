if (!polenObj.developer) {
	console = {
		debug: function () {},
		error: function () {},
		info: function () {},
		log: function () {},
		warn: function () {},
	};
}
function docReady(fn) {
	if (
		document.readyState === "complete" ||
		document.readyState === "interactive"
	) {
		setTimeout(fn, 1);
	} else {
		document.addEventListener("DOMContentLoaded", fn);
	}
}
