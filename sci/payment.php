<?php
/**
* Bitcoin Payment Gateway
*
* @author Jacob Bruce
* www.bitfreak.info
*/
require_once dirname(__FILE__).'/config.php';
require_once dirname(__FILE__).'/../lib/common.lib.php';
require_once dirname(__FILE__).'/dbconnect.php';

// start session
session_start();

//change the confirmation number to 0 confirms for fast verification
$confirm_num_0 = 0;

// generic function to handle input errors
function invalid_input($error_msg='')
{
  if (empty($error_msg)) {
    die('An unexpected error occurred. Go back and try again.');
  } else {
    die($error_msg);
  }
}

// call ipn script when transaction is confirmed
function confirm_transaction($ipn_url, $pub_add, $sec_str)
{
  // set final session vars
  $_SESSION['tranHash'] = md5($pub_add.$sec_str);
  $_SESSION['confirmed'] = $pub_add.':confirmed';

  // execute IPN control
  header('Location: '.$ipn_url);
  exit;
}

// ensure these are invalid for security reasons
$_SESSION['tranHash'] = '';
$_SESSION['confirmed'] = 'unconfirmed';

// get session id and transaction code
$sid = session_id();
$ted = empty($_GET['t']) ? 0 : preg_replace('/[^a-z0-9]/i', '', $_GET['t']);

// save currency type to session
$currency = (empty($_GET['c'])) ? 'btc' : strtolower($_GET['c']);
$curr_upp = safe_str(strtoupper($currency));
$dollar_amount = safe_str($_GET['dollar_amount']);

// save client type to session variable
if (empty($_SESSION['client_type'])) {
  if (file_get_contents('t_data/'.hash('sha256', $ted))) {
    $_SESSION['client_type'] = 'torcon';
  } else {
    $_SESSION['client_type'] = 'normal';
  }
}

// check if client is using Tor
if ($_SESSION['client_type'] != 'torcon') {
  $tfh = hash('sha256', get_ip_hash().$ted);
} else {
  $tfh = hash('sha256', $ted);
}

// save IP hash to session variable
if (empty($_SESSION['ip_hash'])) {
  $_SESSION['ip_hash'] = get_ip_hash();
}

// check if IP has changed
if ($_SESSION['ip_hash'] !== get_ip_hash()) {
  if ($_SESSION['client_type'] != 'torcon') {
    invalid_input();
  }
}

// get currency name for printing
switch ($currency) {
  case 'btc':
    $coin_names = array('Bitcoin', 'bitcoin', 'bitcoins');
    $pinc = $prog_inc;
	break;
  case 'ltc':
    $coin_names = array('Litecoin', 'litecoin', 'litecoins');
	$pinc = $prog_inc * 3;
	break;
  default: invalid_input("ERROR: unsupported currency.");
}

// decode the t_data (pulled from file)
if (!empty($ted) && !empty($tfh)) {
  $t_data = file_get_contents('t_data/'.$tfh);
  if ($t_data !== false) {
    $_SESSION['t_data'] = bitsci::decrypt_data(base64_decode($t_data));
  } else {
    invalid_input('ERROR: no transactions linked to the current transaction code!');
  }
} else {
  invalid_input('Transaction code is empty. Go back and try again.');
}

// save the transaction data to individual variables
list($pubAdd, $price, $quantity, $item, $seller, $success_url, $cancel_url, $note, $baggage, $dollar_amount) = explode('|', $_SESSION['t_data']);

// check for errors in price and quantity
if (empty($price) || !is_numeric($price) || empty($quantity) || !is_numeric($quantity)) {
  invalid_input('Error calculating item price. Go back and try again.');
} else {
  // get the total price
  $_SESSION['total_price'] = bitsci::btc_num_format($price * $quantity);
}

// check for errors in address
if (($currency == 'btc') && !bitcoin::checkAddress($pubAdd)) {
  invalid_input('Invalid bitcoin address. Go back and try again.');
} elseif (($currency == 'ltc') && !litecoin::checkAddress($pubAdd)) {
  invalid_input('Invalid litecoin address. Go back and try again.');
}

// success? confirm for a 2nd time then redirect
if (isset($_GET['success'])) {
  $check_result = bitsci::check_payment($_SESSION['total_price'], $pubAdd, $confirm_num_0, $p_variance, false, $currency);
  if ($check_result === true) {
    confirm_transaction('ipn-control.php?sid='.$sid, $pubAdd, $sec_str);
  } else {
    invalid_input('Error confirming transaction. Refresh the page or go back and try again.');
  }
}

// check for potential errors before proceeding
if (empty($_GET['u'])) {

  $check_result = bitsci::check_payment($_SESSION['total_price'], $pubAdd, $confirm_num_0, $p_variance, true, $currency);

  if ($check_result === 'e1') {
    invalid_input('All API\'s are unavailable. Please try again later.');
  } elseif ($check_result === 'e2') {
    invalid_input('The address is corrupt. Please go back and try again.');
  } elseif ($check_result === 'e3') {
    invalid_input('An unknown error occurred. Please try again later.');
  } elseif ($check_result === true) {
    confirm_transaction('ipn-control.php?sid='.$sid, $pubAdd, $sec_str);
  }

}

// save refresh time in milliseconds
$ms_rt = $refresh_time * 500;

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=0">
<meta name="robots" content="noindex,nofollow" />
<title><?php echo $coin_names[0]; ?> Payment Gateway</title>
<script type="text/javascript" src="../scripts/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="../scripts/jquery.qrcode.min.js"></script>
<script src="../scripts/sweet-alert.js"></script>
<link rel="stylesheet" type="text/css" href="../css/sweet-alert.css">

<?php if (!empty($_GET['u'])) {
	?>
<script language="JavaScript" type="text/javascript">
var sid = encodeURIComponent('<?php echo $sid; ?>');
var ted = encodeURIComponent('<?php echo $ted; ?>');
var cur = encodeURIComponent('<?php echo $currency; ?>');
var confHandle = 0;
var stepHandle = 0;
var stepCount = 0;

function confirmTransaction_unconfirmed()
{
	sweetAlert({
		title: "Bitcoins are on their way",
		text: "Please do not close the transaction window.",
		type: "info",
		confirmButtonText: "Got it!",
    allowOutsideClick: true },
		function (isConfirm) {
			if (isConfirm) {
				    var top = document.getElementById('con_sta').offsetTop;
				    window.scrollTo(0, top);
				}
	})
}

function updateProgress(pro_txt)
{
  pro_txt = pro_txt.split(':');
  $('#pro_txt').html(pro_txt[0]+'%');
  $('#pro_bar').css('width', pro_txt[0]);
  $('#con_sta').html("<b>Status:</b> "+pro_txt[1]);

  if (pro_txt[1] == 'payment verified!') {
    clearInterval(confHandle);
    clearInterval(stepHandle);
    window.location = '?t='+ted+'&c='+cur+'&success';
 }
}


function stepProgress()
{
  if (stepCount >= <?php echo $pinc-1; ?>) {
    stepCount = 0;
  } else {
    var new_pro = $('#pro_bar').width()+1;
	if (new_pro < 100) {
      $('#pro_bar').css('width', new_pro);
	  $('#pro_txt').html(new_pro+'%');
	}
    stepCount++;
  }
}

function checkPaymentStatus()
{
  if ($.support.ajax == false) {
    // lets go old school
    var scriptObject = document.createElement('script');
    scriptObject.type = 'text/javascript';
	scriptObject.async = true;
    scriptObject.src = '<?php echo $site_url.$bitsci_url; ?>ajax_hack.php?sid='+sid+'&t='+ted+'&c='+cur;
	document.getElementsByTagName('head')[0].appendChild(scriptObject);
  } else {
    // simple jquery ajax
    $.ajax({
	  url: 'check-status.php',
	  data: {'sid': sid, 't': ted, 'c': cur},
      dataType: "text",
	  success: function (txt_out) {
	    updateProgress(txt_out);
			console.log(txt_out);
      }
    })
}}

function startConfirmation()
{
  confHandle = setInterval('checkPaymentStatus();', <?php echo round($ms_rt); ?>);
  stepHandle = setInterval('stepProgress();', <?php echo round($ms_rt / $pinc); ?>);
}

$(document).ready(function () {
  $('#qrcode').qrcode('<?php echo $coin_names[1].':'.$pubAdd.'?amount='.$price; ?>');
});

$(window).load(startConfirmation);
</script>
<?php } else { ?>
<script language="JavaScript">
function confirmCancel()
{
	sweetAlert({
		title: "Warning",
		text: "Are you sure you want to cancel this transaction?",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Yes, Cancel it!",
		cancelButtonText: "No, Don't Cancel!",
		closeOnConfirm: false,
		closeOnCancel: true },
		function (isConfirm) {
			if (isConfirm) {
		    document.cookie = 'tcode=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/';
		    window.top.location = '<?php echo $cancel_url; ?>';
				  } else {
					   } })
}


$(document).ready(function () {
  $('#qrcode').qrcode('<?php echo $coin_names[1].':'.$pubAdd.'?amount='.$price; ?>');
});
</script>
<?php } ?>
<style>
.alert_txt {
  color: red;
  font-weight: bold;
}

.small_txt {
	font-size: 8pt;
}

.nojs_box {
	border: 2px solid red;
	background-color: yellow;
	text-align: center;
}

#pro_box {
  width: 102px;
  height: 22px;
  border: solid 1px black;
  text-align: left;
}

#pro_bar {
  height: 20px;
  border: solid 1px red;
  background-color: orange;
}

#total_price {
	font-size:25px;
	font-weight:bold;
}
</style>
</head>
<body>

<center>

  <noscript>
    <div class="nojs_box">
      <p class="alert_txt">WARNING: PLEASE ENABLE JAVASCRIPT IN YOUR WEB BROWSER!</p>
    </div>
  </noscript>

  <p><img src='img/<?php echo $coin_names[1]; ?>_logo.png' alt='' />
  <h1><?php echo $coin_names[0]; ?> Payment Gateway</h1>

<!--   <p>You are Paying <?php echo $price.' '.$curr_upp; ?></b> ($ <?php echo $dollar_amount; ?>) To <b><?php safe_echo($seller); ?></b></p>
-->
  <p>Please transfer <i>exactly</i> <br> <b><?php echo $price.' '.$curr_upp; ?></b> ($ <?php echo $dollar_amount; ?>)<br> To <b><?php safe_echo($seller); ?> </b>at:</p>
 <!-- <p>Please transfer <i>exactly</i> <span id="total_price"><?php echo $_SESSION['total_price'].' '.$curr_upp; ?></span> to the following address:</p>
  -->

  <h5>
    <?php echo '<a href="'.$coin_names[1].':'.$pubAdd.'?amount='.$_SESSION['total_price'].'" title="Click this address to launch your '.$coin_names[0].' client" target="_blank">'.safe_str($pubAdd).'</a>'; ?>
  </h5>
  <div id="qrcode"></div>
	<p>Notes: <?php echo $note ?> </p>

  <?php if (empty($_GET['u'])) { ?>

  <p>Click the confirm button after the <?php echo $curr_upp; ?> has been sent.</p>
  <hr style="width:300px" />
  <p><a href="?<?php echo safe_str('t='.urlencode($_GET['t']).'&u=1&c='.$currency); ?>" target="_self"><img border='0' src='img/conf_btn.png' alt='CONFIRM PAYMENT' /></a></p>
  <p><a href="#" onClick="confirmCancel();"><img border='0' src='img/canc_btn.png' alt='CANCEL PAYMENT' /></a></p>

  <?php } else { ?>

  <p id="con_sta"><b>Status:</b> confirming payment</p>
  <hr style="width:300px" />
  <p class='alert_txt'><b>PLEASE DO NOT CLOSE THIS PAGE!</p>
  <!--<body onload="confirmTransaction();"> -->
  <p>You will be redirected when the payment is confirmed.</b></p>
	<p> Meanwhile check the transaction at<br><a href="https://blockchain.info/address/<?php echo $pubAdd; ?>" target=_blank><?php echo $pubAdd; ?></a></p>
  <p>Please wait while the <?php echo $coin_names[1]; ?> network confirms the payment.
  <?php if ($confirm_num <= 1) { echo "<br />The progress bar may jump back to 0% after reaching 100%"; } ?></p>

  <p><b>Progress:</b></p>
  <table cellpadding='0' cellspacing='0' id='pro_box'><tr><td align='left'>
    <div id='pro_bar' style='width:0px'></div>
  </td></tr></table>
  <span id='pro_txt'>0%</span>


  <?php } ?>

</center>

</body>
</html>
<?php
session_write_close();
?>
