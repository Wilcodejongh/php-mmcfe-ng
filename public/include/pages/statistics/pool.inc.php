<?php

// Make sure we are called from index.php
if (!defined('SECURITY'))
    die('Hacking attempt');

// Fetch data from litecoind
if ($bitcoin->can_connect() === true){
  if (!$dDifficulty = $memcache->get('dDifficulty')) {
    $dDifficulty = $bitcoin->query('getdifficulty');
    $memcache->set('dDifficulty', $dDifficulty);
  }
  if (!$iBlock = $memcache->get('iBlock')) {
    $iBlock = $bitcoin->query('getblockcount');
    $memcache->set('iBlock', $iBlock);
  }
} else {
  $iDifficulty = 1;
  $iBlock = 0;
  $_SESSION['POPUP'][] = array('CONTENT' => 'Unable to connect to pushpool service: ' . $bitcoin->can_connect(), 'TYPE' => 'errormsg');
}

// Top share contributors
$aContributorsShares = $statistics->getTopContributors('shares', 15);

// Top hash contributors
  $aContributorsHashes = $statistics->getTopContributors('hashes', 15);

// Grab the last 10 blocks found
$aBlocksFoundData = $statistics->getBlocksFound(10);
$aBlockData = $aBlocksFoundData[0];

// Estimated time to find the next block
$iCurrentPoolHashrate =  $statistics->getCurrentHashrate();
// Time in seconds, not hours, using modifier in smarty to translate
$iEstTime = $dDifficulty * pow(2,32) / ($iCurrentPoolHashrate * 1000);

// Time since last block
$now = new DateTime( "now" );
if (!empty($aBlockData)) {
  $dTimeSinceLast = ($now->getTimestamp() - $aBlockData['time']);
} else {
  $dTimeSinceLast = 0;
}

// Propagate content our template
$smarty->assign("ESTTIME", $iEstTime);
$smarty->assign("TIMESINCELAST", $dTimeSinceLast);
$smarty->assign("BLOCKSFOUND", $aBlocksFoundData);
$smarty->assign("CONTRIBSHARES", $aContributorsShares);
$smarty->assign("CONTRIBHASHES", $aContributorsHashes);
$smarty->assign("CURRENTBLOCK", $iBlock);
$smarty->assign("LASTBLOCK", $aBlockData['height']);
$smarty->assign("DIFFICULTY", $dDifficulty);
$smarty->assign("REWARD", $config['reward']);

if ($_SESSION['AUTHENTICATED']) {
  $smarty->assign("CONTENT", "authenticated.tpl");
} else {
  $smarty->assign("CONTENT", "default.tpl");
}
?>
