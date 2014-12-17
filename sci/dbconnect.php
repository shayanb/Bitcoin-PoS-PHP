<?php
$username = "DB_USER";
$password = "DB_PASS";
$hostname = "localhost"; 
$dbname = "DB_NAME";
	
//connection to the database
$dbhandle = mysql_connect($hostname, $username, $password) 
  or die("Unable to connect to MySQL");
//echo "Connected to MySQL<br>";
$db = mysql_select_db($dbname,$dbhandle) 
  or die("Could not select the DB");
?>

