<?php

/**
 * Create an Subscriber
 */

class sxSubscriberCreateProcessor extends modProcessor
{
    public $objectType = 'sxSubscriber';
    public $classKey = 'sxSubscriber';
    public $languageTopics = array('sendex');
    public $permission = '';


    /**
     * @return array|string
     */
    public function process()
    {
        $user_id = (int) $this->getProperty('user_id');
        $newsletter_id = (int) $this->getProperty('newsletter_id');

        if (!$user_id) {
            $this->addFieldError('user_id', $this->modx->lexicon('field_required'));
        }
        if (!$newsletter_id) {
            $this->addFieldError('newsletter_id', $this->modx->lexicon('field_required'));
        }
        if ($this->hasErrors()) {
            return $this->failure($this->modx->lexicon('sendex_subscriber_err_save'));
        }

        /** @var modUserProfile $profile */
        $profile = $this->modx->getObject('modUserProfile', array(
            'internalKey' => $user_id,
        ));
        if (!$profile) {
            return $this->failure($this->modx->lexicon('sendex_subscriber_err_email'));
        }

        $email = $profile->get('email');
        if (empty($email) || strpos($email, '@') === false) {
            return $this->failure($this->modx->lexicon('sendex_subscriber_err_email'));
        }

        if (
            $this->modx->getCount($this->classKey, array(
                'newsletter_id' => $newsletter_id,
                'user_id'       => $user_id,
                'email'         => $email,
            ))
        ) {
            return $this->failure($this->modx->lexicon('sendex_subscriber_err_ae'));
        }

        /** @var sxNewsletter $newsletter */
        $newsletter = $this->modx->getObject('sxNewsletter', $newsletter_id);
        if (!$newsletter) {
            return $this->failure($this->modx->lexicon('sendex_newsletter_err_nf'));
        }

        $result = $newsletter->subscribe($user_id, $email);
        if ($result !== true) {
            return $this->failure(
                is_string($result) ? $result : $this->modx->lexicon('sendex_subscriber_err_save')
            );
        }

        return $this->success();
    }
}

return 'sxSubscriberCreateProcessor';
