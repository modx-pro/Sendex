<?php

require_once dirname(__FILE__) . '/sxuserplaceholders.class.php';

/**
 * User/Profile boundary helpers for MODX 2/3 (#74).
 */
class sxUserProfile
{
    /**
     * @param object|null $user modUser-like
     * @param object $xpdo
     * @return array|null Profile::toArray() or null
     */
    public static function profileArray($user, $xpdo)
    {
        if ($user === null) {
            return null;
        }

        $profile = null;
        if (method_exists($user, 'getOne')) {
            $profile = $user->getOne('Profile');
        }

        if ($profile && method_exists($profile, 'toArray')) {
            return $profile->toArray();
        }

        if (!method_exists($user, 'get')) {
            return null;
        }

        $userId = (int) $user->get('id');
        if ($userId <= 0) {
            return null;
        }

        $profileObject = $xpdo->getObject('modUserProfile', array('internalKey' => $userId));
        if ($profileObject && method_exists($profileObject, 'toArray')) {
            return $profileObject->toArray();
        }

        return null;
    }

    /**
     * @param object $user modUser-like
     * @return bool
     */
    public static function isActiveUser($user)
    {
        if (!is_object($user) || !method_exists($user, 'get')) {
            return false;
        }

        return (bool) $user->get('active');
    }

    /**
     * @param object $modx
     * @param object $user modUser-like
     * @param array $base
     * @return array
     */
    public static function authenticatedPlaceholders($modx, $user, array $base)
    {
        $userData = method_exists($user, 'toArray') ? $user->toArray() : array();

        return sxUserPlaceholders::mergeAuthenticated(
            $userData,
            self::profileArray($user, $modx),
            $base
        );
    }
}
