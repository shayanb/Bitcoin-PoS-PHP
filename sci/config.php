<?php
// business name
$seller = 'Cafe Aunja';

// full website url (with slash on end)
$site_url = 'http://URL';

// location of bitcoin sci folder from root
$bitsci_url = 'sci/';

// number of confirmations needed (can't be 0)
$confirm_num = 1;

// amount of time between each refresh (in seconds)
$refresh_time = 15;

// amount the progress bar increases with each refresh
$prog_inc = 1;

// payment precision (allow a bit of wiggle room)
$p_variance = 0.000001;

// bitcoin price thousands separator
$t_separator = ',';

// should you receive an email upon confirmation?
$send_email = true;

// email for receiving confirmation notices
$contact_email = 'EMAILADDRESS';

// admin control panel password
//TODO: implement these in the database
// you need the admin pass only the first time to generate the RSA keys
$admin_pass = 'ADMINPASS';

// report pass is for report.php, it shows all the transaction and temp transactions
$report_pass = 'REPORT_PASS';

// this is for superadmin.php, report page with more options on the database
$su_pass = 'SUPERADMIN_PASS';


// security string used for encryption (16 chars)
$sec_str = 'SECRetSTRinG';

// public RSA key used to encrypt private keys
$pub_rsa_key =  'RSA_PUBLIK_KEY';

/////////////////////////////////////
/* IGNORE ANYTHING UNDER THIS LINE */
/////////////////////////////////////
define('CONF_NUM', $confirm_num);
define('SEC_STR', $sec_str);
define('SEP_STR', $t_separator);

// turn on/off error reporting
ini_set('display_errors', 1); 
error_reporting(0);

$app_version = '0.6.2';
?>