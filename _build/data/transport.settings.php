<?php

$settings = [];

$tmp = [
	'export_fields' => [
		'xtype' => 'textfield',
		'value' => 'email',
		'key' => 'sendex_export_fields',
		'area' => 'sendex_main',
	],
	'hide_export_button' => array(
		'xtype' => 'combo-boolean',
		'value' => false,
		'key' => 'sendex_hide_export_button',
		'area' => 'sendex_main',
	),
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
