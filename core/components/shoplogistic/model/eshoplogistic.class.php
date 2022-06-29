<?php

/**
 * eShopLogistic Class
 *
 * @package shoplogistic
 */
class eShopLogistic
{
	function __construct(shopLogistic &$sl, modX &$modx, array $config = array())
	{
		$this->sl =& $sl;
		$this->modx =& $modx;
		$this->modx->lexicon->load('shoplogistic:default');

		$corePath = $this->modx->getOption('shoplogistic_core_path', $config, $this->modx->getOption('core_path') . 'components/shoplogistic/');
		$assetsUrl = $this->modx->getOption('shoplogistic_assets_url', $config, $this->modx->getOption('assets_url') . 'components/shoplogistic/');
		$assetsPath = $this->modx->getOption('shoplogistic_assets_path', $config, $this->modx->getOption('base_path') . 'assets/components/shoplogistic/');
		$api_key = $this->modx->getOption('shoplogistic_api_key', $config, '');

		$this->config = array_merge([
			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'processorsPath' => $corePath . 'processors/',

			'connectorUrl' => $assetsUrl . 'connector.php',
			'assetsUrl' => $assetsUrl,
			'assetsPath' => $assetsPath,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',

			'api_key' => $api_key
		], $config);
	}

	public function init(){
		if (!$sl_data = $this->modx->cacheManager->get('shoplogistic')) {
			$sl_data = $this->query('site');
			$this->modx->cacheManager->set('shoplogistic', $sl_data, 3600*24);
		}
		return $sl_data;
	}

	public function query($method='init', $data=[])
	{
		$apiKey = $this->config['api_key'];
		if(empty($apiKey)) {
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'shoplogistic: необходимо указать Ключ API');
			return [];
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://api.eshoplogistic.ru/api/'.$method);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, array_merge($data,['key' => $apiKey]));
		$result = curl_exec($curl);
		curl_close($curl);
		//$this->modx->log(1, print_r($result, 1));
		return json_decode($result,1);
	}

	public function add_toorder($data=[]){
		if($data['data']){
			if ($this->ms2Init()) {
				//$this->modx->log(1, print_r($data, 1));
				$dirty_data = json_decode($data['data'], 1);

				$this->modx->log(1, print_r($dirty_data, 1));
				$method = $dirty_data['service']['method'];
				$service = $dirty_data['service']['main_key'];
				$save_data = [
					'price' => $dirty_data['service'][$service]['price'][$method]['price'],
					'time' => $dirty_data['service'][$service]['price'][$method]['time'],
					'service' => $dirty_data['service'][$service]['name'],
				];
				$save_data['mode'] = $this->modx->lexicon('shoplogistic_frontend_mode_' . $method);
				if($method == 'terminal'){
					$save_data['address'] = $dirty_data['pvz']['code'] . ' || ' . $dirty_data['pvz']['address'];
				}
				if($method == 'door' || $service == 'postrf'){
					$save_data['address'] = $this->modx->lexicon('shoplogistic_frontend_no_address');
				}
				//$this->modx->log(1, print_r($save_data, 1));
				//$this->modx->log(1, json_encode($save_data, JSON_UNESCAPED_UNICODE));
				$this->ms2->order->remove('sl_data');
				$response = $this->ms2->order->add('sl_data', json_encode($save_data, JSON_UNESCAPED_UNICODE));
				//$this->modx->log(1, json_encode($response, JSON_UNESCAPED_UNICODE));
				return array(
					"success" => true,
					"data" => array(
						"re_calc" => 1
					)
				);
			}
		}
	}

	private function ms2Init(){
		if(is_dir($this->modx->getOption('core_path').'components/minishop2/model/minishop2/')) {
			$this->ms2 = $this->modx->getService('miniShop2');
			if ($this->ms2 instanceof miniShop2) {
				$context = $this->modx->context->key ? $this->modx->context->key : 'web';
				$this->ms2->initialize($context, ['json_response' => true]);
				return true;
			}
		}
		return false;
	}

	public function getYaDeliveryPrice($type, $id = 0){
		$url = "https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/check-price";
		$start_coodinats = explode(",", $_SESSION['sl_location']['store']['coordinats']);
		foreach($start_coodinats as $key => $val){
			$start_coodinats[$key] = (float) $val;
		}
		$data = array();
		$data['route_points'] = array(
			0 => array(
				"coordinates" => array((float) $_SESSION['sl_location']['store']['lng'], (float) $_SESSION['sl_location']['store']['lat'])
			),
			1 => array(
				"coordinates" => array((float) $_SESSION['sl_location']['location']['data']['geo_lon'], (float) $_SESSION['sl_location']['location']['data']['geo_lat'])
			)
		);
		if($type == 'card'){
			if($id){
				$product = $this->modx->getObject("modResource", $id);
				$tmp = array();
				if($product){
					$par = json_decode($product->getTVValue("delivery_attributes"), true);
					if($par){
						foreach($par as $p) {
							$tmplr = array();
							$tmplr['weight'] = $p['weight'];
							$tmplr['dimensions'] = explode('*', $p['dimensions']);
							$data['items'][] = array(
								"quantity" => 1,
								"size" => array(
									"length" => str_replace(",", ".", $tmplr['dimensions'][0]),
									"width" => str_replace(",", ".", $tmplr['dimensions'][1]),
									"height" => str_replace(",", ".", $tmplr['dimensions'][2]),
								),
								"weight" => $tmplr['weight']
							);
						}
					}else{
						$q = $this->modx->newQuery('msProductOption', [
							'product_id' => $id,
							'key:IN' => ['length','width','height','netto','brutto']
						]);

						$options = $this->modx->getIterator('msProductOption', $q);

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

						$data['items'][] = array(
							"quantity" => 1,
							"size" => array(
								"length" => (float) str_replace(",", ".", $params['dimensions'][0]) * 0.01,
								"width" => (float) str_replace(",", ".", $params['dimensions'][1]) * 0.01,
								"height" => (float) str_replace(",", ".", $params['dimensions'][2]) * 0.01,
							),
							"weight" => (float) $params['weight']
						);
					}
					$ya_delivery_data = $this->yaDeliveryRequest($url, $data);
					if(isset($ya_delivery_data['code'])){
						$this->yaDeliveryReport($url.' '.$ya_delivery_data['code'].' '.$ya_delivery_data['message']);
						$this->yaDeliveryReport($data);
						return false;
					}else{
						return $ya_delivery_data;
					}
				}
			}
		}
		if($type == 'cart'){

		}
	}

	public function yaDeliveryRequest($url, $data){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
		curl_setopt($ch, CURLOPT_POST, 1);

		$headers = array();
		$headers[] = "Content-Type: application/json";
		$headers[] = "Accept: application/json";
		$headers[] = "Authorization: Bearer AQAAAABhRjNRAAVM1a9aVo-TC0-iuG7YqKTnWoA";
		$headers[] = "Accept-Language: ru";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			$this->modx->log(xPDO::LOG_LEVEL_ERROR,  'YA Delivery Error:' . curl_error($ch));
		}
		curl_close ($ch);

		return json_decode($result, 1);
	}

	public function yaDeliveryReport($text){
		$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($text, 1), array(
			'target' => 'FILE',
			'options' => array(
				'filename' => 'ya_delivery_log.log'
			)
		));
	}
}