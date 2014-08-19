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
		$c->leftJoin('sxSubscriber', 'Subscribers');
		$c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
		$c->select($this->modx->getSelectColumns('modTemplate', 'Template', '', array('templatename')));
		$c->select('COUNT(`Subscribers`.`id`) as `subscribers`');
		$c->groupby($this->classKey . '.id');

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
		$array['actions'] = array();

		// Update
		$array['actions'][] = array(
			'class' => '',
			'button' => true,
			'menu' => true,
			'icon' => 'edit',
			'type' => 'updateNewsletter',
		);
		// Disable
		if (empty($array['active'])) {
			$array['actions'][] = array(
				'class' => '',
				'button' => true,
				'menu' => true,
				'icon' => 'check',
				'type' => 'enableNewsletter',
			);
		}
		// or Enable
		else {
			$array['actions'][] = array(
				'class' => '',
				'button' => true,
				'menu' => true,
				'icon' => 'power-off',
				'type' => 'disableNewsletter',
			);
		}
		// Remove
		$array['actions'][] = array(
			'class' => '',
			'button' => true,
			'menu' => true,
			'icon' => 'trash-o',
			'type' => 'removeNewsletter',
		);

		return $array;
	}

}

return 'sxNewsletterGetListProcessor';