<?php

require_once dirname(__FILE__) . '/sxnewslettersubscription.class.php';
require_once dirname(__FILE__) . '/sxnewsletterqueuebuilder.class.php';
require_once dirname(__FILE__) . '/sxnewslettercascade.class.php';
require_once dirname(__FILE__) . '/sxsendexevent.class.php';

/**
 * Newsletter persistence + thin API over subscription / queue helpers (#62).
 */
class sxNewsletter extends xPDOSimpleObject
{
    /**
     * Remove newsletter, subscribers (composite), and queue rows (#59).
     *
     * @param array $ancestors
     * @return bool
     */
    public function remove(array $ancestors = array())
    {
        sxNewsletterCascade::deleteQueues($this->xpdo, $this->get('id'));

        return parent::remove($ancestors);
    }

    /**
     * @return int|string
     */
    public function addQueues()
    {
        $builder = new sxNewsletterQueueBuilder($this);

        return $builder->addQueues();
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
