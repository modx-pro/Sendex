<?php

/**
 * Remove an Queue
 */
class sxQueueRemoveAllProcessor extends modProcessor {
	public $objectType = 'sxQueue';
	public $classKey = 'sxQueue';


	/** {inheritDoc} */
	public function process() {
		$this->modx->removeCollection($this->classKey, array());

		return $this->success();
	}
}

return 'sxQueueRemoveAllProcessor';
