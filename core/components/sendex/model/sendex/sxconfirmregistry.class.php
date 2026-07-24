<?php

/**
 * Confirm-hash registry: consume on read, restore when subscribe did not succeed.
 */
class sxConfirmRegistry
{
    const PATH_PREFIX = '/sendex/subscribe/';
    const DEFAULT_TTL = 1800;

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
     * @param object $xpdo
     * @param string $hash
     * @param array $entry
     * @param int $ttl
     * @return bool
     */
    public static function restore($xpdo, $hash, array $entry, $ttl = self::DEFAULT_TTL)
    {
        $instance = self::register($xpdo);
        if (!$instance) {
            return false;
        }

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
     * @return object|null
     */
    private static function register($xpdo)
    {
        $registry = $xpdo->getService('registry', 'registry.modRegistry');
        if (!$registry) {
            return null;
        }

        return $registry->getRegister('user', 'registry.modDbRegister');
    }
}
