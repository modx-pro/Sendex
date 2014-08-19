<?php
/**
 * Enable an Newsletter
 */
class sxNewsletterEnableProcessor extends modProcessor {
	public $objectType = 'sxNewsletter';
	public $classKey = 'sxNewsletter';
	public $languageTopics = array('sendex');


	/** {inheritDoc} */
	public function process() {
		if (!$ids = explode(',', $this->getProperty('ids'))) {
			return $this->failure($this->modx->lexicon('sendex_newsletters_err_ns'));
		}

		$newsletters = $this->modx->getIterator($this->classKey, array('id:IN' => $ids, 'active' => false));
		/** @var sxNewsletter $newsletter */
		foreach ($newsletters as $newsletter) {
			$newsletter->set('active', true);
			$newsletter->save();
		}

		return $this->success();
	}

}

return 'sxNewsletterEnableProcessor';
