<?php

require_once dirname(__FILE__, 5) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('sendex_core_path', null, $modx->getOption('core_path') . 'components/sendex/');
require_once $corePath . 'bootstrap.php';
sendexBootstrap($modx);

sxQueueSender::flush($modx, array(
    'limit'     => $modx->getOption('sendex_queue_limit', null, 100, true),
    'logErrors' => true,
));
