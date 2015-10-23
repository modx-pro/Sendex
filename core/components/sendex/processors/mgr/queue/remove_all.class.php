<?php
/**
 * Remove an Queue
 */
class sxQueueRemoveAllProcessor extends modProcessor {

    public $objectType = 'sxQueue';
    public $classKey = 'sxQueue';

    /** {inheritDoc} */
    public function process() {
        $result = $this->modx->removeCollection($this->classKey);

        if (!$result) {
            return $this->failure('Can not remove queue...');
        }

        return $this->success();
    }
}

return 'sxQueueRemoveAllProcessor';
