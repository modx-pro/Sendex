<?php
/**
 * Get a list of Newsletters
 */
class sxNewsletterGetListProcessor extends modObjectGetListProcessor {
	public $objectType = 'sxNewsletter';
	public $classKey = 'sxNewsletter';
	public $defaultSortField = 'id';
	public $defaultSortDirection = 'DESC';


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$c->leftJoin('modTemplate', 'Template');
		$c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
		$c->select($this->modx->getSelectColumns('modTemplate', 'Template', '', array('templatename')));

		if ($query = $this->getProperty('query')) {
			$c->where(array(
				'name:LIKE' => "%$query%",
				'OR:description:LIKE' => "%$query%"
			));
		}
		if ($this->getProperty('combo')) {
			$c->where(array('active' => 1));
		}
		return $c;
	}


	/**
	 * @param xPDOObject $object
	 *
	 * @return array
	 */
	public function prepareRow(xPDOObject $object) {
		$array = $object->toArray();

		return $array;
	}

}

return 'sxNewsletterGetListProcessor';