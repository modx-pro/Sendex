<?php
/**
 * Get a list of Subscribers
 */
class sxSubscriberGetListProcessor extends modObjectGetListProcessor {
	public $objectType = 'sxSubscriber';
	public $classKey = 'sxSubscriber';
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'DESC';


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$c->where(array('newsletter_id' => $this->getProperty('newsletter_id')));
		$c->leftJoin('modUser', 'modUser', 'sxSubscriber.user_id = modUser.id');
		$c->leftJoin('modUserProfile', 'modUserProfile', 'sxSubscriber.user_id = modUserProfile.internalKey');

		$c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
		$c->select('modUser.username, modUserProfile.fullname');

		return $c;
	}

}

return 'sxSubscriberGetListProcessor';