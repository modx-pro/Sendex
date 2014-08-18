<?php
/**
 * Send an Queue
 */
class sxQueueSendProcessor extends modProcessor {
	public $classKey = 'sxQueue';


	/** {inheritDoc} */
	public function process() {
		if (!$ids = explode(',', $this->getProperty('ids'))) {
			return $this->failure($this->modx->lexicon('sendex_queue_err_ns'));
		}

		$queues = $this->modx->getIterator($this->classKey, array('id:IN' => $ids));
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

return 'sxQueueSendProcessor';