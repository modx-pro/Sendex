<?php

require_once dirname(__DIR__) . '/sendexprocessor.class.php';

/**
 * Remove an Newsletters
 */
class sxNewsletterRemoveProcessor extends sxSendexProcessor
{
    public $classKey = 'sxNewsletter';


    /** {inheritDoc} */
    public function process()
    {
        if ($failure = $this->failureIfNoPermission()) {
            return $failure;
        }
        list($ids, $failure) = $this->requireIds('sendex_newsletters_err_ns');
        if ($failure) {
            return $failure;
        }

        $newsletters = $this->modx->getIterator($this->classKey, array('id:IN' => $ids));
        /** @var sxNewsletter $newsletter */
        foreach ($newsletters as $newsletter) {
            $newsletter->remove();
        }

        return $this->success();
    }
}

return 'sxNewsletterRemoveProcessor';
