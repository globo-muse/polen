const cartAdvanced = new Vue({
  el: "#cart-advanced",
  data: {
    activeItem: 1,
    offered_by: cart_items.offered_by || "",
    email_to_video: cart_items.email_to_video || "",
    video_to: cart_items.video_to || "",
    instructions_to_video: cart_items.instructions_to_video || "",
    phone: cart_items.phone || "",
    occasion: cart_items.video_category || "",
    submit: true,
  },
  methods: {
    nextStep: function (num) {
      if(!formCheckValidationsPartial()) {
        return;
      }
      this.submit = false;
      this.activeItem = num;
    },
    handlePhoneChange: function (e) {
      this.phone = mtel(e.target.value);
    },
    handleInstructionsChange: function(e) {
      this.instructions_to_video = e.target.value.trim();
    },
    step1Disabled: function() {
      return !this.offered_by || !polMailValidate(this.email_to_video)
    },
    video_toHandle: function(e) {
      this.video_to = e.detail;
    },
    occasionHandle: function(e){
      this.occasion = e.detail;
    },
    isComplete: function() {
      return this.offered_by != ""
      && this.email_to_video != ""
      && this.video_to != ""
      && this.instructions_to_video != ""
      && this.activeItem == 2
      && this.occasion;
    },
    isForOther: function() {
      return this.video_to == "other_one";
    },
    isInstructionsOk: function() {
      return checkWordsInInstructions(this.instructions_to_video);
    },
    handleSubmit: function(e) {
      if(!this.submit) {
        e.preventDefault();
      }
      if(this.occasion === "") {
        e.preventDefault();
        polMessages.error("É Necessário escolher uma ocasião para o vídeo");
      }
      if(this.instructions_to_video.length < 10) {
        e.preventDefault();
        polMessages.error("Preencha corretamente o campo de instruções. É através dele que o Ídolo saberá o que gravar no video.");
      }
    }
  },
});

function formCheckValidationsPartial() {
  const offered_by = document.querySelector("input[name=offered_by]");
  const email_to_video = document.querySelector("input[name=email_to_video]");
  const phone = document.querySelector("input[name=phone]");
  return offered_by.checkValidity() && email_to_video.checkValidity() && phone.checkValidity();
}

function formCheckValidations() {
  let valid = true;
  const form_items = document.querySelector(form).elements;
  [].map.call(form_items, e => {
    if (!e.checkValidity()) {
      return valid = e.checkValidity();
    }
    return valid;
  });
  return valid;
}

// Checando se existem palavras proibidas na instrução do video
function checkWordsInInstructions(instruction) {
  let forbiddenWords = [
    "música",
    "musica",
    "canta",
    "cantar",
    "toca",
    "tocar",
    "palinha",
    "palhinha",
    '"',
  ];
  if (forbiddenWords.some((v) => instruction.toLowerCase().includes(v))) {
    return false;
  }
  return true;
}

function verify_checkbox_selected_to_hidde_or_show_fields() {
  return;
  let value_checked = "";
  if (document.querySelectorAll('input[name="video_to"')[1].checked) {
    value_checked = document.querySelectorAll('input[name="video_to"')[1].value;
  } else {
    value_checked = document.querySelectorAll('input[name="video_to"')[0].value;
  }

  if (value_checked == "to_myself") {
    jQuery(".video-to-info").hide();
    jQuery("input[name=offered_by]").prop("required", false);
  } else {
    jQuery(".video-to-info").show();
    jQuery("input[name=offered_by]").prop("required", true);
  }
}

(function ($) {
  // $(document).on(
  //   "click",
  //   ".cart-video-to",
  //   verify_checkbox_selected_to_hidde_or_show_fields
  // );

  $(document).ready(function () {
    verify_checkbox_selected_to_hidde_or_show_fields();
    $("input, textarea").on("blur change paste click", function () {
      var cart_id = $("input[name=cart-item-key]").val();
      var item_name = $(this).attr("name");
      var allowed_item = [
        "offered_by",
        "video_to",
        "name_to_video",
        "email_to_video",
        "video_category",
        "instructions_to_video",
        "allow_video_on_page",
        "phone"
      ];
      if ($.inArray(item_name, allowed_item) !== -1) {
        let item_value;

        if (item_name == "allow_video_on_page") {
          if ($("#cart_" + item_name + "_" + cart_id).is(":checked")) {
            item_value = "on";
          } else {
            item_value = "off";
          }
        } else {
          item_value = $(this).val();
        }
        $.ajax({
          type: "POST",
          url: polenObj.ajax_url,
          data: {
            action: "polen_update_cart_item",
            security: $("#woocommerce-cart-nonce").val(),
            polen_data_name: item_name,
            polen_data_value: item_value,
            cart_id: cart_id,
          },
          success: function (response) {
            //	$('.cart_totals').unblock();
            //$( '.woocommerce-cart-form' ).find( ':input[name="update_cart"]' ).prop( 'disabled', false ).attr( 'aria-disabled', false );
          },
        });
      }
    });

    /*

    // Função para assistir as mudanças nas instruções do video
    // e exibir o aviso para não pedir músicas/cantar
    $("textarea[name='instructions_to_video']").on("change keyup", function () {
      var cart_id = $(this).data("cart-id");
      let item_value = $("#cart_instructions_to_video" + "_" + cart_id).val();
      checkWordsInInstructions(item_value);
    });

    $(".select-ocasion").on("change", function () {
      return;
      var item_value = $(this).val();

      if (item_value) {
        $(".video-instruction-refresh").click();
      }
    });

    function messagesPreloader(active) {
      var loader = document.getElementById("reload");
      loader.classList[active ? "add" : "remove"]("spin");
    }

    $(".video-instruction-refresh").on("click", function () {
      return;
      var category_item = $('select[name="video_category"]');
      var category_name = category_item.val();
      var cart_id = category_item.attr("data-cart-id");

      if (category_name) {
        messagesPreloader(true);
        $.ajax({
          type: "POST",
          url: polenObj.ajax_url,
          data: {
            action: "get_occasion_description",
            occasion_type: category_name,
            refresh: 1,
          },
          success: function (response) {
            let obj = $.parseJSON(response);
            //console.log(obj['response'][0].description);

            if (obj) {
              if (obj["response"][0].description) {
                $("#cart_instructions_to_video_" + cart_id).html(
                  obj["response"][0].description
                );
              }
            }
          },
          complete: function () {
            messagesPreloader(false);
          },
        });
      }
    });

    // Tratando a div que funciona como Placeholder no textarea
    const ta = document.querySelector("textarea[name=instructions_to_video]");
    const pp = document.querySelector(".placeholder");

    ta.addEventListener("input", () => {
      pp.classList.toggle("d-none", ta.value !== "");
    });
    */
  });
})(jQuery);

