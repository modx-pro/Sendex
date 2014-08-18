<?php
/**
 * Remove an Newsletter
 */
class sxSubscriberRemoveProcessor extends modProcessor {
	public $classKey = 'sxSubscriber';


	/** {inheritDoc} */
	public function process() {
		if (!$ids = explode(',', $this->getProperty('ids'))) {
			return $this->failure($this->modx->lexicon('sendex_subscribers_err_ns'));
		}

		$subscribers = $this->modx->getIterator($this->classKey, array('id:IN' => $ids));
		/** @var sxSubscriber $subscriber */
		foreach ($subscribers as $subscriber) {
			$subscriber->remove();
		}

		return $this->success();
	}
}

return 'sxSubscriberRemoveProcessor';