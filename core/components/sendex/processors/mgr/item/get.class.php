<?php
/**
 * Get an Item
 */
class SendexItemGetProcessor extends modObjectGetProcessor {
	public $objectType = 'SendexItem';
	public $classKey = 'SendexItem';
	public $languageTopics = array('sendex:default');
}

return 'SendexItemGetProcessor';