<?php

use Phinx\Migration\AbstractMigration;

/**
 * InnoDB for Sendex tables; queue index rename; backfill subscriber_id → sxSubscriber.id (#67 / #52).
 */
class InnodbQueueSubscriberLink extends AbstractMigration
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
            $this->convertToInnoDb($tableName);
        }

        $this->renameQueueSubscriberIndex();
        $this->backfillQueueSubscriberIds();
    }

    public function down()
    {
        $table = $this->table('sendex_queue');

        if ($table->hasIndexByName('subscriber_id')) {
            $table->removeIndexByName('subscriber_id')->update();
        }

        if (!$table->hasIndexByName('user_id')) {
            $table
                ->addIndex(
                    array('subscriber_id'),
                    array(
                        'name' => 'user_id',
                    )
                )
                ->update();
        }

        // Engine and data remaps are not reverted.
    }

    /**
     * @param string $tableName Bare name without MODX prefix
     */
    protected function convertToInnoDb($tableName)
    {
        if (!$this->hasTable($tableName)) {
            return;
        }

        $this->execute('ALTER TABLE ' . $this->quotedTableName($tableName) . ' ENGINE=InnoDB');
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

    protected function renameQueueSubscriberIndex()
    {
        if (!$this->hasTable('sendex_queue')) {
            return;
        }

        $table = $this->table('sendex_queue');

        if ($table->hasIndexByName('user_id')) {
            $table->removeIndexByName('user_id')->update();
        }

        if (!$table->hasIndexByName('subscriber_id')) {
            $table
                ->addIndex(
                    array('subscriber_id'),
                    array(
                        'name' => 'subscriber_id',
                    )
                )
                ->update();
        }
    }

    /**
     * Legacy rows stored modUser.id (or 0 for guests). Prefer correct sxSubscriber.id.
     */
    protected function backfillQueueSubscriberIds()
    {
        if (!$this->hasTable('sendex_queue') || !$this->hasTable('sendex_subscribers')) {
            return;
        }

        $queue = $this->quotedTableName('sendex_queue');
        $subscribers = $this->quotedTableName('sendex_subscribers');

        // user_id → subscriber PK when stored value is not already a PK in this newsletter
        $this->execute(
            'UPDATE ' . $queue . ' AS q'
            . ' INNER JOIN ' . $subscribers . ' AS s'
            . '   ON s.newsletter_id = q.newsletter_id'
            . '  AND s.user_id = q.subscriber_id'
            . '  AND q.subscriber_id > 0'
            . ' LEFT JOIN ' . $subscribers . ' AS already'
            . '   ON already.id = q.subscriber_id'
            . '  AND already.newsletter_id = q.newsletter_id'
            . ' SET q.subscriber_id = s.id'
            . ' WHERE already.id IS NULL'
        );

        // guests / zero: match by email within newsletter
        $this->execute(
            'UPDATE ' . $queue . ' AS q'
            . ' INNER JOIN ' . $subscribers . ' AS s'
            . '   ON s.newsletter_id = q.newsletter_id'
            . '  AND s.email = q.email_to'
            . '  AND q.email_to <> \'\''
            . ' SET q.subscriber_id = s.id'
            . ' WHERE q.subscriber_id = 0'
        );
    }
}
