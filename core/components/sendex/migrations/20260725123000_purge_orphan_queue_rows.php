<?php

use Phinx\Migration\AbstractMigration;

/**
 * Remove queue rows whose newsletter was deleted before #59 cascade (#59).
 */
class PurgeOrphanQueueRows extends AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('sendex_queue') || !$this->hasTable('sendex_newsletters')) {
            return;
        }

        $queue = $this->quotedTableName('sendex_queue');
        $newsletters = $this->quotedTableName('sendex_newsletters');

        $this->execute(
            'DELETE q FROM ' . $queue . ' AS q'
            . ' LEFT JOIN ' . $newsletters . ' AS n ON n.id = q.newsletter_id'
            . ' WHERE n.id IS NULL'
        );
    }

    public function down()
    {
        // Data cleanup is not reverted.
    }

    /**
     * @param string $tableName Bare name without MODX prefix
     * @return string Quoted physical table name (with prefix)
     */
    protected function quotedTableName($tableName)
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
