<?php

/**
 * Subscribe confirm hashes in modDbRegister (/sendex/subscribe/).
 */
class sxSubscribeRegistry
{
    const PATH_PREFIX = '/sendex/subscribe/';
    const DEFAULT_TTL = 1800;
    const EXPIRES_KEY = '_expires';

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
}
