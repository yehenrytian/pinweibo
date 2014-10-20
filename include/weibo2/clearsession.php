<?php
/**
 * @file
 * Clears PHP sessions and redirects to the connect page.
 */
 
/* Load and clear sessions */
session_start();

if (isset($_GET['weibo']) && $_GET['weibo'] == 'clear') {
   unset($_SESSION['weibostatus']);
   unset($_SESSION['weiboOAuth']);
   unset($_SESSION['WB_ACCESSTOKEN']);
   unset($_SESSION['emotions']);
   unset($_SESSION['lastCategory']);
   unset($_SESSION['loadCount']);
   unset($_SESSION['weibo']);
   unset($_SESSION['token']);
  
   $_SESSION = array();
   // remove the session cookie
   if (isset($_COOKIE[session_name()]))
      setcookie(session_name(), '', time() - 45000); 

   session_destroy();
  header('Location: ../../');
}

else{
$_SESSION = array();

// remove the session cookie
if (isset($_COOKIE[session_name()]))
   setcookie(session_name(), '', time() - 45000); 

session_destroy();
/* Redirect to page with the connect to Twitter option. */
header('Location: ../../');
}

?>