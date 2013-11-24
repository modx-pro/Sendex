<?php
/**
 * Remove an Newsletter
 */
class sxSubscriberRemoveProcessor extends modObjectRemoveProcessor {
	public $checkRemovePermission = true;
	public $objectType = 'sxSubscriber';
	public $classKey = 'sxSubscriber';
	public $languageTopics = array('sendex');

}

return 'sxSubscriberRemoveProcessor';