<?php
// to prevent this file being loaded directly #security
if (__FILE__ == $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']) {
  die("Direct access forbidden");
}
?>

<script src="../scripts/bitcoinprices.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>

<script>
(function ($) {

    // Entry point to processing
    $(document).ready(function () {

      bitcoinprices.init({

          // Where we get bitcoinaverage data
        //  url: "https://api.bitcoinaverage.com/ticker/all",
	 			url : "https://api.bitcoinaverage.com/ticker/global/all",

          // Which of bitcoinaverages value we use to present prices
          //marketRateVariable: "24h_avg",
          marketRateVariable: "bid",

          // Which currencies are in shown to the user
          currencies: ["BTC", "CAD"],

          // Special currency symbol artwork
          symbols: {
              "BTC": "<i class='fa fa-btc'></i>"
          },

          // Which currency we show user by the default if
          // no currency is selected
          defaultCurrency: "BTC",

          // How the user is able to interact with the prices
          ux : {
              // Make everything with data-btc-price HTML attribute clickable
              clickPrices : true,

              // Build Bootstrap dropdown menu for currency switching
              menu : true,

              // Allow user to cycle through currency choices in currency:

              clickableCurrencySymbol:  true
          },

          // Allows passing the explicit jQuery version to bitcoinprices.
          // This is useful if you are using modular javascript (AMD/UMD/require()),
          // but for most normal usage you don't need this
          jQuery: jQuery,

          // Price source data attribute
          priceAttribute: "data-btc-price",

          // Price source currency for data-btc-price attribute.
          // E.g. if your shop prices are in USD
          // but converted to BTC when you do Bitcoin
          // checkout, put USD here.
          priceOrignalCurrency: "BTC"

      });
  });
})(jQuery); </script>
		<link rel="stylesheet" type="text/css" href="../css/report.css">

<script>
function logout()
{
document.cookie = 'PrivatePageLogin' + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
location.href = "report.php";
}
</script>

</head>
<body>
 <?php  if (isset($_COOKIE['PrivatePageLogin'])) {

        // php report pages
        $sql = "Select * FROM tx_history;";
        $retval = mysql_query( $sql, $dbhandle );

        if (! $retval) {
          die('Could not retrieve data: ' . mysql_error());
        }

        echo '<div class="Report_table"><table><tr><td>Id</td><td>Date</td><td>Bitcoin Amount</td><td>Sale Dollar Amount</td><td>Note</td><td>Bitcoin Address</td></tr><tr>';
        $totalbtc = 0;
        $totaldollar = 0; //to sum up to total
        // making the table and retrieving data from the database
        while ($row = mysql_fetch_assoc($retval)) {

            echo  '<td>'.$row['index'].
                        '</td><td>'.$row['date'].
                        '</td><td><span data-btc-price='.$row['btc_amount'].' data-price-symbol="on">'.$row['btc_amount'].'</span>'.
                        '</td><td>'. $row['dollar_amount'].' CAD'.
                        '</td><td>'.$row['note'].
                        '</td><td><a href="https://blockchain.info/address/'.$row['pub_add'].'" target="_blank">'.$row['pub_add'].'</a>'.
                        '</td></tr>';
                        $totalbtc += $row['btc_amount'];
                        $totaldollar += $row['dollar_amount'];

        }
        echo '<tr><p><td>Summary</td>
			<td></td>
			<td><span data-btc-price='.$totalbtc.' data-price-symbol="on">'.$totalbtc.'</span></td>
			<td>'.$totaldollar.' CAD </td>
			<td></td>
			<td></td></tr></p>';
        echo "</table></div>";

        ?>
	<br><br>
		<left>
			<input type="button" value="Show Temp Table" onClick="location.href='report.php?temptable=true'" style="float: right;">
		</left>
		<center>
	
	<input type="button" value="Start Over" onClick="location.href='report.php'">
		<input type="button" value="logout" onclick="logout()">
</center>



<?php
} else {
        // if someone tries to load the page without loggin in #security
            echo "Gotcha!";
        }

  if (isset($_GET['temptable']) && isset($_COOKIE['PrivatePageLogin'])) {

            // php report pages
            $sql_temp = "Select * FROM tmp_tx_history;";
            $retval_temp = mysql_query( $sql_temp, $dbhandle );

            if (! $retval_temp) {
              die('Could not retrieve temporary data: ' . mysql_error());
            }
            echo "<br><center>Here are the transactions that are in temporary transaction table, please check with their blockchain.info link if the payment has been received.</center><br>";
            echo '<div class="Report_table"><table><tr><td>Id</td><td>Date</td><td>Bitcoin Amount</td><td>Sale Dollar Amount</td><td>Note</td><td>Bitcoin Address</td></tr><tr>';
            $totalbtc = 0;
            $totaldollar = 0; //to sum up to total
            // making the table and retrieving data from the database
            while ($row = mysql_fetch_assoc($retval_temp)) {

                echo  '<td>'.$row['index'].
                            '</td><td>'.$row['date'].
                            '</td><td><span data-btc-price='.$row['btc_amount'].' data-price-symbol="on">'.$row['btc_amount'].'</span>'.
                            '</td><td>'. $row['dollar_amount'].' CAD'.
                            '</td><td>'.$row['note'].
                            '</td><td><a href="https://blockchain.info/address/'.$row['pub_add'].'" target="_blank">'.$row['pub_add'].'</a>'.
                            '</td></tr>';
                            $totalbtc += $row['btc_amount'];
                            $totaldollar += $row['dollar_amount'];

            }
            echo '<tr><p><td>Summary</td>
	 			<td></td>
	 			<td><span data-btc-price='.$totalbtc.' data-price-symbol="on">'.$totalbtc.'</span></td>
	 			<td>'.$totaldollar.' CAD </td>
	 			<td></td>
	 			<td></td></tr></p>';
            echo "</table></div>";

                        echo '<center><br><br><input type="button" value="Delete Temp Data" onClick=deletefunction();>';
                        echo '<script>
function deletefunction()
{
    var r = confirm("Are you sure you want to delete all the data in the temp table?");
    if (r == true) {
        location.href="report.php?resettemp=true";
    } else {
      location.href="report.php?temptable=true";
    }
}
</script>';

}

if (isset($_GET['resettemp']) && isset($_COOKIE['PrivatePageLogin'])) {
    //delete the transaction from the temp table
    $sql_delete = "DELETE from tmp_tx_history";
    $delete_tmp = mysql_query ($sql_delete,$dbhandle);
    if (! $delete_tmp) {
      die('Could not delete temporary data: ' . mysql_error());
    }
		else {
			echo "DONE!";
		}
    mysql_close($dbhandle);

}

?>


</body>
</html>
