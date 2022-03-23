const btn_play = document.getElementById("btn-play");
const video_tribute = document.getElementById("tribute-home-video");
const formCreateName = "form#form-create-tribute";
const formCreate = document.querySelector(formCreateName);
const slug = document.getElementById("slug");
const nameInput = document.getElementById("name_honored");
const formsResend = document.querySelectorAll(".resend-email");

const SESSION_OBJ_INVITES = "polen_tributes_invites";

function playVideo(evt) {
	if (video_tribute.paused) {
		btn_play.classList.add("hidden");
		video_tribute.play();
	} else {
		btn_play.classList.remove("hidden");
		video_tribute.pause();
	}
}

function slugValidate(valid, message) {
	const slug_message = document.getElementById("slug-message");
	slug.classList.remove("error");
	slug_message.classList.remove("error");
	slug_message.innerText = "";
	if (!valid) {
		slug.classList.add("error");
		slug_message.classList.add("error");
	}
	slug_message.innerText = message;
}

function getSlug() {
	if (slug.value) {
		return;
	}
	slug.value = polSlugfy(nameInput.value);
	checkSlug();
}

function checkSlug() {
	polSpinner(CONSTANTS.SHOW, ".slug-wrap");
	slug.value = polSlugfy(slug.value);
	jQuery.ajax({
		type: "POST",
		url: polenObj.ajax_url,
		data: {
			action: "check_tribute_slug_exists",
			slug: slug.value,
		},
		success: function (response) {
			if (response.success) {
				slugValidate(true, response.data);
			} else {
				slugValidate(false, response.data);
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			slugValidate(false, jqXHR.responseJSON.data);
		},
		complete: function () {
			polSpinner(CONSTANTS.HIDDEN);
		},
	});
}

function createTribute(evt) {
	evt.preventDefault();
	if (slug.classList.contains("error")) {
		polError("É preciso uma URL válida para seu Colab");
		return;
	}
	if (document.getElementById("deadline").classList.contains("error")) {
		polError("Data inválida. A data não pode ser anterior a de hoje.");
		return;
	}
	polSpinner();
	jQuery
		.post(
			polenObj.ajax_url,
			jQuery(formCreateName).serialize(),
			function (result) {
				if (result.success) {
					setSessionMessage(
						CONSTANTS.SUCCESS,
						"Colab criado",
						"Agora convide seus amigos para essa homenagem"
					);
					window.location.href = result.data.url_redirect;
				} else {
					polError(result.data);
				}
			}
		)
		.fail(function (e) {
			polSpinner(CONSTANTS.HIDDEN);
			if (e.responseJSON) {
				polError(e.responseJSON.data);
			} else {
				polError(e.statusText);
			}
		});
}

function reSendEmail(evt) {
	evt.preventDefault();
	polAjaxForm(
		`form#${evt.target.id}`,
		function () {
			polMessage("Enviado", "e-mail foi enviado com sucesso");
		},
		function (res) {
			polError(res);
		}
	);
}

if (btn_play) {
	btn_play.addEventListener("click", playVideo);
}

if (slug) {
	slug.addEventListener("focusout", checkSlug);
	nameInput.addEventListener("focusout", getSlug);
}

if (formCreate) {
	formCreate.addEventListener("submit", createTribute);
}

if (formsResend.length) {
	formsResend.forEach(function (item) {
		item.addEventListener("submit", reSendEmail);
	});
}

if (document.getElementById("invite-friends")) {
	function saveToDisk(obj) {
		sessionStorage.setItem(SESSION_OBJ_INVITES, JSON.stringify(obj));
	}

	function getToDisk() {
		const st = sessionStorage.getItem(SESSION_OBJ_INVITES);
		return st ? JSON.parse(st) : [];
	}

	function submitFriends(_this) {
		const formName = "form#friends-form";
		polAjaxForm(
			formName,
			function () {
				sessionStorage.removeItem(SESSION_OBJ_INVITES);
				setSessionMessage(
					CONSTANTS.SUCCESS,
					"Amigos adicionados com sucesso",
					"Seus amigos receberão as instruções por e-mail"
				);
				_this.friends = [];
				window.location.href = "./detalhes";
			},
			function (e) {
				polError(e);
			}
		);
	}

	const inviteFriends = new Vue({
		el: "#invite-friends",
		data: {
			name: "",
			email: "",
			friends: getToDisk(),
		},
		methods: {
			resetAddFriend: function () {
				this.name = this.email = "";
			},
			updateDisk: function () {
				saveToDisk(this.friends);
			},
			addFriend: function () {
				this.friends.push({ name: this.name, email: this.email });
				this.resetAddFriend();
				this.updateDisk();
				document.getElementById("add-name").focus();
			},
			removeFriend: function (email) {
				this.friends = this.friends.filter(
					(friend) => friend.email != email
				);
				this.updateDisk();
			},
			onChangeEmail: function (evt) {
				if (evt.key == "Enter") {
					this.addFriend();
				}
			},
			sendFriends: function () {
				submitFriends(this);
			},
		},
	});
}

function formatDate(date) {
	return (
		date.getDate() + "/" + (date.getMonth() + 1) + "/" + date.getFullYear()
	);
}

function daysOfMonth(month, year) {
	var date = new Date(year, parseInt(month) + 1, 0);
	return date.getDate();
}

if (document.getElementById("deadline-wrapp")) {
	const formValidate = new Vue({
		el: "#deadline-wrapp",
		data: {
			date: formatDate(new Date()),
			day: new Date().getDate(),
			month: new Date().getMonth(),
			year: new Date().getFullYear(),
			days: daysOfMonth(new Date().getMonth(), new Date().getFullYear()),
			months: [
				{ index: 0, name: "Janeiro" },
				{ index: 1, name: "Fevereiro" },
				{ index: 2, name: "Março" },
				{ index: 3, name: "Abril" },
				{ index: 4, name: "Maio" },
				{ index: 5, name: "Junho" },
				{ index: 6, name: "Julho" },
				{ index: 7, name: "Agosto" },
				{ index: 8, name: "Setembro" },
				{ index: 9, name: "Outubro" },
				{ index: 10, name: "Novembro" },
				{ index: 11, name: "Dezembro" },
			],
			years: [new Date().getFullYear(), new Date().getFullYear() + 1],
		},
		methods: {
			updateDate: function (evt) {
				const obj = evt.target;
				if (obj.id === "date_day") {
					this.day = obj.value;
				} else {
					if (obj.id === "date_year") {
						this.year = obj.value;
					} else if (obj.id === "date_month") {
						this.month = obj.value;
					}
					this.days = daysOfMonth(this.month, this.year);
				}
				this.date = formatDate(
					new Date(this.year, this.month, this.day)
				);
				this.checkDate();
			},
			checkDate: function () {
				const el = document.getElementById("deadline");
				el.classList.remove("error");

				const d = new Date(
					new Date().getFullYear(),
					new Date().getMonth(),
					new Date().getDate()
				);

				const df = this.date.split("/");
				const d2 = new Date(df[2], df[1] - 1, df[0]);

				const t1 = d.getTime();
				const t2 = d2.getTime();

				if (!t2 || t2 < t1) {
					el.classList.add("error");
				}
			},
		},
	});
}

if (document.querySelectorAll(".tribute_delete_invite").length) {
	jQuery(".tribute_delete_invite").click(function (evt) {
		evt.preventDefault();
		polSpinner();
		let invite_hash = jQuery(evt.currentTarget).attr("data_invite");
		let tribute_hash = jQuery(evt.currentTarget).attr("data_tribute");
		let action = "tribute_delete_invite";
		let security = jQuery("#wpnonce").val();
		jQuery
			.post(
				polenObj.ajax_url,
				{ action, invite_hash, tribute_hash, security },
				function (data, status, b) {
					if (data.success) {
						setSessionMessage(
							CONSTANTS.SUCCESS,
							"Sucesso",
							data.data
						);
						document.location.reload();
					} else {
						polError(data.data);
					}
				}
			)
			.fail(function (jqxhr, settings, ex) {
				polSpinner(CONSTANTS.HIDDEN);
				polError("failed, " + ex);
			});
	});
}
