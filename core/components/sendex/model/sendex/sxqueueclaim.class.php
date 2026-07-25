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
     * @var string
     */
    private static $queueClass = 'sxQueue';

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

        $claimed = self::tryAtomicDelete($xpdo, $id);
        if ($claimed !== null) {
            return $claimed;
        }

        /** @var object|null $row */
        $row = $xpdo->getObject(self::$queueClass, $id);
        if (!$row) {
            return false;
        }

        return (bool) $row->remove();
    }

    /**
     * @param object $xpdo
     * @param int $id
     * @return bool|null true/false when SQL path is available, null when fallback needed
     */
    private static function tryAtomicDelete($xpdo, $id)
    {
        if (!method_exists($xpdo, 'getConnection') || !method_exists($xpdo, 'getTableName')) {
            return null;
        }

        $connection = $xpdo->getConnection();
        if (!is_object($connection) || !method_exists($connection, 'prepare')) {
            return null;
        }

        $table = (string) $xpdo->getTableName(self::$queueClass);
        if ($table === '') {
            return null;
        }

        $stmt = $connection->prepare('DELETE FROM ' . $table . ' WHERE id = ?');
        if (!$stmt || !$stmt->execute(array($id))) {
            return false;
        }

        if (!method_exists($stmt, 'rowCount')) {
            return true;
        }

        return (int) $stmt->rowCount() > 0;
    }
}
