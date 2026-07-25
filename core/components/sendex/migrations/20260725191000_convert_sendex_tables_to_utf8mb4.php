<?php

use Phinx\Migration\AbstractMigration;

/**
 * Normalize Sendex tables to utf8mb4 (#105 M7).
 */
class ConvertSendexTablesToUtf8mb4 extends AbstractMigration
{
    /** @var string[] */
    private $sendexTableNames = array(
        'sendex_newsletters',
        'sendex_subscribers',
        'sendex_queue',
    );

    public function up()
    {
        foreach ($this->sendexTableNames as $tableName) {
            if (!$this->hasTable($tableName)) {
                continue;
            }
            $this->execute(
                'ALTER TABLE ' . $this->quotedTableName($tableName)
                . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
        }
    }

    public function down()
    {
        foreach ($this->sendexTableNames as $tableName) {
            if (!$this->hasTable($tableName)) {
                continue;
            }
            $this->execute(
                'ALTER TABLE ' . $this->quotedTableName($tableName)
                . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci'
            );
        }
    }

    /**
     * @param string $tableName
     * @return string
     */
    private function quotedTableName($tableName)
    {
        $adapter = $this->getAdapter();
        if (method_exists($adapter, 'getAdapterTableName')) {
            $tableName = $adapter->getAdapterTableName($tableName);
        } elseif ($adapter->hasOption('table_prefix')) {
            $tableName = (string) $adapter->getOption('table_prefix') . $tableName;
        }

        return $adapter->quoteTableName($tableName);
    }
}
