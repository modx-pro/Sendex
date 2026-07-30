<?php

/**
 * Ensures queue claim/retry columns exist when Phinx did not run (#105).
 *
 * Protects mgr/cron from xPDO SELECT failures on claimed_at/attempts/expires_at.
 */
class sxQueueSchema
{
    /**
     * @var bool
     */
    private static $ensured = false;

    /**
     * @var string
     */
    private static $queueClass = 'sxQueue';

    /**
     * Reset once-per-process guard (unit tests only).
     *
     * @return void
     */
    public static function resetEnsured()
    {
        self::$ensured = false;
    }

    /**
     * @param object $xpdo
     * @return bool true when schema is ready (or already ensured)
     */
    public static function ensureClaimFields($xpdo)
    {
        if (self::$ensured) {
            return true;
        }

        if (!is_object($xpdo) || !method_exists($xpdo, 'getTableName')) {
            return false;
        }

        $table = (string) $xpdo->getTableName(self::$queueClass);
        if ($table === '') {
            return false;
        }

        $columns = self::listColumns($xpdo, $table);
        if ($columns === null) {
            return false;
        }

        $alters = array();
        if (!isset($columns['claimed_at'])) {
            $alters[] = 'ADD COLUMN `claimed_at` TIMESTAMP NULL DEFAULT NULL';
        }
        if (!isset($columns['attempts'])) {
            $alters[] = 'ADD COLUMN `attempts` INT(10) UNSIGNED NOT NULL DEFAULT 0';
        }
        if (!isset($columns['expires_at'])) {
            $alters[] = 'ADD COLUMN `expires_at` TIMESTAMP NULL DEFAULT NULL';
        }

        if ($alters === array()) {
            self::$ensured = true;

            return true;
        }

        $sql = 'ALTER TABLE ' . $table . ' ' . implode(', ', $alters);
        $stmt = self::prepare($xpdo, $sql);
        if (!$stmt || !$stmt->execute()) {
            // Concurrent bootstrap may add the same columns first.
            $retry = self::listColumns($xpdo, $table);
            if (
                is_array($retry)
                && isset($retry['claimed_at'], $retry['attempts'], $retry['expires_at'])
            ) {
                self::$ensured = true;

                return true;
            }

            if (method_exists($xpdo, 'log')) {
                $xpdo->log(
                    defined('xPDO::LOG_LEVEL_ERROR') ? constant('xPDO::LOG_LEVEL_ERROR') : 0,
                    '[Sendex] Failed to ensure queue claim columns: ' . $sql
                );
            }

            return false;
        }

        self::$ensured = true;

        return true;
    }

    /**
     * @param object $xpdo
     * @param string $sql
     * @return object|false|null
     */
    private static function prepare($xpdo, $sql)
    {
        if (method_exists($xpdo, 'prepare')) {
            return $xpdo->prepare($sql);
        }

        if (!method_exists($xpdo, 'getConnection')) {
            return null;
        }

        $connection = $xpdo->getConnection();
        if (!is_object($connection) || !method_exists($connection, 'prepare')) {
            return null;
        }

        return $connection->prepare($sql);
    }

    /**
     * @param object $xpdo
     * @param string $table
     * @return array<string,true>|null
     */
    private static function listColumns($xpdo, $table)
    {
        $stmt = self::prepare($xpdo, 'SHOW COLUMNS FROM ' . $table);
        if (!$stmt || !$stmt->execute()) {
            return null;
        }

        if (!method_exists($stmt, 'fetchAll')) {
            return null;
        }

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return null;
        }

        $columns = array();
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $name = '';
            if (isset($row['Field'])) {
                $name = (string) $row['Field'];
            } elseif (isset($row[0])) {
                $name = (string) $row[0];
            }
            if ($name !== '') {
                $columns[strtolower($name)] = true;
            }
        }

        return $columns;
    }
}
