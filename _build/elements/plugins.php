<?php

return [
    'shopLogistic' => [
        'file' => 'shoplogistic',
        'description' => 'Base functional plugin',
        'events' => [
			'msOnCreateOrder' => [],
			'msOnManagerCustomCssJs' => [],
			'OnDocFormRender' => [],
			'OnHandleRequest' => [],
			'OnLoadWebDocument' => [],
			'OnMODXInit' => [],
			'OnPageNotFound' => [],
        ],
    ],
];