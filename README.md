#PHP Bitcoin Point of Sale 
####Customized for [Cafe Aunja], Montreal


Some features:
* Easy to install (PHP and MySQL)
* Real-time BTC/CAD(/USD) conversion
* Uses Blockchain.info and Blockexplorer API (No need for Bitcoind)
* Generates a new address for each transaction (Privacy Preserved)
* RSA Encrypted private keys
* Report page for the merchant, with showing the sales price and the realtime price of bitcoin amount
* Admin page to decrypt and export the private keys associated with the addresses that holds a balance in MultiBit.key format


----------------------------------------
Open source projects used:
* [Bitcoin SCI] : Bitcoin Shopping Card Interface (0.5.4 (beta))
    -   [phpseclib]
    -   [PHP Elliptic Curve library]
* [Sweet Alert] : A Beautiful replacement for javascript's "Alert"
* [bitcoin-prices] : Display bitcoin prices in human-friendly manner in fiat currency using bitcoinaverage.com market data

------------------
Installation
===
1. Download this repo and upload it to your webserver
2. Create the MySQL database with the same schema as the one in DB_SCHEMA.sql and assign the DB user/password
3. Edit sci/config.php and sci/dbconnet.php
  1. Make sure to set admin/report/superadmin password and also Database credentials
  2. Security String can be ANY 16 characters or more.
  3. Leave PUBLIC RSA Key alone at this point. (We will come back to it)
  4. Save and upload config.php and dbconnect.php
4. visit URL/sci/admin.php
5. Login with your __admin__ password from config.php
6. Click RSA KeyGen. Save the private key offline in a safe place, put the public key in config.php
7. (Optional) Add your logo to /sci/img/logo.jpg (or change the refrence in /css/main.css)
Done!

----------------------------
Some clarifications on the admins:

**sci/admin.php**
to be used on the first time to generate the RSA keys, and also in case you want to decrypt or check balance any specific address

**sci/report.php**
A simple report page that shows all the confirmed transactions in the database

![alt text](https://github.com/shayanb/Bitcoin-PoS-PHP/raw/master/docs/report.png "Report Page")

**sci/superadmin.php**
Almost same as report.php but has the option to rescan the whole database to check the balances or just checks the temporary table to see if there was any transactions that has not been added to the final table, also you can __extract all the bitcoin private keys assosiated with the addresses that has balance in them__ with MultiBit.key style.
You can easily save the output in a .key file and import it in [MultiBit] or import it in blockchain.info.

------------
Most of the core functionality is from Bitcoin SCI by Jacob Bruce.
Some notes by bitfreak group:
>The Bitcoin Shopping Cart Interface package is a set of libraries and tools that     enable you to process bitcoin tansactions with only PHP. You can have your own Instant Payment Notification system without the need for a middleman. If you've been wondering how to handle customer payment since MyBitcoin went down, look no further, because this is the safest solution.

>An elliptic curve library written in PHP is used to achieve server side generation of FRESH bitcoin addresses for each customer. The script monitors the status of a payment by making use of the data supplied by blockexplorer.com. As such, there is no need to install a heavy duty service such as bitcoind on your server. The only limitation with this PHP package is that you can't make outgoing payments.

>The bitcoin private keys are now encrypted using RSA public-key cryptography technology. This means that the bitcoins keys are encrypted with a public RSA key, but they can only be decrypted with a private RSA key. So even if a hacker gains access to your bitcoin keys, they wont be able to decrypt that data unless they have your private RSA key. You can manage your keys by visiting the sci/admin.php script.

>The SCI package comes with a simple example to give you an idea about how to generate new keys and initiate a new payment through the Bitcoin Payment Gateway. This is NOT full shopping cart software, you would typically use this script to offer Bitcoins as one method of payment. The sci/config.php file needs to be modified to work properly on your website. You may also need to customize the following files:

>sci/process-order.php and 
>sci/ipn-control.php

>**Note:** PHP 5.3 or later (earlier versions of PHP should work but will not support alt-coins)
NOTE: if you do not have 5.3 installed and wish to use BitcoinSCI, open up lib/bitcoin.lib.php and change line 38 and 42 from return static:: to return self::
PHPExtension BCMath must be installed (most webhosts have it enabled by default)

----------------------------------------------
Screenshots
===
![alt text](https://github.com/shayanb/Bitcoin-PoS-PHP/raw/master/docs/First_View.png "First View - bitcoin-prices.js realtime price CAD/USD")

![alt text](https://github.com/shayanb/Bitcoin-PoS-PHP/raw/master/docs/Payment_View.png "Payment View - Bitcoin SCI")

![alt text](https://github.com/shayanb/Bitcoin-PoS-PHP/raw/master/docs/Success_View.png "Success View - SweetAlert")

-----------------------------------------------
This project was done to meet client's requirments, most of the funtionalities have the potencial to be a lot more complete or have another model for implementation (such as admin/report view)

Contributions are more than welcome.

1ARH4G6BCKM8xoFucEtaKP3Vq5Ahr7dqcv

### Todo's

 - __Fast Confirmation__, check blockchain.info API with Z0ro confirmation (who is going to do a successful double spend for a coffee?)
 - One complete admin panel, preferebly with a seperate report page
 - Nicer User Interface
 - __BIP32__ for address generation

License
----

GNU General Public License v2 (GPL-2)

You may copy, distribute and modify the software as long as you track changes/dates of in source files and keep all modifications under GPL. You can distribute your application using a GPL library commercially, but you must also disclose the source code.

[Bitcoin SCI]:http://bitfreak.info/?page=tools&t=bitsci
[Sweet Alert]:http://tristanedwards.me/sweetalert
[phpseclib]:http://phpseclib.sourceforge.net/
[PHP Elliptic Curve library]:http://matejdanter.com/
[Cafe Aunja]:http://blog.theshayan.com/2014/10/23/have-your-coffee-with-bitcoin/
[Multibit]: https://multibit.org/
[bitcoin-prices]:https://github.com/miohtama/bitcoin-prices
