<?php

// Cache titles for a week.
$CACHEFOR=60*60*24*7;

// Quick hack to prevent other sites using this API.
//if (!isset($_SERVER('HTTP_REFERER')) || $_SERVER('HTTP_REFERER') != $_SERVER['SERVER_NAME']) {
//  echo "document.title = 'Broken Referrer';";
//}

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
$url = $_GET['url'];

$urlbits = parse_url($url);
if (!@isset($urlbits['host']) || (urlencode($urlbits['host']) != $urlbits['host'])) {
 header("Content-Type: text/plain");
 var_dump($urlbits);
 var_dump(array(urlencode(@$urlbits['host']) , @$urlbits['host']));
 exit;
}

// ~Only allow one fetch at a time...
while($memcache->get(md5($url)."-lock"))
 sleep(0.1);

$title = $memcache->get(md5($url)."-title");
if ($title === FALSE) {
  $memcache->set(md5($url)."-lock", $_SERVER['REMOTE_ADDR']);

  // Get the title
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
  $memcache->set(md5($url)."-title", $title, MEMCACHE_COMPRESSED, 60*60*24*7);
  $memcache->delete(md5($url)."-lock");
  $memcached_title = false;
} else {
  $memcached_title = true;
}

// Titles don't change very often.
if ($memcached_title) {
  header("Cache-Control: max-age=$CACHEFOR, public");
  $expstr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $CACHEFOR) . " GMT";
  header($expstr);
} else {
  header("Cache-Control: must-revalidate, private");
  $expstr = "Expires: " . gmdate("D, d M Y H:i:s", 0) . " GMT";
  header($expstr);
}
header("Last-Modified: ". gmdate("D, d M Y H:i:s", time()) . " GMT");

// This is a json response
header("Content-Type: application/javascript");
echo "document.title = " . json_encode ( $title ) . "; // cached? " . ($memcached_title ? "yes" : "no") . "\n";
