<?php
/**
 * Send an Queue
 */
class sxQueueSendProcessor extends modProcessor {
	public $objectType = 'sxQueue';
	public $classKey = 'sxQueue';


	/** {inheritDoc} */
	public function process() {
		if (!$id = $this->getProperty('id')) {
			return $this->failure($this->modx->lexicon('sendex_queue_err_ns'));
		}
		elseif (!$queue = $this->modx->getObject('sxQueue', $id)) {
			return $this->failure($this->modx->lexicon('sendex_queue_err_nf'));
		}

		/** @var sxQueue $queue */
		$result = $queue->send();
		if ($result !== true) {
			return $this->failure($result);
		}
		else {
			return $this->success();
		}
	}

}

return 'sxQueueSendProcessor';