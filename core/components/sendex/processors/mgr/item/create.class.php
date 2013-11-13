<?php
/**
 * Create an Item
 */
class SendexItemCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'SendexItem';
	public $classKey = 'SendexItem';
	public $languageTopics = array('sendex');
	public $permission = 'new_document';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$alreadyExists = $this->modx->getObject('SendexItem', array(
			'name' => $this->getProperty('name'),
		));
		if ($alreadyExists) {
			$this->modx->error->addField('name', $this->modx->lexicon('sendex_item_err_ae'));
		}

		return !$this->hasErrors();
	}

}

return 'SendexItemCreateProcessor';