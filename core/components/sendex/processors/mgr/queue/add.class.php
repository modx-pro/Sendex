<?php

require_once dirname(__DIR__) . '/sendexprocessor.class.php';

/**
 * Add a list of Queues
 */
class sxQueueAddProcessor extends sxSendexProcessor
{
    public $objectType = 'sxQueue';
    public $classKey = 'sxQueue';


    /** {inheritDoc} */
    public function process()
    {
        if ($failure = $this->failureIfNoPermission()) {
            return $failure;
        }

        if (!$id = $this->getProperty('newsletter_id')) {
            return $this->failure($this->modx->lexicon('sendex_newsletter_err_ns'));
        } elseif (!$newsletter = $this->modx->getObject('sxNewsletter', $id)) {
            return $this->failure($this->modx->lexicon('sendex_newsletter_err_nf'));
        }

        /** @var sxNewsletter $newsletter */
        $result = $newsletter->addQueues();
        if (!is_int($result)) {
            return $this->failure($result);
        }

        return $this->success(
            $this->modx->lexicon('sendex_newsletter_queues_added', array('count' => $result)),
            array('count' => $result)
        );
    }
}

return 'sxQueueAddProcessor';
