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

if($id){
	$product = $modx->getObject("modResource", $id);
	$tmp = array();
	$tmp[$id] = array(
		"price" => $product->get("price"),
		"count" => 1
	);
	if($product){
		$par = json_decode($product->getTVValue("delivery_attributes"), true);
		if($par){
			$params = array();
			foreach($par as $p){
				$tmplr = array();
				$tmplr['weight'] = $p['weight'];
				$tmplr['dimensions'] = $p['dimensions'];
				$params['places'][] = $tmplr;
			}
			$tmp[$id]['places'] = $params['places'];
		}else{
			$q = $modx->newQuery('msProductOption', [
				'product_id' => $id,
				'key:IN' => ['length','width','height','netto','brutto']
			]);

			$options = $modx->getIterator('msProductOption', $q);

			$params = [
				'weight' => 0,
				'dimensions' => []
			];

			foreach($options as $option) {
				switch($option->key) {
					case 'brutto':
						$params['weight'] = $option->value;
						break;
					case 'netto':
						if(empty($params['weight'])) {
							$params['weight'] = $option->value;
						}
						break;
					case 'length':
						$params['dimensions'][0] = (int)$option->value / 10;
						break;
					case 'width':
						$params['dimensions'][1] = (int)$option->value / 10;
						break;
					case 'height':
						$params['dimensions'][2] = (int)$option->value / 10;
						break;
				}
			}

			if(!empty($params['dimensions'])) {
				$tmp[$id]['dimensions'] = implode('*', $params['dimensions']);
			}
			$tmp[$id]['weight'] = $params['weight'];
		}
		// $tmp[$id] = Array ( [dimensions] => 4,1*3,1*3,5 [weight] => 6.57 )
		$offset = $shopLogistic->getDeliveryDateOffset("card", $id);

		$delivery_data = array();
		// get pickup
		$remains = $modx->getObject("slStoresRemains", array("product_id" => $id, "store_id" => $_SESSION['sl_location']['store']['id']));
		if($remains){
			// если в наличии
			$delivery_data['pickup']['price'] = 0;
			$delivery_data['pickup']['term'] = 'сегодня';
			$delivery_data['pickup']['term_default'] = 0;
		}else{
			// если нет в наличии проверяем ближайшую отгрузку +1 день
			$query = $modx->newQuery("slWarehouseShipment");
			$query->where(array(
				"date:>=" => date('Y-m-d H:i:s') ,
				"FIND_IN_SET({$_SESSION['sl_location']['store']['id']}, store_ids) > 0"
			));
			$query->sortby('date','ASC');
			$obj = $modx->getObject("slWarehouseShipment", $query);
			if($obj){
				$delivery_data['pickup']['price'] = 0;
				$newDate = new DateTime($obj->get('date'));
				$delivery_data['pickup']['term_default'] = $newDate->format('Y-m-d H:i:s');
				$newDate->add(new DateInterval('P1D'));
				$delivery_data['pickup']['term'] = $newDate->format('Y-m-d H:i:s');
			}
		}
		$load = $shopLogistic->loadServices('web');
		$shopLogistic->esl = new eShopLogistic($shopLogistic, $modx);
		// get cost pvz (SDEK or DPD)
		// Проверяем сразу СДЭК
		$to = $_SESSION['sl_location']["location"]["data"]? : $_SESSION['sl_location']['data'];

		$to_data = array(
			"target" => $to['city_fias_id']? : $to['fias_id']
		);

		$resp = $shopLogistic->esl->query("search", $to_data);

		$to = $resp['data'][0]['services']['sdek'];

		$city = $modx->getObject("slCityCity", $_SESSION['sl_location']['store']['city']);
		if($city){
			$pos = array((float) $city->get("lat"), (float) $city->get("lng"));
			$dt = json_decode($shopLogistic->getGeoData($pos), 1);
			$data = $dt['suggestions'][0];

			$from = $data['data']['city_fias_id']? : $data['data']['fias_id'];
			$from_data = array(
				"target" => $from
			);
			$resp = $shopLogistic->esl->query("search", $from_data);
			$from = $resp['data'][0]['services']['sdek'];
		}
		$sdek_data = array(
			"from" => $from,
			"to" => $to,
		);
		$sdek_data['offers'] = json_encode($tmp);
		$resp = $shopLogistic->esl->query("delivery/sdek", $sdek_data);
		if($resp){
			$delivery_data['pvz']['price'] = $resp['data']['terminal']['price'];
			$newDate = new DateTime();
			$d = explode("-", $resp['data']['terminal']['time']);
			$days = (int) preg_replace('/[^0-9]/', '', $d[0]);
			$interval = 'P'.$days.'D';
			$newDate->add(new DateInterval($interval));
			$delivery_data['pvz']['term_default'] = $newDate->format('Y-m-d H:i:s');
			$interval = 'P'.$offset.'D';
			$newDate->add(new DateInterval($interval));
			$delivery_data['pvz']['term'] = $newDate->format('Y-m-d H:i:s');
		}

		// get cost delivery (Yandex or DPD)
		$arr = $shopLogistic->esl->getYaDeliveryPrice("card", $id);
		// проверяем наличие Я.Доставки
		if($arr){
			$delivery_data['delivery']['price'] = $arr['price'];
			$newDate = new DateTime();
			$delivery_data['delivery']['term_default'] = $newDate->format('Y-m-d H:i:s');
			$newDate->add(new DateInterval('P'.$offset.'D'));
			//$delivery_data['delivery']['term'] = $newDate->format('Y-m-d H:i:s');
			$delivery_data['delivery']['term'] = 0;
		}else{
			// иначе выставляем postrf
			$to = $_SESSION['sl_location']['location']['data']['postal_code']? : $_SESSION['sl_location']['data']['postal_code'];
			$city = $modx->getObject("slCityCity", $_SESSION['sl_location']['store']['city']);
			if($city){
				$pos = array((float) $city->get("lat"), (float) $city->get("lng"));
				$dt = json_decode($shopLogistic->getGeoData($pos), 1);
				$data = $dt['suggestions'][0];
				$from = $data['data']['postal_code'];
			}
			$arr = $shopLogistic->esl->getPostRfPrice("card", $to, $from, $tmp);
			$delivery_data['delivery']['price'] = round($arr['door']['price']);
			$newDate = new DateTime();
			$newDate->add(new DateInterval('P7D'));
			$delivery_data['delivery']['term_default'] = $newDate->format('Y-m-d H:i:s');
			$newDate->add(new DateInterval('P'.$offset.'D'));
			$delivery_data['delivery']['term'] = $newDate->format('Y-m-d H:i:s');
		}
		//$modx->log(1, 'OFFSET: '.$offset);
		//$modx->log(1, print_r($delivery_data, 1));
	}
}

$output = $pdoFetch->getChunk($tpl, $delivery_data);
return $output;