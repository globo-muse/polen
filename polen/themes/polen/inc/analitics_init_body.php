<?php
if ( DEVELOPER ) {
	// return;
}
global $Polen_Plugin_Settings;

if( !empty( $Polen_Plugin_Settings['polen_google_tagmanager_key'] ) ) :
?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= $Polen_Plugin_Settings['polen_google_tagmanager_key']; ?>"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

<?php endif;

if( !empty( $Polen_Plugin_Settings['polen_ca_pub_key'] ) ) : ?>
	<script data-ad-client="<?= $Polen_Plugin_Settings['polen_ca_pub_key']; ?>" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<?php endif;

?>
<script type="text/javascript">
  <?php $current_user = wp_get_current_user(); ?>
  var _user_id = "<?php echo !empty( $current_user ) ? $current_user->user_email : ''; ?>";
  var _session_id = "<?php echo wp_create_nonce(); ?>";

  var _sift = window._sift = window._sift || [];
  _sift.push(['_setAccount', '57a5a2769e']);
  _sift.push(['_setUserId', _user_id]);
  _sift.push(['_setSessionId', _session_id]);
  _sift.push(['_trackPageview']);

 (function() {
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
</script>