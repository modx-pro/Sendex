<?php

require_once dirname(__FILE__, 5) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';
require_once MODX_CORE_PATH . 'components/sendex/model/sendex/sxqueuesender.class.php';

$modx->addPackage('sendex', MODX_CORE_PATH . 'components/sendex/model/');

sxQueueSender::flush($modx, array(
    'limit'     => $modx->getOption('sendex_queue_limit', null, 100, true),
    'logErrors' => true,
));
