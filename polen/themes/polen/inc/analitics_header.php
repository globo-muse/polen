<?php

if ( DEVELOPER ) {
	return;
}

global $Polen_Plugin_Settings;
?>
<?php if( !empty( $Polen_Plugin_Settings['polen_google_tagmanager_key'] )) : ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?= $Polen_Plugin_Settings['polen_google_tagmanager_key']; ?>');</script>
    <!-- End Google Tag Manager -->
<?php endif; ?>

<?php if( !empty( $Polen_Plugin_Settings['polen_heapio_key'] )) : ?>
    <!-- heap.io -->
    <script type="text/javascript">
      window.heap=window.heap||[],heap.load=function(e,t){window.heap.appid=e,window.heap.config=t=t||{};var r=document.createElement("script");r.type="text/javascript",r.async=!0,r.src="https://cdn.heapanalytics.com/js/heap-"+e+".js";var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(r,a);for(var n=function(e){return function(){heap.push([e].concat(Array.prototype.slice.call(arguments,0)))}},p=["addEventProperties","addUserProperties","clearEventProperties","identify","resetIdentity","removeEventProperty","setEventProperties","track","unsetEventProperty"],o=0;o<p.length;o++)heap[p[o]]=n(p[o])};
      heap.load("<?= $Polen_Plugin_Settings['polen_heapio_key']; ?>");
    </script>
<?php endif; ?>

<?php if( !empty( $Polen_Plugin_Settings['polen_google_optimize_key'] )) : ?>
    <script src="https://www.googleoptimize.com/optimize.js?id=<?= $Polen_Plugin_Settings['polen_google_optimize_key']; ?>"></script>
<?php endif; ?>

<?php if( !empty( $Polen_Plugin_Settings['polen_google_analitics_key'] )) : ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $Polen_Plugin_Settings['polen_google_analitics_key']; ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', '<?= $Polen_Plugin_Settings['polen_google_analitics_key']; ?>');
    </script>
<?php endif; ?>

<?php if( !empty( $Polen_Plugin_Settings['polen_google_analitics_universal_key'] )) : ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $Polen_Plugin_Settings['polen_google_analitics_universal_key']; ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?= $Polen_Plugin_Settings['polen_google_analitics_universal_key']; ?>');
    </script>
<?php endif; ?>


<?php if( !empty( $Polen_Plugin_Settings['polen_hotjar_key'] )) : ?>
    <!-- Hotjar Tracking Code for https://polen-homolog.c9t.pw/ -->
    <script>
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:<?= $Polen_Plugin_Settings['polen_hotjar_key']; ?>,hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
    </script>
<?php endif; ?>

<?php if( !empty( $Polen_Plugin_Settings['polen_facebookpixel_key'] )) : ?>
    <!-- Facebook Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?= $Polen_Plugin_Settings['polen_facebookpixel_key']; ?>'); 
    fbq('track', 'PageView');
    </script>
    <noscript>
    <img height="1" width="1" 
    src="https://www.facebook.com/tr?id=<?= $Polen_Plugin_Settings['polen_facebookpixel_key']; ?>&ev=PageView
    &noscript=1"/>
    </noscript>
    <!-- End Facebook Pixel Code -->
<?php endif; ?>
<?php
