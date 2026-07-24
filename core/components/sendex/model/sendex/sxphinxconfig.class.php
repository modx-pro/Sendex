<?php

/**
 * Phinx / MySQL charset helpers for Sendex (MODX 2, PHP 7.4+).
 */
class sxPhinxConfig
{
    /**
     * @param string|null $dsn
     * @return string|null
     */
    public static function extractDsnCharset($dsn)
    {
        if ($dsn === null || $dsn === '') {
            return null;
        }

        if (preg_match('/(?:^|;)charset=([^;]+)/i', $dsn, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    /**
     * @param string|null $charset
     * @param bool $preferUtf8mb4
     * @return string
     */
    public static function normalizeMysqlCharset($charset, $preferUtf8mb4 = false)
    {
        $normalized = strtolower(trim((string) $charset));
        $normalized = str_replace('-', '', $normalized);

        if ($normalized === '' || $normalized === 'utf8' || $normalized === 'utf8mb3') {
            return $preferUtf8mb4 ? 'utf8mb4' : 'utf8';
        }

        if ($normalized === 'utf8mb4') {
            return 'utf8mb4';
        }

        $safe = preg_replace('/[^a-z0-9_]/', '', $normalized);

        return $safe !== null && $safe !== '' ? $safe : 'utf8mb4';
    }

    /**
     * @param string $charset
     * @return string
     */
    public static function defaultMysqlCollation($charset)
    {
        if ($charset === 'utf8') {
            return 'utf8_general_ci';
        }
        if ($charset === 'utf8mb4') {
            return 'utf8mb4_unicode_ci';
        }

        return $charset . '_general_ci';
    }

    /**
     * @param string $tablePrefix
     * @return string
     */
    public static function migrationTableName($tablePrefix)
    {
        return (string) $tablePrefix . 'sendex_migrations';
    }

    /**
     * Build Phinx environments DB config from a MODX-like options getter.
     *
     * @param object $modx object with getOption($key, $options = null, $default = null)
     * @return array
     */
    public static function buildDbConfig($modx)
    {
        $dsnCharset = self::extractDsnCharset($modx->getOption('database_dsn', null, null));
        $databaseCharset = $modx->getOption('database_charset', null, null);

        if ($dsnCharset !== null) {
            $mysqlCharset = self::normalizeMysqlCharset($dsnCharset);
        } elseif ($databaseCharset !== null && trim((string) $databaseCharset) !== '') {
            $mysqlCharset = self::normalizeMysqlCharset($databaseCharset);
        } else {
            $mysqlCharset = self::normalizeMysqlCharset($modx->getOption('charset', null, 'utf8mb4'), true);
        }

        $mysqlCollation = $modx->getOption(
            'database_collation',
            null,
            $modx->getOption(
                'collation',
                null,
                self::defaultMysqlCollation($mysqlCharset)
            )
        );

        return array(
            'adapter' => 'mysql',
            'host' => $modx->getOption('host', null, 'localhost'),
            'name' => $modx->getOption('dbname'),
            'user' => $modx->getOption('username'),
            'pass' => $modx->getOption('password'),
            'port' => $modx->getOption('port', null, '3306'),
            'charset' => $mysqlCharset,
            'collation' => $mysqlCollation,
            'table_prefix' => $modx->getOption('table_prefix', null, ''),
        );
    }
}
