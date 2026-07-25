<?php

/**
 * Atomic claim of a queue row before send (#55, #105).
 *
 * Strategy: UPDATE claimed_at/attempts first, DELETE fallback for legacy schema.
 */
class sxQueueClaim
{
    /**
     * @var int
     */
    private const CLAIM_TTL_SECONDS = 900;

    /**
     * @var string
     */
    private static $queueClass = 'sxQueue';

    /**
     * @var string
     */
    private static $lastStrategy = 'none';

    /**
     * @param object $xpdo
     * @param int $id
     * @return bool true when this worker owns the row
     */
    public static function tryClaim($xpdo, $id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            self::$lastStrategy = 'none';
            return false;
        }

        $claimed = self::tryAtomicUpdateClaim($xpdo, $id);
        if ($claimed !== null) {
            self::$lastStrategy = 'lease';
            return $claimed;
        }

        $claimed = self::tryAtomicDelete($xpdo, $id);
        if ($claimed !== null) {
            self::$lastStrategy = 'delete';
            return $claimed;
        }

        /** @var object|null $row */
        $row = $xpdo->getObject(self::$queueClass, $id);
        if (!$row) {
            self::$lastStrategy = 'none';
            return false;
        }

        self::$lastStrategy = 'delete';
        return (bool) $row->remove();
    }

    /**
     * @return string
     */
    public static function lastStrategy()
    {
        return self::$lastStrategy;
    }

    /**
     * @param object $xpdo
     * @param int $id
     * @return bool|null true/false when SQL path is available, null when fallback needed
     */
    private static function tryAtomicUpdateClaim($xpdo, $id)
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

        $sql = 'UPDATE ' . $table
            . ' SET claimed_at = CURRENT_TIMESTAMP,'
            . ' attempts = attempts + 1,'
            . ' expires_at = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL ' . self::CLAIM_TTL_SECONDS . ' SECOND)'
            . ' WHERE id = ?'
            . ' AND (claimed_at IS NULL OR (expires_at IS NOT NULL AND expires_at < CURRENT_TIMESTAMP))';
        $stmt = $connection->prepare($sql);
        if (!$stmt) {
            return null;
        }
        if (!$stmt->execute(array($id))) {
            if (self::hasLegacySchemaError($stmt, $connection)) {
                return null;
            }
            return false;
        }

        if (!method_exists($stmt, 'rowCount')) {
            return true;
        }

        return (int) $stmt->rowCount() > 0;
    }

    /**
     * @param object $statement
     * @param object $connection
     * @return bool
     */
    private static function hasLegacySchemaError($statement, $connection)
    {
        $sources = array($statement, $connection);
        foreach ($sources as $source) {
            if (!is_object($source) || !method_exists($source, 'errorInfo')) {
                continue;
            }

            $info = $source->errorInfo();
            $code = isset($info[0]) ? (string) $info[0] : '';
            $message = isset($info[2]) ? strtolower((string) $info[2]) : '';
            if ($code === '42S22' || $code === '42S02') {
                return true;
            }
            if (strpos($message, 'unknown column') !== false || strpos($message, 'no such column') !== false) {
                return true;
            }
        }

        return false;
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
