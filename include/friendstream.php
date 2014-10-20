<?php

require_once ('weibo2/config.php');
require_once ('weibo2/saetv2.ex.class.php');


$func = '';
if (isset($_GET['func']))
   {
   $func = $_GET['func']; 
   }

if (function_exists($func)) 
   {
   if ($func == "short_streampost")
      {
	  echo (call_user_func_array($func, $_GET['parms']));
	  }
   else if (isset($_GET['parms']))
      {
      $parms = $_GET['parms'];
	  $args = explode(',', $parms);
	  //print_r($args);
	  echo (call_user_func_array($func, $args));
	  }
   else
      echo ($func());
   }

function echo_title($title)
   {
   echo ($title); 
   }
  
// function used to shorten URL in streamed post
function short_streampost($t) 
  {
  if (get_magic_quotes_gpc())
     $t = stripslashes($t);
  
   // link URLs
  // make sure to exclude already shorten url by sina t.cn service	  
  //$t = " ".preg_replace( "/(([[:alnum:]]+:\/\/)|www\.)(?!(t|sinaurl)\.cn)([^[:space:]]*)".
	 // "([[:alnum:]#?\/&=])/ie", "getShortUrlStreamIt('\\1\\3\\4')", $t);
  
  $t = " ".preg_replace( "/(([htps]+:\/\/)|www\.)([^[:space:]]*)".
	  "([[:alnum:]#?\/&=])/ie", "getShortUrlStreamIt('$1$3$4')", $t);
  
  //$t = " ".preg_replace('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', "getShortUrlStreamIt('$1')", $t);
  return trim($t);
  }
   
function isValidURL($url)
   {
   return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
   }   
   
// function to get short url for streamit   
function getShortUrlStreamIt($longUrl)
   {
   if (strlen($longUrl) <= 25)
      return $longUrl;
	
   //$ch = curl_init('http://api.t.sina.com.cn/short_url/shorten.xml?source=1550075579&url_long='.$longUrl);
   //$ch = curl_init('http://www.lnk.cm/?module=ShortURL&file=Add&mode=api&url='.$longUrl);
   $postData = array('longUrl' => $longUrl, 'key' => 'AIzaSyBEWJCHPrertbdyKklQmtiL4tCQuPS-Lsg');
   $jsonData = json_encode($postData);
   $curlObj = curl_init();
   /*
   curl_setopt($ch, CURLOPT_HEADER, 0);
   curl_setopt($ch, CURLOPT_POST, 1);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   */
   curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url');
   curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
   curl_setopt($curlObj, CURLOPT_HEADER, 0);
   curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
   curl_setopt($curlObj, CURLOPT_POST, 1);
   curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);
      
   try {
	   $response = curl_exec($curlObj);
	   $json = json_decode($response);
       curl_close($curlObj);
	   $surl = $json->id;
	   
	   if (isValidURL($surl))
	      return $surl;
	   else
	      return $longUrl;
	   }
       catch(Exception $o){
		   //curl_close($ch);
		   return $longUrl;
           //dump($o);
        }
   }
   
   
// function used to get the publick Tencent weibo stream
function get_tencentweibos_pin($category = 'home')
   {
   ini_set('session.gc_maxlifetime', 3600);
   session_cache_limiter ('private, must-revalidate'); 
   session_cache_expire(60); // in minutes 	   
   session_start();  

   // do pagenavigation
   if (isset($_SESSION['lastCategory']))
      {
	  if ($_SESSION['lastCategory'] == $category)
	     {
		 $_SESSION['loadCount']++;
		 }
	  else
	     {
		 $_SESSION['lastCategory'] = $category;
		 $_SESSION['loadCount'] = 1;
		 }
	  }
   else
      {
      $_SESSION['lastCategory'] = $category;
	  $_SESSION['loadCount'] = 1;  
	  }

   
   $loadCount = $_SESSION['loadCount'];
  
   $max_id = 0;
   // fetch public timeline in json format
  if ($category == 'public')
      {
	  $weibo = $_SESSION['weiboOAuth'];
      if (!$weibo)
         {
         $error .= '<h3>Session expired! Please refresh page to sign in again.</h3>';
	     return $error;
	     }
	  //$wb = $weibo->http('http://api.t.sina.com.cn/statuses/public_timeline.json?&count=20&page='.$loadCount.'&source=' . WB_AKEY, 'GET');
	  $wb = $weibo->http('http://open.t.qq.com/api/statuses/public_timeline?format=json&pos=0&reqnum=20&appkey=801232586', 'GET');
	  $wb  = json_decode($wb, true);
	  print_r($wb);
	  
	  $wb = $wb['data']['info'];
	  $max_id = $wb['data']['pos'];
	  }
   

   //print_r($wb);
   
   $weibos = '<div id="weibolist">';
   $weibos .= '<h4>' . count($wb) . ' (条)微博. </h4>';
   $weibos .= '<div style="overflow:auto; overflow-x: hidden; height:100%; width:100%; -moz-border-radius: 15px;">';
   if ($loadCount > 1)
      $weibos .= '<div id="container'.$loadCount.'" class="clearfix">';
   else
      $weibos .= '<div id="container" class="clearfix">';
   
   if (count($wb) > 0)
   foreach($wb as $status){
	     if ($status['type'] == 2) $retweeted_status = $status['source'];
		 $user = $status['user'];
	     $weibos .= '<div class="box col2"><div class="grid-item weibo"><div class="grid-item-content">';

         $weibos .= (parse_weibo(htmlspecialchars($status['text']), $_SESSION['emotions']));
		 if ($status['image'][0] != NULL)
		    {
			$weibos .= '<br/><a href="javascript:void(0);"><img onmouseover="imgpreloader(\''.$status['image'][0].'\')" class="weiboimg" onclick="zC(\''.$status['image'][0].'\')" src="'.$status['image'][0].'"/></a>';
		    }
		 
	     $weibos .= '<br/><img src="../images/weibo_64x64.png" width="16px" height="16px" alt="weibo" /><a target="_blank" href="http://api.t.sina.com.cn/'.$user['id'].'/statuses/'.$status['id'].'">微博地址</a>';
		 
		 if ($retweeted_status != NULL) // retweet
		    {
		    $weibos .= '<br/><div class="retweet"><img src="../images/wall post.png" width="16px" height="16px" alt="zhuanfa" />';
			$weibos .= '<a target="_blank" href="http://t.qq.com/'.$retweeted_status['name'].'">@'.$retweeted_status['nick'].'</a>';			
			if ($retweeted_status['isvip'])
			   $weibos .= '<img src="../images/icon_vip.gif" alt="vip" width="20px" height="13px" />';
			
			$weibos .=  ': '.(parse_weibo(htmlspecialchars($retweeted_status['text']), $_SESSION['emotions']));
			if ($retweeted_status['image'] != NULL)
		       {
			   $weibos .= '<br/><a href="javascript:void(0);"><img onmouseover="imgpreloader(\''.$retweeted_status['image'].'\')" class="weiboimg" onclick="zC(\''.$retweeted_status['image'].'\')" src="'.$retweeted_status['image'].'"/></a>';	
		       }
			$weibos .= '</div>';	
			}
	     else
		    $weibos .= '<br/>';
			
			//repostPopUp('http://tp4.sinaimg.cn/1880690955/50/1291316522/1','//@yehenrytian:测试空格 转发 因','','6963646225',0)
      if (isset($_SESSION['weibo']))
		 {
	     $weibos .= '<div align="right" style="font-size:90%"><img src="../images/icon-twitter-retweet.png" alt="retweet" style="width:13px; height:13px;"/><a href="javascript:void(0);" onclick="repostPopUp(\''.htmlspecialchars(addcslashes($user['profile_image_url'], "\n\r\'\""), ENT_QUOTES).'\',\''.htmlspecialchars(addcslashes('//@'.$user['name'].':'.$status['text'], "\n\r\'\""), ENT_QUOTES).'\',\''.(($retweeted_status != NULL) ? ('//@'.$retweeted_status['user']['name'].':'.htmlspecialchars(addcslashes($retweeted_status['text'], "\n\r\'\""), ENT_QUOTES)):"").'\',\''.$status['id'].'\','.(($retweeted_status != NULL) ? '\''.$retweeted_status['id'].'\'':0).')">转发('.($status['reposts_count'] ? $status['reposts_count'] : 0).')</a> | <img src="../images/comment-icon.png" alt="retweet" style="width:13px; height:13px;"/><a href="javascript:void(0);" onclick="commentPopUp(\''.htmlspecialchars(addcslashes($user['profile_image_url'], "\n\r\'\""), ENT_QUOTES).'\',\''.'@'.$user['name'].':'.htmlspecialchars(addcslashes($status['text'], "\n\r\'\""), ENT_QUOTES).'\',\''.(($retweeted_status != NULL) ? ('//@'.$retweeted_status['user']['name'].':'.htmlspecialchars(addcslashes($retweeted_status['text'], "\n\r\'\""), ENT_QUOTES)):"").'\',\''.$status['id'].'\','.(($retweeted_status != NULL) ? '\''.$retweeted_status['id'].'\'':0).')">评论('.($status['comments_count'] ? $status['comments_count'] : 0).')</a></div>';
		 
		 /*$weibos .= ' | <img src="../images/fsicon2.png" alt="friendstream" style="width:13px; height:13px;"/><a href="javascript:void(0);" onclick="getShortUrl(\''.htmlspecialchars(addcslashes($streamit, "\n\r\'\""), ENT_QUOTES).'\')">广播转发</a></div>';*/
		 }
      else if (0)
	     {
		 $weibos .= '<div align="right" style="font-size:90%"><img src="../images/icon-twitter-retweet.png" alt="retweet" style="width:13px; height:13px;"/><a target="_blank" href="http://api.t.sina.com.cn/'.$user['id'].'/statuses/'.$status['id'].'">转发('.($status['reposts_count'] ? $status['reposts_count'] : 0).')</a> | <img src="../images/comment-icon.png" alt="retweet" style="width:13px; height:13px;"/><a target="_blank" href="http://api.t.sina.com.cn/'.$user['id'].'/statuses/'.$status['id'].'">评论('.($status['comments_count'] ? $status['comments_count'] : 0).')</a></div>';	 
	     }
		 
		 $weibos .= '</div><div class="grid-item-meta">';
	     $weibos .= '<a target="_blank" href="http://t.sina.com.cn/'.$user['id'].'"><img class="grid-item-avatar" style="width:25px; height:25px;" src="'.$user['profile_image_url'].'"></a>';
         $weibos .= '<a target="_blank" href="http://t.sina.com.cn"><img src="../images/weibo128.png" class="grid-service-icon" alt="Weibo"  style="width: 16px; height: 16px;"></a>';
	     $weibos .= '<div>发布者: <a target="_blank" href="http://t.sina.com.cn/'.$user['id'].'">'.$user['name'].'</a>';
		 if ($user['verified'])
			 $weibos .= '<img src="../images/icon_vip.gif" alt="vip" width="20px" height="13px" />';
		 
		 $weibos .= ' 来自 '.$status['source'];
		 if ($user['location'] != NULL)
		    $weibos .= ' 地点: '.$user['location'].'<br /><span class="grid-item-date">'.$status['created_at'].'</span></div>';
	     else
		    $weibos .= '<br /><span class="grid-item-date">'.$status['created_at'].'</span></div>';

	 $weibos .= '</div></div></div>';
     }
   $weibos .= '</div></div></div>';
   
   // auto load control
   //if ($category != 'repostdaily' && $category != 'repostweekly' && !(isset($_SESSION['weibo']) && $category == 'public') && $loadCount <= 10)
   if ($category != 'repostdaily' && $category != 'repostweekly' && $category != 'public' && $loadCount <= 10)
      {
	  $weibos .= '<span class="screw" rel="../include/friendstream.php?func=get_weibos_pin&parms='.$category.'"></span>';
	  } 
   
   return $weibos;
   }    
   
   
// function used to get recent weibos for the user in Pinterest style
function get_weibos_pin($category = 'home')
   {
   ini_set('session.gc_maxlifetime', 3600);
   session_cache_limiter ('private, must-revalidate'); 
   session_cache_expire(60); // in minutes 	   
   session_start();  

   // do pagenavigation
   if (isset($_SESSION['lastCategory']))
      {
	  if ($_SESSION['lastCategory'] == $category)
	     {
		 $_SESSION['loadCount']++;
		 }
	  else
	     {
		 $_SESSION['lastCategory'] = $category;
		 $_SESSION['loadCount'] = 1;
		 }
	  }
   else
      {
      $_SESSION['lastCategory'] = $category;
	  $_SESSION['loadCount'] = 1;  
	  }

   
   $loadCount = $_SESSION['loadCount'];
   //echo ("loadCount is ".$loadCount);
   $isjingxuan = false;
   $max_id = 0;
   // fetch public timeline in json format
   if (isset($_SESSION['weibo']))
      {
	  $weibo = $_SESSION['weibo'];
      if (!$weibo)
         {
         $error .= '<h3>Session expired! Please refresh page to sign in again.</h3>';
	     return $error;
	     }
	  
	  switch($category)
	     {
		 case 'home':
		    $wb = $weibo->home_timeline($loadCount, 20, 0, $max_id);  // home weibos
			$wb = $wb['statuses'];  
			break;
		 case 'me':
		    // get current user id
	        $uid_get = $weibo->get_uid();
            $uid = $uid_get['uid'];
			$wb = $weibo->user_timeline_by_id($uid, $loadCount, 20);  // user's weibos
			$wb = $wb['statuses'];
			break;
		 case 'public':
		    //echo($loadCount);
		    //$wb = $weibo->public_timeline($loadCount, 20);  // public weibos
			$wb = $weibo->public_timeline(1, 150);  // public weibos
			$wb = $wb['statuses']; 
			break;
		 case 'repostdaily':
		    $wb = $weibo->repost_daily(50); // repost daily
			break;
		 case 'repostweekly':
		    $wb = $weibo->repost_weekly(50); // repost weekly
			break;
	     case 'jingyule':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 1);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;
		 case 'jinggaoxiao':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 2);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;
		 case 'jingmeinv':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 3);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;
		 case 'jingshiping':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 4);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;
		 case 'jingxingzuo':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 5);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;
		 case 'jinggezhongmeng':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 6);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;
		 case 'jingshishang':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 7);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;
		 case 'jingmingche':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 8);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;
		 case 'jingmeishi':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 9);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;
		 case 'jingyinyue':
		    $wb = $weibo->suggestions_hot_status($loadCount, 20, 10);  // weibo jingxuan
			$wb = $wb['statuses']; 
			$isjingxuan = true;
			break;	
	     }
	  
	  //print_r($wb);
	  
	  }
   else if ($category == 'public')
      {
	  $weibo = $_SESSION['weiboOAuth'];
      if (!$weibo)
         {
         $error .= '<h3>Session expired! Please refresh page to sign in again.</h3>';
	     return $error;
	     }
	  //$wb = $weibo->http('http://api.t.sina.com.cn/statuses/public_timeline.json?&count=20&page='.$loadCount.'&source=' . WB_AKEY, 'GET');
	  $wb = $weibo->http('http://open.t.qq.com/api/statuses/public_timeline?format=json&pos=0&reqnum=20&appkey=801232586', 'GET');
	  $wb  = json_decode($wb, true);
	  print_r($wb);
	  
	  //$wb = $wb['statuses'];
	  }
   else if ($category == 'repostdaily')
      {
	  $weibo = $_SESSION['weiboOAuth'];
      if (!$weibo)
         {
         $error .= '<h3>Session expired! Please refresh page to sign in again.</h3>';
	     return $error;
	     }
	  //$wb = $weibo->http('http://api.t.sina.com.cn/statuses/hot/repost_daily.json?count=50&source=' . WB_AKEY, 'GET');
	  $wb = $weibo->http('https://api.weibo.com/2/statuses/hot/repost_daily.json?count=50&access_token='. $_SESSION['WB_ACCESSTOKEN'] . '&source=' . WB_AKEY, 'GET');
	  $wb  = json_decode($wb, true);
	  //$wb = $wb['statuses'];
	  }
   else if ($category == 'repostweekly')
      {
	  $weibo = $_SESSION['weiboOAuth'];
      if (!$weibo)
         {
         $error .= '<h3>Session expired! Please refresh page to sign in again.</h3>';
	     return $error;
	     }
	  //$wb = $weibo->http('http://api.t.sina.com.cn/statuses/hot/repost_weekly.json?count=50&source=' . WB_AKEY, 'GET');
	  $wb = $weibo->http('https://api.weibo.com/2/statuses/hot/repost_weekly.json?count=50&access_token='. $_SESSION['WB_ACCESSTOKEN'] . '&source=' . WB_AKEY, 'GET');
	  $wb  = json_decode($wb, true);
	  //$wb = $wb['statuses'];
	  }

   //print_r($wb);
   
   $weibos = '<div id="weibolist">';
   $weibos .= '<h4>' . count($wb) . ' (条)微博. </h4>';
   $weibos .= '<div style="overflow:auto; overflow-x: hidden; height:100%; width:100%; -moz-border-radius: 15px;">';
   if ($loadCount > 1)
      $weibos .= '<div id="container'.$loadCount.'" class="clearfix">';
   else
      $weibos .= '<div id="container" class="clearfix">';
   
   //print_r($_SESSION['token']['access_token']);
   
   if (count($wb) > 0)
   foreach($wb as $status){
	     // handle special format of Jinagxuan weibos
	     if ($isjingxuan) $status = $status['status']; 
		 $max_id = $status['id'];
	     $retweeted_status = $status['retweeted_status'];
		 $user = $status['user'];
	     $weibos .= '<div class="box col3"><div class="grid-item weibo"><div class="grid-item-content">';

         $weibos .= (parse_weibo(htmlspecialchars($status['text']), $_SESSION['emotions']));
		 if ($status['thumbnail_pic'] != NULL)
		    {
			$weibos .= '<br/><a href="javascript:void(0);"><img onmouseover="imgpreloader(\''.$status['bmiddle_pic'].'\')" class="weiboimg" onclick="zC(\''.$status['bmiddle_pic'].'\')" src="'.$status['thumbnail_pic'].'"/></a>';
		    }
		 
	     $weibos .= '<br/><img src="../images/weibo_64x64.png" width="16px" height="16px" alt="weibo" /><a target="_blank" href="http://api.t.sina.com.cn/'.$user['id'].'/statuses/'.$status['id'].'">微博地址</a>';
		 
		 if ($retweeted_status != NULL)
		    {
		    $weibos .= '<br/><div class="retweet"><img src="../images/wall post.png" width="16px" height="16px" alt="zhuanfa" />';
			$weibos .= '<a target="_blank" href="http://t.sina.com.cn/'.$retweeted_status['user']['id'].'">@'.$retweeted_status['user']['name'].'</a>';			
			if ($retweeted_status['user']['verified'])
			   $weibos .= '<img src="../images/icon_vip.gif" alt="vip" width="20px" height="13px" />';
			
			$weibos .=  ': '.(parse_weibo(htmlspecialchars($retweeted_status['text']), $_SESSION['emotions']));
			if ($retweeted_status['thumbnail_pic'] != NULL)
		       {
			   $weibos .= '<br/><a href="javascript:void(0);"><img onmouseover="imgpreloader(\''.$retweeted_status['bmiddle_pic'].'\')" class="weiboimg" onclick="zC(\''.$retweeted_status['bmiddle_pic'].'\')" src="'.$retweeted_status['thumbnail_pic'].'"/></a>';	
		       }
			$weibos .= '</div>';	
			}
	     else
		    $weibos .= '<br/>';
			
			//repostPopUp('http://tp4.sinaimg.cn/1880690955/50/1291316522/1','//@yehenrytian:测试空格 转发 因','','6963646225',0)
      if (isset($_SESSION['weibo']))
		 {
	     $weibos .= '<div align="right" style="font-size:90%"><img src="../images/icon-twitter-retweet.png" alt="retweet" style="width:13px; height:13px;"/><a href="javascript:void(0);" onclick="repostPopUp(\''.htmlspecialchars(addcslashes($user['profile_image_url'], "\n\r\'\""), ENT_QUOTES).'\',\''.htmlspecialchars(addcslashes('//@'.$user['name'].':'.$status['text'], "\n\r\'\""), ENT_QUOTES).'\',\''.(($retweeted_status != NULL) ? ('//@'.$retweeted_status['user']['name'].':'.htmlspecialchars(addcslashes($retweeted_status['text'], "\n\r\'\""), ENT_QUOTES)):"").'\',\''.$status['id'].'\','.(($retweeted_status != NULL) ? '\''.$retweeted_status['id'].'\'':0).')">转发('.($status['reposts_count'] ? $status['reposts_count'] : 0).')</a> | <img src="../images/comment-icon.png" alt="retweet" style="width:13px; height:13px;"/><a href="javascript:void(0);" onclick="commentPopUp(\''.htmlspecialchars(addcslashes($user['profile_image_url'], "\n\r\'\""), ENT_QUOTES).'\',\''.'@'.$user['name'].':'.htmlspecialchars(addcslashes($status['text'], "\n\r\'\""), ENT_QUOTES).'\',\''.(($retweeted_status != NULL) ? ('//@'.$retweeted_status['user']['name'].':'.htmlspecialchars(addcslashes($retweeted_status['text'], "\n\r\'\""), ENT_QUOTES)):"").'\',\''.$status['id'].'\','.(($retweeted_status != NULL) ? '\''.$retweeted_status['id'].'\'':0).')">评论('.($status['comments_count'] ? $status['comments_count'] : 0).')</a></div>';
		 
		 /*$weibos .= ' | <img src="../images/fsicon2.png" alt="friendstream" style="width:13px; height:13px;"/><a href="javascript:void(0);" onclick="getShortUrl(\''.htmlspecialchars(addcslashes($streamit, "\n\r\'\""), ENT_QUOTES).'\')">广播转发</a></div>';*/
		 }
      else if (0)
	     {
		 $weibos .= '<div align="right" style="font-size:90%"><img src="../images/icon-twitter-retweet.png" alt="retweet" style="width:13px; height:13px;"/><a target="_blank" href="http://api.t.sina.com.cn/'.$user['id'].'/statuses/'.$status['id'].'">转发('.($status['reposts_count'] ? $status['reposts_count'] : 0).')</a> | <img src="../images/comment-icon.png" alt="retweet" style="width:13px; height:13px;"/><a target="_blank" href="http://api.t.sina.com.cn/'.$user['id'].'/statuses/'.$status['id'].'">评论('.($status['comments_count'] ? $status['comments_count'] : 0).')</a></div>';	 
	     }
		 
		 $weibos .= '</div><div class="grid-item-meta">';
	     $weibos .= '<a target="_blank" href="http://t.sina.com.cn/'.$user['id'].'"><img class="grid-item-avatar" style="width:25px; height:25px;" src="'.$user['profile_image_url'].'"></a>';
         $weibos .= '<a target="_blank" href="http://t.sina.com.cn"><img src="../images/weibo128.png" class="grid-service-icon" alt="Weibo"  style="width: 16px; height: 16px;"></a>';
	     $weibos .= '<div>发布者: <a target="_blank" href="http://t.sina.com.cn/'.$user['id'].'">'.$user['name'].'</a>';
		 if ($user['verified'])
			 $weibos .= '<img src="../images/icon_vip.gif" alt="vip" width="20px" height="13px" />';
		 
		 $weibos .= ' 来自 '.$status['source'];
		 if ($user['location'] != NULL)
		    $weibos .= ' 地点: '.$user['location'].'<br /><span class="grid-item-date">'.$status['created_at'].'</span></div>';
	     else
		    $weibos .= '<br /><span class="grid-item-date">'.$status['created_at'].'</span></div>';

	 $weibos .= '</div></div></div>';
     }
   $weibos .= '</div></div></div>';
   
   // auto load control
   //if ($category != 'repostdaily' && $category != 'repostweekly' && !(isset($_SESSION['weibo']) && $category == 'public') && $loadCount <= 10)
   if ($category != 'repostdaily' && $category != 'repostweekly' && $category != 'public' && $loadCount <= 10)
      {
	  $weibos .= '<span class="screw" rel="../include/friendstream.php?func=get_weibos_pin&parms='.$category.'"></span>';
	  } 
   
   return $weibos;
   } 
   
   
   
//替换微博中的表情 
function replace_emotions($text, $emotions) 
{ 
  //解析表情数组 
  if (is_array($emotions)){ 
    foreach($emotions as $key=>$value) 
    { 
      $k[]  =  $key; //表情的中文字符 
      $v[]  =  "<img src='{$value}'>";//表情图片的url 
    } 
    return str_replace($k,$v,$text); 
  } else{ 
    return $text; 
  }  
}    
   

// function used to repost a weibo
function repost_weibo($text, $sid, $comment)
   {
   ini_set('session.gc_maxlifetime', 3600);
   session_cache_limiter ('private, must-revalidate'); 
   session_cache_expire(60); // in minutes 	   
   session_start();
   $weibo = $_SESSION['weibo'];
   
   if (!$weibo)
      {
      $error .= '<h3>Session expired! Please refresh page to sign in again.</h3>';
	  return $error;
	  }

   // fetch public timeline in xml format
   if ($text != NULL)
      $weibo->repost($sid, $text);
   else
      $weibo->repost($sid);
   
   // also comment
   if ($comment)
      {
	  if ($text != NULL)
         $weibo->send_comment($sid, $text);
      else
	     $weibo->send_comment($sid, '转发微博');
	  }
   }

// function used to parse sina weibo
function parse_weibo($t, $emotions) 
  {
   
   
   // link URLs
   $t = " ".preg_replace( "/(([htps]+:\/\/)|www\.)([^[:space:]]*)".
	      "([[:alnum:]#?\/&=])/i", "<a href=\"\\1\\3\\4\" target=\"_blank\">".
	        "\\1\\3\\4</a>", $t);
	 
   // link mailtos
   //$t = preg_replace( "/(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)".
	       // "([[:alnum:]-]))/i", "<a href=\"mailto:\\1\">\\1</a>", $t);
	 
   //link Sina users
   $t = preg_replace( "/ *@([\x{4e00}-\x{9fa5}A-Za-z0-9_-]+) ?/u", " <a href=\"http://t.sina.com.cn/n/$1\" target=\"_blank\">@$1</a> ", $t);
		
   // $t = preg_replace( "/ *@([a-z0-9_]*) ?/i", " <a href=\"http://twitter.com/\\1\" target=\"_blank\">@\\1</a> ", $t);
        
		/*
        $pregstr = "/[\x{4e00}-\x{9fa5}]+/u";
        if (preg_match("/ *@[\x{4e00}-\x{9fa5}A-Za-z0-9_]+/u ",$t,$matchArray)){
            //echo $matchArray[0];
           }*/
	 
	//link sina hot topics
	$t = preg_replace( "/ *#([\x{4e00}-\x{9fa5}A-Za-z0-9_[:space:]]*)# ?/u", " <a href=\"http://t.sina.com.cn/k/\\1\" target=\"_blank\">#\\1#</a> ", $t);
	
	// truncates long urls that can cause display problems (optional)
	$t = preg_replace("/>(([[:alnum:]]+:\/\/)|www\.)([^[:space:]]".
	        "{30,40})([^[:space:]]*)([^[:space:]]{10,20})([[:alnum:]#?\/&=])".
	        "</", ">\\3...\\5\\6<", $t);
	
	// 替换表情
	$t = replace_emotions($t, $emotions);		
    
	return trim($t);
   }

function genChineseNameUrl($cName)
   {
   echo $cName;
   $utf8Name = utf8_decode($cName);
   echo($utf8Name);
   return ('<a href="http://t.sina.com.cn/n/'.$utf8Name.'" target="_blank\">@'.utf8Name.'</a>'); 
   }

// function used to parse twitter tweets
function parse_twitter($t) 
  {
	    // link URLs
		$t = " ".preg_replace( "/(([htps]+:\/\/)|www\.)([^[:space:]]*)".
	        "([[:alnum:]#?\/&=])/i", "<a href=\"\\1\\3\\4\" target=\"_blank\">".
	        "\\1\\3\\4</a>", $t);
		
	    /*$t = " ".preg_replace( "/(([[:alnum:]]+:\/\/)|www\.)([^[:space:]]*)".
	        "([[:alnum:]#?\/&=])/i", "<a href=\"\\1\\3\\4\" target=\"_blank\">".
	        "\\1\\3\\4</a>", $t);*/
	 
	    // link mailtos
	    $t = preg_replace( "/(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)".
	        "([[:alnum:]-]))/i", "<a href=\"mailto:\\1\">\\1</a>", $t);
	 
	    //link twitter users
	    $t = preg_replace( "/ *@([a-z0-9_]*) ?/i", " <a href=\"http://twitter.com/\\1\" target=\"_blank\">@\\1</a> ", $t);
	 
	    //link twitter arguments
	    $t = preg_replace( "/ *#([a-z0-9_]*) ?/i", " <a href=\"http://twitter.com/search?q=%23\\1\" target=\"_blank\">#\\1</a> ", $t);
	 
	    // truncates long urls that can cause display problems (optional)
	    $t = preg_replace("/>(([[:alnum:]]+:\/\/)|www\.)([^[:space:]]".
	        "{30,40})([^[:space:]]*)([^[:space:]]{10,20})([[:alnum:]#?\/&=])".
	        "</", ">\\3...\\5\\6<", $t);
	    return trim($t);
   }   
   
// function used to echo sidebar
function echo_sidebar()
  {
  $sidebar = '
  <ul>
<li>
<a target="_blank" href="http://twitter.com/yehenrytian"><img src="../images/twitter-button.png" width="150" height="56" alt="follow me" /></a>
</li>
<li>
<p>
<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like href="http://friendstream.ca" layout="box_count" font="arial"></fb:like>
</p>
</li>
<li>
<br/>
<p>
<a title="Post to Google Buzz" class="google-buzz-button" href="http://www.google.com/buzz/post" data-button-style="normal-count"></a>
<script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script>
</p>
</li>
<li>
<br/>
<p>
<a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="yehenrytian">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
</p>
</li>
<li>
<br/>
 <!-- Include the Google Friend Connect javascript library. -->
<script type="text/javascript" src="http://www.google.com/friendconnect/script/friendconnect.js"></script>
<!-- Define the div tag where the gadget will be inserted. -->
<div id="div-8048205862728875412" style="width:180px;border:1px solid #cccccc;"></div>
<!-- Render the gadget into a div. -->
<script type="text/javascript">
var skin = {};
skin[\'BORDER_COLOR\'] = \'#cccccc\';
skin[\'ENDCAP_BG_COLOR\'] = \'#e0ecff\';
skin[\'ENDCAP_TEXT_COLOR\'] = \'#333333\';
skin[\'ENDCAP_LINK_COLOR\'] = \'#0000cc\';
skin[\'ALTERNATE_BG_COLOR\'] = \'#ffffff\';
skin[\'CONTENT_BG_COLOR\'] = \'#ffffff\';
skin[\'CONTENT_LINK_COLOR\'] = \'#0000cc\';
skin[\'CONTENT_TEXT_COLOR\'] = \'#333333\';
skin[\'CONTENT_SECONDARY_LINK_COLOR\'] = \'#7777cc\';
skin[\'CONTENT_SECONDARY_TEXT_COLOR\'] = \'#666666\';
skin[\'CONTENT_HEADLINE_COLOR\'] = \'#333333\';
skin[\'NUMBER_ROWS\'] = \'4\';
google.friendconnect.container.setParentUrl(\'/smartpage/\' /* location of rpc_relay.html and canvas.html */);
google.friendconnect.container.renderMembersGadget(
 { id: \'div-8048205862728875412\',
   site: \'15178007209702352684\' },
  skin);
</script>
    </li>
    <li>
    <br/>
    <iframe src="http://www.ebuddy.com/widgets/loginbox/custom_login.html?version=small" scrolling="no" frameborder="0"  style="width: 200px; height: 250px;"></iframe>
    </li>
    <li>
    <br/>
    <a href="http://s03.flagcounter.com/more/EzF"><img src="http://s03.flagcounter.com/count/EzF/bg=FFFFFF/txt=075FF7/border=0C9FCC/columns=2/maxflags=12/viewers=0/labels=1/pageviews=1/" alt="free counters" border="0"></a>
    </li>
</ul>
  ';

   return $sidebar;
   }
   
?>