<?php

require_once dirname(__FILE__) . '/sxnewslettersubscription.class.php';

/**
 * Resolve whether guest subscribe requires email confirmation (#38).
 */
class sxSubscribeConfirm
{
    /**
     * Snippet &confirmEmail= overrides system setting sendex_confirm_email.
     *
     * @param object $modx modX
     * @param array $scriptProperties snippet properties
     * @return bool
     */
    public static function isRequired($modx, array $scriptProperties = array())
    {
        if (array_key_exists('confirmEmail', $scriptProperties)) {
            return self::parseBool($scriptProperties['confirmEmail'], true);
        }

        return self::parseBool($modx->getOption('sendex_confirm_email', null, true), true);
    }

    /**
     * @param mixed $value
     * @param bool $default
     * @return bool
     */
    public static function parseBool($value, $default = true)
    {
        if ($value === null || $value === '') {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, array('0', 'false', 'no', 'off'), true)) {
            return false;
        }
        if (in_array($normalized, array('1', 'true', 'yes', 'on'), true)) {
            return true;
        }

        return $default;
    }

    /**
     * Guest subscribe: email confirm or immediate active subscription (#38).
     *
     * @param sxNewsletterSubscription $subscription
     * @param string $email
     * @param int $userId
     * @param int $linkTTL
     * @param bool $requireConfirm
     * @return array{status:string,hash?:string,message?:string}
     */
    public static function guestSubscribe(
        sxNewsletterSubscription $subscription,
        $email = '',
        $userId = 0,
        $linkTTL = 1800,
        $requireConfirm = true
    ) {
        if (!$requireConfirm) {
            $result = $subscription->subscribe($userId, $email, 'guest');
            if ($result === true) {
                return array('status' => 'subscribed');
            }
            if ($result === false) {
                return array('status' => 'invalid');
            }

            return array(
                'status'  => 'error',
                'message' => (string) $result,
            );
        }

        $response = $subscription->checkEmail($email, $userId, $linkTTL);
        if ($response === true) {
            return array('status' => 'already');
        }
        if ($response === false) {
            return array('status' => 'invalid');
        }

        return array(
            'status' => 'confirm',
            'hash'   => $response,
        );
    }
}
