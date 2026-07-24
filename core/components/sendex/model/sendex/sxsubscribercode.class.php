<?php

/**
 * Subscriber unsubscribe/confirm code generation rules.
 */
class sxSubscriberCode
{
    /**
     * @param mixed $code
     * @return bool
     */
    public static function needsNewCode($code)
    {
        return $code === null || $code === '';
    }

    /**
     * @param mixed $userId
     * @param mixed $newsletterId
     * @param mixed $email
     * @return string
     */
    public static function generate($userId, $newsletterId, $email)
    {
        return sha1(uniqid(sha1($userId . $newsletterId . $email), true));
    }
}
