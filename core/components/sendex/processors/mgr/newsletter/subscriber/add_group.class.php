<?php

require_once dirname(__DIR__, 2) . '/sendexprocessor.class.php';

/**
 * Add subscribers from a user group (bulk insert; #70).
 */
class sxSubscriberAddGroupProcessor extends sxSendexProcessor
{
    public $classKey = 'modUser';
    public $languageTopics = array('sendex');

    /**
     * @return array|string
     */
    public function process()
    {
        if ($failure = $this->failureIfNoPermission()) {
            return $failure;
        }

        if (!$group_id = (int) $this->getProperty('group_id')) {
            return $this->failure($this->modx->lexicon('sendex_subscriber_err_group'));
        } elseif (!$newsletter_id = (int) $this->getProperty('newsletter_id')) {
            return $this->failure($this->modx->lexicon('sendex_newsletter_err_ns'));
        }

        /** @var sxNewsletter $newsletter */
        $newsletter = $this->modx->getObject('sxNewsletter', $newsletter_id);
        if (!$newsletter) {
            return $this->failure($this->modx->lexicon('sendex_newsletter_err_nf'));
        }

        $result = $newsletter->subscribeGroup($group_id);

        return $result === true
            ? $this->success()
            : $this->failure(implode('<br/>', $result));
    }
}

return 'sxSubscriberAddGroupProcessor';
