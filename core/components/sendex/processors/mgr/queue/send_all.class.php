<?php
/**
 * Send an Queue
 */
class sxQueueSendAllProcessor extends modProcessor {
	public $objectType = 'sxQueue';
	public $classKey = 'sxQueue';


	/** {inheritDoc} */
	public function process() {
		$queues = $this->modx->getIterator($this->classKey);
		/** @var sxQueue $queue */
		foreach ($queues as $queue) {
			$result = $queue->send();
			if ($result !== true) {
				return $this->failure($result);
			}
		}

		return $this->success();
	}

}

return 'sxQueueSendAllProcessor';