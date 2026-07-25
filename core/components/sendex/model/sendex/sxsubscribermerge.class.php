<?php

require_once dirname(__FILE__) . '/sxsubscribermatch.class.php';
require_once dirname(__FILE__) . '/sxuserprofile.class.php';

/**
 * Attach guest subscriber rows to a modUser when email matches (#39).
 */
class sxSubscriberMerge
{
    /**
     * Set user_id on guest rows (user_id = 0) with the same email across newsletters.
     *
     * Policy: merge, never create a second row for the same newsletter email.
     *
     * @param object $xpdo
     * @param int $userId
     * @param string $email
     * @return int Number of rows updated
     */
    public static function attachGuestsByEmail($xpdo, $userId, $email)
    {
        $userId = (int) $userId;
        $email = sxSubscriberMatch::normalizeEmail($email);
        if ($userId <= 0 || $email === '') {
            return 0;
        }

        $updated = 0;
        foreach (self::findGuestRows($xpdo, $email) as $subscriber) {
            if ((int) $subscriber->get('user_id') !== 0) {
                continue;
            }
            if (strcasecmp((string) $subscriber->get('email'), $email) !== 0) {
                continue;
            }

            $subscriber->set('user_id', $userId);
            if ($subscriber->save()) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Resolve profile email from a modUser-like object.
     *
     * @param object $xpdo
     * @param object|null $user Object with get('id') and optional getOne('Profile')
     * @return string
     */
    public static function emailFromUser($xpdo, $user)
    {
        if ($user === null) {
            return '';
        }

        $profile = sxUserProfile::profileArray($user, $xpdo);
        if (!$profile) {
            return '';
        }

        return sxSubscriberMatch::normalizeEmail($profile['email']);
    }

    /**
     * Merge guests for an activated / saved user.
     *
     * @param object $xpdo
     * @param object|null $user
     * @return int
     */
    public static function attachGuestsForUser($xpdo, $user)
    {
        if ($user === null || !method_exists($user, 'get')) {
            return 0;
        }

        $userId = (int) $user->get('id');
        $email = self::emailFromUser($xpdo, $user);

        return self::attachGuestsByEmail($xpdo, $userId, $email);
    }

    /**
     * @param object $xpdo
     * @param string $email
     * @return object[]
     */
    private static function findGuestRows($xpdo, $email)
    {
        if (!method_exists($xpdo, 'getCollection')) {
            return array();
        }

        $q = $xpdo->newQuery('sxSubscriber');
        $q->where(array(
            'user_id' => 0,
        ));

        $rows = $xpdo->getCollection('sxSubscriber', $q);
        if (!is_array($rows)) {
            return array();
        }

        $matched = array();
        foreach ($rows as $subscriber) {
            if (strcasecmp((string) $subscriber->get('email'), $email) === 0) {
                $matched[] = $subscriber;
            }
        }

        return $matched;
    }
}
