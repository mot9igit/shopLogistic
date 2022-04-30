<?php

$_lang['area_shoplogistic_main'] = 'Основные';
$_lang['area_shoplogistic_eshoplogistic'] = 'eShopLogistic';
$_lang['area_shoplogistic_city'] = 'Города и папки';

$_lang['setting_shoplogistic_frontend_css'] = 'СSS-файл для фронта';
$_lang['setting_shoplogistic_frontend_css_desc'] = 'Можно указать тут свой файл или перенести стили в свой css-файл и очистить поле.';
$_lang['setting_shoplogistic_frontend_js'] = 'JS-файл для фронта';
$_lang['setting_shoplogistic_frontend_js_desc'] = 'Можно указать тут свой скрипт или перенести логику в свой js-файл и очистить поле.';

$_lang['setting_shoplogistic_api_key'] = 'Ключ API eShopLogistic';
$_lang['setting_shoplogistic_api_key_desc'] = '<a href="https://eshoplogistic.ru" target="_blank">eshoplogistic.ru</a>';
$_lang['setting_shoplogistic_api_key_dadata'] = 'Ключ API DaData';
$_lang['setting_shoplogistic_api_key_dadata_desc'] = '<a href="https://dadata.ru/" target="_blank">dadata.ru</a>';
$_lang['setting_shoplogistic_secret_key_dadata'] = 'Secret key API DaData';
$_lang['setting_shoplogistic_secret_key_dadata_desc'] = '<a href="https://dadata.ru/" target="_blank">dadata.ru</a>';
$_lang['setting_shoplogistic_default_delivery'] = 'Способ доставки по-умолчанию';
$_lang['setting_shoplogistic_default_delivery_desc'] = 'ID способа доставки MS2, если не получено ни одного результата по другим вариантам.';

$_lang['setting_shoplogistic_curier_delivery'] = 'Способ доставки курьером';
$_lang['setting_shoplogistic_curier_delivery_desc'] = 'ID способа доставки MS2 курьером, у доставки нужен класс обработчик slHandler.';

$_lang['setting_shoplogistic_punkt_delivery'] = 'Способ доставки в пункт выдачи';
$_lang['setting_shoplogistic_punkt_delivery_desc'] = 'ID способа доставки MS2 в пункт выдачи, у доставки нужен класс обработчик slHandler.';

$_lang['setting_shoplogistic_post_delivery'] = 'Способ доставки почтой России';
$_lang['setting_shoplogistic_post_delivery_desc'] = 'ID способа доставки MS2 почтой России, у доставки нужен класс обработчик slHandler.';

$_lang['setting_shoplogistic_regexp_gen_code'] = 'Маска для генерации ключа API';
$_lang['setting_shoplogistic_regexp_gen_code_desc'] = 'sl-/([a-zA-Z0-9]{4-10})/';
$_lang['setting_shoplogistic_open_fields_store'] = 'Поля доступные для редактирование в ЛК Магазина';
$_lang['setting_shoplogistic_open_fields_store_desc'] = 'Список ключей через запятую';
$_lang['setting_shoplogistic_open_fields_warehouse'] = 'Поля доступные для редактирование в ЛК Склада';
$_lang['setting_shoplogistic_open_fields_warehouse_desc'] = 'Список ключей через запятую';

$_lang['setting_shoplogistic_phx_prefix'] = 'Префикс плейсхолдеров';
$_lang['setting_shoplogistic_cityfolder_phx_prefix_desc'] = 'По данному префиксу можно получить доступ к плейсхолдерам';

$_lang['setting_shoplogistic_city_fields'] = 'Поля таблицы';
$_lang['setting_shoplogistic_city_fields_desc'] = 'Поля таблицы городов';

$_lang['setting_shoplogistic_catalogs'] = 'Каталоги, участвующие в данном городе';
$_lang['setting_shoplogistic_catalogs_desc'] = 'Лучше глобально использовать компонент';

$_lang['setting_shoplogistic_km'] = 'Кол-во километров для определения ближайшего города';
$_lang['setting_shoplogistic_km_desc'] = 'Если расстояние больше данного значения, то выберется город по умолчанию. Если данный параметр не нужен, напишите 0';