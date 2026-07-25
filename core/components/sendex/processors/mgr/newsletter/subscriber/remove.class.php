<?php

require_once dirname(__DIR__, 2) . '/sendexprocessor.class.php';

/**
 * Remove subscribers
 */
class sxSubscriberRemoveProcessor extends sxSendexProcessor
{
    public $classKey = 'sxSubscriber';


    /** {inheritDoc} */
    public function process()
    {
        if ($failure = $this->failureIfNoPermission()) {
            return $failure;
        }
        list($ids, $failure) = $this->requireIds('sendex_subscribers_err_ns');
        if ($failure) {
            return $failure;
        }

        $errors = array();
        $subscribers = $this->modx->getIterator($this->classKey, array('id:IN' => $ids));
        /** @var sxSubscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            /** @var sxNewsletter $newsletter */
            $newsletter = $this->modx->getObject('sxNewsletter', $subscriber->get('newsletter_id'));
            if ($newsletter) {
                $result = $newsletter->unSubscribe($subscriber->get('code'), 'mgr');
                if ($result !== true) {
                    $errors[] = is_string($result)
                        ? $result
                        : $this->modx->lexicon('sendex_subscriber_err_remove');
                }
            } elseif (!$subscriber->remove()) {
                $errors[] = $this->modx->lexicon('sendex_subscriber_err_remove');
            }
        }

        return !empty($errors)
            ? $this->failure(implode('<br/>', $errors))
            : $this->success();
    }
}

return 'sxSubscriberRemoveProcessor';
