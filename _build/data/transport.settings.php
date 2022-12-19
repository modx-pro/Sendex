<?php

$settings = [];

$tmp = [
	'export_fields' => [
		'value' => 'email',
		'xtype' => 'textfield',
		'area' => 'sendex_main',
		'key' => 'sendex_export_fields',
	],
];

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'sendex_' . $k,
			'namespace' => PKG_NAME_LOWER,
		),
		$v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
