<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/../lib/bit-sci.lib.php');

if (!empty($_GET['sid']) && !empty($_GET['t']) && !empty($_GET['c'])) {
  echo "updateProgress('".bitsci::curl_simple_post($site_url.$bitsci_url.
  'check-status.php?sid='.urlencode($_GET['sid'])."&t=".urlencode($_GET['t']).
  "&c=".urlencode($_GET['c']))."');";
} else {
  echo "updateProgress('invalid parameters sent to ajax_hack.php');";
}
?>