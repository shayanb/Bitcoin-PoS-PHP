<?php
// this script is used to show the bitcoin addresses (or litecoin) to get the final balance of each
// also updates the final balance in the database
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/dbconnect.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" type="text/css" href="../css/login.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
<script src="/scripts/sweet-alert.js"></script> 
<link rel="stylesheet" type="text/css" href="/css/sweet-alert.css">


<title>Bitcoin Payment Gateway: Report Page</title>


<!-- LOGIN COOKIE MAKER-->
<?php
$password = $report_pass;
$nonsense = "supercshayAnBAunjAhdiFxpialidocious";

if (isset($_COOKIE['PrivatePageLogin'])) {
   if ($_COOKIE['PrivatePageLogin'] == md5($password.$nonsense)) {
?>


<!-- PUT THE LOGGED IN CODE HERE-->
<?php include_once('report_auth.php'); ?>


<?php //login errors
      exit;
   } else {
      echo "Bad Cookie.";
      exit;
   }
}


if (isset($_GET['p']) && $_GET['p'] == "login") {
    if ($_POST['keypass'] != $password) {
      echo '<script> $(window).load(function(){sweetAlert({   
			title: "Authentication Failed!",   
			text: "Wrong Password!",   
			type: "error",
			confirmButtonText: "OK"},
			function(){    window.location = "report.php"; });})</script>';
			$line = date('Y-m-d H:i:s') . " - $_SERVER[REMOTE_ADDR]" . " - " . $_POST['keypass'];
			file_put_contents('wrongpass.log', $line . PHP_EOL, FILE_APPEND);
      exit;
   } else if ($_POST['keypass'] == $password) {
      setcookie('PrivatePageLogin', md5($_POST['keypass'].$nonsense));
      header("Location: $_SERVER[PHP_SELF]");
   } else {
      echo "Sorry, you could not be logged in at this time.";
   }

}

?>


<!-- LOGIN PAGE -->
<span href="#" class="button" id="toggle-login">Log in</span>
<div id="login">
  <div id="triangle"></div>
  <h1>Aunja Bitcoin Report</h1>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>?p=login" method="post">
    <input type="password" name="keypass" id="keypass" placeholder="Password" />
    <input type="submit" id="submit" value="Log in" />
  </form>
</div>

<script>
$('#toggle-login').click(function(){
  $('#login').toggle();
}); </script>
