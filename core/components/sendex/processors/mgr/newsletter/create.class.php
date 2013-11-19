<?php
/**
 * Create an Item
 */
class sxNewsletterItemCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'sxNewsletter';
	public $classKey = 'sxNewsletter';
	public $languageTopics = array('sendex');
	public $permission = 'new_document';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$alreadyExists = $this->modx->getObject('sxNewsletter', array(
			'name' => $this->getProperty('name'),
		));
		if ($alreadyExists) {
			$this->modx->error->addField('name', $this->modx->lexicon('sendex_item_err_ae'));
		}

		return !$this->hasErrors();
	}

}

return 'sxNewsletterItemCreateProcessor';