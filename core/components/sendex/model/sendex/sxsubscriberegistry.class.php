<?php

require_once dirname(__FILE__) . '/sxsubscribermatch.class.php';

/**
 * Subscribe confirm hashes in modDbRegister (/sendex/subscribe/).
 */
class sxSubscribeRegistry
{
    const PATH_PREFIX = '/sendex/subscribe/';
    const DEFAULT_TTL = 1800;
    const EXPIRES_KEY = '_expires';
    const RATE_LIMIT_PREFIX = 'rate:';

    /**
     * @param object $xpdo
     * @param string $hash
     * @param array $entry
     * @param int $ttl
     * @return bool
     */
    public static function store($xpdo, $hash, array $entry, $ttl = self::DEFAULT_TTL)
    {
        $instance = self::register($xpdo);
        if (!$instance) {
            return false;
        }

        $ttl = (int) $ttl;
        if ($ttl < 1) {
            $ttl = self::DEFAULT_TTL;
        }

        $entry[self::EXPIRES_KEY] = time() + $ttl;

        $instance->connect();
        $instance->subscribe(self::PATH_PREFIX);
        $instance->send(
            self::PATH_PREFIX,
            array(
                $hash => $entry,
            ),
            array('ttl' => $ttl)
        );

        return true;
    }

    /**
     * @param object $xpdo
     * @param string $hash
     * @return array|null
     */
    public static function consume($xpdo, $hash)
    {
        $instance = self::register($xpdo);
        if (!$instance) {
            return null;
        }

        $instance->connect();
        $instance->subscribe(self::PATH_PREFIX . $hash);

        $messages = $instance->read(array('poll_limit' => 1));
        if (empty($messages[0])) {
            return null;
        }

        $entry = reset($messages);

        return is_array($entry) ? $entry : null;
    }

    /**
     * Re-store after a failed confirm; keeps remaining TTL from entry meta.
     *
     * @param object $xpdo
     * @param string $hash
     * @param array $entry
     * @return bool
     */
    public static function restore($xpdo, $hash, array $entry)
    {
        $ttl = self::remainingTtl($entry);
        unset($entry[self::EXPIRES_KEY]);

        return self::store($xpdo, $hash, $entry, $ttl);
    }

    /**
     * @param array $entry
     * @return int
     */
    public static function remainingTtl(array $entry)
    {
        if (!isset($entry[self::EXPIRES_KEY])) {
            return self::DEFAULT_TTL;
        }

        $left = (int) $entry[self::EXPIRES_KEY] - time();

        return $left > 0 ? $left : 1;
    }

    /**
     * @param object $xpdo
     * @param string $email
     * @param int $window
     * @return bool
     */
    public static function isConfirmRateLimited($xpdo, $email, $window)
    {
        $window = (int) $window;
        if ($window < 1) {
            return false;
        }

        $entry = self::consume($xpdo, self::rateLimitKey($email));
        if ($entry === null) {
            return false;
        }

        $ttl = self::remainingTtl($entry);
        self::restore($xpdo, self::rateLimitKey($email), $entry);

        return $ttl > 0;
    }

    /**
     * @param object $xpdo
     * @param string $email
     * @param int $window
     * @return bool
     */
    public static function touchConfirmRateLimit($xpdo, $email, $window)
    {
        $window = (int) $window;
        if ($window < 1) {
            return true;
        }

        return self::store(
            $xpdo,
            self::rateLimitKey($email),
            array(
                'email' => sxSubscriberMatch::normalizeEmail($email),
                'type' => 'confirm_limit',
            ),
            $window
        );
    }

    /**
     * Best-effort lock: deny when active window exists, otherwise create it.
     *
     * @param object $xpdo
     * @param string $email
     * @param int $window
     * @return bool
     */
    public static function claimConfirmRateLimit($xpdo, $email, $window)
    {
        $window = (int) $window;
        if ($window < 1) {
            return true;
        }

        $lockHandle = self::openRateLimitLock($email);
        if ($lockHandle && function_exists('flock')) {
            if (!flock($lockHandle, LOCK_EX)) {
                fclose($lockHandle);
                $lockHandle = null;
            }
        }

        $allowed = !self::isConfirmRateLimited($xpdo, $email, $window);
        if ($allowed) {
            $allowed = self::touchConfirmRateLimit($xpdo, $email, $window);
        }

        if ($lockHandle && function_exists('flock')) {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }

        return $allowed;
    }

    /**
     * Allow immediate retry after transient send failures.
     *
     * @param object $xpdo
     * @param string $email
     * @return void
     */
    public static function releaseConfirmRateLimit($xpdo, $email)
    {
        self::consume($xpdo, self::rateLimitKey($email));
    }

    /**
     * @param object $xpdo
     * @return object|null
     */
    private static function register($xpdo)
    {
        $registry = sxModxCompat::getRegistry($xpdo);
        if (!$registry) {
            return null;
        }

        return $registry->getRegister('user', 'registry.modDbRegister');
    }

    /**
     * @param string $email
     * @return string
     */
    private static function rateLimitKey($email)
    {
        return self::RATE_LIMIT_PREFIX . sha1(strtolower(trim((string) $email)));
    }

    /**
     * @param string $email
     * @return resource|false
     */
    private static function openRateLimitLock($email)
    {
        if (!function_exists('sys_get_temp_dir') || !function_exists('fopen')) {
            return false;
        }

        $path = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'sendex-confirm-' . sha1(strtolower(trim((string) $email))) . '.lock';

        return @fopen($path, 'c');
    }
}
