<?php

require_once dirname(__FILE__, 3) . '/model/sendex/sxprocessorinput.class.php';

/**
 * Base mgr processor: permission gate + shared ids parsing (#69).
 */
abstract class sxSendexProcessor extends modProcessor
{
    /** @var string */
    public $permission = 'edit_document';

    /**
     * @return array|null failure response when permission is missing
     */
    protected function failureIfNoPermission()
    {
        if ($this->permission === '' || $this->permission === null) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        return null;
    }

    /**
     * @param string $lexiconKey
     * @return array{0:int[],1:array|null}
     */
    protected function requireIds($lexiconKey)
    {
        $ids = sxProcessorInput::parseIds($this->getProperty('ids'));
        if ($ids === array()) {
            return array(array(), $this->failure($this->modx->lexicon($lexiconKey)));
        }

        return array($ids, null);
    }
}
