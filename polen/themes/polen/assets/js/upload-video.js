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

const duracaoMinima = 10; //segundos
const duracaoMaxima = 65; //segundos

window.URL = window.URL || window.webkitURL;
let videoDuration;
let vimeo_id;

window.onload = () => {
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

		upload_video.file_size = file_input.files[0].size.toString();
		jQuery
			.post(
				polenObj.ajax_url + "?action=make_video_slot_vimeo",
				upload_video,
				(data, textStatus, jqXHR) => {
					if (jqXHR.status == 200) {
						let file = file_input.files[0];
						vimeo_id = data.data.body.uri;
						let upload = new tus.Upload(file, {
							uploadUrl: data.data.body.upload.upload_link,
							onError: errorHandler,
							onProgress: progressFunction,
							onSuccess: completeHandler,
						});
						upload.start();
					} else {
						console.log("deu error");
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
	polSpinner();
	content_upload.innerHTML =
		'<p class="my-4"><strong id="progress-value">Enviado</strong></p>';
	let obj_complete_order = {
		action: "order_status_completed",
		order: upload_video.order_id,
		vimeo_id,
	};
	jQuery
		.post(polenObj.ajax_url, obj_complete_order, (data, textStatus, jqXHR) => {
			window.location.href =
				polenObj.base_url +
				"/my-account/success-upload/?order_id=" +
				upload_video.order_id;
		})
		.fail(errorHandler)
		.complete(function () {
			polSpinner(CONSTANTS.HIDDEN);
		});
};
let errorHandler = (data, textStatus, jqXHR) => {
	alert("Erro no envio do vídeo, tente novamente.");
	document.location.reload();
};

function progressFunction(loaded, total) {
	progress_value.innerText = `Enviando vídeo ${Math.floor(
		(loaded / total) * 100
	)}%`;
}

function changeText() {
	document.getElementById("info").innerText = "Vídeo gravado com sucesso";
}

function changeIcon() {
	document.querySelector(".image.wait").classList.remove("show");
	document.querySelector(".image.complete").classList.add("show");
}
