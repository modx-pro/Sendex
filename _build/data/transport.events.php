<?php

$events = array();

$tmp = array(
    'sxOnBeforeSubscribe',
    'sxOnSubscribe',
    'sxOnBeforeUnsubscribe',
    'sxOnUnsubscribe',
    'sxOnBeforeAddQueues',
    'sxOnAddQueues',
    'sxOnBeforeQueueSend',
    'sxOnQueueSend',
    'sxOnQueueSendFailed',
    'sxOnQueueFlushComplete',
);

foreach ($tmp as $v) {
    /** @var modEvent $event */
    $event = $modx->newObject('modEvent');
    $event->fromArray(array(
        'name'      => $v,
        'service'   => 6,
        'groupname' => PKG_NAME,
    ), '', true, true);
    $events[] = $event;
}

return $events;
