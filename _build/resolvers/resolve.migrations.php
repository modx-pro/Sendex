<?php

/**
 * Run Phinx migrations on package install/upgrade (shared hosting safe, no CLI).
 */

use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Reconnect if MySQL wait_timeout killed the PDO handle.
 *
 * @param modX $modx
 */
function sendexReconnectMigrations($modx)
{
    if ($modx->pdo === null) {
        $modx->connect();

        return;
    }

    try {
        $result = @$modx->pdo->query('SELECT 1');
        if ($result !== false) {
            return;
        }
    } catch (PDOException $e) {
        // Connection lost
    }

    $modx->log(modX::LOG_LEVEL_WARN, '[Sendex] DB connection lost, reconnecting...');
    $modx->pdo = null;
    if ($modx->connection) {
        $modx->connection->pdo = null;
    }
    $modx->connect();
}

/** @var xPDOTransport $object */
/** @var array $options */
/** @var modX $modx */

if ($object->xpdo) {
    $modx = $object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            @ini_set('max_execution_time', '300');
            @ini_set('memory_limit', '256M');

            $componentPath = MODX_CORE_PATH . 'components/sendex/';
            $vendorAutoload = $componentPath . 'vendor/autoload.php';
            $phinxConfig = $componentPath . 'phinx.php';

            if (!file_exists($vendorAutoload)) {
                $modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[Sendex] Phinx vendor/autoload.php not found at: ' . $vendorAutoload
                );
                $modx->log(
                    modX::LOG_LEVEL_ERROR,
                    '[Sendex] Run "composer install --no-dev" in: ' . $componentPath . ' before building the package.'
                );
                break;
            }

            if (!file_exists($phinxConfig)) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[Sendex] Phinx config not found at: ' . $phinxConfig);
                break;
            }

            sendexReconnectMigrations($modx);

            try {
                if (!class_exists('Phinx\\Config\\Config')) {
                    require_once $vendorAutoload;
                }

                $configArray = require $phinxConfig;
                if (!isset($configArray['paths']['migrations'])) {
                    $modx->log(modX::LOG_LEVEL_ERROR, '[Sendex] Invalid Phinx config: missing migrations path');
                    break;
                }

                $config = new Config($configArray);
                $input = new StringInput('');
                $output = new BufferedOutput();
                $manager = new Manager($config, $input, $output);

                $modx->log(modX::LOG_LEVEL_INFO, '[Sendex] Starting database migrations...');

                try {
                    $manager->migrate('production');
                } catch (Exception $migrateEx) {
                    $modx->log(
                        modX::LOG_LEVEL_ERROR,
                        '[Sendex] Migration execution failed: ' . $migrateEx->getMessage()
                    );
                    throw $migrateEx;
                }

                $outputText = $output->fetch();
                if (!empty($outputText)) {
                    foreach (explode("\n", $outputText) as $line) {
                        if (trim($line) !== '') {
                            $modx->log(modX::LOG_LEVEL_INFO, '  ' . $line);
                        }
                    }
                }

                $modx->log(modX::LOG_LEVEL_INFO, '[Sendex] Database migrations completed');
            } catch (Exception $e) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[Sendex] Migration error: ' . $e->getMessage());
                $modx->log(modX::LOG_LEVEL_ERROR, '[Sendex] Stack trace: ' . $e->getTraceAsString());
            }

            sendexReconnectMigrations($modx);
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $modx->log(modX::LOG_LEVEL_INFO, '[Sendex] Database tables are preserved during uninstall');
            break;
    }
}

return true;
