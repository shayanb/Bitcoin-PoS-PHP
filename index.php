<?php
// Typically you would just pass the item ID
// via the URL and in the process-order.php
// page you would extract the item name and
// the item price from a database using that ID.
// But for this example we'll just do it this way.

require_once(dirname(__FILE__).'/sci/config.php');
require_once(dirname(__FILE__).'/lib/common.lib.php');

if (!empty($_POST['coin_amount']) && !($_POST['coin_amount'] == "0")) {
  $coin_amount = $_POST['coin_amount'];
  $coin_type = $_POST['coin_type'];
  $client_type = $_POST['client_type'];
	$note= $_POST['note'];
	$dollar_amount = $_POST['dollar_amount'];
  $item = htmlentities(urlencode('Aunja'));
  
  redirect("sci/process-order.php?btc_amount=$coin_amount&item=$item&c=$coin_type&client=$client_type&dollar_amount=$dollar_amount&note=$note");
  exit;
  
} 
/*elseif ($_POST['coin_amount'] == "0")
		{
			    echo "<h1>Invalid input value</h1>\n";
			    echo "<p>Please select a valid value for input price ( > 0 ) </p>\n";
					echo '<FORM><INPUT Type="button" VALUE="Back" onClick="history.go(-1);return true;"></FORM>';
		}		*/
	else 
{
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pay in Bitcoins</title>
<script src="/scripts/sweet-alert.js"></script> 
<link rel="stylesheet" type="text/css" href="/css/sweet-alert.css">

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
<script src="/scripts/bitcoinprices.js"></script>
<script>
    $(document).ready(function() {
        bitcoinprices.init({

            // Where we get bitcoinaverage data
       //    url: "https://api.bitcoinaverage.com/ticker/all",
			 			url : "https://api.bitcoinaverage.com/ticker/global/all",
            // Which of bitcoinaverages value we use to present prices
           // marketRateVariable: "24h_avg",
            marketRateVariable: "bid",

            // Which currencies are in shown to the user
            currencies: ["CAD", "USD"],

            // Special currency symbol artwork
            symbols: {
                "BTC": "<i class='fa fa-btc'></i>"
            },

            // Which currency we show user by the default if
            // no currency is selected
            defaultCurrency: "CAD",

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
</script>
<script>
        // Manual conversion example
        $(document).on("marketdataavailable", function() {
            var inputAmount = $("#dollar_input");
            var outputAmount = $("#amount_input");
            var inputCurrency = $("#input_currency");
            var outputCurrency = $("#output_currency");

            function updateConversion() {

                // Reset output in the case we run into exception
                outputAmount.val("");

                var input = inputCurrency.val();
                var output = outputCurrency.val();
                var amount = parseFloat(inputAmount.val(), 10);
                try {
                    var val = bitcoinprices.convert(amount, input, output);
                    val = bitcoinprices.formatPrice(val, output, false);
                    outputAmount.val(val);
                } catch(e) {
                    throw e;
                }
            }

            inputAmount.change(updateConversion);
            inputAmount.on("keyup", updateConversion);
            inputCurrency.change(updateConversion);
            outputCurrency.change(updateConversion);

            // Initial take off
            updateConversion();
	        });
						
		</script>
		<link rel="stylesheet" type="text/css" href="/css/main.css">
		
<script>
$(function removeZero() {
    $('#dollar_input').click(function() {
        $(this).val('').unbind('click');
    });
});
</script>

<script>
function checkTextField(field) {
    if (field.value == '') {
			sweetAlert("Oops...", "Price amount cannot be empty!", "error"); }
     else if (field.value == '0') {
			 sweetAlert("Oops...", "Price amount cannot be zero!", "error");
    }
}
</script>

</head>
<body>
	<center>
<?php
  if (empty($_GET['result'])) {
    if (!empty($_COOKIE['tcode'])) {
?> 
<div style="margin-top:10px;padding:5px;background-color:yellow;border:1px solid red;">
  It appears that you did not complete your last order properly. <a href="<?php echo $site_url.$bitsci_url.'payment.php?t='.safe_str($_COOKIE['tcode']).'&amp;c='.safe_str($_COOKIE['tcurr']); ?>" target="_self">Click here</a> to complete the transaction.
</div>
<?php } ?>
<!--<h1>Payment Page</h1> -->

<form name="sale_form" method="post" action="">
<table id="main_table" cellspacing="0">
	<tr>
		<td>
			<h1>Cafe Aunja: Bitcoins Payment</h1>
		</td>
	</tr>
	<tr>
		<td>
			<p>
	  		<div>Bitcoin price: <span id="bitcoin-price" data-btc-price="1.0">1.0 BTC</span></div>
			</p>
		</td>
	</tr>
	<tr>
		<td>
			<table cellspacing="0">
				<tr>
					<td>
						<label>Payment Amount</label><br/>
					</td>
					<td>
						<input class="currency_text" type="text" name="dollar_amount" id="dollar_input" tabindex="1" maxlength="30" value="0" onblur="checkTextField(this);"/>
						<input type="hidden" name="input_currency" id="input_currency" maxlength="30" value="CAD"/>
					</td>
					<td>
						<label>CAD</label><br/>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<input class="currency_text" type="text" name="coin_amount" id="amount_input" maxlength="30" readonly/>
						<input type="hidden" name="output_currency" id="output_currency" maxlength="30" value="BTC"/>
					</td>
					<td>
						<label>BTC</label><br/>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<textarea name="note" id="note" maxlength="200" tabindex="3" placeholder="Notes..."></textarea>
		</td>
	</tr>
<!--	<tr>
		<td>
			<p>
				<label style="font-weight:bold;">Which coin are you paying in?</label><br />
				Bitcoin: <input type="radio" name="coin_type" value="btc" checked="checked" />
				&nbsp;&nbsp;
				Litecoin: <input type="radio" name="coin_type" value="ltc" />
  		</p>
  	<td>
	</tr> -->
	<tr>
		<td>
<!--	<p><label style="font-weight:bold;">Select your connection type:</label><br />
	Normal Client: <input type="radio" name="client_type" value="ncon" checked="checked" />
	&nbsp;&nbsp;
	Tor Client: <input type="radio" name="client_type" value="tcon" />
  </p>
  <p><b>Important:</b> If you are connected through the Tor network or any other service which causes your IP to rapidly change then select the 2nd option. However, if you are not connected to this website via the https protocol then choosing the 2nd option will increase the risk of a session hijacking attack occurring. JavaScript must still be enabled for Tor clients.</p> -->
  		<button id="submit_btn" type="submit">Checkout</button>
		</td>
	</tr>	
</table>
</form>

<?php
  } elseif ($_GET['result'] == 'success') {
    echo "<h1>Transaction Successful!</h1>\n";
		echo '<script> $(window).load(function(){sweetAlert({   
			title: "Great",   
			text: "The Transaction was successful!",   
			type: "success",
			confirmButtonText: "Awesome"},
			function(){    window.location = "/"; });})</script>';
  } elseif ($_GET['result'] == 'cancel') {
    echo "<h1>Transaction Failed!</h1>\n";
    echo "<p>The transaction was cancelled.</p>";
		echo '<script> $(window).load(function(){sweetAlert({   
			title: "Canceled!",   
			text: "The Transaction has been canceled!",   
			type: "error",
			confirmButtonText: "OK"},
			function(){    window.location = "/"; });})</script>';
//		echo '<p><form action='.$site_url.'>    <input type="submit" value="Home"></form></p>';
  }
  echo "</center></body>\n</html>";
}
?>
