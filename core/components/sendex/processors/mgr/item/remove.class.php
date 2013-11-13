<?php
/**
 * Remove an Item
 */
class SendexItemRemoveProcessor extends modObjectRemoveProcessor {
	public $checkRemovePermission = true;
	public $objectType = 'SendexItem';
	public $classKey = 'SendexItem';
	public $languageTopics = array('sendex');

}

return 'SendexItemRemoveProcessor';