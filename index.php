<?php
ob_start();

// The default URL is google
$url = 'http://www.google.com';

$servername = $_SERVER['SERVER_NAME'];
$serveruri  = urldecode($_SERVER['REQUEST_URI']);
switch($servername) {

// Normal mode - Take the URL from the request URI
case 'www.appsplot.com':
case 'appsplot.com':
  if (strlen($serveruri) > 1)
    $url = substr($serveruri, 1);
  break;

// Appengine Mode - Host is from the servername, reset is from server URI.
default:
  $hostname = substr($servername, 0, strlen($servername)-strlen('.appsplot.com'));
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

$jsurl = json_encode($url);
$jsurl = substr($jsurl, 1, strlen($jsurl)-2);
$htmlurl = htmlspecialchars($url);

// Set the cache headers
$CACHEFOR = 60*60*24*7;
header("Cache-Control: max-age=$CACHEFOR, public");
$expstr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $CACHEFOR) . " GMT";
header($expstr);
header("Last-Modified: ". gmdate("D, d M Y H:i:s", time()) . " GMT");
header("Content-Type: text/html; charset=UTF-8");
?>
<html>  
<head>  
  <meta charset='utf-8'/>  
  <title></title>
  <link rel='shortcut icon' href='http://<?php echo $urlbits['host']; ?>/favicon.ico' />

  <style type='text/css'>
<?php include 'index.min.css'; ?>
  </style>
</head>  

<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js' type='text/javascript' charset='utf-8'></script>
  
<body onload='onLoad();'>
<script type='text/javascript'>
<?php include 'jquery.extra.js'; ?>
</script>

<div id=paintbox>
</div>
<div id=canvas>
  <div id=sploot>&nbsp;</div>
</div>
<iframe id=frame src='<?php echo $htmlurl; ?>'></iframe>

<div id=wtflink>
 <a class='button' href='javascript: showWTF();'>
  <span class='b'>wtf is going on!?!?</span>
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
  <a href='http://blog.mithis.com/'>blog.mithis.com</a>, can follow him on
  twitter at <a href='http://twitter.com/mithro'>@mithro</a>, or email him at
  <a href='mailto:appsplot@mithis.com'>appsplot@mithis.com</a>.
 </p><p>
  A big thankyou is also due to <a href='http://coryschires.com/'>Cory Schires</a>
  who made the <a href='http://coryschires.com/jquery-splatter-plugin/'>jQuery Splatter Plugin</a>
  which does the hard work of creating the splats.
 </p><p>
  Code for this project can be found at 
  <a href='http://appsplot.com/github.com/mithro/appsplot.git'>github.com/mithro/appsplot.git</a>.
 </p>

 <div style='margin-left: auto; margin-right: auto; width: 25%;'>
  <a id=goforth class='bigbutton' onclick='goForth = true; goForthAndMultiple();'>
   <span class='b'>
     Head to your destination<br>
     <span style='font-size: 8pt; font-style: italic;'>(<?php echo $htmlurl ?>)</span>
    </span>
  </a>
 </div>
</div>
</div>
</div>
<div class='cache'>
<?php include 'tocache.php' ?>
</div>
</body>  
</html> 
