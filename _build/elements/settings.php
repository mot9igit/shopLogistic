<?php

return [
	'frontend_css' => [
		'xtype' => 'textfield',
		'value' => '[[+cssUrl]]web/shoplogistic.css',
		'area' => 'shoplogistic_main',
	],
	'frontend_js' => [
		'xtype' => 'textfield',
		'value' => '[[+jsUrl]]web/shoplogistic.js',
		'area' => 'shoplogistic_main',
	],
    'api_key' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'shoplogistic_eshoplogistic',
    ],
	'api_key_dadata' => [
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'shoplogistic_eshoplogistic',
	],
	'secret_key_dadata' => [
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'shoplogistic_eshoplogistic',
	],
	'default_delivery' => [
		'xtype' => 'textfield',
		'value' => 1,
		'area' => 'shoplogistic_eshoplogistic',
	],
	'curier_delivery' => [
		'xtype' => 'textfield',
		'value' => 1,
		'area' => 'shoplogistic_eshoplogistic',
	],
	'punkt_delivery' => [
		'xtype' => 'textfield',
		'value' => 1,
		'area' => 'shoplogistic_eshoplogistic',
	],
	'post_delivery' => [
		'xtype' => 'textfield',
		'value' => 1,
		'area' => 'shoplogistic_eshoplogistic',
	]
];