<?php
/**
 * Update an Item
 */
class sxNewsletterUpdateProcessor extends modObjectUpdateProcessor {
	public $objectType = 'sxNewsletter';
	public $classKey = 'sxNewsletter';
	public $languageTopics = array('sendex');
	public $permission = 'update_document';
}

return 'sxNewsletterUpdateProcessor';