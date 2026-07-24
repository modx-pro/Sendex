<?php

use Phinx\Migration\AbstractMigration;

/**
 * Create Sendex tables via xPDO Manager from model metadata.
 */
class InitialSchema extends AbstractMigration
{
    /** @var string[] */
    protected $modelClasses = array(
        'sxNewsletter',
        'sxSubscriber',
        'sxQueue',
    );

    public function up()
    {
        $modx = $this->bootModx();
        $modelPath = MODX_CORE_PATH . 'components/sendex/model/';
        $modx->addPackage('sendex', $modelPath);
        $manager = $modx->getManager();

        foreach ($this->modelClasses as $className) {
            $tableName = $modx->getTableName($className);
            $bare = trim($tableName, '`');

            $sql = "SHOW TABLES LIKE " . $this->adapter->getConnection()->quote($bare);
            $stmt = $this->adapter->getConnection()->query($sql);
            if ($stmt && $stmt->fetch()) {
                continue;
            }

            $manager->createObjectContainer($className);
        }
    }

    public function down()
    {
        // Keep data: do not drop tables on rollback.
    }

    /**
     * @return modX
     */
    protected function bootModx()
    {
        $modxConfigPath = dirname(__FILE__, 5) . '/config.core.php';
        if (!file_exists($modxConfigPath)) {
            throw new RuntimeException('MODX config.core.php not found');
        }

        if (!defined('MODX_CORE_PATH')) {
            require_once $modxConfigPath;
        }
        if (!defined('MODX_CORE_PATH')) {
            throw new RuntimeException('MODX_CORE_PATH not defined');
        }

        if (!class_exists('modX')) {
            require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
        }

        $modx = new modX();
        $modx->initialize('mgr');

        return $modx;
    }
}
