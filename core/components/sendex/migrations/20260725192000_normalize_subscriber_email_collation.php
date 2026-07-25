<?php

use Phinx\Migration\AbstractMigration;

/**
 * Lowercase subscriber emails and enforce case-insensitive collation (#105 M8).
 */
class NormalizeSubscriberEmailCollation extends AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('sendex_subscribers')) {
            return;
        }

        $table = $this->quotedTableName('sendex_subscribers');
        $this->execute(
            'UPDATE ' . $table . ' SET email = LOWER(email) WHERE email IS NOT NULL'
        );
        $this->execute(
            'ALTER TABLE ' . $table
            . ' MODIFY email VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT \'\''
        );
    }

    public function down()
    {
        if (!$this->hasTable('sendex_subscribers')) {
            return;
        }

        $table = $this->quotedTableName('sendex_subscribers');
        $this->execute(
            'ALTER TABLE ' . $table
            . ' MODIFY email VARCHAR(191) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT \'\''
        );
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
