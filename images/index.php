<?php
session_start();

//require_once ('../include/dbc.php'); 
require_once ('../include/common.funcs.php');
//require_once ('../include/jsonwrapper.php');


$needlogin = false;
$request_link = '';

/* If access tokens are not available redirect to connect page. */
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
        $needlogin = true;

   // get the authorize connection link
   if ($consumer_key === '' || $consumer_secret === '') {
      echo 'You need a consumer key and secret to test the sample code. Get one from <a href="https://twitter.com/apps">https://twitter.com/apps</a>';
      exit;
   }
    /*
    Create a new TwitterOAuth object, and then
    get a request token. The request token will be used
    to build the link the user will use to authorize the
    application. 

    You should probably use a try/catch here to handle errors gracefully
    */
    $to = new TwitterOAuth($consumer_key, $consumer_secret);
    $tok = $to->getRequestToken(OAUTH_CALLBACK);

    /*
    Save tokens for later  - we need these on the callback page to ask for the
    access tokens
    */
    $_SESSION['oauth_token'] = $token = $tok['oauth_token'];
    $_SESSION['oauth_token_secret'] = $tok['oauth_token_secret'];
	
	/* If last connection failed don't display authorization link. */
    switch ($to->http_code) {
      case 200:
        /* Build authorize URL and redirect user to Twitter. */
        $request_link = $to->getAuthorizeURL($tok);
        break;
      default:
        /* Show notification if something went wrong. */
        echo 'Could not connect to Twitter. Refresh the page or try again later.';
     }	
}
else {
  
/* Get user access tokens out of the session. */
$access_token = $_SESSION['access_token'];

/* Create a TwitterOauth object with consumer/user tokens. */
$twitter = new TwitterOAuth($consumer_key, $consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);

/* Create facebook connection */
include_once "fbmain.php";
$config['baseurl']  =  'http://firsttimer.ca/smartpage/';
 
    //if user is logged in and session is valid.
    if ($fbme){
        //Retriving movies those are user like using graph api
        try{
            $movies = $facebook->api('/me/movies');
			$friends = $facebook->api('/me/home');
        }
        catch(Exception $o){
            dump($o);
        }
		
        //update user's status using graph api
        if (isset($_POST['tt'])){
            try {
                $statusUpdate = $facebook->api('/me/feed', 'post', array('message'=> $_POST['tt'], 'cb' => ''));
            } catch (FacebookApiException $e) {
                dump($e);
            }
        }		
		
    } 
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
    <title>SmartPage | The only page you need</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="Smartpage is the page which you can get all your SNA updates in one page" />
    <meta name="keywords" content="twitter, facebook, web 2.0, smartphone, social networking, SNA, email" />
    <meta name="author" content="Ye Henry Tian" />
    <meta name="distribution" content="global" />
    <meta name="robots" content="follow, all" />
    <meta name="language" content="en" />
    <meta name="revisit-after" content="2 days" />
    <meta content="jaolb+4U3+k7xWefD1IT+pPv3Nevk/TJsQW8ZV3uXBI=" name="verify-v1" />
    <link rel="shortcut icon" href="../firsttimer-ico.png" />
    <link rel="stylesheet" type="text/css" media="screen" href="./master.css" />

</head>
<body id="index">
   <div id="fb-root"></div>
        <script type="text/javascript">
            window.fbAsyncInit = function() {
                FB.init({appId: '<?=$fbconfig['appid' ]?>', status: true, cookie: true, xfbml: true});

                /* All the events registered */
                FB.Event.subscribe('auth.login', function(response) {
                    // do something with response
                    login();
                });
                FB.Event.subscribe('auth.logout', function(response) {
                    // do something with response
                    logout();
                });
            };
            (function() {
                var e = document.createElement('script');
                e.type = 'text/javascript';
                e.src = document.location.protocol +
                    '//connect.facebook.net/en_US/all.js';
                e.async = true;
                document.getElementById('fb-root').appendChild(e);
            }());

            function login(){
                document.location.href = "<?=$config['baseurl']?>";
            }
            function logout(){
                document.location.href = "<?=$config['baseurl']?>";
            }
</script>

    <div id="wrapper">
        <div id="header">
           <div id="navigation">
              <ul>
                <li></li>
                <li id="tindex"><a href="/">Home</a></li>
                <!--<li id="tarchive"><a href="/">Archive</a></li>
                <li id="tshowroom"><a href="/showroom">Showroom</a></li>-->
                <li id="tabout"><a href="/about">About</a></li>
              </ul>
            </div>
            
            <h1><a href="./" title="to the homepage of SmartPage"></a></h1>
            
            <div id="search">
            <form action="http://www.google.ca/cse" id="cse-search-box" target="_blank">
             <div>
               <input type="hidden" name="cx" value="partner-pub-6635598879568444:elvvuq-36na" />
               <input type="hidden" name="ie" value="UTF-8" />
               <input type="text" name="q" size="31" />
               <input align="absbottom" type="image" style="width:16px; height:16px" name="sa" value="Search" src="../images/search.gif"/>
               <!-- <input type="submit" name="sa" value="Search" /> -->
             </div>
           </form>
           <script type="text/javascript" src="http://www.google.ca/cse/brand?form=cse-search-box&amp;lang=en"></script>
           </div>
           
            <?php
	        //if (!$needlogin)
               //echo ('<h3><a href="../include/twitterapi/clearsession.php" title="Sign out"></a></h3>');
			?>
           
        </div>
      <div id="body">
          
        <div class="highlight">        
<div class="column left">
    <script type="text/javascript" src="../include/ajax.js"></script>
	
    <h3>Recent Tweets</h3>
    <a href="./" title="refresh tweets">refresh tweets</a>
    <div id="twitterlist" style="overflow:auto; overflow-x: hidden; height:650px; width:270px; -moz-border-radius: 15px;">
    <?php
	
	if ($needlogin)
	   {   
	   echo '<p><a href="'.$request_link.'" title="Sign in with Twitter"><img src="../images/lighter.png" alt="Sign in with Twitter"/></a></p>';
	   }
	else
	   {
	   echo '<p><img src="http://twitter-badges.s3.amazonaws.com/t_small-c.png" alt="Sign out of Twitter"/><a href="../include/twitterapi/clearsession.php">Sign out of Twitter</a></p>';
	   echo (get_tweets($twitter));
	   }
	?> 
     
   </div>
</div>        
        
   <div class="column left" >
    <h3>Recent Facebook Updates</h3>
    <ul>
    <li>
     <?php if (!$fbme) { ?>
        You've to login using FB Login Button to see api calling result.
    <?php } ?>
    <p>
        <fb:login-button autologoutlink="true"></fb:login-button>
    </p>
    </li>
    <li>
    <?php if ($fbme){ ?>
   
                <!-- Data retrived from user profile are shown here -->
                <div class="box">
                    <b>Home feed using Graph API</b>
                    <?php dump($friends); ?>
                </div>
     <?php } ?>            
    </li>    
    
    
    <li>
    <a href="http://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="yehenrytian">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
    </li>
    <li>
    Tweet this page
    </li>
    </ul>
</div>
        
        <div>
        <ul>
        <li class="twitter1"><a href="http://twitter.com/yehenrytian">Twitter <span>Account</span></a></li>
        <li><a href="">Journal <span>RSS Feed</span></a></li>
        <li><a href="">Sidenotes <span>RSS Feed</span></a></li>
        <li>
        <script src="http://www.gmodules.com/ig/ifr?url=http://www.google.com/ig/modules/calendar3.xml&amp;up_calendarFeeds=&amp;up_calendarColors=&amp;up_firstDay=0&amp;up_dateFormat=0&amp;up_timeFormat=1%3A00pm&amp;up_showDatepicker=1&amp;up_hideAgenda=0&amp;up_showEmptyDays=0&amp;up_showExpiredEvents=1&amp;synd=open&amp;w=220&amp;h=365&amp;title=&amp;lang=en&amp;country=ALL&amp;border=%23ffffff%7C0px%2C1px+solid+%23004488%7C0px%2C1px+solid+%23005599%7C0px%2C1px+solid+%230077BB%7C0px%2C1px+solid+%230088CC&amp;output=js"></script>
        </li>
        </ul>
        </div>



</div>
</div>

<div id="sidenotes">
<div class="column left">
    <!--<h3>Sidenotes</h3>-->
    <ul>
        <li>
            <div id="copyright">
      <h4><strong>&copy; 2009&ndash;2010</strong> Firsttimer.ca</h4>
     
    </div>
        </li>
    </ul>
</div>
<div class="column left">
    <ul>
        <li>
            <a href="./">
                <strong>Home</strong>
                <span>Testing </span>
                Where you’re.
            </a>
        </li>
         <div class="rss">
          <ul>
              <!--<li class="seo"><a href="http://www.roarhosting.com">Website hosting</a></li>-->
              <!--<li><a href="http://feeds.feedburner.com/bartelme/journal">Journal <span>RSS 2.0</span></a></li>
              <li><a href="http://feeds.feedburner.com/bartelme/sidenotes">Sidenotes <span>RSS 2.0</span></a></li>-->
          </ul>
        </div>
    </ul>
</div>

 
<!--
    <div class="column right" id="twitter goodies">
    <script src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
  version: 2,
  type: 'search',
  search: 'news',
  interval: 6000,
  title: 'Excitement is in the air...',
  subject: 'Latest News',
  width: 250,
  height: 300,
  theme: {
    shell: {
      background: '#8ec1da',
      color: '#ffffff'
    },
    tweets: {
      background: '#ffffff',
      color: '#444444',
      links: '#1985b5'
    }
  },
  features: {
    scrollbar: true,
    loop: true,
    live: true,
    hashtags: true,
    timestamp: true,
    avatars: true,
    toptweets: true,
    behavior: 'default'
  }
}).render().start();
</script>
</div>-->

</div>
<div id="copyright">

</div>
   
</div>
</body>
</html>



