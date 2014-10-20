<?php
require_once ('weibo2/config.php');
require_once ('weibo2/saetv2.ex.class.php');

ini_set('session.gc_maxlifetime', 3600);
session_cache_limiter ('private, must-revalidate');
session_cache_expire(60); // in minutes 
session_start();


$streamWeibo = isset($_GET['weibo']) ? '1' : '0';
$streamStatus = trim($_GET['post']);
//$streamStatus = ($_GET['post']);

// strip "/"
if (get_magic_quotes_gpc())
   $streamStatus = stripslashes($streamStatus);


// only parse the status content once   
//$shortStatus = parse_longurl($streamStatus);

//print_r ($streamStatus);

$shortStatus = $streamStatus;

// check for Sina Weibo
if ($streamWeibo)
   {
   $weibo = $_SESSION['weibo'];
   
   if (!$weibo)
      {
      echo 'Session expired! Please refresh page to sign in again!';
	  return;
	  }
	  
   // publish status update
   try {
	   $rr = $weibo->update($shortStatus);	
       } catch (Exception $e) {
         print_r($e);
       }
   }   

//header('Location: ../friendstream/');

// function used to shorten URL in streamed post
function parse_longurl($t) 
  {
  // make sure to exclude already shorten url by sina t.cn service	  
  $t = " ".preg_replace( "/(([[:alnum:]]+:\/\/)|www\.)(?!t\.cn)([^[:space:]]*)".
	  "([[:alnum:]#?\/&=])/ie", "getShortUrl('\\1\\3\\4')", $t);
	   
  return trim($t);
  }

function getShortUrl($longUrl)
   {
   $ch = curl_init('http://api.t.sina.com.cn/short_url/shorten.xml?source=1550075579&url_long='.$longUrl);
   curl_setopt($ch, CURLOPT_HEADER, 0);
   curl_setopt($ch, CURLOPT_POST, 0);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $surl = curl_exec($ch);      
   curl_close($ch);
   $surl_xml = new SimpleXMLElement($surl);
   return ($surl_xml->url[0]->url_short); 
   }

function getShortUrl2($api, $longUrl)
   {
   $surl = $api->http('http://api.t.sina.com.cn/short_url/shorten.xml?source=1550075579&url_long='.$longUrl, 'GET');
   $surl_xml = new SimpleXMLElement($surl);
   return ($surl_xml->url[0]->url_short); 
   }
   

?>