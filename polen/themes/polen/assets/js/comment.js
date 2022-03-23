Vue.component("pol-stars", {
	template: `
	<div class="col-md-12 d-flex justify-content-center box-stars">
		<span class="icon-star-item" v-for="star in stars" v-bind:class="{active: star.key <= rate}" v-on:click="handle(star.key)">
			<i class="icon icon-star"></i>
			<i class="icon icon-star-fill" style="color: #FFCF34;"></i>
		</span>
		<input type="hidden" name="rate" id="rate" v-model="rate" />
	</div>
	`,
	props: ["rate", "handle"],
	data: function () {
		return {
			stars: [
				{ id: "star-1", key: 1 },
				{ id: "star-2", key: 2 },
				{ id: "star-3", key: 3 },
				{ id: "star-4", key: 4 },
				{ id: "star-5", key: 5 },
			],
		};
	},
});

const commentbox = new Vue({
	el: "#comment-box",
	data: {
		rate: 5,
		comment: "",
	},
	methods: {
		changeRate: function (e) {
			this.rate = e;
		},
		sendComment: function (e) {
			e.preventDefault();
			polSpinner();
			jQuery
				.post(
					polenObj.ajax_url,
					jQuery("#form-comment").serialize(),
					function (result) {
						if (result.success) {
							setSessionMessage(
								CONSTANTS.SUCCESS,
								"Vídeo avaliado com sucesso!",
								"Seu comentário poderá aparecer na página do Ídolo"
							);
							window.location.href = "/my-account/orders";
						} else {
							polSpinner("hidden");
							polError(result.data);
						}
					}
				)
				.fail(function (e) {
					polSpinner("hidden");
					if(e.responseJSON) {
						polError(e.responseJSON.data);
					} else {
						polError(e.statusText);
					}
				});
		},
	},
});
