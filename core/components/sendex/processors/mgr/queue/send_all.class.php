<?php

require_once dirname(__DIR__) . '/sendexprocessor.class.php';
require_once dirname(dirname(dirname(dirname(__FILE__))))
    . '/model/sendex/sxqueuesender.class.php';

/**
 * Send all queue rows (mgr).
 */
class sxQueueSendAllProcessor extends sxSendexProcessor
{
    public $objectType = 'sxQueue';
    public $classKey = 'sxQueue';


    /** {inheritDoc} */
    public function process()
    {
        if ($failure = $this->failureIfNoPermission()) {
            return $failure;
        }

        $stats = sxQueueSender::flush($this->modx, array(
            'stopOnError' => true,
        ));

        if ($stats['firstError'] !== null) {
            return $this->failure($stats['firstError']);
        }

        return $this->success();
    }
}

return 'sxQueueSendAllProcessor';
