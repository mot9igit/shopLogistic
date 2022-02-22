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
}