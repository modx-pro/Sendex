<?php

require_once dirname(__DIR__) . '/sendexprocessor.class.php';

/**
 * Remove queue rows
 */
class sxQueueRemoveProcessor extends sxSendexProcessor
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

        $queues = $this->modx->getIterator($this->classKey, array('id:IN' => $ids));
        /** @var sxQueue $queue */
        foreach ($queues as $queue) {
            $queue->remove();
        }

        return $this->success();
    }
}

return 'sxQueueRemoveProcessor';
