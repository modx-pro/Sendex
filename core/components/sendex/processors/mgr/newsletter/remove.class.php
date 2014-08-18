<?php
/**
 * Remove an Newsletters
 */
class sxNewsletterRemoveProcessor extends modProcessor {
	public $classKey = 'sxNewsletter';


	/** {inheritDoc} */
	public function process() {
		if (!$ids = explode(',', $this->getProperty('ids'))) {
			return $this->failure($this->modx->lexicon('sendex_newsletters_err_ns'));
		}

		$subscribers = $this->modx->getIterator($this->classKey, array('id:IN' => $ids));
		/** @var sxSubscriber $subscriber */
		foreach ($subscribers as $subscriber) {
			sleep(1);
			$subscriber->remove();
		}

		return $this->success();
	}

}

return 'sxNewsletterRemoveProcessor';