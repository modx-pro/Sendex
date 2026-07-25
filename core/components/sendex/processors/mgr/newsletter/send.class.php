<?php

require_once dirname(__DIR__) . '/sendexprocessor.class.php';

/**
 * Queue all subscribers and send pending queue rows for one newsletter (#29).
 */
class sxNewsletterSendProcessor extends sxSendexProcessor
{
    public $objectType = 'sxNewsletter';
    public $classKey = 'sxNewsletter';


    /** {inheritDoc} */
    public function process()
    {
        if ($failure = $this->failureIfNoPermission()) {
            return $failure;
        }

        $id = (int) $this->getProperty('id', $this->getProperty('newsletter_id'));
        if ($id <= 0) {
            return $this->failure($this->modx->lexicon('sendex_newsletter_err_ns'));
        }

        /** @var sxNewsletter|null $newsletter */
        if (!$newsletter = $this->modx->getObject('sxNewsletter', $id)) {
            return $this->failure($this->modx->lexicon('sendex_newsletter_err_nf'));
        }

        $result = $newsletter->sendToSubscribers();
        if (!$result['success']) {
            return $this->failure($result['message'], array(
                'queued'  => $result['queued'],
                'sent'    => $result['sent'],
                'skipped' => $result['skipped'],
                'failed'  => $result['failed'],
            ));
        }

        return $this->success($result['message'], array(
            'queued'  => $result['queued'],
            'sent'    => $result['sent'],
            'skipped' => $result['skipped'],
            'failed'  => $result['failed'],
        ));
    }
}

return 'sxNewsletterSendProcessor';
