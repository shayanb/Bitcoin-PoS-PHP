<?php
// to prevent this file being loaded directly #security
if (__FILE__ == $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']) {
  die("Direct access forbidden");
}
?>

<script src="../scripts/bitcoinprices.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
<?php require_once(dirname(__FILE__).'/../scripts/common.js'); ?>

<script language="JavaScript">
var e_num = 0;
var priv_key = ''

function do_decrypt(pk, ciphertext)
{
	var rsa = new RSAKey();
	var pub_dat = "<?php echo $pub_rsa_key;?>";
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
/* function decrypt_key(key_str, en) {
	var pk_div = document.getElementById('priv_key'+en);
	priv_key = prompt('Private Key:', '');
	pk_div.innerHTML = do_decrypt(priv_key, key_str);
}*/
function decrypt_key(key_str)
{
	document.write(do_decrypt(priv_key, key_str));
}

function get_privkey()
{
	priv_key = prompt('Private Key:', '');
}

function get_balance(address, en)
{
	e_num = en;
	if ($.support.ajax != false) {
		$.ajax({
			url: 'get_balance.php',
			data: 'address='+address,
			dataType: "text",
			success: function (response) {
				updateBalance(response);
			},
			error: function (e) {
				alert('There was a problem getting the balance.');
			}
		});
	} else {
	  alert('Your web browser does not support AJAX!');
	}
}
</script>

		<link rel="stylesheet" type="text/css" href="../css/report.css">

<script>
function logout()
{
document.cookie = 'PrivatePageLogin_sudo' + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
location.href = "superadmin.php";
}
</script>

</head>
<body>
 <?php
require_once dirname(__FILE__).'/dbconnect.php';

 if (isset($_COOKIE['PrivatePageLogin_sudo'])) {

        // php report pages
        $sql = "Select pub_key as read_pub_key,priv_enc,btc_amount FROM wifkeys INNER JOIN tx_history ON wifkeys.pub_key = tx_history.pub_add;";

        $retval = mysql_query( $sql, $dbhandle );

        if (! $retval) {
          die('Could not enter data: ' . mysql_error());
        }

        echo '<div class="Report_table"><table><tr><td>Bitcoin Address</td><td>BTC Amount</td><td>Encrypted Priv Key</td></tr><tr>';
        // making the table and retrieving data from the database
        $totalbtc = 0;
        $priv_keys = array();

        while ($row = mysql_fetch_assoc($retval)) {

            echo  '<td><a href="https://blockchain.info/address/'.$row['read_pub_key'].'" target="_blank">'.$row['read_pub_key'].'</a>'.
                        '</td><td>'.$row['btc_amount'].
                        '</td><td>'.$row['priv_enc'].
                        '</td></tr>';
                        $totalbtc += $row['btc_amount'];
                        $priv_keys[] = $row['priv_enc'];

        }

        echo '<tr><p><td>Summary</td>
			<td><span data-btc-price='.$totalbtc.' data-price-symbol="on">'.$totalbtc.'</span></td>
			<td></td>
			</tr></p>';
        echo "</table></div>";

        ?>
<br><br><center>
	<b>NOTES:</b><br> *Update Database scans the whole database to find the final balances that has not been set, If you have huge number of generated keys, it might take a while. Be patient <br><br> <!--** Only restart the database after you transferred the bitcoins to another bitcoin address, This will make the server to fetch the new balances after you update the database<br> -->
	<input type="button" value="Export Decrypted" onClick="location.href='superadmin.php?decrypt=true'">
	<input type="button" value="Update Database" onClick="location.href='superadmin.php?update=true'">
	<input type="button" value="Reset balances" onClick="location.href='superadmin.php?resetdb=true'"> <br>
	<input type="button" value="Start Over" onClick="location.href='superadmin.php'">
<br><br><input type="button" value="logout" onclick="logout()">

</center>


<?php
    } else {
        // if someone tries to load the page without loggin in #security
            echo "Gotcha!";
        }

?>



<?php
// function to decrypt the private keys and output in the text area for ($x=0; $x<=10; $x++)
  function runDecrypt($priv_keys)
  {
        echo $number_of_keys;
        echo '<script>get_privkey();</script>';

        echo "#Save this output as multibit.key and import it in multibit or blockchain.info<br>";

        foreach ($priv_keys as $item) {

        echo '<script>decrypt_key("'.$item.'");</script> 2014-10-21T23:14:31Z<br>';

  }
    echo "# End of private keys";

}

function update_payments($dbhandle)
{
    // TEMP way to deal with transactions that has not been added to tx_history
    // usually because payment.php has been closed before the transaction is confirmed
    $sql_update = "SELECT pub_key as db_pubkey ,last_balance as db_last_balance , dollar_amount, note FROM wifkeys INNER JOIN tmp_tx_history ON wifkeys.pub_key = tmp_tx_history.pub_add;";

    $retval_update = mysql_query( $sql_update, $dbhandle );
    if (! $retval_update) {
      die('Could not retrieve data: ' . mysql_error());
    }
    echo "Reading all the generated addresses... <br>";
    echo "Checking for not set balances... <br>";

    while ($row = mysql_fetch_assoc($retval_update)) {
        $currency = 'btc';
        $confirms = 1;
        $update_pub_key = $row['db_pubkey'];

        if (empty($row['db_last_balance']) && strlen($row['db_last_balance']) == 0 ) {
            echo "Updating database for: ".$update_pub_key."<br>";
          $balance = bitsci::get_balance(urlencode($row['db_pubkey']), $confirms, $currency);
            // update the last_balance
            $update_query = "UPDATE wifkeys SET last_balance=$balance WHERE pub_key='$update_pub_key';";
            $update_db = mysql_query( $update_query, $dbhandle );

            if (! $update_db) {
            die('Could not update data: ' . mysql_error());
            }

						//fetch NOW dollar amount of the equivalent btc price 
            //$btc_price=floatval(file_get_contents('https://api.bitcoinaverage.com/ticker/global/CAD/bid'));
						//$dollar_amount = $btc_price * $balance;
						
            //update History table too - to add the transaction
            if ($balance != 0) {
            echo "Inserting ".$update_pub_key." with ".$balance." to transaction database...<br>";
						$notes = $row['note'];
						$dollar_amount = $row['dollar_amount'];

            $insert_tx_query = "INSERT INTO tx_history ".
               "(currency,btc_amount, dollar_amount, note, pub_add, file_hash)".
               "VALUES('btc','$balance','$dollar_amount', '$notes', '$update_pub_key', 'NOHASH');";

            $insert_tx_db = mysql_query( $insert_tx_query, $dbhandle );

            if (! $insert_tx_db) {
            die('Could not insert data: ' . mysql_error());
            }

         }
        }

    }
    echo "... DONE! ... ";
    mysql_close($dbhandle);

}

//function reset_db($dbhandle) {}
	//resets the btc_amount in wifkeys


    if (isset($_GET['decrypt']) && isset($_COOKIE['PrivatePageLogin_sudo'])) {
    runDecrypt($priv_keys);
  }

    if (isset($_GET['update']) && isset($_COOKIE['PrivatePageLogin_sudo'])) {
    update_payments($dbhandle);
  }
	
  if (isset($_GET['resetdb']) && isset($_COOKIE['PrivatePageLogin_sudo'])) {
  //reset_db($dbhandle);
}
	

?>

</body>
</html>
