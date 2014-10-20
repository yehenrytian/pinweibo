<?php
require_once ('../include/friendstream.php');
ini_set('session.gc_maxlifetime', 3600);
session_cache_limiter ('private, must-revalidate');
session_cache_expire(60); // in minutes 
session_start();
$disabled = false;
if (!isset($_SESSION['weibo'])) { 
   $disabled = true;
}

$category = NULL;
if (isset($_GET['cat']))
   {
   $category = $_GET['cat'];
   if ($_SESSION['lastCategory'] != $category)
	  {
	  $_SESSION['lastCategory'] = $category;
	  }
   $_SESSION['loadCount'] = 0;	  
   }
else
   unset($_SESSION['lastCategory']);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--<html xmlns="http://www.w3.org/1999/xhtml">-->
<html xmlns:wb="http://open.weibo.com/wb">
<head>
    <title>Pinweibo - Pinterest style weibo wall</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="Pinterest style of Sina weibo" />
    <meta name="keywords" content="weibo, pinterest, huaban, 花瓣, 美丽说, 微博, 新浪 " />
    <meta name="author" content="Ye Henry Tian" />
    <meta name="distribution" content="global" />
    <meta name="robots" content="follow, all" />
    <meta name="language" content="en" />
    <meta name="revisit-after" content="2 days" />
    <meta content="jaolb+4U3+k7xWefD1IT+pPv3Nevk/TJsQW8ZV3uXBI=" name="verify-v1" />
    <meta property="wb:webmaster" content="309eb8104fb27ab2" />
    <link rel="shortcut icon" href="../images/pinterest-icon.jpg" />
    <link href="fscss2.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../include/desandro-vanilla-masonry-1e41589/css/style.css" />
</head>

<body>

<script src="../include/desandro-vanilla-masonry-1e41589/masonry.min.js"></script>
<script type="text/javascript">
window.onload = function() {
  var wall = new Masonry( document.getElementById('container') );
  
};
//columnWidth: 100
</script>

<script src="http://tjs.sjs.sinajs.cn/open/api/js/wb.js?appkey=1554546537" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript" src="../include/jQuery-Screw/examples/js/jquery.1.6.1.js"></script>
<script type="text/javascript" src="../include/jQuery-Screw/examples/js/jquery.screw.js"></script>
<script type="text/javascript">
// Initialize jQuery
jQuery(document).ready(function($){

// Call screw on the body selector  
$("body").screw({
    loadingHTML: '<div align="center"><img alt="Loading" src="../images/loadingBlack64.gif"></div>'
});

});
</script>

<!-- JiaThis Button BEGIN -->
<script type="text/javascript" >
var jiathis_config={
	url:"http://pinweibo.tk",
	summary:"",
	title:"拼微博 - 拼出你感兴趣的微博合集 #拼微博# #花瓣# #美丽说# #pinterest# #美女#",
	pic:"http://pinweibo.tk/images/PinweiboSS1.png",
	ralateuid:{
		"tsina":"yehenrytian"
	},
	showClose:true,
	hideMore:false
}
</script>
<script type="text/javascript" src="http://v2.jiathis.com/code/jiathis_r.js?btn=r4.gif&move=0" charset="utf-8"></script>
<!-- JiaThis Button END -->

<!-- UJian Button BEGIN -->
<script type="text/javascript" src="http://v1.ujian.cc/code/ujian.js?type=slide"></script>
<!-- UJian Button END -->

<div id="friendstreamwrapper">
<div id="wrapper">

<div id="head">
    <h1><a href="http://pinweibo.tk"></a></h1>

<div id="search" align="right">
<form action="http://www.google.ca/cse" id="cse-search-box" target="_blank">
  <div>
    <input type="hidden" name="cx" value="partner-pub-6635598879568444:c8xv0514j98" />
    <input type="hidden" name="ie" value="UTF-8" />
    <input type="text" name="q" size="30" />
    <input type="image" style="width:25px; height:25px;" name="sa" value="Search" src="../images/icon_search.png"/>
  </div>
</form>
<script type="text/javascript" src="http://www.google.ca/cse/brand?form=cse-search-box&amp;lang=en"></script>
</div>

</div>

<div id="navigationhome">
<nav role="navigation">
	<ul>
		<li><img src="../images/new-twitter-home.png" style="width:16px; height:16px; float:left;"/><a href="./">首页</a></li>
		<li><a href="./about.html">关于</a></li>
		<li><a href="http://weibo.com/yehenrytian" target="_blank">微博</a></li>
	</ul>
</nav>
</div>


<div id="columns">
       
<ul id="column2" class="column">
  <li class="widget color-red" id="intro">  
    <div class="widget-head">
      <h3>拼微博beta - 拼出你感兴趣的微博合集</h3>
    </div>
    <div class="widget-content">
    <?php	
	$weibologin = false;
    $aurl = '';
	/* If access tokens are not available redirect to connect page. */
    if ($_SESSION['weibostatus'] != 'verified') {
	   $weibologin = true;

    // get the authorize connection link
    if (!defined('WB_AKEY') || !defined('WB_SKEY')) {
       echo 'You need a consumer key and secret to test the sample code. Get one from <a href="http://http://open.weibo.com">http://open.weibo.com</a>';
       exit;
    }
   
   $callback = 'http://pinweibo.tk/include/weibo2/callback.php';
     
   $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
   $_SESSION['weiboOAuth'] = $o;
   /*
   if (!isset($_SESSION['emotions']))
      {
      $emotions = $o->http('https://api.t.sina.com.cn/emotions.json?source=' . WB_AKEY, 'GET');
      $emotions = json_decode($emotions, true);
      foreach($emotions as $key=>$value) 
         { 
         $data[$value['phrase']] = $value['url'];  
         } 
      //write_to_file('emotions', $data); 
      $_SESSION['emotions'] = $data;
	  }*/
		
   $aurl = $o->getAuthorizeURL( $callback  );
   //$keys = array('username' => 'yehenrytian', 'password' => 'L6B1l822');
   //$temps = $o->getAccessToken( 'password', $keys );
   //$_SESSION['WB_ACCESSTOKEN'] = $o->getAccessToken( 'password', $keys  );
   //print_r($temps);
}
else {
	 if (!isset($_SESSION['weibo']))
	    {
        $weibo = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
		$_SESSION['weibo'] = $weibo;
		}
     
	 if (!isset($_SESSION['emotions']))
        {
        //$emotions = $weibo->http('https://api.weibo.com/2/emotions.json?source=' . WB_AKEY, 'GET');
        //$emotions = json_decode($emotions, true);
		$emotions = $_SESSION['weibo']->emotions();
		//print_r($emotions);
        foreach($emotions as $value) 
           { 
           $data[$value['phrase']] = $value['url'];  
           } 
        //write_to_file('emotions', $data); 
        $_SESSION['emotions'] = $data;
	    }		
     }

	if ($weibologin)
	   {
	   echo '<div class="snaoauth"><span class="notice">通过您的新浪微薄帐号登录，查看更多微博精选拼盘。</span>';
	   /*
	   echo '<p><a href="'.$aurl.'" title="登录新浪微博"><img style="padding-top:5px; width:151px; height:28px;" src="../images/weibologin.png" alt="用微博帐号登录"/></a> | <img src="../images/adept_update.png" alt="refresh" width="25px" height="25px"/><a href="./?cat=public" title="大家正在说" '.(($category == NULL || $category == 'public') ? 'class="button red"' : '').'>大家正在说</a> | <a href="./?cat=repostdaily" title="每天热门转发榜" '.(($category == 'repostdaily') ? 'class="button red"' : '').'>每天热门转发榜</a> | <a href="./?cat=repostweekly" title="每周热门转发榜" '.(($category == 'repostweekly') ? 'class="button red"' : '').'>每周热门转发榜</a></p></div>';*/
	   
	   echo '<p><a href="'.$aurl.'" title="登录新浪微博"><img style="padding-top:5px; width:151px; height:28px;" src="../images/weibologin.png" alt="用微博帐号登录"/></a> | <img src="../images/adept_update.png" alt="refresh" width="25px" height="25px"/><a href="./?cat=public" title="大家正在说" '.(($category == NULL || $category == 'public') ? 'class="button red"' : '').'>大家正在说</a></p></div>';
	   
	   // emit weibolist
	   //if (isset($category))
	      //$weiboList = get_weibos_pin($category);
	   //else
	      //$weiboList = get_tencentweibos_pin('public');
	   //echo ($weiboList);
	   
	   echo '<iframe width="33%" height="800"  frameborder="0" scrolling="no" src="http://widget.weibo.com/livestream/listlive.php?language=zh_cn&width=0&height=800&uid=1880690955&skin=10&refer=1&appkey=1554546537&pic=1&titlebar=1&border=0&publish=0&atalk=1&recomm=1&at=0&listid=472931307&atopic=%E7%BE%8E%E5%A5%B3&colordiy=0&ptopic=%E6%8B%BC%E5%BE%AE%E5%8D%9A&dpc=1"></iframe>';
	   
	   echo '<iframe frameborder="0" scrolling="no" src="http://wall.v.t.qq.com/index.php?c=wall&a=index&t=%E7%BE%8E%E5%A5%B3&ak=801232672&w=0&h=800&o=3&s=1" width="33%" height="800"></iframe>';
	   
	   echo '<iframe frameborder="0" scrolling="no" src="http://pinweibo.tk/twitterlive2.html" width="33%" height="800"></iframe>';
	  
	   }
	else
	   {
	   $uid_get = $_SESSION['weibo']->get_uid();
       $uid = $uid_get['uid'];
       $user_info = $_SESSION['weibo']->show_user_by_id($uid);//根据ID获取用户等基本信息
	   
	   echo '<div class="snaoauth"><span style="float:right; margin-top:12px;"><wb:publish toolbar="face,image,topic" button_type="red" button_size="middle" default_text="#拼微博#" button_text="发微博" ></wb:publish></span><p>';
	   echo '<span><a href="http://t.sina.com.cn/'.$user_info['id'].'" title="去新浪微博主页" target="_blank"><img src="'.$user_info['profile_image_url'].'" alt="prifile img" width="32px" height="32px"/></a> <a href="http://t.sina.com.cn/'.$user_info['id'].'" title="去新浪微博主页" target="_blank" style="color:#679ef1;">'. $user_info['screen_name'] . '</a> <strong>您好！</strong>'; 
	   
	   echo ' <img src="../images/weibo48x48.png" alt="Sign out of Weibo" width="20px" height="20px"/><a class="button red" href="../include/weibo2/clearsession.php?weibo=clear">退出新浪微博</a> | </span>';
	   
	   /*echo '<img src="../images/adept_update.png" alt="refresh" width="25px" height="25px"/><a href="./?cat=home" title="我的首页">我的首页</a> | <a href="./?cat=me" title="我的微博">我的微博</a> | <a href="./?cat=public" title="大家正在说">大家正在说</a> | <a href="./?cat=repostdaily" title="每天热门转发榜">每天热门转发榜</a> | <a href="./?cat=repostweekly" title="每周热门转发榜">每周热门转发榜</a> | 微博精选推荐: <a href="./?cat=jingyule" title="娱乐">娱乐</a> | <a href="./?cat=jinggaoxiao" title="搞笑">搞笑</a> | <a href="./?cat=jingmeinv" title="美女">美女</a> | <a href="./?cat=jingshiping" title="视频">视频</a> | <a href="./?cat=jingxingzuo" title="星座">星座</a> | <a href="./?cat=jinggezhongmeng" title="各种萌">各种萌</a> | <a href="./?cat=jingshishang" title="时尚">时尚</a> | <a href="./?cat=jingmingche" title="名车">名车</a> | <a href="./?cat=jingmeishi" title="美食">美食</a> | <a href="./?cat=jingyinyue" title="音乐">音乐</a></p></div>';*/
	   
	   echo '<img src="../images/adept_update.png" alt="refresh" width="25px" height="25px"/><a id="home" href="./?cat=home" title="我的首页" '. (($category == NULL || $category == 'home') ? 'class="button red"' : '').'>我的首页</a> | <a id="me" href="./?cat=me" title="我的微博" '.(($category == 'me') ? 'class="button red"' : '').'>我的微博</a> | <a id="public" href="./?cat=public" title="大家正在说" '.(($category == 'public') ? 'class="button red"' : '').'>大家正在说</a> | <a id="repostdaily" href="./?cat=repostdaily" title="每天热门转发榜" '.(($category == 'repostdaily') ? 'class="button red"' : '').'>每天热门转发榜</a> | <a id="repostweekly" href="./?cat=repostweekly" title="每周热门转发榜" '.(($category == 'repostweekly') ? 'class="button red"' : '').'>每周热门转发榜</a> | <span style="color:red; font-weight:bold;">微博精选推荐:</span> <a id="jingyule" href="./?cat=jingyule" title="娱乐" '.(($category == 'jingyule') ? 'class="button red"' : '').'>娱乐</a> | <a id="jinggaoxiao" href="./?cat=jinggaoxiao" title="搞笑" '.(($category == 'jinggaoxiao') ? 'class="button red"' : '').'>搞笑</a> | <a id="jingmeinv" href="./?cat=jingmeinv" title="美女" '.(($category == 'jingmeinv') ? 'class="button red"' : '').'>美女</a> | <a id="jingshiping" href="./?cat=jingshiping" title="视频" '.(($category == 'jingshiping') ? 'class="button red"' : '').'>视频</a> | <a id="jingxingzuo" href="./?cat=jingxingzuo" title="星座" '.(($category == 'jingxingzuo') ? 'class="button red"' : '').'>星座</a> | <a id="jinggezhongmeng" href="./?cat=jinggezhongmeng" title="各种萌" '.(($category == 'jinggezhongmeng') ? 'class="button red"' : '').'>各种萌</a> | <a id="jingmingche" href="./?cat=jingmingche" title="名车" '.(($category == 'jingmingche') ? 'class="button red"' : '').'>名车</a> | <a id="jingmeishi" href="./?cat=jingmeishi" title="美食" '.(($category == 'jingmeishi') ? 'class="button red"' : '').'>美食</a></p></div>';
	   
	   // <img src="../images/new_tweet1.png" alt="new weibo" width="25px" height="20px"/><a href="javascript:void(0);" onclick="newWeiboPopUp();" style="color:#679ef1;">发微博</a>
	   
	   if (isset($category))
	      $weiboList = get_weibos_pin($category);
	   else
	      $weiboList = get_weibos_pin();
	   echo ($weiboList);
	   }
	?> 

   
    </div>
  </li> 
</ul> 
</div> <!--columns-->



<div id="columns"> <!--footer-->
<ul id="column-footer" class="column">
  <li class="widget color-black" id="intro">
   <div class="widget-head">
    <h3></h3>
   </div>
   <div class="widget-content">
   <div class="columnleft" style="width:25%;">
   <h3>合作网站</h3>
	<ul>
    <li><a target="_blank" href="http://www.pinamazon.tk"><strong>Pinamazon.tk</strong></a> - Your Pinterest style amazon mall</li>
    <li><a target="_blank" href="http://www.pintweet.tk"><strong>Pintweet.tk</strong></a> - Your Pinterest style tweets wall</li>
    <li><a target="_blank" href="http://www.friendstream.ca"><strong>Friendstream.ca</strong></a> - Your realtime social network updates</li>
    <li><a target="_blank" href="http://www.firsttimer.ca"><strong>Firsttimer.ca</strong></a> - Get things done yourself</li>
    <li><a target="_blank" href="http://desandro.com/"><strong>David DeSandro</strong></a> - Revels in the creative process.</li>
    <!-- <li><a target="_blank" href="http://net.tutsplus.com/"><strong>Nettuts+</strong></a> - Web development tutorials</li>-->
    </ul>
</div>

<div class="columnleft" style="width:10%;">
   <h3>链接</h3>
	<ul>
    <li><a target="_blank" href="http://t.sina.com.cn"><strong>新浪微博</strong></a></li>				
	<li><a target="_blank" href="http://www.pinterest.com/"><strong>Pinterest</strong></a></li>
	<li><a target="_blank" href="http://www.huaban.com/"><strong>花瓣网</strong></a></li>
    <li><a target="_blank" href="http://www.meilishuo.com/+"><strong>美丽说</strong></a></li>
    <li><a target="_blank" href="http://www.duitang.com/"><strong>堆糖</strong></a></li>	
	</ul>
</div>

<div class="columnleft" style="width:25%;">
     <!--<a target="_blank" href="http://twitter.com/yehenrytian"><img src="../images/twitter-button.png" width="150" height="56" alt="follow me" /></a>-->
     <iframe width="230" height="24" frameborder="0" allowtransparency="true" marginwidth="0" marginheight="0" scrolling="no" border="0" src="http://widget.weibo.com/relationship/followbutton.php?language=zh_cn&width=230&height=24&uid=1880690955&style=3&btn=red&dpc=1"></iframe>
     <p>Developed by: <a target="_blank" href="http://about.me/yehenrytian">Ye Henry Tian</a></p>
     <h4>&copy; copyright 2012 <a href="http://pinweibo.tk">Pinweibo.tk</a></h4>
     <a href="//affiliates.mozilla.org/link/banner/27789"><img src="//affiliates.mozilla.org/media/uploads/banners/910443de740d4343fa874c37fc536bd89998c937.png" alt="Download: Fast, Fun, Awesome" /></a>
</div>

<div class="columnleft" style="float:right;width:25%;">      
  <a href="http://s03.flagcounter.com/more/EzF"><img src="http://s03.flagcounter.com/count/EzF/bg=FFFFFF/txt=075FF7/border=0C9FCC/columns=2/maxflags=12/viewers=0/labels=1/pageviews=1/" alt="free counters" border="0"></a>
</div>

   </div>
   </li>
</ul>
</div> <!--footer-->

</div> <!--wrapper-->
</div> <!--friendstreamwrapper-->

 <!--<script type='text/javascript' src='../include/jquery-1.4.4.min.js?ver=1.4.4'></script>-->
 <script type="text/javascript" language="javascript" src="../include/ajax2.js" charset="utf-8"></script>
 <!--<script type="text/javascript" src="jquery-ui-personalized-1.6rc2.min.js"></script>-->
 <script type="text/javascript" src="inettuts.js"></script>
    
<style type='text/css'>@import url('http://getbarometer.s3.amazonaws.com/install/assets/css/barometer.css');</style>
<script type="text/javascript" charset="utf-8">
  BAROMETER.load('f17xCQkSM27COTrmjXKrE');
</script>
 
</body>
</html>
