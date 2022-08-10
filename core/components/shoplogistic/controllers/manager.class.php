<?php

class slManagerController extends modExtraManagerController
{
	/** @var shopLogistic $shoplogistic */
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
	 * @param string $script
	 */
	public function addCss($script)
	{
		$script = $script . '?v=' . $this->shopLogistic->version;
		parent::addCss($script);
	}


	/**
	 * @param string $script
	 */
	public function addJavascript($script)
	{
		$script = $script . '?v=' . $this->shopLogistic->version;
		parent::addJavascript($script);
	}


	/**
	 * @param string $script
	 */
	public function addLastJavascript($script)
	{
		$script = $script . '?v=' . $this->shopLogistic->version;
		parent::addLastJavascript($script);
	}


	/**
	 * @param string $key
	 * @param array $options
	 * @param mixed $default
	 * @return mixed
	 */
	public function getOption($key, $options = null, $default = null, $skipEmpty = false)
	{
		$option = $default;
		if (!empty($key) and is_string($key)) {
			if (is_array($options) && array_key_exists($key, $options)) {
				$option = $options[$key];
			} elseif ($options = $this->modx->_userConfig and array_key_exists($key, $options)) {
				$option = $options[$key];
			} else {
				$option = $this->modx->getOption($key);
			}
		}
		if ($skipEmpty and empty($option)) {
			$option = $default;
		}

		return $option;
	}
}