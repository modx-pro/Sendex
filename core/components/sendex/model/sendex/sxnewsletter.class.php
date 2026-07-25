<?php

require_once dirname(__FILE__) . '/sxnewslettersubscription.class.php';
require_once dirname(__FILE__) . '/sxnewsletterqueuebuilder.class.php';
require_once dirname(__FILE__) . '/sxnewsletterdispatch.class.php';
require_once dirname(__FILE__) . '/sxnewslettergroupsubscribe.class.php';
require_once dirname(__FILE__) . '/sxsendexevent.class.php';

/**
 * Newsletter persistence + thin API over subscription / queue helpers (#62).
 * Queue rows cascade via composite `Queues` in schema on parent::remove() (#59).
 */
class sxNewsletter extends xPDOSimpleObject
{
    /**
     * @return int|string
     */
    public function addQueues()
    {
        $builder = new sxNewsletterQueueBuilder($this);

        return $builder->addQueues();
    }

    /**
     * Queue subscribers and send pending rows for this newsletter (#29).
     *
     * @param array $options Optional sxQueueSender::flush options
     * @return array{success:bool,message:string,queued:int,sent:int,skipped:int,failed:int}
     */
    public function sendToSubscribers(array $options = array())
    {
        return sxNewsletterDispatch::queueAndSend($this, $options);
    }

    /**
     * @param int $user_id
     * @param string $email
     * @return int
     */
    public function isSubscribed($user_id = 0, $email = '')
    {
        return $this->subscription()->isSubscribed($user_id, $email);
    }

    /**
     * @param string $email
     * @param int $user_id
     * @param int $linkTTL
     * @return bool|string
     */
    public function checkEmail($email = '', $user_id = 0, $linkTTL = 1800)
    {
        return $this->subscription()->checkEmail($email, $user_id, $linkTTL);
    }

    /**
     * @param string $hash
     * @return bool|string
     */
    public function confirmEmail($hash)
    {
        return $this->subscription()->confirmEmail($hash);
    }

    /**
     * @param int $user_id
     * @param string $email
     * @return true|false|string
     */
    public function subscribe($user_id = 0, $email = '')
    {
        return $this->subscription()->subscribe($user_id, $email);
    }

    /**
     * Bulk subscribe active members of a user group (mgr add_group; #70).
     *
     * @param int $group_id
     * @return true|string[]
     */
    public function subscribeGroup($group_id = 0)
    {
        return sxNewsletterGroupSubscribe::subscribeGroup($this, $group_id);
    }

    /**
     * @param string $code
     * @return true|false|string
     */
    public function unSubscribe($code)
    {
        return $this->subscription()->unSubscribe($code);
    }

    /**
     * @param string $name
     * @param array $params
     * @return true|string
     */
    protected function invokeSendexEvent($name, array $params)
    {
        return sxSendexEvent::invoke($this->xpdo, $name, $params);
    }

    /**
     * @return sxNewsletterSubscription
     */
    protected function subscription()
    {
        return new sxNewsletterSubscription($this);
    }
}
