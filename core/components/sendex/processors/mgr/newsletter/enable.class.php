<?php

require_once dirname(__DIR__) . '/sendexprocessor.class.php';

/**
 * Enable an Newsletter
 */
class sxNewsletterEnableProcessor extends sxSendexProcessor
{
    public $objectType = 'sxNewsletter';
    public $classKey = 'sxNewsletter';
    public $languageTopics = array('sendex');


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

        $newsletters = $this->modx->getIterator($this->classKey, array('id:IN' => $ids, 'active' => false));
        /** @var sxNewsletter $newsletter */
        foreach ($newsletters as $newsletter) {
            $newsletter->set('active', true);
            $newsletter->save();
        }

        return $this->success();
    }
}

return 'sxNewsletterEnableProcessor';
