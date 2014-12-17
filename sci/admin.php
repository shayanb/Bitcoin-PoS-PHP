<?php
// this script is used to descrypt private bitcoin keys using the RSA algorithm
// it could be easily edited to pull information from a database
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');

// start session
session_start();

if (!empty($_GET['page']) && ($_GET['page'] === 'logout')) {
  // clear the session
  session_unset();
  session_destroy();
  
  // NOW LOGGED OUT - goto home page
  header('Location: admin.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!empty($_POST['admin_pass']) && ($_POST['admin_pass'] === $admin_pass)) {
    $_SESSION['admin_valid'] = true;
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow">
<title>Bitcoin Payment Gateway</title>
<?php require_once(dirname(__FILE__).'/../scripts/common.js'); ?>
<style>
body {
	margin:0px;
	padding:10px;
	font-size:12px;
	font-family:Verdana, Geneva, sans-serif;
}

.key_con {
	margin:5px;
	padding:5px;
	height:135px;
	width:600px;
	border:1px solid black;
	background-color:#C4C5C6;
}

.key_box {
	margin:5px;
	padding:2px;
	width:584px;
	height:35px;
	border:1px solid black;
	overflow:scroll;
	white-space:nowrap;
	background-color:#939494;
}

.key_title {
	margin:5px;
}
</style>
<script language="JavaScript">
var e_num = 0;

function do_decrypt(pk, ciphertext) {

	var rsa = new RSAKey();
	var pub_dat = '<?php echo $pub_rsa_key; ?>';
	var pri_dat = pk.split(':');

	var n = pub_dat;
	var d = pri_dat[0];
	var p = pri_dat[1];
	var q = pri_dat[2];
	var dp = pri_dat[3];
	var dq = pri_dat[4];
	var c = pri_dat[5];

	rsa.setPrivateEx(n, '10001', d, p, q, dp, dq, c);

	var res = rsa.decrypt(ciphertext);

	if (res == null) {
		return "*** Invalid Ciphertext ***";
	} else {
		return res;
	}
}

function decrypt_key(key_str, en) {
	var pk_div = document.getElementById('priv_key'+en);
	priv_key = prompt('Private Key:', '');
	pk_div.innerHTML = do_decrypt(priv_key, key_str);
}

function gen_keys() {
  var rsa = new RSAKey();
  var e = '10001';
  rsa.generate(parseInt(document.kgf.bits.value), e);
  
  n_value = rsa.n.toString(16);
  d_value = rsa.d.toString(16);
  p_value = rsa.p.toString(16);
  q_value = rsa.q.toString(16);
  dmp1_value = rsa.dmp1.toString(16);
  dmq1_value = rsa.dmq1.toString(16);
  coeff_value = rsa.coeff.toString(16);
  
  document.getElementById('pub_key').innerHTML = n_value;
  document.getElementById('priv_key').innerHTML = d_value+':'+p_value+':'+q_value+':'+dmp1_value+':'+dmq1_value+':'+coeff_value;
}

function updateBalance(response) {
	document.getElementById('pah'+e_num).innerHTML = 'Bitcoin Address ('+response+' BTC)';
	hideElement('pal'+e_num);
}

function get_balance(address, en) {
	e_num = en;
	if ($.support.ajax != false) {
		$.ajax({
			url: 'get_balance.php',
			data: 'address='+address,
			dataType: "text",
			success: function(response) {
				updateBalance(response);
			},
			error: function(e) {
				alert('There was a problem getting the balance.');
			}
		});
	} else {
	  alert('Your web browser does not support AJAX!');
	}
}
</script>
</head>
<body>

<?php
if (!empty($_SESSION['admin_valid']) && ($_SESSION['admin_valid'] === true)) {

  if (empty($_GET['page'])) {
  
    echo "<h1>SCI Admin Panel</h1>";
    echo "<p>Select an option:</p>";
	echo "<p><a href='admin.php?page=keys'>LIST KEYS</a><br />".
	     "<a href='admin.php?page=rsagen'>RSA KEYGEN</a><br />".
				"<a href='report.php'>Report Page(accessible Seperately)</a></p>".
			"<a href='report_su.php'>Super User Report (Export key functionality)</a></p>".
		 "<a href='admin.php?page=logout'>LOGOUT</a></p>";
		 
  } elseif ($_GET['page'] === 'keys') {

    if (file_exists(dirname(__FILE__).'/wif-keys.csv')) {
  
      $wif_keys = explode(',', file_get_contents(dirname(__FILE__).'/wif-keys.csv'));
      $num_keys = count($wif_keys);
  
      if (count(explode(':', $wif_keys[0])) == 2) {

        for ($i=0;$i<$num_keys;$i++) {
          $key_data = explode(':', $wif_keys[$i]);
	      if (count($key_data) == 2) {
	        $key_data[0] = trim($key_data[0]);
	        $key_data[1] = trim($key_data[1]);
?>

  <div class='key_con'>
    <b class="key_title">Private Key</b>
    <a href="#" onClick="decrypt_key('<?php echo $key_data[0]; ?>', <?php echo $i; ?>)">(decrypt)</a>
    <div id='priv_key<?php echo $i; ?>' class='key_box'>
      <?php echo $key_data[0]; ?>
    </div>
    <b id="pah<?php echo $i; ?>" class="key_title">Bitcoin Address</b>
    <a id="pal<?php echo $i; ?>" href="#" onClick="get_balance('<?php echo $key_data[1]; ?>', <?php echo $i; ?>);">(get balance)</a>
    <div id='pub_key<?php echo $i; ?>' class='key_box'>
      <?php echo $key_data[1]; ?>
    </div>
  </div>
	
<?php
	      }
	    }
		echo "<p><a href='admin.php'>GO BACK</a></p>";
      } else {
        echo "<p>Your wif-keys.csv file is empty.</p>";
      }
    } else {
      echo "<p>Your wif-keys.csv file does not exist.</p>";
    }
  } elseif (!empty($_GET['page']) && ($_GET['page'] === 'rsagen')) {
?>

<h1>RSA Key Generator</h1>

<div style="margin:5px;">

  <form name="kgf">
    Bits: <input type="text" id="bits" value="1024" maxlength="30" />
	<input type="button" value="generate" onClick="gen_keys();" />
  </form><br />

  <div style="margin:5px;">
    <b>Public Key:</b>
    <div style="width:400px;height:50px;overflow:auto;border:1px solid black;">
      <p id='pub_key'>&nbsp;<p/>
    </div>
    <br />
    <b>Private Key:</b>
    <div style="width:400px;height:50px;overflow:auto;border:1px solid black;">
      <p id='priv_key'>&nbsp;<p/>
    </div>
  </div>

</div>

<div style='width:400px;'>

  <p><b>Information:</b> after you generate a public and private key, edit the SCI Settings and set the value of $pub_rsa_key to the public key. The public key is only used to encrypt data, only the private key can decrypt data encrypted with the public key.</p>

  <p>The private key must be stored offline, or for the most security you could write or print it out. When you are viewing your bitcoin keys in the admin panel you will see an option to decrypt the private bitcoin key. The decryption happens client-side with JavaScript.</p>

  <p>When you choose to decrypt the private bitcoin key attached to an order you will be required to input the private key generated here. If you lose that private key <u>you wont be able to access your BTC</u> because you wont be able to decrypt the private bitcoin keys.</p>
  
</div>

<p><a href='admin.php'>GO BACK</a></p>

<?php
  }
} else {
?>

<h1>Admin Login</h1>

<form name="login_form" method="post" action="" enctype="application/x-www-form-urlencoded">
  <input type="password" name="admin_pass" size="20" maxlength="99" value="" />
  <input type="submit" value="login" />
</form>

<?php } ?>
</body>
</html>