<?php
class sxNewsletter extends xPDOSimpleObject {


	/**
	 * Prepares emails and set them to queue
	 *
	 * @return bool|mixed|string
	 */
	public function addQueues() {
		$snippet = $template = null;
		$params = $this->toArray();
		/** @var modParser $parser */
		$parser = $this->xpdo->getService('parser', $this->xpdo->getOption('parser_class', null, 'modParser'), $this->xpdo->getOption('parser_class_path', null, ''));

		if ($this->get('snippet')) {
			/** @var modSnippet $snippet */
			$snippet = $this->xpdo->getObject('modSnippet', $this->snippet);
			$snippet->setCacheable(false);
		}
		elseif ($this->get('template')) {
			/** @var modTemplate $template */
			$template = $this->xpdo->getObject('modTemplate', $this->template);
			$template->setCacheable(false);
		}

		if (!$subscribers = $this->getMany('Subscribers')) {
			return $this->xpdo->lexicon('sendex_newsletter_err_no_subscribers');
		}
		/** @var sxSubscriber $subscriber */
		foreach ($subscribers as $subscriber) {
			$scriptProperties = array(
				'newsletter' => $params,
				'user' => $subscriber->toArray()
			);

			if ($snippet && $snippet instanceof modSnippet) {
				$body = $snippet->process($scriptProperties);
			}
			elseif ($template && $template instanceof modTemplate) {
				$body = $template->process($scriptProperties);
			}
			else {
				return 'Could not prepare email';
			}

			if ($parser && $parser instanceof modParser) {
				$maxIterations = (integer) $this->xpdo->getOption('parser_max_iterations', null, 10);
				$parser->processElementTags('', $body, false, false, '[[', ']]', array(), $maxIterations);
				$parser->processElementTags('', $body, true, true, '[[', ']]', array(), $maxIterations);
			}

			// Get email from user profile, if possible
			/** @var modUser $user */
			if (!empty($subscriber->subscriber_id) && $user = $this->xpdo->getObject('modUser', $subscriber->subscriber_id)) {
				/** @var modUserProfile $profile */
				$profile = $user->getOne('Profile');
				if ($user->active && !$profile->blocked) {
					$email = $profile->email;
				}
				// Skip inactive users
				else {
					continue;
				}
			}
			else {
				$email = $subscriber->email;
			}
			$subject = !empty($this->email_subject) ? $this->email_subject : 'No subject';
			$from = !empty($this->email_from) ? $this->email_from : $this->xpdo->getOption('emailsender');
			$from_name = !empty($this->email_from_name) ? $this->email_from_name : $this->xpdo->getOption('site_name');
			$email_reply = !empty($this->email_reply) ? $this->email_reply : $from;

			/** @var sxQueue $queue */
			$queue = $this->xpdo->newObject('sxQueue');
			$queue->fromArray(array(
				'subscriber_id' => $subscriber->id,
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

}