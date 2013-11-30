<?php
/**
 * Update an Item
 */
class sxNewsletterUpdateProcessor extends modObjectUpdateProcessor {
	public $objectType = 'sxNewsletter';
	public $classKey = 'sxNewsletter';
	public $languageTopics = array('sendex');
	public $permission = 'update_document';


	/**
	 * @return bool
	 */
	public function beforeSet() {

		$required = array('name');
		foreach ($required as $tmp) {
			if (!$this->getProperty($tmp)) {
				$this->addFieldError($tmp, $this->modx->lexicon('field_required'));
			}
		}

		// We need template or snippet
		if (!$this->getProperty('template') && !$this->getProperty('snippet')) {
			$this->addFieldError('template', $this->modx->lexicon('sendex_newsletter_err_template'));
			$this->addFieldError('snippet', $this->modx->lexicon('sendex_newsletter_err_snippet'));
		}

		if ($this->hasErrors()) {
			return false;
		}

		$unique = array('name');
		foreach ($unique as $tmp) {
			if ($this->modx->getCount($this->classKey, array('name' => $this->getProperty($tmp), 'id:!=' => $this->getProperty('id')))) {
				$this->addFieldError($tmp, $this->modx->lexicon('sendex_newsletter_err_ae'));
			}
		}

		$active = $this->getProperty('active');
		$this->setProperty('active', !empty($active) && $active != 'false');

		return !$this->hasErrors();
	}

}

return 'sxNewsletterUpdateProcessor';