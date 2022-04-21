<?php

class shopLogistic
{
	/** @var modX $modx */
	public $modx;


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = [])
	{
		$this->modx =& $modx;
		$corePath = $this->modx->getOption('shoplogistic_core_path', $config, $this->modx->getOption('core_path') . 'components/shoplogistic/');
		$assetsUrl = $this->modx->getOption('shoplogistic_assets_url', $config, $this->modx->getOption('assets_url') . 'components/shoplogistic/');
		$assetsPath = $this->modx->getOption('shoplogistic_assets_path', $config, $this->modx->getOption('base_path') . 'assets/components/shoplogistic/');

		$this->config = array_merge([
			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'processorsPath' => $corePath . 'processors/',
			'version' => '0.0.4',

			'connectorUrl' => $assetsUrl . 'connector.php',
			'actionUrl' => $assetsUrl . 'action.php',
			'assetsUrl' => $assetsUrl,
			'assetsPath' => $assetsPath,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',

			'regexp_gen_code' => $this->modx->getOption('shoplogistic_regexp_gen_code')
		], $config);


		$this->modx->addPackage('shoplogistic', $this->config['modelPath']);
		$this->modx->lexicon->load('shoplogistic:default');

		if ($this->pdoTools = $this->modx->getService('pdoFetch')) {
			$this->pdoTools->setConfig($this->config);
		}
	}

	/**
	 * Initializes component into different contexts.
	 *
	 * @param string $ctx The context to load. Defaults to web.
	 * @param array $scriptProperties Properties for initialization.
	 *
	 * @return bool
	 */
	public function initialize($ctx = 'web', $scriptProperties = array())
	{
		if (isset($this->initialized[$ctx])) {
			return $this->initialized[$ctx];
		}
		$this->config = array_merge($this->config, $scriptProperties);
		$this->config['ctx'] = $ctx;
		$this->modx->lexicon->load('shoplogistic:default');

		if ($ctx != 'mgr' && (!defined('MODX_API_MODE') || !MODX_API_MODE) && !$this->config['json_response']) {
			$config = $this->pdoTools->makePlaceholders($this->config);
			// dadata css
			$this->modx->regClientCSS("https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/css/suggestions.min.css");
			$this->modx->regClientScript("https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/js/jquery.suggestions.min.js");

			// Register CSS
			$css = trim($this->modx->getOption('shoplogistic_frontend_css'));
			if (!empty($css) && preg_match('/\.css/i', $css)) {
				if (preg_match('/\.css$/i', $css)) {
					$css .= '?v=' . substr(md5($this->config['version']), 0, 10);
				}
				$this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
			}

			// Register JS
			$js = trim($this->modx->getOption('shoplogistic_frontend_js'));
			if (!empty($js) && preg_match('/\.js/i', $js)) {
				if (preg_match('/\.js$/i', $js)) {
					$js .= '?v=' . substr(md5($this->config['version']), 0, 10);
				}
				$this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));


				$js_setting = array(
					'cssUrl' => $this->config['cssUrl'] . 'web/',
					'jsUrl' => $this->config['jsUrl'] . 'web/',
					'actionUrl' => $this->config['actionUrl'],
					'dadata_api_key' => $this->modx->getOption('shoplogistic_api_key_dadata'),

					'default_delivery' => $this->modx->getOption('shoplogistic_default_delivery'),
					'post_delivery' => $this->modx->getOption('shoplogistic_post_delivery'),
					'punkt_delivery' => $this->modx->getOption('shoplogistic_punkt_delivery'),
					'curier_delivery' => $this->modx->getOption('shoplogistic_curier_delivery'),

					'regexp_gen_code' => $this->modx->getOption('shoplogistic_regexp_gen_code'),

					'ctx' => $ctx
				);

				$data = json_encode($js_setting, true);
				$this->modx->regClientStartupScript(
					'<script>shoplogisticConfig = ' . $data . ';</script>',
					true
				);
			}
		}
		$load = $this->loadServices($ctx);
		$this->initialized[$ctx] = $load;

		// init shoplogistic
		$this->esl = new eShopLogistic($this, $this->modx);
		$this->esl->init();

		return $load;
	}

	/**
	 * @param string $ctx
	 *
	 * @return bool
	 */
	public function loadServices($ctx = 'web')
	{
		// Default classes
		if (!class_exists('eShopLogistic')) {
			require_once dirname(__FILE__) . '/eshoplogistic.class.php';
		}
		if (!class_exists('Dadata')) {
			require_once dirname(__FILE__) . '/dadata.class.php';
		}
		// link ms2
		if(is_dir($this->modx->getOption('core_path').'components/minishop2/model/minishop2/')) {
			$this->ms2 = $this->modx->getService('miniShop2');
			if ($this->ms2 instanceof miniShop2) {
				$context = $this->config['ctx'];
				$this->ms2->initialize($context);
				return true;
			}
		}
		return true;
	}

	/**
	 * Handle frontend requests with actions
	 *
	 * @param $action
	 * @param array $data
	 *
	 * @return array|bool|string
	 */
	public function handleRequest($action, $data = array())
	{
		$ctx = !empty($data['ctx'])
			? (string)$data['ctx']
			: 'web';
		if ($ctx != 'web') {
			$this->modx->switchContext($ctx);
		}
		$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
		$this->initialize($ctx, array('json_response' => $isAjax));
		switch ($action) {
			case 'get/suggestion':
				if($data['value']){
					$token = $this->modx->getOption('shoplogistic_api_key_dadata');
					$secret = $this->modx->getOption('shoplogistic_secret_key_dadata');
					$this->dadata = new Dadata($token, $secret);
					$this->dadata->init();
					$response = $this->dadata->clean("address", $data['value']);
				}
				break;
			case 'delivery/get_price':
				$s = $data['service'];
				if($data['fias']){
					$data = array(
						"target" => $data['fias']
					);
					$this->esl = new eShopLogistic($this, $this->modx);
					$init = $this->esl->init();
					$resp = $this->esl->query("search", $data);
					//$this->modx->log(1, print_r($resp, 1));
					$services = array();
					foreach($init['data']['services'] as $key => $val){
						$tmp = array(
							"name" => $val["name"],
							"from" => $val["city_code"],
							"to" => $resp["data"][0]["services"][$key],
							"logo" => $val["logo"]
						);
						$services[$key] = $tmp;
					}

					// TODO: link weight and demensions: parameter OFFERS
					$data = array(
						"from" => $services[$s]['from'],
						"to" => $services[$s]['to'],
					);

					$offers = array();
					if($this->ms2){
						$cart = $this->ms2->cart->get();
						foreach($cart as $product){
							$offers[$product['id']] = [
								'article' => $product['id'],
								'name' => $product['id'],
								'count' => $product['count'],
								'price' => $product['price'],
								'weight' => $product['weight'],
								'dimensions' => $product['dimensions'] ?:''
							];
						}
					}

					$data['offers'] = json_encode($offers);

					$resp = $this->esl->query("delivery/".$s, $data);
					//$this->modx->log(1, print_r($resp,1));
					$services[$s]["price"] = $resp['data'];
					$services['main_key'] = $s;
					$response = $services;
				}
				break;
			case 'delivery/add_order':
				$response = $this->esl->add_toorder($data);
				break;
			case 'apikey/generate':
				$response = $this->apikeyGenerate($data);
				break;
			case 'p_filter/set':
				$response = $this->p_filter($data);
				break;
		}
		return $response;
	}

	public function p_filter($data){
		$b_criteria = array(
			'element' => 'sl.profile-products',
			'parents' => 0,
			'limit' => 10,
			'showZeroPrice' => 1,
			'type' => $data['type'],
			'col_id' => $data['col_id'],
			"tpl" => "@FILE chunks/profile_products.tpl",
			'tplPage' => '@INLINE <li class="page-item"><a class="page-link" href="{$href}" data-number="{$pageNo}">{$pageNo}</a></li>',
			'tplPageWrapper' => '@INLINE  <nav><ul class="pagination justify-content-center">{$prev}{$pages}{$next}</ul></nav>',
			'tplPageActive' => '@INLINE <li class="page-item active"><a class="page-link" href="{$href}" data-number="{$pageNo}">{$pageNo}</a></li>',
			'tplPagePrev' => '@INLINE <li class="page-item"><a class="page-link" href="{$href}" aria-label="Previous" data-number="{$pageNo}"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>',
			'tplPageNext' => '@INLINE <li class="page-item"><a class="page-link" href="{$href}" aria-label="Next" data-number="{$pageNo}"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>',
			'tplPagePrevEmpty' => '@INLINE ',
			'tplPageNextEmpty' => '@INLINE '
		);
		if($data['type'] == 'slStores'){
			$obj = 'slStoresRemains';
			$col = "store_id";
		}else{
			$obj = 'slWarehouseRemains';
			$col = "warehouse_id";
		}
		if($data['only_remains']){
			$ids = array();
			$criteria = array(
				$col => $data['col_id']
			);
			$cols = $this->modx->getCollection($obj, $criteria);
			foreach($cols as $col){
				$ids[] = $col->get('product_id');
			}
			//$b_criteria['parents'] = 0;
			//$b_criteria['resources'] = implode(',', $ids);
			$b_criteria['where'][] = "msProduct.id IN (".implode(',', $ids).')';
		}
		if($data['name']){
			if(count($b_criteria['where'])){
				$b_criteria['where'][] = array(
					"pagetitle:LIKE" => '%'.$data['name'].'%',
					"OR:Data.article:LIKE" => '%'.$data['name'].'%',
				);
			}else{
				$b_criteria['where'] = array(
					"pagetitle:LIKE" => '%'.$data['name'].'%',
					"OR:Data.article:LIKE" => '%'.$data['name'].'%',
				);
			}
		}
		//$this->modx->log(1, print_r($b_criteria, 1));
		$out = array();
		//$this->modx->log(1, $data['spage'].'_'.$data['type'].'_'.$data['col_id']);
		$_SESSION['sl_filters'][$data['spage'].'_'.$data['type'].'_'.$data['col_id']] = $b_criteria['where'];
		$out['data'] = $this->modx->runSnippet("pdoPage", $b_criteria);
		$out['pagination'] = $this->modx->getPlaceholder('page.nav');
		$out['total'] = $this->modx->getPlaceholder('page.total');
		$out['topdo'] = 1;
		return $out;
	}

	public function apikeyGenerate($data){
		if($data['type'] && $data['id'] && $data['apikey']){
			$object = $this->modx->getObject($data['type'], $data['id']);
			if($object){
				$object->set('apikey', $data['apikey']);
				$object->save();
				if($data['type'] == 'slStores'){
					$data['type'] = 's';
				}else{
					$data['type'] = 'w';
				}
				return $this->success('', $data);
			}else{
				return $this->error('Объект не найден.');
			}
		}else{
			return $this->error('Некорректные данные.');
		}
	}

	/**
	 * Load custom js & css
	 */
	public function loadCustomOrderJsCss (){
		$this->modx->controller->addCss($this->config['cssUrl'] . 'mgr/shoplogistic.css?v='.$this->config['version']);
		$this->modx->controller->addJavascript($this->config['jsUrl'] . 'mgr/shoplogistic.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/utils.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/combo.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/order.info.js');
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/order.tab.js');

		$this->modx->controller->addHtml('<script>
            Ext.onReady(function() {
                shopLogistic.config = ' . json_encode($this->config) . ';
                shopLogistic.config.connector_url = "' . $this->config['connectorUrl'] . '";
            });
        </script>');

		$this->modx->controller->addLexiconTopic('shoplogistic:default');
	}

	/**
	 * Load custom js & css
	 */
	public function loadCustomJsCss (){
		//$this->modx->log(1, $this->config['version']);

		$this->modx->controller->addCss($this->config['cssUrl'] . 'mgr/shoplogistic.css?v='.$this->config['version']);
		$this->modx->controller->addJavascript($this->config['jsUrl'] . 'mgr/shoplogistic.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/utils.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/misc/combo.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/resource.tab.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/resource.panel.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/resource.grid.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/resource.windows.js?v='.$this->config['version']);
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/order.info.js');
		$this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/order.tab.js');

		$this->modx->controller->addHtml('<script>
            Ext.onReady(function() {
                shopLogistic.config = ' . json_encode($this->config) . ';
                shopLogistic.config.connector_url = "' . $this->config['connectorUrl'] . '";
            });
        </script>');

		$this->modx->controller->addLexiconTopic('shoplogistic:default');
	}

	/**
	 * Get store name by store id
	 * @param type $store_id
	 * @return type
	 */
	public function getStoreNameById($store_id) {
		$response = $this->modx->getObject('slStores', ['id' => $store_id]);

		return $response->name;
	}

	/**
	 * Get warehouse name by warehouse id
	 * @param type $warehouse_id
	 * @return type
	 */
	public function getWarehouseNameById($warehouse_id) {
		$response = $this->modx->getObject('slWarehouse', ['id' => $warehouse_id]);

		return $response->name;
	}

	/**
	 * This method returns an error of the order
	 *
	 * @param string $message A lexicon key for error message
	 * @param array $data .Additional data, for example cart status
	 * @param array $placeholders Array with placeholders for lexicon entry
	 *
	 * @return array|string $response
	 */
	public function error($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => false,
			//'message' => $this->modx->lexicon($message, $placeholders),
			'message' => $message,
			'data' => $data,
		);

		return $this->config['json_response']
			? json_encode($response)
			: $response;
	}


	/**
	 * This method returns an success of the order
	 *
	 * @param string $message A lexicon key for success message
	 * @param array $data .Additional data, for example cart status
	 * @param array $placeholders Array with placeholders for lexicon entry
	 *
	 * @return array|string $response
	 */
	public function success($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => true,
			'message' => $this->modx->lexicon($message, $placeholders),
			'data' => $data,
		);

		return $this->config['json_response']
			? json_encode($response)
			: $response;
	}
}