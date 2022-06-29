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

$criteria = array(
	"user_id" => $id
);

$out = array();
$out['access_denied'] = 0;
$id = $modx->user->id;
$criteria = array(
	"user_id" => $id,
	"warehouse_id" => $_REQUEST['col_id']
);
$modx->log(1, print_r($criteria, 1));
$whs = $modx->getObject("slWarehouseUsers", $criteria);
if(!$whs){
	$out['access_denied'] = 1;
}

if(!$out['access_denied']){
	$criteria = array(
		"warehouse_id" => $_REQUEST['col_id']
	);

	$stores = $modx->getCollection("slWarehouseStores", $criteria);


	foreach($stores as $store){
		$s = $store->getOne("Store");
		if($s){
			$out['stores'][] = $s->toArray();
		}
	}
}

$output = $pdoFetch->getChunk($tpl, $out);
return $output;