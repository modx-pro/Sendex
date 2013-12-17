<?php
/**
 * Create an Item
 */
class sxSubscriberCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'sxNewsletter';
	public $classKey = 'sxNewsletter';
	public $languageTopics = array('sendex');
	public $permission = 'new_document';


	/**
	 * @return bool
	 */
	public function beforeSet() {

		$required = array('name', 'template');
		foreach ($required as $tmp) {
			if (!$this->getProperty($tmp)) {
				$this->addFieldError($tmp, $this->modx->lexicon('field_required'));
			}
		}

		if ($this->hasErrors()) {
			return false;
		}

		$unique = array('name');
		foreach ($unique as $tmp) {
			if ($this->modx->getCount($this->classKey, array('name' => $this->getProperty($tmp)))) {
				$this->addFieldError($tmp, $this->modx->lexicon('sendex_newsletter_err_ae'));
			}
		}

		$active = $this->getProperty('active');
		$this->setProperty('active', !empty($active) && $active != 'false');

		return !$this->hasErrors();
	}

}

return 'sxSubscriberCreateProcessor';