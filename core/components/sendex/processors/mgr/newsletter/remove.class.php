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

		$newsletters = $this->modx->getIterator($this->classKey, array('id:IN' => $ids));
		/** @var sxNewsletter $newsletter */
		foreach ($newsletters as $newsletter) {
			$newsletter->remove();
		}

		return $this->success();
	}

}

return 'sxNewsletterRemoveProcessor';