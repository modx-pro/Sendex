<?php

$properties = array();

$tmp = array(
	'id' => array(
		'type' => 'numberfield',
		'value' => '',
	),
	'showInactive' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
	'tplSubscribeAuth' => array(
		'type' => 'textfield',
		'value' => 'tpl.Sendex.subscribe.auth',
	),
	'tplSubscribeGuest' => array(
		'type' => 'textfield',
		'value' => 'tpl.Sendex.subscribe.guest',
	),
	'tplUnsubscribe' => array(
		'type' => 'textfield',
		'value' => 'tpl.Sendex.unsubscribe',
	),
	'tplActivate' => array(
		'type' => 'textfield',
		'value' => 'tpl.Sendex.activate',
	),
);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(
		array(
			'name' => $k,
			'desc' => PKG_NAME_LOWER . '_prop_' . $k,
			'lexicon' => PKG_NAME_LOWER . ':properties',
		), $v
	);
}

return $properties;