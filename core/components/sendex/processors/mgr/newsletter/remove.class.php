<?php
/**
 * Remove an Item
 */
class sxNewsletterRemoveProcessor extends modObjectRemoveProcessor {
	public $checkRemovePermission = true;
	public $objectType = 'sxNewsletter';
	public $classKey = 'sxNewsletter';
	public $languageTopics = array('sendex');

}

return 'sxNewsletterRemoveProcessor';