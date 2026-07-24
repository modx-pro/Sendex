<?php

/**
 * Merge authenticated user (+ optional Profile) into snippet placeholders.
 */
class sxUserPlaceholders
{
    /**
     * Profile overwrites user keys; $base (newsletter) overwrites both.
     *
     * @param array $userData modUser::toArray()
     * @param array|null $profileData Profile::toArray() or null when missing
     * @param array $base Existing placeholders (e.g. newsletter)
     * @return array
     */
    public static function mergeAuthenticated(array $userData, $profileData, array $base)
    {
        if (is_array($profileData)) {
            $userData = array_merge($userData, $profileData);
        }

        return array_merge($userData, $base);
    }
}
