<?php

/**
 * Atomic-ish claim of a queue row before send (#55).
 *
 * Strategy: remove-before-send. First worker deletes the row; peers see a miss.
 * On mail failure the caller must requeue from in-memory fields.
 */
class sxQueueClaim
{
    /**
     * @param object $xpdo
     * @param int $id
     * @return bool true when this worker owns the row
     */
    public static function tryClaim($xpdo, $id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }

        /** @var object|null $row */
        $row = $xpdo->getObject('sxQueue', $id);
        if (!$row) {
            return false;
        }

        return (bool) $row->remove();
    }
}
