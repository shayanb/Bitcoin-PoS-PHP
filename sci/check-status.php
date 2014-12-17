<?php
/**
* Bitcoin Payment Gateway
*
* @author Jacob Bruce
* www.bitfreak.info
*/
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');

if (!empty($_GET['sid'])) {

  // start the session
  session_id($_GET['sid']);
  session_start();
  
  // clean GET['t'] and GET['c']
  $ted = empty($_GET['t']) ? 0 : preg_replace('/[^a-z0-9]/i', '', $_GET['t']);
  $currency = safe_str(strtolower($_GET['c']));
  $unconfirmed_flag = false;
	
  // read t_data from file again if session expired
  if (empty($_SESSION['t_data'])) {
    if (!empty($_GET['t'])) {
	
      // save client type to session variable
      if (file_get_contents('t_data/'.hash('sha256', $ted))) {
        $_SESSION['client_type'] = 'torcon';
      } else {
        $_SESSION['client_type'] = 'normal';
      }

      // check if client is using Tor
      if ($_SESSION['client_type'] != 'torcon') {
        $tfh = hash('sha256', get_ip_hash().$ted);
      } else {
        $tfh = hash('sha256', $ted);
      }
  
      // pull t_data from file
      $t_data = file_get_contents('t_data/'.$tfh);
	  
      if ($t_data !== false) {
        $_SESSION['t_data'] = bitsci::decrypt_data(base64_decode($t_data));
	    $thash = $_SESSION['t_data'];
      } else {
        die("session error");
      }
    } else {
      die("session error");
    }
  }
  
  // update progress according to currency type
  switch ($currency) {
    case 'btc': $pinc = $prog_inc; break;
    case 'ltc': $pinc = $prog_inc * 3; break;
	default: die('ERROR: unsupported currency');
  }

  // save the transaction data to individual variables
  list($pubAdd, $price, $quantity, $item, $seller, $success_url, $cancel_url, $note, $baggage, $dollar_amount) = explode('|', $_SESSION['t_data']);
  
  // get the total price
  $total = $price * $quantity;

  // reset or increase the progress
  if (!isset($_SESSION[$pubAdd.'-confirms'])) {
    $_SESSION[$pubAdd.'-confirms'] = 1;
    //$_SESSION[$pubAdd.'-confirms'] = 0;
		
	$_SESSION[$pubAdd.'-progress'] = $pinc;
  } else {
    $_SESSION[$pubAdd.'-progress'] += $pinc;
    $_SESSION[$pubAdd.'-confirms']++;
  }
  
  // check if the payment has been received
  $check_result = bitsci::check_payment($total, $pubAdd, $_SESSION[$pubAdd.'-confirms'], $p_variance, true, $currency);
 // $check_result_unconfirmed = bitsci::check_payment($_SESSION['total_price'], $pubAdd, 0, $p_variance, true, $currency);

  if ($check_result === false) {
  
	// the payment isn't confirmed yet
    $_SESSION[$pubAdd.'-confirms']--;
		
    $payment_status = 'confirming payment';
	
  } elseif ($check_result === 'e1') {
	
	// we have no working API's...
    $_SESSION[$pubAdd.'-confirms']--;
    $payment_status = 'All API\'s are unavailable';
	
  } elseif ($check_result === 'e2') {
	
	// this really shouldn't happen...
    $_SESSION[$pubAdd.'-confirms']--;
    $payment_status = 'address is invalid!';
	
  } elseif ($check_result === 'e3') {
	
	// something weird happened...
    $_SESSION[$pubAdd.'-confirms']--;
    $payment_status = 'unexpected error occurred';
	
  } elseif ($check_result === 'e4') {
	
	// not enough funds sent yet...
    $_SESSION[$pubAdd.'-confirms']--;
    $payment_status = 'partial payment received';
	
//  } elseif ( $check_result_unconfirmed === true and $unconfirmed_flag ===false) {
		
		//transaction is broadcasted to the network but not yet confirmed
//		$unconfirmed_flag=true;
//	 $_SESSION[$pubAdd.'-progress'] += 50;
 //	 $payment_status = 'unconfirmed payment verified';
	
	} else {
  
    if ($_SESSION[$pubAdd.'-confirms'] >= $confirm_num)  {
      $payment_status = 'payment verified!';
	  $_SESSION[$pubAdd.'-progress'] = 100;
    } else {
	  $payment_status = 'confirming payment';
    }
  }
  
  $perc_prog = ($_SESSION[$pubAdd.'-confirms'] / $confirm_num) * 100;
  $next_prog = (($_SESSION[$pubAdd.'-confirms']+1) / $confirm_num) * 100; 
  
  if ((($_SESSION[$pubAdd.'-progress'] < $perc_prog) && ($perc_prog > 0))
  || ($_SESSION[$pubAdd.'-progress'] >= 96)) {
    $_SESSION[$pubAdd.'-progress'] = $perc_prog;
  } elseif ($_SESSION[$pubAdd.'-progress'] > $next_prog) {
    $_SESSION[$pubAdd.'-progress'] -= $pinc;
  }
  
  if ($_SESSION[$pubAdd.'-progress'] >= 100) {
    if ($payment_status === 'payment verified!') {
      $_SESSION[$pubAdd.'-progress'] = 100;
	} else {
      $_SESSION[$pubAdd.'-progress'] = 99;
	}
  }

  echo $_SESSION[$pubAdd.'-progress'].':'.$payment_status;
}
?>