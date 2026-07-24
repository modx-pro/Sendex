<?php

/**
 * Subscriber identity within one newsletter: match by user_id OR email.
 */
class sxSubscriberMatch
{
    /**
     * @param int $userId
     * @param string $email
     * @return bool
     */
    public static function hasIdentity($userId, $email)
    {
        return (int) $userId > 0 || self::normalizeEmail($email) !== '';
    }

    /**
     * @param mixed $email
     * @return string
     */
    public static function normalizeEmail($email)
    {
        return is_string($email) ? trim($email) : '';
    }

    /**
     * Whether a subscriber row matches the identity (OR when both set).
     *
     * @param int $newsletterId
     * @param int $userId
     * @param string $email
     * @param int $rowNewsletterId
     * @param int $rowUserId
     * @param string $rowEmail
     * @return bool
     */
    public static function rowMatches(
        $newsletterId,
        $userId,
        $email,
        $rowNewsletterId,
        $rowUserId,
        $rowEmail
    ) {
        if ((int) $rowNewsletterId !== (int) $newsletterId) {
            return false;
        }

        $userId = (int) $userId;
        $email = self::normalizeEmail($email);
        $rowUserId = (int) $rowUserId;
        $rowEmail = self::normalizeEmail($rowEmail);

        if ($userId > 0 && $email !== '') {
            return $rowUserId === $userId || strcasecmp($rowEmail, $email) === 0;
        }
        if ($userId > 0) {
            return $rowUserId === $userId;
        }
        if ($email !== '') {
            return strcasecmp($rowEmail, $email) === 0;
        }

        return false;
    }

    /**
     * xPDO where for newsletter + optional OR(user_id, email).
     *
     * @param int $newsletterId
     * @param int $userId
     * @param string $email
     * @return array|null
     */
    public static function whereClause($newsletterId, $userId, $email)
    {
        $userId = (int) $userId;
        $email = self::normalizeEmail($email);

        if (!self::hasIdentity($userId, $email)) {
            return null;
        }

        $where = array(
            'newsletter_id' => (int) $newsletterId,
        );

        if ($userId > 0 && $email !== '') {
            $where[] = array(
                'user_id'      => $userId,
                'OR:email:='   => $email,
            );
        } elseif ($userId > 0) {
            $where['user_id'] = $userId;
        } else {
            $where['email'] = $email;
        }

        return $where;
    }

    /**
     * Evaluate FakeQuery/xPDO-like where against a subscriber row (tests + stubs).
     *
     * @param array $where
     * @param object $subscriber
     * @return bool
     */
    public static function matchesWhere(array $where, $subscriber)
    {
        foreach ($where as $key => $value) {
            if (is_int($key) && is_array($value)) {
                if (!self::matchesOrGroup($value, $subscriber)) {
                    return false;
                }
                continue;
            }

            if ((string) $subscriber->get($key) !== (string) $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $group
     * @param object $subscriber
     * @return bool
     */
    private static function matchesOrGroup(array $group, $subscriber)
    {
        foreach ($group as $key => $value) {
            if (strpos($key, 'OR:') === 0) {
                $field = self::fieldFromOrKey($key);
                if ($field !== '' && strcasecmp((string) $subscriber->get($field), (string) $value) === 0) {
                    return true;
                }
                continue;
            }

            if ((string) $subscriber->get($key) === (string) $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $key e.g. OR:email:=
     * @return string
     */
    private static function fieldFromOrKey($key)
    {
        $parts = explode(':', $key);
        return isset($parts[1]) ? $parts[1] : '';
    }

    /**
     * If user_id empty, resolve modUser by profile email.
     *
     * @param object $xpdo
     * @param int $userId
     * @param string $email
     * @return int
     */
    public static function resolveUserId($xpdo, $userId, $email)
    {
        $userId = (int) $userId;
        if ($userId > 0) {
            return $userId;
        }

        $email = self::normalizeEmail($email);
        if ($email === '') {
            return 0;
        }

        $profile = $xpdo->getObject('modUserProfile', array('email' => $email));
        if (!$profile) {
            return 0;
        }

        return (int) $profile->get('internalKey');
    }
}
