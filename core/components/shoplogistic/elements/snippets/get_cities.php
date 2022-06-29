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

$cities = $modx->getCollection('slCityCity');

foreach($cities as $city){
	$tmp = $city->toArray();
	$pos = array((float) $tmp['lat'], (float) $tmp['lng']);
	$data = json_decode($shopLogistic->getGeoData($pos), 1);
	if(count($data['suggestions'])){
		$tmp['data'] = str_replace("{", "{ ", json_encode($data['suggestions'][0]));
	}
	$out['cities'][] =  $tmp;
}

$output = $pdoFetch->getChunk($tpl, $out);
return $output;