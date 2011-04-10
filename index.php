<?php

function get_web_page( $url )
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "http://appsplot.com/".urlencode($url), // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 5,        // timeout on connect
        CURLOPT_TIMEOUT        => 10,       // timeout on response
        CURLOPT_MAXREDIRS      => 5,        // stop after 10 redirects
        CURLOPT_RANGE          => '0-5',    // only get the first bit of the file.
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}

$memcache = new Memcache;
$memcache->addServer('localhost', 11211);

// The default URL is google
$url = 'http://www.google.com';

$servername = $_SERVER['SERVER_NAME'];
$serveruri  = urldecode($_SERVER['REQUEST_URI']);
switch($servername) {

// Normal mode - Take the URL from the request URI
case "www.appsplot.com":
case "appsplot.com":
  if (strlen($serveruri) > 1)
    $url = substr($serveruri, 1);
  break;

// Appengine Mode - Host is from the servername, reset is from server URI.
default:
  $hostname = substr($servername, 0, strlen($servername)-strlen(".appsplot.com"));
  $url = "$url$serveruri";
}

// Check the URL seems valid
$urlbits = parse_url($url);
if (!isset($urlbits['scheme'])) {
 $url = 'http://' . $url;
}
$urlbits = parse_url($url);
if (!isset($urlbits['host']) || (urlencode($urlbits['host']) != $urlbits['host'])) {
// header('Location: http://appsplot.com');
var_dump($urlbits);
 exit;
}

$url = htmlspecialchars($url);

// Get the title
$title = $memcache->get(md5($url)."-title");
if (!$title) {
  $html = get_web_page($url);
  if ($html['errno'] == 0) {
   // Extract the title 
   $result = array();
   preg_match("/<title>(.*?)<\/title>/i", $html['content'], $result);
   if (sizeof($result) > 0) {
    $title = htmlspecialchars($result[1]);
   }
  } else {
   $title = "";
  }
  $memcache->set(md5($url)."-title",$title,MEMCACHE_COMPRESSED);
  $memcached_title = false;
  $title = $title." - New";
} else {
  $memcached_title = true;
  $title = $title." - Memcached";
}
?>
<html>  
<head>  
  <meta charset="utf-8"/>  
  <title><?php echo $title ?></title>
  <link rel="shortcut icon" href="http://<?php echo $urlbits['host']; ?>/favicon.ico" />

  <style type="text/css">

html, body, iframe {
    padding:0; 
    margin:0; 
    border: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

div#paintbox {
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 100;
    background-color: #ffffff;

    overflow: hidden;

    filter:alpha(opacity=99);
    -moz-opacity:0.00001;
    -khtml-opacity: 0.000001;
    opacity: 0.000001;
}
div#canvas {
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 1;
    overflow: hidden;
}
div#sploot {
    font-family: Helvetica, sans-serif;
}

div#wtflink {
    display: none;
    position: fixed;
    bottom: 5px;
    right: 5px;
    z-index: 100
}

div#wtf {
    display: none;
    z-index: 101;
    width: 100%;
    height: 90%;
    overflow: hidden;
    float: left;
    position: fixed;
    top: 0;
    left: 0;
}
div#wtf div#wtf-border {
    margin-left: auto;
    margin-right: auto;
    width: 90%;
    height: 95%;
    background: #fff;
    border-left: 10px solid #000;
    border-right: 10px solid #000;
    border-bottom: 10px solid #000;
    border-bottom-left-radius: 50px;
    border-bottom-right-radius: 50px;
    padding-right: 10px;
    padding-left: 10px;
    padding-bottom: 10px;
}
div#wtf div#wtf-content {
    height: 100%;
    width: 100%;
    overflow: auto;
}

div#wtf div#wtf-content h1 {
    text-align: center;
    padding: 1em;
}

a.button {
    background: transparent url('/i/bg_button_a.gif') no-repeat scroll top right;
    color: #444;
    display: block;
    float: left;
    font: normal 12px arial, sans-serif;
    height: 24px;
    margin-right: 6px;
    padding-right: 18px; /* sliding doors padding */
    text-decoration: none;
    text-align: center;
}
a.button span.b {
    background: transparent url('/i/bg_button_span.gif') no-repeat;
    display: block;
    line-height: 14px; 
    padding: 5px 0 5px 18px;
}

a.bigbutton {
    background: transparent url('/i/bg_bigbutton_a.gif') no-repeat scroll top right;
    color: #444;
    display: block;
    height: 73px;
    text-decoration: none;
    text-align: center;
    padding: 0;
    margin: 0;
    margin-right: 18px;
    padding-right: 54px; /* sliding doors padding */
}

a.bigbutton span.b {
    background: transparent url('/i/bg_bigbutton_span.gif') no-repeat;
    display: block;
    padding: 15px 0 15px 54px;
    height: 43px;
}

a.button:active, a.bigbutton:active {
    background-position: bottom right;
    color: #000;
    outline: none; /* hide dotted outline in Firefox */
}

a.button:active span.b {
    background-position: bottom left;
    padding: 6px 0 4px 18px; /* push text down 1px */
}
a.bigbutton:active span.b {
    background-position: bottom left;
    padding: 16px 0 16px 55px; /* push text down 1px */
}

  </style>
</head>  

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.js" type="text/javascript" charset="utf-8"></script>
<script src="/getscrollbarwidth/jquery.getscrollbarwidth.js" type="text/javascript" charset="utf-8"></script>
  
<body onload="matchHashes();">
  <?php // For some reason splatter seems to need to be after the body has been created. ?>
  <script src="/splatter/src/jquery.splatter.js" type="text/javascript" charset="utf-8"></script>

  <!-- Memcached the title? - <?php echo $memcached_title; ?> -->

<!-- Google Analytics to see who gets splooted (and what they are splooting)! -->
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setCustomVar', 1, 'Splooting', '<?php echo $url; ?>', 2]);
  _gaq.push(['_setAccount', 'UA-22631058-1']);
  _gaq.push(['_setDomainName', '.appsplot.com']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

function Sploot(evt) {
 $("#sploot").splatter({
  min_font_size: 100,
  splat_count: 1,
  position: [Math.round(evt.pageX), Math.round(evt.pageY)],
 });

 // In 5 seconds show the wtf
 window.setTimeout(showWTFLink, 1e3);
 // In 60 seconds, send them to their real site
 window.setTimeout(goForthAndMultiple, 5e3);
}

function showWTFLink() {
 $("#wtflink").css('right', 5+ $.getScrollbarWidth() );
 $("#wtflink").fadeIn('slow');
};

var goForth = true;
function showWTF() {
  // Don't leave appsplot.com during the wtf
  goForth = false;
  $('#wtf').slideDown('slow', 'swing');
}

function goForthAndMultiple() {
  if (goForth) {
    window.location.href = "<?php echo $url; ?>#" + window.location.hash;
  }
}

function matchHashes() {
  // Hash part doesn't get to the server, so we do it client side.
  $("#frame").src = "<?php echo $url; ?>#" + window.location.hash;
}
</script>
<div id=paintbox onclick="Sploot(evt);">
</div>
<div id=canvas>
  <div id=sploot>&nbsp;</div>
</div>
<iframe id=frame src="<?php echo $url; ?>"></iframe>

<div id=wtflink>
 <a class="button" href="javascript: showWTF();">
  <span class="b">wtf is going on!?!?</span>
 </a>
</div>

<div id=wtf>
<div id=wtf-border>
<div id=wtf-content>
 <h1>WTF is going on!?</h1>
 <p>
  Some sneaky, or not so sneaky person as sent you to a website called "appsplot.com".
 </p><p>
  This website uses a technic called "click jacking" to intercept your mouse
  clicks and display "splotches" over top of the actual webpage your trying to
  view.
 </p><p>
  Click jacking isn't just useful for having a bit of fun, careful use of
  clickjacking can be used to steal your login and password, or maybe even your
  credit card details!
 </p><p>
  Like all forms of phishing the way to stay safe is to pay attention to your URL
  bar. If you look carefully, you'll see that URL includes appsplot.com. Even
  though the title and faviroute icon match, it's still not the site you where
  expecting!
 </p>

 <h1>Why did you do this?</h1>
 <p>
  Click jacking is an up coming treat, yet has gotten very little attention.
  This site is a playful attempt at raising awareness of this technic and
  hopefully make it less effective. The more people who know about this issue,
  the better.
 </p><p>
  Plus I also wanted to cover www.google.com in all splotches when bored and to
  impress my friends. (My friends are kinda nerdy like me.)
 </p>

 <h1>Credits</h1>
 <p>
  This website was created by Tim 'mithro' Ansell. You can find his blog at 
  <a href="http://blog.mithis.com/">blog.mithis.com</a>, can follow him on
  twitter at <a href="http://twitter.com/mithro">@mithro</a>, or email him at
  <a href="mailto:appsplot@mithis.com">appsplot@mithis.com</a>.
 </p><p>
  A big thankyou is also due to <a href="http://coryschires.com/">Cory Schires</a>
  who made the <a href="http://coryschires.com/jquery-splatter-plugin/">jQuery Splatter Plugin</a>
  which does the hard work of creating the splats.
 </p><p>
  Code for this project can be found at 
  <a href="http://appsplot.com/github.com/mithro/appsplot.git">github.com/mithro/appsplot.git</a>.
 </p>

 <div style="margin-left: auto; margin-right: auto; width: 25%;">
  <a id=goforth class="bigbutton" onclick="goForth = true; goForthAndMultiple();">
   <span class="b">
     Head to your destination<br>
     <span style="font-size: 8pt; font-style: italic;">(<?php echo $url ?>)</span>
    </span>
  </a>
 </div>
</div>
</div>
</div>

</body>  
</html> 

