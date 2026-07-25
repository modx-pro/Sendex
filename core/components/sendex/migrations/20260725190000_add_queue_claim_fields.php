<?php

use Phinx\Migration\AbstractMigration;

/**
 * Queue fields for atomic claim/retry flow (#105 M2, M11).
 */
class AddQueueClaimFields extends AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('sendex_queue')) {
            return;
        }

        $table = $this->table('sendex_queue');

        if (!$table->hasColumn('claimed_at')) {
            $table->addColumn('claimed_at', 'timestamp', array(
                'null' => true,
                'default' => null,
                'after' => 'timestamp',
            ));
        }
        if (!$table->hasColumn('attempts')) {
            $table->addColumn('attempts', 'integer', array(
                'limit' => 10,
                'null' => false,
                'default' => 0,
                'signed' => false,
                'after' => 'claimed_at',
            ));
        }
        if (!$table->hasColumn('expires_at')) {
            $table->addColumn('expires_at', 'timestamp', array(
                'null' => true,
                'default' => null,
                'after' => 'attempts',
            ));
        }

        // Keep queue link policy explicit: guest rows use 0, not NULL.
        if ($table->hasColumn('subscriber_id')) {
            $table->changeColumn('subscriber_id', 'integer', array(
                'limit' => 10,
                'null' => false,
                'default' => 0,
                'signed' => false,
            ));
        }

        $table->update();
    }

    public function down()
    {
        if (!$this->hasTable('sendex_queue')) {
            return;
        }

        $table = $this->table('sendex_queue');
        if ($table->hasColumn('expires_at')) {
            $table->removeColumn('expires_at');
        }
        if ($table->hasColumn('attempts')) {
            $table->removeColumn('attempts');
        }
        if ($table->hasColumn('claimed_at')) {
            $table->removeColumn('claimed_at');
        }
        if ($table->hasColumn('subscriber_id')) {
            $table->changeColumn('subscriber_id', 'integer', array(
                'limit' => 10,
                'null' => true,
                'default' => 0,
                'signed' => false,
            ));
        }

        $table->update();
    }
}
