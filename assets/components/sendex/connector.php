<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('sendex_core_path', null, $modx->getOption('core_path') . 'components/sendex/');
require_once $corePath . 'model/sendex/sendex.class.php';
$modx->sendex = new Sendex($modx);

$modx->lexicon->load('sendex:default');

/* handle request */
$path = $modx->getOption('processorsPath', $modx->sendex->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
	'processors_path' => $path,
	'location' => '',
));