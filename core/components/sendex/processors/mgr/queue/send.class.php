<?php

require_once dirname(__DIR__) . '/sendexprocessor.class.php';
require_once dirname(__FILE__, 4) . '/model/sendex/sxqueuesender.class.php';

/**
 * Send queue rows by id (mgr).
 */
class sxQueueSendProcessor extends sxSendexProcessor
{
    public $classKey = 'sxQueue';


    /** {inheritDoc} */
    public function process()
    {
        if ($failure = $this->failureIfNoPermission()) {
            return $failure;
        }
        list($ids, $failure) = $this->requireIds('sendex_queue_err_ns');
        if ($failure) {
            return $failure;
        }

        $stats = sxQueueSender::flush($this->modx, array(
            'criteria'    => array('id:IN' => $ids),
            'stopOnError' => true,
        ));

        if ($stats['firstError'] !== null) {
            return $this->failure($stats['firstError']);
        }

        return $this->success();
    }
}

return 'sxQueueSendProcessor';
