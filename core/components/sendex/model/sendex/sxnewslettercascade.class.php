<?php

/**
 * Related rows cleaned when a newsletter is removed (#59).
 */
class sxNewsletterCascade
{
    /**
     * Drop queued letters for a newsletter (orphans would still be sent by cron).
     *
     * @param object $xpdo modX / xPDO
     * @param int $newsletterId
     * @return bool|int
     */
    public static function deleteQueues($xpdo, $newsletterId)
    {
        return $xpdo->removeCollection('sxQueue', array(
            'newsletter_id' => (int) $newsletterId,
        ));
    }
}
