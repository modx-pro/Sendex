<?php
/**
 * Create an Subscriber
 */
class sxSubscriberCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'sxSubscriber';
	public $classKey = 'sxSubscriber';
	public $languageTopics = array('sendex');
	public $permission = '';


	/**
	 * @return bool
	 */
	public function beforeSet() {

		$required = array('user_id', 'newsletter_id');
		foreach ($required as $tmp) {
			if (!$this->getProperty($tmp)) {
				$this->addFieldError($tmp, $this->modx->lexicon('field_required'));
			}
		}

		if ($this->hasErrors()) {
			return $this->modx->lexicon('sendex_subscriber_err_save');
		}

		/** @var modUserProfile $profile */
		if ($profile = $this->modx->getObject('modUserProfile', $this->getProperty('user_id'))) {
			$email = $profile->get('email');
			if (empty($email) || strpos($email, '@') === false) {
				return $this->modx->lexicon('sendex_subscriber_err_email');
			}
			$this->setProperty('email', $email);
		}

		if ($this->modx->getCount($this->classKey, array(
			'newsletter_id' => $this->getProperty('newsletter_id'),
			'user_id' => $this->getProperty('user_id'),
			'email' => $this->getProperty('email')
		))) {
			return $this->modx->lexicon('sendex_subscriber_err_ae');
		}

		return !$this->hasErrors();
	}

}

return 'sxSubscriberCreateProcessor';