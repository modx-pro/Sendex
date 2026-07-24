<?php

use Phinx\Migration\AbstractMigration;

/**
 * Unique subscriber key: (newsletter_id, email) — one address per newsletter (#54).
 */
class SubscriberUniqueEmail extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('sendex_subscribers');

        if ($table->hasIndexByName('key')) {
            $table->removeIndexByName('key')->update();
        }

        if (!$table->hasIndexByName('key')) {
            $table
                ->addIndex(
                    array('newsletter_id', 'email'),
                    array(
                        'unique' => true,
                        'name' => 'key',
                    )
                )
                ->update();
        }
    }

    public function down()
    {
        $table = $this->table('sendex_subscribers');

        if ($table->hasIndexByName('key')) {
            $table->removeIndexByName('key')->update();
        }

        if (!$table->hasIndexByName('key')) {
            $table
                ->addIndex(
                    array('newsletter_id', 'user_id', 'email'),
                    array(
                        'unique' => true,
                        'name' => 'key',
                    )
                )
                ->update();
        }
    }
}
