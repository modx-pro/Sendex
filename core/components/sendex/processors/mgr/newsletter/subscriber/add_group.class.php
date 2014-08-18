<?php
/**
 * Add a Subscribers from the UserGroup
 */
class sxSubscriberAddGroupProcessor extends modObjectProcessor {
	public $classKey = 'modUser';
	public $languageTopics = array('sendex');
	public $permission = '';


	/**
	 * @return bool
	 */
	public function process() {
		if (!$group_id = $this->getProperty('group_id')) {
			return $this->failure($this->modx->lexicon('sendex_subscriber_err_group'));
		}
		elseif (!$newsletter_id = $this->getProperty('newsletter_id')) {
			return $this->failure($this->modx->lexicon('sendex_newsletter_err_ns'));
		}
		$errors = array();

		$c = $this->modx->newQuery($this->classKey);
		$c->select($this->modx->getSelectColumns($this->classKey, $this->classKey, '', array('id', 'username')));
		$c->innerJoin('modUserGroupMember', 'UserGroupMembers');
		$c->innerJoin('modUserGroup', 'UserGroup', '`UserGroupMembers`.`user_group` = `UserGroup`.`id` AND `UserGroup`.`id` = ' . $group_id);
		$c->leftJoin('sxSubscriber', 'Subscriber', '`Subscriber`.`user_id` = `modUser`.`id` AND `Subscriber`.`newsletter_id` = ' . $newsletter_id);
		$c->where(array(
			'Subscriber.user_id' => null
		));
		if ($c->prepare() && $c->stmt->execute()) {
			while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
				/** @var modProcessorResponse $response */
				$this->modx->error->reset();
				$response = $this->modx->runProcessor(
					'mgr/newsletter/subscriber/create',
					array(
						'newsletter_id' => $newsletter_id,
						'user_id' => $row['id']
					),
					array(
						'processors_path' => MODX_CORE_PATH . 'components/sendex/processors/'
					)
				);
				if ($response->isError()) {
					$errors[] = $row['username'] . ': ' . $response->getMessage();
				}
			}
		}

		return !empty($errors)
			? $this->failure(implode('<br/>', $errors))
			: $this->success();
	}

}

return 'sxSubscriberAddGroupProcessor';