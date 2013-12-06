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
	'tplSubscribeForm' => array(
		'type' => 'textfield',
		'value' => 'tpl.Sendex.subscribe.form',
	),
	'tplUnsubscribeForm' => array(
		'type' => 'textfield',
		'value' => 'tpl.Sendex.unsubscribe.form',
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