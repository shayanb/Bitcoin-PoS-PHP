<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../lib/common.lib.php');

session_start();

$currency = (empty($_GET['curr'])) ? 'btc' : strtolower($_GET['curr']);
$confirms = (empty($_GET['conf']) || !is_numeric($_GET['conf'])) ? 1 : round($_GET['conf']);

if (!empty($_GET['address'])) {
  echo bitsci::get_balance(urlencode($_GET['address']), $confirms, $currency);
}

?>