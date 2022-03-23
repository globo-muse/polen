let form = document.querySelector("#form-video-upload");
let file_input = document.querySelector("#file-video");
let progress = document.querySelector("#progress");
let content_info = document.getElementById("content-info");
let content_upload = document.getElementById("content-upload");
let progress_value = document.getElementById("progress-value");
let video_input = document.getElementById("file-video");
let video_name = document.getElementById("video-file-name");
let video_rec_click = document.querySelectorAll(".video-rec");
let response;
let invite_hash_input = document.getElementById("tribute_invite_hash");
let invite_id_input = document.getElementById("tribute_invite_id");
let tribute_hash_input = document.getElementById("tribute_hash");

const duracaoMinima = 10; //segundos
const duracaoMaxima = 65; //segundos

window.URL = window.URL || window.webkitURL;
let videoDuration;

window.onload = () => {
	if (!form) {
		return;
	}
	form.onsubmit = function (evt) {
		if (file_input.files.length == 0) {
			evt.preventDefault();
			return false;
		}
		if (!videoIsOk()) {
			evt.preventDefault();
			polError(
				`A duração do video deve ficar entre ${duracaoMinima} e ${
					duracaoMaxima - 5
				} segundos.`
			);
			return false;
		}
		console.log("Iniciando upload");
		content_info.classList.remove("show");
		content_upload.classList.add("show");
		document.querySelector("#video-rec-again").classList.remove("show");
		document.querySelector("#video-send").classList.remove("show");
		let upload_video = {};
		upload_video.file_size    = file_input.files[0].size.toString();
		upload_video.invite_hash  = invite_hash_input.value;
		upload_video.invite_id    = invite_id_input.value;
		upload_video.tribute_hash = tribute_hash_input.value;

		jQuery
			.post(
				polenObj.ajax_url + "?action=tribute_create_vimeo_slot",
				upload_video,
				(data, textStatus, jqXHR) => {
					if (jqXHR.status == 200) {
						let file = file_input.files[0];
						console.log(data.data.body.upload.upload_link);
						let upload = new tus.Upload(file, {
							uploadUrl: data.data.body.upload.upload_link,
							onError: errorHandler,
							onProgress: progressFunction,
							onSuccess: completeHandler,
						});
						upload.start();
					} else {
						console.log("deu erro");
					}
				}
			)
			.fail(errorHandler);
		evt.preventDefault();
		return false;
	};

	video_rec_click.forEach(function (item) {
		item.addEventListener("click", function (e) {
			e.preventDefault();
			video_input.click();
		});
	});

	video_input.addEventListener("change", function (e) {
		setFileInfo();
		changeIcon();
		showFileName();
		document.querySelector("#video-rec").classList.remove("show");
		document.querySelector("#video-rec-again").classList.add("show");
		document.querySelector("#video-send").classList.add("show");
	});
};

function showFileName() {
	document.getElementById("info").innerText = file_input.files[0].name;
}

function setFileInfo() {
	var files = file_input.files;
	var video = document.createElement("video");
	video.preload = "metadata";

	video.onloadedmetadata = function () {
		window.URL.revokeObjectURL(video.src);
		videoDuration = video.duration;
		// console.log(videoIsOk() ? "Duração Ok" : "Duração Errada");
	};

	video.src = URL.createObjectURL(files[0]);
}

function videoIsOk() {
	return true; //videoDuration > duracaoMinima && videoDuration < duracaoMaxima;
}

let completeHandler = () => {
	// polSpinner();
	// content_upload.innerHTML =
	// 	'<p class="my-4"><strong id="progress-value">Enviado</strong></p>';
	// let obj_complete_order = {
	// 	action: "order_status_completed",
	// 	order: upload_video.order_id,
	// };
	// jQuery
		// .post(polenObj.ajax_url, obj_complete_order, () => {
			// window.location.href =
			// 	polenObj.base_url +
			// 	"/tributes/f37ec6a00c382e273adb39e7c22ddbaa/invite/a459252415bc3427f31c962f9e707b0f/?FOI_E_FOI_BOM";
		// })
		// .fail(errorHandler)
		// .complete(function () {
			// polSpinner(CONSTANTS.HIDDEN);
		// });
		window.location.href = "./sucesso";
		changeText();
		changeIcon(true);
};

let errorHandler = (data, textStatus, jqXHR) => {
	console.log(data);
	setSessionMessage(CONSTANTS.ERROR, null, "Erro no envio do arquivo, tente novamente");
	document.location.reload();
};

function progressFunction(loaded, total) {
	progress_value.innerText = `Enviando vídeo ${Math.floor(
		(loaded / total) * 100
	)}%`;
}

function changeText() {
	document.getElementById("video-message").innerText = "Vídeo enviado com sucesso";
}

function changeIcon(final) {
	document.querySelector(".image.wait").classList.remove("show");
	document.querySelector("#content-upload").classList.remove("show");
	document.querySelector(".image.complete").classList.add("show");
	document.querySelector(".content-info").classList.add("show");
	if(final) {
		document.querySelector(".final-button").classList.add("show");
	}
}
