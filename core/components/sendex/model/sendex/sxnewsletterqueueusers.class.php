<?php

/**
 * Batch-load modUser + Profile for addQueues (#63).
 */
class sxNewsletterQueueUsers
{
    /**
     * @param object $xpdo
     * @param int[] $userIds
     * @return array{eligible:array<int,array{user:array,profile:array}>,loadedIds:int[]}
     */
    public static function loadContexts($xpdo, array $userIds)
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if ($userIds === array()) {
            return array(
                'eligible'  => array(),
                'loadedIds' => array(),
            );
        }

        $profilesByUser = array();
        foreach ($xpdo->getCollection('modUserProfile', array('internalKey:IN' => $userIds)) as $profile) {
            $profilesByUser[(int) $profile->get('internalKey')] = $profile;
        }

        $eligible = array();
        $loadedIds = array();
        foreach ($xpdo->getCollection('modUser', array('id:IN' => $userIds)) as $user) {
            $id = (int) $user->get('id');
            $loadedIds[] = $id;
            $profile = isset($profilesByUser[$id]) ? $profilesByUser[$id] : null;
            if (!$profile || !sxUserProfile::isActiveUser($user) || $profile->get('blocked')) {
                continue;
            }

            $eligible[$id] = array(
                'user'    => $user->toArray(),
                'profile' => $profile->toArray(),
            );
        }

        return array(
            'eligible'  => $eligible,
            'loadedIds' => $loadedIds,
        );
    }
}
