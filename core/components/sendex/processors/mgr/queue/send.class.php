<?php

require_once dirname(dirname(dirname(dirname(__FILE__))))
    . '/model/sendex/sxqueuesender.class.php';

/**
 * Send queue rows by id (mgr).
 */
class sxQueueSendProcessor extends modProcessor
{
    public $classKey = 'sxQueue';


    /** {inheritDoc} */
    public function process()
    {
        $ids = explode(',', (string) $this->getProperty('ids'));
        if (!$ids || $ids === array('')) {
            return $this->failure($this->modx->lexicon('sendex_queue_err_ns'));
        }

        $stats = sxQueueSender::flush($this->modx, array(
            'criteria'    => array('id:IN' => $ids),
            'stopOnError' => true,
        ));

        if ($stats['firstError'] !== null) {
            return $this->failure($stats['firstError']);
        }

        return $this->success();
    }
}

return 'sxQueueSendProcessor';
