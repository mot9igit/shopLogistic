<?php

return [
    'shopLogistic' => [
        'file' => 'shoplogistic',
        'description' => 'Base functional plugin',
        'events' => [
            'OnDocFormRender' => [],
			'OnLoadWebDocument' => [],
			'msOnManagerCustomCssJs' => [],
			'msOnCreateOrder' => [],
			'OnHandleRequest' => [],
			'OnPageNotFound' => [],
        ],
    ],
];