<?php
// Load MODX config
if (file_exists(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php')) {
	require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
}
else {
	require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/config.core.php';
}

require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$modx->addPackage('sendex', MODX_CORE_PATH . 'components/sendex/model/');

$q = $modx->newQuery('sxQueue');
$q->limit($modx->getOption('sendex_queue_limit', null, 100, true));

$queue = $modx->getCollection('sxQueue');
/** @var sxQueue $email */
foreach ($queue as $email) {
	$email->send();
}