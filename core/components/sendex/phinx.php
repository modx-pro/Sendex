<?php

/**
 * Phinx configuration for Sendex (MODX Revolution 2).
 *
 * Uses MODX database settings. CLI: vendor/bin/phinx migrate -c phinx.php
 */

require_once __DIR__ . '/model/sendex/sxphinxconfig.class.php';

if (!isset($modx)) {
    $modxConfigPath = dirname(__FILE__, 4) . '/config.core.php';

    if (!file_exists($modxConfigPath)) {
        die('MODX config.core.php not found. Please ensure MODX is properly installed.');
    }

    if (!defined('MODX_CORE_PATH')) {
        require_once $modxConfigPath;
    }

    if (!defined('MODX_CORE_PATH')) {
        die('MODX_CORE_PATH not defined in config.core.php');
    }

    if (!class_exists('modX')) {
        require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    }

    $modx = new modX();
    $modx->initialize('mgr');
}

$dbConfig = sxPhinxConfig::buildDbConfig($modx);
$migrationTable = sxPhinxConfig::migrationTableName($dbConfig['table_prefix']);

return array(
    'paths' => array(
        'migrations' => __DIR__ . '/migrations',
        'seeds' => __DIR__ . '/seeds',
    ),
    'environments' => array(
        'default_migration_table' => $migrationTable,
        'default_environment' => 'production',
        'production' => $dbConfig,
        'development' => $dbConfig,
    ),
    'version_order' => 'creation',
);
