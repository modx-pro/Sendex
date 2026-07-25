<?php

use Phinx\Migration\AbstractMigration;

/**
 * Add index for subscriber user merge/group sync lookups (#103 P5).
 */
class SubscriberUserIdIndex extends AbstractMigration
{
    public function up()
    {
        if (!$this->hasTable('sendex_subscribers')) {
            return;
        }

        $table = $this->table('sendex_subscribers');
        if ($table->hasIndexByName('user_id')) {
            return;
        }

        $table
            ->addIndex(
                array('user_id'),
                array(
                    'name' => 'user_id',
                )
            )
            ->update();
    }

    public function down()
    {
        if (!$this->hasTable('sendex_subscribers')) {
            return;
        }

        $table = $this->table('sendex_subscribers');
        if ($table->hasIndexByName('user_id')) {
            $table->removeIndexByName('user_id')->update();
        }
    }
}
