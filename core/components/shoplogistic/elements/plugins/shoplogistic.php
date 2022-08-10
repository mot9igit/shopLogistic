<?php
$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
/** @var modX $modx */
switch ($modx->event->name) {
	case 'OnMODXInit':
		$modx->loadClass('msOrder');
		$modx->map['msOrder']['fields']['store_id'] = 0;
		$modx->map['msOrder']['fieldMeta']['store_id'] = [
			'dbtype' => 'int',
			'precision' => 11,
			'phptype' => 'integer',
			'null' => true,
			'default' => 0
		];
		$modx->map['msOrder']['fields']['warehouse_id'] = 0;
		$modx->map['msOrder']['fieldMeta']['warehouse_id'] = [
			'dbtype' => 'int',
			'precision' => 11,
			'phptype' => 'integer',
			'null' => true,
			'default' => 0
		];
		$modx->map['msOrder']['fields']['view_ids'] = '';
		$modx->map['msOrder']['fieldMeta']['view_ids'] = [
			'dbtype' => 'varchar',
			'precision' => 255,
			'phptype' => 'string',
			'null' => true,
			'default' => ''
		];
		$modx->loadClass('msOrderProduct');
		$modx->map['msOrderProduct']['fields']['type'] = '';
		$modx->map['msOrderProduct']['fieldMeta']['type'] = [
			'dbtype' => 'text',
			'phptype' => 'string',
			'null' => true,
			'default' => ''
		];
		break;
	case 'OnLoadWebDocument':
		$scriptProperties = array();
		$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
		$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
		if (!$shopLogistic) {
			return 'Could not load shoplogistic class!';
		}else{
			$shopLogistic->initialize($modx->context->key);
		}
		if ($modx->getPlaceholder($modx->getOption('shoplogistic_phx_prefix').'city'));{
		$content = $shopLogistic->getContent($modx->getPlaceholder($modx->getOption('shoplogistic_phx_prefix').'city'), $modx->resource->id);
		if($content){
			$modx->resource->cacheable = 0;
			$modx->resource->content = $content;
		}
	}

		if(is_dir($modx->getOption('core_path').'components/minishop2/model/minishop2/')) {
			$ms2 = $modx->getService('miniShop2');
			if ($ms2 instanceof miniShop2) {
				$context = $modx->context->key ? $modx->context->key : 'web';
				$ms2->initialize($context, ['json_response' => true]);
				$ms2->order->remove('sl_data');
			}
		}
		break;
	case 'OnDocFormRender':
		$controller->shopLogistic = $shopLogistic;

		$controller->shopLogistic->loadCustomJsCss();

		$modx->regClientStartupHTMLBlock('
            <script type="text/javascript">
                Ext.onReady(function() {
                    shopLogistic.config.richtext = ' . $resource->richtext . ';
                });
            </script>
        ');
		break;
	case 'msOnManagerCustomCssJs':
		if(!empty($scriptProperties['page'])) {
			if($scriptProperties['page'] == 'orders') {

				$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
				$controller->shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');

				$controller->shopLogistic->loadCustomOrderJsCss();


				$modx->controller->addHtml("
        	        <script>
                    Ext.ComponentMgr.onAvailable('minishop2-window-order-update', function(){
                        let orderTab = this.fields.items[2].items
                        let obj = {
                            layout: 'column',
                            defaults: {
                                msgTarget: 'under',
                                border: false
                            },
                            anchor: '100%',
                            items: [
                                { 
                                    columnWidth: 1,
                                    layout: 'form',
                                    items:[
                                        {
                                            title: 'Магазины и склады',
                                            xtype: 'fieldset',
                                            id: 'minishop2-fieldset-tc',
                                            labelAlign: 'top',
                                            autoHeight: true,
                                            border: false,
                                            items: [
                                                {
                                                    xtype: 'shoplogistic-combo-store',
                            						name: 'store_id',
                            						fieldLabel: 'Дилер',
                            						anchor: '100%',
                                                    value: this.record.store_id
                                                },{
                                                    xtype: 'shoplogistic-combo-warehouse',
                            						name: 'warehouse_id',
                            						fieldLabel: 'Дистрибьютор',
                            						anchor: '100%',
                                                    value: this.record.warehouse_id
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                        orderTab.push(obj)
                    });                
                </script>");
			}
		}
		break;
	case 'msOnCreateOrder':

		$order_data = $order->get();
		$sl_data = [];

		if(!empty($order_data['sl_data'])) {
			$sl_data = json_decode($order_data['sl_data'], 1);
		}

		if(!empty($sl_data)) {
			$order_properties = $msOrder->get('properties');
			$order_properties['sl'] = $sl_data;
			$msOrder->set('properties', $order_properties);
			$msOrder->save();
		}

		if(isset($_SESSION['sl_location']['store']['id'])){
			$msOrder->set('store_id', $_SESSION['sl_location']['store']['id']);
			$msOrder->save();
		}

		break;
	case 'msOnChangeOrderStatus':
		if ($status = $modx->getObject('msOrderStatus', array('id' => $status, 'active' => 1))) {
			if ($miniShop2 = $modx->getService('miniShop2')) {
				$miniShop2->initialize($modx->context->key, array(
					'json_response' => true,
				));
				if (!($miniShop2 instanceof miniShop2)) {
					return;
				}

				if($status->id == 2){
					$pls = $order->toArray();
					$tax = $modx->getOption('shoplogistic_tax_percent') / 100;
					$cost = $pls['cost'] * (1 - $tax);
					$store_id = $pls['store_id'];

					// add log
					$balance = $modx->newObject("slStoreBalance");
					$balance->set("store_id", $store_id);
					$balance->set("type", 1);
					$balance->set("value", $cost);
					$balance->set("createdon", date('Y-m-d H:i:s'));
					$balance->set("description", "Начисление за заказ №".$pls['num']);
					$balance->save();

					//add to store
					$store = $modx->getObject("slStores", $store_id);
					if($store){
						$b = $store->get('balance');
						$store->set('balance', $b + $cost);
						$store->save();
					}
				}
			}
		}
		break;
	case 'OnHandleRequest':
		if ($modx->context->get('key') == 'mgr' || $shopLogistic->isAjaxRequestInAssets()) {
			return;
		}
		// !!! add include path parameters
		$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$parse = parse_url($url);
		$path = explode("/", $parse['path']);
		$key = $path[1];
		// Ведущий слэш и первый каталог убираем, проверяем на наличие в компоненте
		unset($path[0]);
		unset($path[1]);
		if(count($path) != 1){
			$page_uri = implode("/", $path);
			$criteria = array(
				"uri" => $page_uri
			);
			$res = $modx->getObject("modResource", $criteria);
		}else{
			$res = $modx->getObject("modResource", $modx->getOption('site_start'));
		}
		$needle_id = explode(",", $modx->getOption("shoplogistic_catalogs"));
		if(count($needle_id)) {
			// local mode
			$id = $res->id;
			$pids = $modx->getParentIds($id, 10, array('context' => 'web'));
			$searcher = false;
			if (in_array($id, $needle_id)) {
				$searcher = true;
			}
			foreach ($pids as $pid) {
				if (in_array($pid, $needle_id)) {
					$searcher = true;
				}
			}
			if ($searcher) {
				if ($city = $shopLogistic->detectCity()) {
					$shopLogistic->setCity($city);
				}
			}
		}else{
			// global mode
			if ($city = $shopLogistic->detectCity()) {
				$shopLogistic->setCity($city);
			}
		}
		break;
	case 'OnPageNotFound':
		/* @var pdoFetch $pdoFetch */
		if (!$pdo = $modx->getService('pdoFetch')) {
			return 'Could not load pdoFetch class!';
		}

		$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$parse = parse_url($url);
		$path = explode("/", $parse['path']);
		$key = $path[1];
		// Ведущий слэш и первый каталог убираем, проверяем на наличие в компоненте
		unset($path[0]);
		unset($path[1]);
		$id = $shopLogistic->getDomainId($key);
		// город найден
		if($id){
			if(count($path) >= 1){
				$page_uri = implode("/", $path);
				$criteria = array(
					"uri" => $page_uri
				);
				//$modx->log(1, print_r($criteria, 1));
				$res = $modx->getObject("modResource", $criteria);
				if(!$res){
					$res = $modx->getObject("modResource", $modx->getOption('site_start'));
				}
			}else{
				$res = $modx->getObject("modResource", $modx->getOption('site_start'));
			}
			$needle_id = explode(",", $modx->getOption("shoplogistic_catalogs"));
			$id = $res->get("id");
			$modx->log(1, $id);
			$pids = $modx->getParentIds($id, 10, array('context' => 'web'));
			$searcher = false;
			if(count($needle_id)){
				// local mode
				if(in_array($id, $needle_id)){
					$searcher = true;
				}
				foreach($pids as $pid){
					if(in_array($pid, $needle_id)){
						$searcher = true;
					}
				}
				if($searcher){
					// формируем плейсхолдеры
					$response = $pdo->getArray('slCityCity', array('key' => $key));
					if (count($response)) {
						$fields = $shopLogistic->getFields($response['id']);
						$modx->setPlaceholders(array_merge($response, $fields), $modx->getOption('shoplogistic_phx_prefix'));
					}
					$modx->sendForward($res->id);
				}else{
					$url = $modx->makeUrl($res->id);
					$modx->sendRedirect($url);
				}
			}else{
				// global mode
				if($res){
					// формируем плейсхолдеры
					$response = $pdo->getArray('slCityCity', array('key' => $key));

					if (count($response)) {
						$fields = $shopLogistic->getFields($response['id']);
						$modx->setPlaceholders(array_merge($response, $fields), $modx->getOption('shoplogistic_phx_prefix'));
					}
					$modx->sendForward($res->id);
				}else{
					$url = $modx->makeUrl($res->id);
					$modx->sendRedirect($url);
				}
			}
		}

		break;
}