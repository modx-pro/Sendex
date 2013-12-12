<?php

$chunks = array();

$tmp = array(
	'tpl.Sendex.subscribe.form' => array(
		'file' => 'subscribe.form',
		'description' => '',
	),
	'tpl.Sendex.unsubscribe.form' => array(
		'file' => 'unsubscribe.form',
		'description' => '',
	),
	'tpl.Sendex.subscribe.activate' => array(
		'file' => 'subscribe.activate',
		'description' => '',
	),
);

foreach ($tmp as $k => $v) {
	/* @avr modChunk $chunk */
	$chunk = $modx->newObject('modChunk');
	$chunk->fromArray(array(
		'id' => 0,
		'name' => $k,
		'description' => @$v['description'],
		'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/chunk.'.$v['file'].'.tpl'),
		'static' => BUILD_CHUNK_STATIC,
		'source' => 1,
		'static_file' => 'core/components/'.PKG_NAME_LOWER.'/elements/chunks/chunk.'.$v['file'].'.tpl',
	),'',true,true);

	$chunks[] = $chunk;
}

unset($tmp);
return $chunks;