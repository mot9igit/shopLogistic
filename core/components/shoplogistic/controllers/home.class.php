<?php

/**
 * The home manager controller for shopLogistic.
 *
 */
class shopLogisticHomeManagerController extends modExtraManagerController
{
    /** @var shopLogistic $shopLogistic */
    public $shopLogistic;


    /**
     *
     */
    public function initialize()
    {
		$corePath = $this->modx->getOption('shoplogistic_core_path', array(), $this->modx->getOption('core_path') . 'components/shoplogistic/');
		$this->shopLogistic = $this->modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['shoplogistic:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('shoplogistic');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->shopLogistic->config['cssUrl'] . 'mgr/shoplogistic.css');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/shoplogistic.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/combo.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.window.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/misc/default.grid.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores.grid.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/stores.windows.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/storeusers.grid.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/storeusers.windows.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouse.grid.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouse.windows.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouseusers.grid.js');
		$this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/warehouseusers.windows.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->shopLogistic->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        shopLogistic.config = ' . json_encode($this->shopLogistic->config) . ';
        shopLogistic.config.connector_url = "' . $this->shopLogistic->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "shoplogistic-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="shoplogistic-panel-home-div"></div>';

        return '';
    }
}