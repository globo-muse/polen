var _user_id = document.getElementById("tuna_tmpuser_id")?.value || ''; // Set to the user's ID, username, or email address, or '' if not yet known.
var _session_id = params.session_id; // Set to a unique session ID for the visitor's current browsing session.

var _sift = window._sift = window._sift || [];
_sift.push(['_setAccount', params.account_key]);
_sift.push(['_setUserId', _user_id]);
_sift.push(['_setSessionId', _session_id]);
_sift.push(['_trackPageview']);

(function () {
  function ls() {
    var e = document.createElement('script');
    e.src = 'https://cdn.sift.com/s.js';
    document.body.appendChild(e);
  }
  if (window.attachEvent) {
    window.attachEvent('onload', ls);
  } else {
    window.addEventListener('load', ls, false);
  }
})();