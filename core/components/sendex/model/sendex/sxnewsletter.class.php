<?php
class sxNewsletter extends xPDOSimpleObject {

	/**
	 * Prepares emails and set them to queue
	 *
	 * @return bool|mixed|string
	 */
	public function addQueues() {
		$template = null;
		$params = $this->toArray();
		/** @var modParser $parser */
		$parser = $this->xpdo->getService('parser', $this->xpdo->getOption('parser_class', null, 'modParser'), $this->xpdo->getOption('parser_class_path', null, ''));

		if (!$subscribers = $this->getMany('Subscribers')) {
			return $this->xpdo->lexicon('sendex_newsletter_err_no_subscribers');
		}

		if ($tmp = $this->get('template')) {
			/** @var modTemplate $template */
			$template = $this->xpdo->getObject('modTemplate', $tmp);
		}

		if (!$template || !($template instanceof modTemplate)) {
			return $this->xpdo->lexicon('sendex_newsletter_err_no_template');
		}

		/** @var sxSubscriber $subscriber */
		foreach ($subscribers as $subscriber) {
			$scriptProperties = array(
				'newsletter' => $params,
				'subscriber' => $subscriber->toArray()
			);

			// Get email from user profile, if possible
			/** @var modUser $user */
			if ($subscriber->get('user_id') && $user = $this->xpdo->getObject('modUser', $subscriber->user_id)) {
				/** @var modUserProfile $profile */
				$profile = $user->getOne('Profile');

				// Skip inactive users
				if (!$user->active || $profile->blocked) {continue;}
				// Add user fields
				$scriptProperties['user'] = $user->toArray();
				$scriptProperties['profile'] = $profile->toArray();
			}

			$email = $subscriber->email;
			$subject = !empty($this->email_subject) ? $this->email_subject : 'No subject';
			$from = !empty($this->email_from) ? $this->email_from : $this->xpdo->getOption('emailsender');
			$from_name = !empty($this->email_from_name) ? $this->email_from_name : $this->xpdo->getOption('site_name');
			$email_reply = !empty($this->email_reply) ? $this->email_reply : $from;

			// Process email body
			$template->_cacheable = false;
			$template->_processed = false;
			$template->_output = '';
			$body = $template->process($scriptProperties);

			if ($parser && $parser instanceof modParser) {
				$maxIterations = (integer) $this->xpdo->getOption('parser_max_iterations', null, 10);
				$parser->processElementTags('', $body, true, true, '[[', ']]', array(), $maxIterations);
			}

			/** @var sxQueue $queue */
			$queue = $this->xpdo->newObject('sxQueue');
			$queue->fromArray(array(
				'subscriber_id' => $subscriber->user_id,
				'newsletter_id' => $this->id,
				'email_to' => $email,
				'email_subject' => $subject,
				'email_body' => $body,
				'email_from' => $from,
				'email_from_name' => $from_name,
				'email_reply' => $email_reply,
			));
			$queue->save();
		}

		return true;
	}


	/**
	 * Returns status of user for this newsletter
	 *
	 * @param int $user_id
	 * @param string $email
	 *
	 * @return int
	 */
	public function isSubscribed($user_id = 0, $email = '') {
		$q = $this->xpdo->newQuery('sxSubscriber', array('newsletter_id' => $this->get('id')));

		if (!empty($user_id)) {
			$q->where(array('user_id' => $user_id));
		}
		if (!empty($email)) {
			$q->where(array('email' => $email));
		}

		/** @var sxSubscriber $subscriber */
		if ($subscriber = $this->xpdo->getObject('sxSubscriber', $q)) {
			return $subscriber->id;
		}
		else {
			return 0;
		}
	}


	/**
	 * Method for send subscription link
	 *
	 * @param string $email
	 * @param int $user_id
	 * @param int $linkTTL
	 *
	 * @return bool|string
	 */
	public function checkEmail($email = '', $user_id = 0, $linkTTL = 1800) {
		if (empty($email) && $profile = $this->xpdo->getObject('modUserProfile', array('internalKey' => $user_id))) {
			$email = $profile->get('email');
		}

		if (empty($email) || !preg_match('/.+@.+\..+/i', $email)) {
			return false;
		}
		elseif ($this->isSubscribed($user_id, $email)) {
			return true;
		}

		$hash = sha1(uniqid(sha1($email), true));

		/** @var modRegistry $registry */
		$registry = $this->xpdo->getService('registry', 'registry.modRegistry');
		$instance = $registry->getRegister('user', 'registry.modDbRegister');

		$instance->connect();
		$instance->subscribe('/sendex/subscribe/');
		$instance->send('/sendex/subscribe/',
			array(
				$hash => array(
					'user_id' => $user_id,
					'newsletter_id' => $this->id,
					'email' => $email,
				)
			),
			array(
				'ttl' => $linkTTL
			)
		);

		return $hash;
	}


	/**
	 * Confirms email of user
	 *
	 * @param $hash
	 *
	 * @return bool
	 */
	public function confirmEmail($hash) {
		if (empty($hash)) {return false;}

		/** @var modRegistry $registry */
		$registry = $this->xpdo->getService('registry', 'registry.modRegistry');
		$instance = $registry->getRegister('user', 'registry.modDbRegister');

		$instance->connect();
		$instance->subscribe('/sendex/subscribe/' . $hash);

		$entry = $instance->read(array('poll_limit' => 1));
		if (!empty($entry[0])) {
			$entry = reset($entry);
			if ($this->id != $entry['newsletter_id']) {
				/** @var sxNewsletter $newsletter */
				if ($newsletter = $this->xpdo->getObject('sxNewsletter', array('id' => $entry['newsletter_id'], 'active' => 1))) {
					$newsletter->Subscribe($entry['user_id'], $entry['email']);
				}
				else {
					return false;
				}
			}
			else {
				return $this->Subscribe($entry['user_id'], $entry['email']);
			}
		}

		return false;
	}


	/**
	 * Subscribes user to the newsletter
	 *
	 * @param int $user_id
	 * @param string $email
	 *
	 * @return bool
	 */
	public function Subscribe($user_id = 0, $email = '') {
		if (empty($email) && $profile = $this->xpdo->getObject('modUserProfile', array('internalKey' => $user_id))) {
			$email = $profile->get('email');
		}

		if (empty($email) || !preg_match('/.+@.+\..+/i', $email)) {
			return false;
		}
		elseif ($this->isSubscribed($user_id, $email)) {
			return true;
		}

		$subscriber = $this->xpdo->newObject('sxSubscriber');
		$subscriber->fromArray(array(
			'newsletter_id' => $this->id,
			'user_id' => $user_id,
			'email' => $email
		), '', true, true);

		return $subscriber->save();
	}


	/**
	 * Unsubscribes user from the newsletter
	 *
	 * @param string $code
	 *
	 * @return bool
	 */
	public function unSubscribe($code) {
		if ($subscriber = $this->xpdo->getObject('sxSubscriber', array('code' => $code))) {
			return $subscriber->remove();
		}

		return false;
	}

}
