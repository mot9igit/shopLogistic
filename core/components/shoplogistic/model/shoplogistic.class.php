<?php

require_once(dirname(__FILE__) . '/eshoplogistic.class.php');

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
			'version' => '0.0.1',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
			'assetsPath' => $assetsPath,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('shoplogistic', $this->config['modelPath']);
        $this->modx->lexicon->load('shoplogistic:default');
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
}