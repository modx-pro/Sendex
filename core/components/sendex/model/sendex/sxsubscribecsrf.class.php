<?php

/**
 * Frontend CSRF helper for subscribe/unsubscribe forms.
 */
class sxSubscribeCsrf
{
    public const SESSION_KEY = 'sendex_csrf_token';

    /**
     * @param object $modx
     * @param array $scriptProperties
     * @return bool
     */
    public static function isRequired($modx, array $scriptProperties = array())
    {
        if (array_key_exists('csrfProtect', $scriptProperties)) {
            return self::parseBool($scriptProperties['csrfProtect']);
        }

        return self::parseBool($modx->getOption('sendex_csrf_protect', null, false));
    }

    /**
     * @return string
     */
    public static function token()
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = array();
        }

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(16));
        }

        return (string) $_SESSION[self::SESSION_KEY];
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isValid($value)
    {
        $token = isset($_SESSION[self::SESSION_KEY]) ? (string) $_SESSION[self::SESSION_KEY] : '';
        if ($token === '') {
            return false;
        }

        return hash_equals($token, (string) $value);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private static function parseBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === null || $value === '') {
            return false;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, array('1', 'true', 'yes', 'on'), true);
    }
}
