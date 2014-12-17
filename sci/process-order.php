<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');
require_once(dirname(__FILE__).'/dbconnect.php');

// generate random tran code string
$t_code = bitcoin::randomString(26);

// check what currency we are using
$currency = (empty($_GET['c'])) ? 'btc' : strtolower($_GET['c']);
  
// set cookies for recovering transaction
setcookie('tcode', $t_code, time()+172800, '/');
setcookie('tcurr', $currency, time()+172800, '/');

// start the session
session_start();
  
if (empty($_GET['btc_amount']) || !is_numeric($_GET['btc_amount'])) {
  
  die('invalid input');
  
} else {
  
  // unset old session data (KEEP THIS)
  unset($_SESSION['tranHash']);
  unset($_SESSION['confirmed']);
  unset($_SESSION['total_price']);
  unset($_SESSION['ip_hash']);
  
  // generate a new key pair
  switch ($currency) {
    case 'btc': $keySet = bitcoin::getNewKeySet(); break;
	case 'ltc': $keySet = litecoin::getNewKeySet(); break;
  }
  
  if (empty($keySet['pubAdd']) || empty($keySet['privWIF'])) {
      die("<p>There was an error generating the payment address. Please go back and try again.</p>");
  }
  
  // form encrypted key data
  $encWIF = bin2hex(bitsci::rsa_encrypt($keySet['privWIF'], $pub_rsa_key));
  $key_data = $encWIF . ':' . $keySet['pubAdd'];
  
  // set up sci variables
  $price = $_GET['btc_amount']; //btc_amount
  $item = $_GET['item']; //not important
  $quantity = 1; //not important
  $note = $_GET['note']; //notes
  $baggage = 'null';
	$dollar_amount = $_GET['dollar_amount']; //dollar_amount
  $cancel_url = $site_url.'index.php?result=cancel';
  $success_url = $site_url.'index.php?result=success';
  
  // generate transaction file name hash
  if ($_POST['client'] == 'tcon') {
    $_SESSION['client_type'] = 'torcon';
	$file_hash = hash('sha256', $t_code);
  } else {
    $_SESSION['client_type'] = 'normal';
	$file_hash = hash('sha256', get_ip_hash().$t_code);
  }
	
  // encrypt transaction data and save to file
  $t_data = bitsci::build_pay_query($keySet['pubAdd'], $price, $quantity, $item, $seller, $success_url, $cancel_url, $note, $baggage, $dollar_amount);
	
  if (file_put_contents('t_data/'.$file_hash, $t_data) !== false) {
	  chmod('t_data/'.$file_hash, 0600);
  } else {
    die("<p class='error_txt'>There was an error creating the transaction. Please go back and try again.</p>");
  }

	//save the temporary data to the database
	$notes = mysql_real_escape_string($note);
	$pubAdd_temp = $keySet['pubAdd'];
	$sql_temp = "INSERT INTO tmp_tx_history ".
	       "(currency,btc_amount, dollar_amount, note, pub_add, file_hash)".
	       "VALUES('btc','$price','$dollar_amount', '$notes', '$pubAdd_temp', '$sid')";
	$retval_temp = mysql_query( $sql_temp, $dbhandle );
	if(! $retval_temp )
	{
	  die('Could not enter data: ' . mysql_error());
	}
	

  // build the URL for the bitcoin payment gateway
  $payment_gateway = $site_url.$bitsci_url.'payment.php?t='.$t_code.'&c='.$currency;
  
	
	
	//save the keys in the database
	$pubkeyadd = $keySet['pubAdd'];
	$sql = "INSERT INTO wifkeys ".
	       "(pub_key,priv_enc, file_hash, gen_date) ".
	       "VALUES('$pubkeyadd','$encWIF','$file_hash', NOW())";
	$retval = mysql_query( $sql, $dbhandle );
	if(! $retval )
	{
	  die('Could not enter data: ' . mysql_error());
	}
	mysql_close($dbhandle);
	
	
  // save encrypted private WIF key to file (along with address).
  // you might want to save these keys to a database instead.
  $fp=fopen(dirname(__FILE__)."/wif-keys.csv","a");
  if ($fp) {
    if (flock($fp, LOCK_EX)) {
      @fwrite($fp, $key_data.",\n");
      flock($fp, LOCK_UN);
    }
    fclose($fp);
  }

  // go to payment gateway
  redirect($payment_gateway);

}
?>