(function( $ ) {
	'use strict';

	function downloadZip(videos, tribute_id) {
		// Create file .zip
		const fileStream = streamSaver.createWriteStream(`colab-videos-${tribute_id}.zip`);

		const files = videos.values();

		// Add files to .zip
		const readableZipStream = new ZIP({
			pull (ctrl) {
				const it = files.next();
				if (it.done) {
					ctrl.close()
				} else {
					return fetch(it.value).then(res => {
						ctrl.enqueue({
							name: `video-${Math.random()}.mp4`,
							stream: () => res.body
						})
					})
				}
			}
		})

		if (window.WritableStream && readableZipStream.pipeTo) {
			return readableZipStream.pipeTo(fileStream).then();
		}

		const writer = fileStream.getWriter()
		const reader = readableZipStream.getReader()
		const pump = () => reader.read()
			.then(res => res.done ? writer.close() : writer.write(res.value).then(pump))

		pump()
	}

	$(function() {
		if( $( '.link-downloads-btn' ).length > 0 ) {
			$('.link-downloads-btn').click(function( evt ){
				evt.preventDefault();
				let tribute_id = jQuery( evt.currentTarget ).attr('data-tribute-id');
				let security = jQuery( evt.currentTarget ).attr('nonce');
				let url = jQuery( evt.currentTarget ).attr('href');
				jQuery.post(url,{tribute_id,security},function(data){
					let videos = data.data;
					if (videos.length > 0) {
						downloadZip(videos, tribute_id); // Baixar o .zip caso exista videos
					} else {
						// Exibir mensagem caso não exista vídeos
						alert("Sem vídeos para baixar! Talvez o processamento ainda não foi finalizado.");
					}
				});
			});
		}
	});

})( jQuery );
