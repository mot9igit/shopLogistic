<?php
/** @var modX $modx */
switch ($modx->event->name) {
	case 'OnLoadWebDocument':
		$scriptProperties = array();
		$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
		$shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
		if (!$shopLogistic) {
			return 'Could not load shoplogistic class!';
		}else{
			$shopLogistic->initialize($modx->context->key);
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
		$corePath = $modx->getOption('shoplogistic_core_path', array(), $modx->getOption('core_path') . 'components/shoplogistic/');
		$controller->shopLogistic = $modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');

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

		break;
}