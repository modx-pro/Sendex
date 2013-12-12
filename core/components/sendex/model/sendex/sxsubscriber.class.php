<?php
class sxSubscriber extends xPDOSimpleObject {

	public function save($cacheFlag = null) {
		$hash = sha1(uniqid(sha1($this->user_id . $this->newsletter_id . $this->email), true));

		$this->set('code', $hash);
		return parent::save($cacheFlag);
	}

}