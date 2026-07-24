<?php

require_once dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/sendexprocessor.class.php';

class TestSendexIdsProcessor extends sxSendexProcessor
{
    /**
     * @return array
     */
    public function process()
    {
        if ($failure = $this->failureIfNoPermission()) {
            return $failure;
        }
        list($ids, $failure) = $this->requireIds('sendex_queue_err_ns');
        if ($failure) {
            return $failure;
        }

        return $this->success('', array('ids' => $ids));
    }
}
