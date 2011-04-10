function sploot(evt) {
 $('#sploot').splatter({
  min_font_size: 100,
  splat_count: 1,
  position: [Math.round(evt.pageX), Math.round(evt.pageY)],
 });

 // In 5 seconds show the wtf
 window.setTimeout(showWTFLink, 1e3);
 // In 60 seconds, send them to their real site
 window.setTimeout(goForthAndMultiple, 60e3);
}

function showWTFLink() {
 $('#wtflink').css('right', 5+ $.getScrollbarWidth() );
 $('#wtflink').fadeIn('slow');
};

var goForth = true;
function showWTF() {
  // Don't leave appsplot.com during the wtf
  goForth = false;
  $('#wtf').slideDown('slow', 'swing');
}

function goForthAndMultiple() {
  if (goForth) {
    window.location.href = '<?php echo $jsurl; ?>' + window.location.hash;
  }
}

function onLoad() {
  $('#paintbox').click( sploot );

  // Hash part doesn't get to the server, so we do it client side.
  $('#myframe').attr('src', '<?php echo $jsurl; ?>' + window.location.hash);

  // Async Analytics
  var _gaq = _gaq || [];
  _gaq.push(['_setCustomVar', 1, 'Splooting', '<?php echo $jsurl; ?>', 2]);
  _gaq.push(['_setAccount', 'UA-22631058-1']);
  _gaq.push(['_setDomainName', '.appsplot.com']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

  // Async request to get the page title
  var s = document.createElement('script');
  s.type = 'text/javascript';
  s.async = true;
  s.src = '/get_title.php?url=<?php echo $jsurl; ?>';
  var x = document.getElementsByTagName('script')[0];
  x.parentNode.insertBefore(s, x);
}
