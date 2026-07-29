<?php

/**
 * Get an Newsletter
 */

class sxNewsletterGetProcessor extends modObjectGetProcessor
{
    public $objectType = 'sxNewsletter';
    public $classKey = 'sxNewsletter';
    public $languageTopics = array('sendex:default');
    public $permission = 'view_document';

    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return $this->modx->hasPermission('view_sendex')
            || $this->modx->hasPermission('view_document');
    }
}

return 'sxNewsletterGetProcessor';
