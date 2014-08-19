<?php
/**
 * Get a list of Queues
 */
class sxQueueGetListProcessor extends modObjectGetListProcessor {
	public $objectType = 'sxQueue';
	public $classKey = 'sxQueue';
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'DESC';


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$c->innerJoin('sxNewsletter', 'sxNewsletter', 'sxNewsletter.id = sxQueue.newsletter_id');
		$c->select($this->modx->getSelectColumns('sxQueue', 'sxQueue'));
		$c->select('sxNewsletter.name as newsletter');

		return $c;
	}


	/**
	 * @param xPDOObject $object
	 *
	 * @return array
	 */
	public function prepareRow(xPDOObject $object) {
		$array = $object->toArray();
		$array['actions'] = array();

		// Send
		$array['actions'][] = array(
			'class' => '',
			'button' => true,
			'menu' => true,
			'icon' => 'send',
			'type' => 'sendQueue',
		);

		// Remove
		$array['actions'][] = array(
			'class' => '',
			'button' => true,
			'menu' => true,
			'icon' => 'trash-o',
			'type' => 'removeQueue',
		);

		return $array;
	}

}

return 'sxQueueGetListProcessor';