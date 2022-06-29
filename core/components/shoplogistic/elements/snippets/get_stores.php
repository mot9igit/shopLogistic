<?php
$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
if (!$shopLogistic) {
	return 'Could not load shoplogistic class!';
}

if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
	return false;
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$out = array();

$stores = $shopLogistic->get_nearby('slStores', array($_SESSION['sl_location']['data']['geo_lat'], $_SESSION['sl_location']['data']['geo_lon']), 9999);

foreach($stores as $store){
	$out['stores'][] =  $store;
}

$output = $pdoFetch->getChunk($tpl, $out);
return $output;