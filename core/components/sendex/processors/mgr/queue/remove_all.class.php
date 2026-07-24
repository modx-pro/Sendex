<?php

require_once dirname(__DIR__) . '/sendexprocessor.class.php';

/**
 * Remove all queue rows
 */
class sxQueueRemoveAllProcessor extends sxSendexProcessor
{
    public $objectType = 'sxQueue';
    public $classKey = 'sxQueue';


    /** {inheritDoc} */
    public function process()
    {
        if ($failure = $this->failureIfNoPermission()) {
            return $failure;
        }

        $this->modx->removeCollection($this->classKey, array());

        return $this->success();
    }
}

return 'sxQueueRemoveAllProcessor';
