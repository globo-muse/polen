const CONSTANTS = {
  MESSAGE_COOKIE: "message_cookie",
  SUCCESS: "success",
  ERROR: "error",
  SHOW: "show",
  HIDDEN: "hidden",
  MESSAGE_TIME: 10,
  THEME: "theme_mode",
};

var interval = setInterval;

function copyToClipboard(text) {
  var copyText = document.createElement("input");
  copyText.id = "share-input";
  copyText.style = "position: fixed; top: 500vh";
  document.body.appendChild(copyText);
  copyText.value = text;
  copyText.select();
  copyText.setSelectionRange(0, 99999); /* For mobile devices */

  document.execCommand("copy");
  document.body.removeChild(copyText);
  polMessage("Sucesso", "Link copiado para Área de transferência");
}

function docReady(fn) {
  if (
    document.readyState === "complete" ||
    document.readyState === "interactive"
  ) {
    setImediate(fn);
  } else {
    document.addEventListener("DOMContentLoaded", fn);
  }
}

function shareVideo(title, url) {
  if (!url) {
    url = window.location.href;
  }
  var shareData = {
    title: title,
    url: url,
  };
  if (navigator.share) {
    try {
      navigator
        .share(shareData)
        .then(() => {
          console.log("Sucesso!", "Link compartilhado com sucesso");
        })
        .catch(console.error);
    } catch (err) {
      polError("Error: " + err);
    }
  } else {
    copyToClipboard(shareData.url);
  }
}

const shareSocial = {
  network: {
    facebook: "https://www.facebook.com/share.php?u=",
    twitter: "https://twitter.com/intent/tweet?text=",
    whatsapp: "https://wa.me/?text=",
  },
  send: function (url, content) {
    if (!content) {
      content = window.location.href;
    }
    window.open(url + content);
  }
}

function toggleShowClass(child) {
  document.querySelector(child).classList.toggle('show');
}

function changeHash(hash) {
  window.location.hash = hash || "";
}

function setImediate(handle) {
  setTimeout(handle, 1);
}

function polMailValidate(value) {
  const mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
  if (value.match(mailformat)) {
    return true;
  }
  return false;
}

function polMessageKill(id) {
  clearInterval(interval);
  var el = document.getElementById(id);
  if (el) {
    el.classList.remove(CONSTANTS.SHOW);
    setImediate(function () {
      el.parentNode.removeChild(el);
    });
  }
}

function polMessageAutoKill(id) {
  interval = setInterval(function () {
    polMessageKill(id);
  }, CONSTANTS.MESSAGE_TIME * 1000);
}

function polSpinner(action, el) {
  if (action === CONSTANTS.HIDDEN) {
    polMessageKill("pol-fog");
  } else {
    polMessageKill("pol-fog");
    var container = null;
    var fog = document.createElement("div");
    fog.id = "pol-fog";
    fog.classList.add("fog");
    fog.innerHTML = `
			<div class="spinner">
				<div class="spinner-border text-primary" role="status">
					<span class="sr-only">Aguarde...</span>
				</div>
			</div>
		`;

    container = document.querySelector(el);
    if (container) {
      fog.classList.add("inner");
    } else {
      container = document.body;
    }
    container.appendChild(fog);
    setImediate(function () {
      fog.classList.add(CONSTANTS.SHOW);
    });
  }
}

const polMessages = {
  message: function (title, message) {
    polMessage(title, message);
  },
  error: function (message) {
    polError(message);
  },
  sessionMessage: function (type, title, message) {
    setSessionMessage(type, title, message);
  },
  toString: function () {
    return "message(title, message), error(message), sessionMessage(type, title, message)";
  }
};

function polMessage(title, message) {
  var id = "message-box";
  polMessageKill(id);

  var messageBox = document.createElement("div");
  messageBox.id = id;
  messageBox.classList.add(id);
  messageBox.classList.add(CONSTANTS.SUCCESS);
  messageBox.innerHTML = `
	<div class="row">
		<div class="col-md-12">
			<i class="bi bi-check-circle" style="color: var(--success)"></i>
		</div>
		<div class="col-md-12">
			<h4 class="message-title">${title}</h4>
			<p class="message-text mt-1">${message}</p>
		</div>
	</div>
	<button class="message-close" onclick="polMessageKill('${id}')">
		<i class="icon icon-close"></i>
	</button>
	`;
  document.body.appendChild(messageBox);
  setImediate(function () {
    messageBox.classList.add(CONSTANTS.SHOW);
    polMessageAutoKill(id);
  });
}

function polError(message) {
  var id = "message-box";
  polMessageKill(id);

  var messageBox = document.createElement("div");
  messageBox.id = id;
  messageBox.classList.add(id);
  messageBox.classList.add(CONSTANTS.ERROR);
  messageBox.innerHTML = `
	<i class="icon icon-error-o" style="color: var(--danger);"></i>
	<p class="message-text px-1">${message}</p>
	<button class="message-close" onclick="polMessageKill('${id}')">
		<i class="icon icon-close"></i>
	</button>
	`;
  document.body.appendChild(messageBox);
  setImediate(function () {
    messageBox.classList.add(CONSTANTS.SHOW);
    polMessageAutoKill(id);
  });
}

function truncatedItems() {
  const ps = document.querySelectorAll(".truncate");
  if (ps.length < 1) {
    return;
  }
  const observer = new ResizeObserver((entries) => {
    for (let entry of entries) {
      entry.target.classList[
        entry.target.scrollHeight > entry.contentRect.height + 1
          ? "add"
          : "remove"
      ]("truncated");
    }
  });

  ps.forEach((p) => {
    observer.observe(p);
  });
}

function polVideoTag(element) {
  const video = document.querySelector(element);

  function addVideoListener() {
    video.load();
    video.addEventListener("click", playVideo);
  }

  function playVideo() {
    video.controls = true;
    setImediate(function () {
      video.play();
    })
    video.removeEventListener("click", playVideo);
  }

  function endVideo() {
    video.controls = false;
    addVideoListener();
  }

  addVideoListener();
  video.addEventListener("ended", endVideo);
}

// Mensagens globais via cookie ----------------------------------------
//type: success || error
//title: only in success
function setSessionMessage(
  type = CONSTANTS.SUCCESS,
  title = "Obrigado!",
  message
) {
  sessionStorage.setItem(
    CONSTANTS.MESSAGE_COOKIE,
    JSON.stringify({ type, title, message })
  );
}

function getSessionMessage() {
  var ck = sessionStorage.getItem(CONSTANTS.MESSAGE_COOKIE);
  if (!ck) {
    return;
  }
  var content = JSON.parse(ck);
  if (content.type === CONSTANTS.SUCCESS) {
    polMessage(content.title, content.message);
  } else if (content.type === CONSTANTS.ERROR) {
    polError(content.message);
  }
  sessionStorage.removeItem(CONSTANTS.MESSAGE_COOKIE);
}

function blockUnblockMaterial(el, block) {
  const materialEl = document.querySelectorAll(`${el} .mdc-text-field`);
  const materialSel = document.querySelectorAll(`${el} .mdc-select`);
  materialEl.forEach(function (element, key, parent) {
    block
      ? element.classList.add("mdc-text-field--disabled")
      : element.classList.remove("mdc-text-field--disabled")
  });
  materialSel.forEach(function (element, key, parent) {
    const select = mdc.select.MDCSelect.attachTo(element);
    select.disabled = block;
  });
}

function blockUnblockInputs(el, block) {
  blockUnblockMaterial(el, block);
  const allEl = document.querySelectorAll(
    `${el} input, ${el} select, ${el} textarea`
  );
  allEl.forEach(function (element, key, parent) {
    block
      ? element.setAttribute("readonly", true)
      : element.removeAttribute("readonly");
  });
  console.log("blocked inputs", block);
}

// -----------------------------------------------------------------------

// ----------------------------
// Handler do Download do Video
function downloadClick_handler(evt) {
  evt.preventDefault();
  let hash = jQuery(evt.currentTarget).attr("data-download");
  let security = jQuery(evt.currentTarget).attr("data-nonce");
  let action = "video-download-link";
  let data = { hash, security, action };
  jQuery.post(polenObj.ajax_url, data, (response) => {
    if (response.success) {
      window.location.href = response.data;
    }
  });
}
// ---------------------------

// Analytics ----------------------------------
const polenGtag = {
  type: {
    purchase: "purchase",
  },
  sendEvent: function (type, value) {
    if (typeof gtag === "function") {
      gtag("event", type, value);
    } else {
      console.log("GTAG", type, value);
    }
  },
};
// --------------------------------------------

// Funções de Cookie -------------------------------------------------------
function polSetCookie(cname, cvalue, exdays = 30) {
  const d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  let expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function polGetCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(";");
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function polAcceptCookies() {
  localStorage.setItem(polenObj.COOKIES.POLICIES, "true");
  const policies_box = document.getElementById("policies-box");
  policies_box.parentNode.removeChild(policies_box);
}

function showLGPDBox() {
  const box = document.getElementById("policies-box");
  if (!localStorage.getItem(polenObj.COOKIES.POLICIES)) {
    box.classList.remove("d-none");
  }
}

function replaceLineBreakString(string) {
  let instruction = string.replace(/&#38;#13;/g, "<br>").replace(/&#38;#10;/g, "<br>");
  return instruction;
}

function polRequestZapier(formName) {
  if (polenObj.developer) {
    return;
  }
  jQuery
    .post(
      polenObj.ajax_url + "?action=zapier_mail",
      jQuery(formName).find(":not(input[name=action])").serialize()
    )
}

function polAjaxForm(formName, callBack, callBackError, reset = true) {
  polSpinner(null, formName);
  blockUnblockInputs(formName, true);
  jQuery
    .post(
      polenObj.ajax_url,
      jQuery(formName).serialize(),
      function (result) {
        if (result.success) {
          reset && document.querySelector(formName).reset();
          callBack(result.data);
        } else {
          callBackError(result.data);
        }
      }
    )
    .fail(function (e) {
      console.log(e);
      if (e.responseJSON) {
        callBackError(e.responseJSON.data.response || e.responseJSON.data.Error || e.responseJSON.data);
      } else {
        callBackError(e.statusText);
      }
    })
    .complete(function (e) {
      polSpinner(CONSTANTS.HIDDEN);
      blockUnblockInputs(formName, false);
    });
}

function polRemoveElement(el) {
  const _this = document.querySelector(el);
  _this.parentNode.removeChild(_this);
}

function polSelectAdvanced() {
  const selects = document.querySelectorAll(".select-advanced");
  if (selects.length < 1) {
    return;
  }
  [...selects].map(select => {
    let id = select.getAttribute("id");
    let component = document.querySelector(`#${id}`);
    let radio = document.querySelectorAll(`#${id} input[name=${id}]`);
    [...radio].map(item => {
      item.addEventListener("click", function (e) {
        [...document.querySelectorAll(`#${id} .item`)].map(item => item.classList.remove("-checked"));
        this.parentNode.classList.add("-checked");
        component.dispatchEvent(new CustomEvent("polselectchange", {
          detail: e.target.value
        }));
      });
    });
  })
}

function onFullScreen(e) {
  var isFullscreenNow = document.webkitFullscreenElement || document.fullscreenElement;
  e.target.style["object-fit"] = isFullscreenNow ? "contain" : "cover";
}

// -------------------------------------------------------------------------

jQuery(document).ready(function () {
  truncatedItems();
  getSessionMessage();
  showLGPDBox();
  polSelectAdvanced();
  jQuery('[data-toggle="tooltip"]').tooltip({
    animated: 'fade',
    trigger: 'click'
  });
  jQuery(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange', function (e) {
    onFullScreen(e);
  });
});

function closeModal() {
  let modal = document.querySelector(".show");
  modal.classList.remove("show");
}

String.prototype.allTrim = String.prototype.allTrim ||
  function () {
    return this.replace(/\s+/g, ' ')
      .replace(/^\s+|\s+$/, '');
  };

function polSlugfy(s, opt) {
  s = String(s);
  opt = Object(opt);

  var defaults = {
    'delimiter': '-',
    'limit': undefined,
    'lowercase': true,
    'replacements': {},
    'transliterate': (typeof (XRegExp) === 'undefined') ? true : false
  };

  // Merge options
  for (var k in defaults) {
    if (!opt.hasOwnProperty(k)) {
      opt[k] = defaults[k];
    }
  }

  var char_map = {
    // Latin
    'À': 'A', 'Á': 'A', 'Â': 'A', 'Ã': 'A', 'Ä': 'A', 'Å': 'A', 'Æ': 'AE', 'Ç': 'C',
    'È': 'E', 'É': 'E', 'Ê': 'E', 'Ë': 'E', 'Ì': 'I', 'Í': 'I', 'Î': 'I', 'Ï': 'I',
    'Ð': 'D', 'Ñ': 'N', 'Ò': 'O', 'Ó': 'O', 'Ô': 'O', 'Õ': 'O', 'Ö': 'O', 'Ő': 'O',
    'Ø': 'O', 'Ù': 'U', 'Ú': 'U', 'Û': 'U', 'Ü': 'U', 'Ű': 'U', 'Ý': 'Y', 'Þ': 'TH',
    'ß': 'ss',
    'à': 'a', 'á': 'a', 'â': 'a', 'ã': 'a', 'ä': 'a', 'å': 'a', 'æ': 'ae', 'ç': 'c',
    'è': 'e', 'é': 'e', 'ê': 'e', 'ë': 'e', 'ì': 'i', 'í': 'i', 'î': 'i', 'ï': 'i',
    'ð': 'd', 'ñ': 'n', 'ò': 'o', 'ó': 'o', 'ô': 'o', 'õ': 'o', 'ö': 'o', 'ő': 'o',
    'ø': 'o', 'ù': 'u', 'ú': 'u', 'û': 'u', 'ü': 'u', 'ű': 'u', 'ý': 'y', 'þ': 'th',
    'ÿ': 'y',

    // Latin symbols
    '©': '(c)',

    // Greek
    'Α': 'A', 'Β': 'B', 'Γ': 'G', 'Δ': 'D', 'Ε': 'E', 'Ζ': 'Z', 'Η': 'H', 'Θ': '8',
    'Ι': 'I', 'Κ': 'K', 'Λ': 'L', 'Μ': 'M', 'Ν': 'N', 'Ξ': '3', 'Ο': 'O', 'Π': 'P',
    'Ρ': 'R', 'Σ': 'S', 'Τ': 'T', 'Υ': 'Y', 'Φ': 'F', 'Χ': 'X', 'Ψ': 'PS', 'Ω': 'W',
    'Ά': 'A', 'Έ': 'E', 'Ί': 'I', 'Ό': 'O', 'Ύ': 'Y', 'Ή': 'H', 'Ώ': 'W', 'Ϊ': 'I',
    'Ϋ': 'Y',
    'α': 'a', 'β': 'b', 'γ': 'g', 'δ': 'd', 'ε': 'e', 'ζ': 'z', 'η': 'h', 'θ': '8',
    'ι': 'i', 'κ': 'k', 'λ': 'l', 'μ': 'm', 'ν': 'n', 'ξ': '3', 'ο': 'o', 'π': 'p',
    'ρ': 'r', 'σ': 's', 'τ': 't', 'υ': 'y', 'φ': 'f', 'χ': 'x', 'ψ': 'ps', 'ω': 'w',
    'ά': 'a', 'έ': 'e', 'ί': 'i', 'ό': 'o', 'ύ': 'y', 'ή': 'h', 'ώ': 'w', 'ς': 's',
    'ϊ': 'i', 'ΰ': 'y', 'ϋ': 'y', 'ΐ': 'i',

    // Turkish
    'Ş': 'S', 'İ': 'I', 'Ç': 'C', 'Ü': 'U', 'Ö': 'O', 'Ğ': 'G',
    'ş': 's', 'ı': 'i', 'ç': 'c', 'ü': 'u', 'ö': 'o', 'ğ': 'g',

    // Russian
    'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo', 'Ж': 'Zh',
    'З': 'Z', 'И': 'I', 'Й': 'J', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N', 'О': 'O',
    'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'C',
    'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sh', 'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu',
    'Я': 'Ya',
    'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'zh',
    'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o',
    'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c',
    'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu',
    'я': 'ya',

    // Ukrainian
    'Є': 'Ye', 'І': 'I', 'Ї': 'Yi', 'Ґ': 'G',
    'є': 'ye', 'і': 'i', 'ї': 'yi', 'ґ': 'g',

    // Czech
    'Č': 'C', 'Ď': 'D', 'Ě': 'E', 'Ň': 'N', 'Ř': 'R', 'Š': 'S', 'Ť': 'T', 'Ů': 'U',
    'Ž': 'Z',
    'č': 'c', 'ď': 'd', 'ě': 'e', 'ň': 'n', 'ř': 'r', 'š': 's', 'ť': 't', 'ů': 'u',
    'ž': 'z',

    // Polish
    'Ą': 'A', 'Ć': 'C', 'Ę': 'e', 'Ł': 'L', 'Ń': 'N', 'Ó': 'o', 'Ś': 'S', 'Ź': 'Z',
    'Ż': 'Z',
    'ą': 'a', 'ć': 'c', 'ę': 'e', 'ł': 'l', 'ń': 'n', 'ó': 'o', 'ś': 's', 'ź': 'z',
    'ż': 'z',

    // Latvian
    'Ā': 'A', 'Č': 'C', 'Ē': 'E', 'Ģ': 'G', 'Ī': 'i', 'Ķ': 'k', 'Ļ': 'L', 'Ņ': 'N',
    'Š': 'S', 'Ū': 'u', 'Ž': 'Z',
    'ā': 'a', 'č': 'c', 'ē': 'e', 'ģ': 'g', 'ī': 'i', 'ķ': 'k', 'ļ': 'l', 'ņ': 'n',
    'š': 's', 'ū': 'u', 'ž': 'z'
  };

  // Make custom replacements
  for (var k in opt.replacements) {
    s = s.replace(RegExp(k, 'g'), opt.replacements[k]);
  }

  // Transliterate characters to ASCII
  if (opt.transliterate) {
    for (var k in char_map) {
      s = s.replace(RegExp(k, 'g'), char_map[k]);
    }
  }

  // Replace non-alphanumeric characters with our delimiter
  var alnum = (typeof (XRegExp) === 'undefined') ? RegExp('[^a-z0-9]+', 'ig') : XRegExp('[^\\p{L}\\p{N}]+', 'ig');
  s = s.replace(alnum, opt.delimiter);

  // Remove duplicate delimiters
  s = s.replace(RegExp('[' + opt.delimiter + ']{2,}', 'g'), opt.delimiter);

  // Truncate slug to max. characters
  s = s.substring(0, opt.limit);

  // Remove delimiter from ends
  s = s.replace(RegExp('(^' + opt.delimiter + '|' + opt.delimiter + '$)', 'g'), '');

  return opt.lowercase ? s.toLowerCase() : s;
}

function mtel(v) {
  v = v.replace(/\D/g, ""); //Remove tudo o que não é dígito
  v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
  v = v.replace(/(\d)(\d{4})$/, "$1-$2"); //Coloca hífen entre o quarto e o quinto dígitos
  return v;
}

function polNextCarousel(slug) {
  document.getElementById(slug).scrollLeft += 300;
}

function polPrevCarousel(slug) {
  document.getElementById(slug).scrollLeft -= 300;
}
