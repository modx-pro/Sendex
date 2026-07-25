<?php

require_once dirname(__FILE__) . '/sxsubscriberegistry.class.php';
require_once dirname(__FILE__) . '/sxsubscribermatch.class.php';
require_once dirname(__FILE__) . '/sxsendexevent.class.php';

/**
 * Subscribe / unsubscribe / confirm flows for one newsletter.
 */
class sxNewsletterSubscription
{
    /** @var sxNewsletter */
    protected $newsletter;

    /**
     * @param sxNewsletter $newsletter
     */
    public function __construct(sxNewsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @param int $userId
     * @param string $email
     * @return int
     */
    public function isSubscribed($userId = 0, $email = '')
    {
        $subscriber = $this->findSubscriber($userId, $email);

        return $subscriber ? (int) $subscriber->id : 0;
    }

    /**
     * @param int $userId
     * @param string $email
     * @return sxSubscriber|null
     */
    protected function findSubscriber($userId = 0, $email = '')
    {
        $xpdo = $this->newsletter->xpdo;
        $userId = (int) $userId;
        $email = sxSubscriberMatch::normalizeEmail($email);

        if ($email === '' && $userId > 0) {
            /** @var modUserProfile $profile */
            $profile = $xpdo->getObject('modUserProfile', array('internalKey' => $userId));
            if ($profile) {
                $email = sxSubscriberMatch::normalizeEmail($profile->get('email'));
            }
        }

        $where = sxSubscriberMatch::whereClause($this->newsletter->get('id'), $userId, $email);
        if ($where === null) {
            return null;
        }

        $q = $xpdo->newQuery('sxSubscriber');
        $q->where($where);

        /** @var sxSubscriber|false $subscriber */
        $subscriber = $xpdo->getObject('sxSubscriber', $q);

        return $subscriber ?: null;
    }

    /**
     * @param string $email
     * @param int $userId
     * @param int $linkTTL
     * @return bool|string
     */
    public function checkEmail($email = '', $userId = 0, $linkTTL = 1800)
    {
        $xpdo = $this->newsletter->xpdo;

        if (empty($email) && $profile = $xpdo->getObject('modUserProfile', array('internalKey' => $userId))) {
            $email = $profile->get('email');
        }

        if (empty($email) || !preg_match('/.+@.+\..+/i', $email)) {
            return false;
        } elseif ($this->isSubscribed($userId, $email)) {
            return true;
        }

        $hash = sha1(uniqid(sha1($email), true));

        sxSubscribeRegistry::store(
            $xpdo,
            $hash,
            array(
                'user_id'       => $userId,
                'newsletter_id' => $this->newsletter->id,
                'email'         => $email,
            ),
            $linkTTL
        );

        return $hash;
    }

    /**
     * @param string $hash
     * @return bool|string
     */
    public function confirmEmail($hash)
    {
        $xpdo = $this->newsletter->xpdo;

        if (empty($hash)) {
            return false;
        }

        $entry = sxSubscribeRegistry::consume($xpdo, $hash);
        if ($entry === null) {
            return false;
        }

        $result = false;
        if ($this->newsletter->id != $entry['newsletter_id']) {
            /** @var sxNewsletter $newsletter */
            $newsletter = $xpdo->getObject('sxNewsletter', array(
                'id'     => $entry['newsletter_id'],
                'active' => 1,
            ));
            if ($newsletter) {
                $result = $newsletter->subscribe($entry['user_id'], $entry['email'], 'confirm');
            }
        } else {
            $result = $this->subscribe($entry['user_id'], $entry['email'], 'confirm');
        }

        if ($result !== true) {
            sxSubscribeRegistry::restore($xpdo, $hash, $entry);
        }

        return $result;
    }

    /**
     * @param int $userId
     * @param string $email
     * @param string $source snippet|ajax|confirm|group|mgr|guest
     * @return true|false|string
     */
    public function subscribe($userId = 0, $email = '', $source = 'snippet')
    {
        $xpdo = $this->newsletter->xpdo;
        $source = self::normalizeSource($source);

        if (empty($email) && $profile = $xpdo->getObject('modUserProfile', array('internalKey' => $userId))) {
            $email = $profile->get('email');
        }

        $email = sxSubscriberMatch::normalizeEmail($email);
        if ($email === '' || !preg_match('/.+@.+\..+/i', $email)) {
            return false;
        }

        $userId = sxSubscriberMatch::resolveUserId($xpdo, $userId, $email);

        if ($subscriber = $this->findSubscriber($userId, $email)) {
            $this->attachUserToSubscriber($subscriber, $userId, $email);

            return true;
        }

        $params = array(
            'newsletter'    => $this->newsletter,
            'newsletter_id' => $this->newsletter->id,
            'user_id'       => $userId,
            'email'         => $email,
            'subscriber'    => null,
            'source'        => $source,
        );

        $before = sxSendexEvent::invoke($xpdo, 'sxOnBeforeSubscribe', $params);
        if ($before !== true) {
            return $before;
        }

        /** @var sxSubscriber $subscriber */
        $subscriber = $xpdo->newObject('sxSubscriber');
        $subscriber->fromArray(array(
            'newsletter_id' => $this->newsletter->id,
            'user_id'       => $userId,
            'email'         => $email,
        ), '', true, true);

        if (!$subscriber->save()) {
            return false;
        }

        $params['subscriber'] = $subscriber;
        sxSendexEvent::invoke($xpdo, 'sxOnSubscribe', $params);

        return true;
    }

    /**
     * @param string $code
     * @param string $source snippet|ajax|mgr
     * @return true|false|string
     */
    public function unSubscribe($code, $source = 'snippet')
    {
        $xpdo = $this->newsletter->xpdo;
        $source = self::normalizeSource($source);

        /** @var sxSubscriber $subscriber */
        if (!$subscriber = $xpdo->getObject('sxSubscriber', array('code' => $code))) {
            return false;
        }

        if ((int) $subscriber->get('newsletter_id') !== (int) $this->newsletter->get('id')) {
            return false;
        }

        $params = array(
            'newsletter'    => $this->newsletter,
            'newsletter_id' => $this->newsletter->id,
            'user_id'       => $subscriber->get('user_id'),
            'email'         => $subscriber->get('email'),
            'code'          => $code,
            'subscriber'    => $subscriber,
            'source'        => $source,
        );

        $before = sxSendexEvent::invoke($xpdo, 'sxOnBeforeUnsubscribe', $params);
        if ($before !== true) {
            return $before;
        }

        if (!$subscriber->remove()) {
            return false;
        }

        sxSendexEvent::invoke($xpdo, 'sxOnUnsubscribe', $params);

        return true;
    }

    /**
     * @param string $source
     * @return string
     */
    public static function normalizeSource($source)
    {
        $source = strtolower(trim((string) $source));
        $allowed = array('snippet', 'ajax', 'confirm', 'group', 'mgr', 'guest');
        if (!in_array($source, $allowed, true)) {
            return 'snippet';
        }

        return $source;
    }

    /**
     * @param sxSubscriber $subscriber
     * @param int $userId
     * @param string $email
     */
    protected function attachUserToSubscriber($subscriber, $userId, $email)
    {
        $userId = (int) $userId;
        if ($userId <= 0 && $email === '') {
            return;
        }

        if (!$subscriber) {
            return;
        }

        $changed = false;
        if ($userId > 0 && (int) $subscriber->get('user_id') === 0) {
            $subscriber->set('user_id', $userId);
            $changed = true;
        }
        if ($email !== '' && strcasecmp((string) $subscriber->get('email'), $email) !== 0) {
            if ($userId > 0 && (int) $subscriber->get('user_id') === $userId) {
                $subscriber->set('email', $email);
                $changed = true;
            }
        }

        if ($changed) {
            $subscriber->save();
        }
    }
}
