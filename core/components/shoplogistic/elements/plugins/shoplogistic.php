<?php
/** @var modX $modx */
switch ($modx->event->name) {
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
}