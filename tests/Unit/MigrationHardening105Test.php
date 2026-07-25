<?php

use PHPUnit\Framework\TestCase;

class MigrationHardening105Test extends TestCase
{
    /** @var string */
    private $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function testQueueClaimAndUtf8MigrationsExist()
    {
        $this->assertFileExists(
            $this->root . '/core/components/sendex/migrations/20260725190000_add_queue_claim_fields.php'
        );
        $this->assertFileExists(
            $this->root . '/core/components/sendex/migrations/20260725191000_convert_sendex_tables_to_utf8mb4.php'
        );
        $this->assertFileExists(
            $this->root . '/core/components/sendex/migrations/20260725192000_normalize_subscriber_email_collation.php'
        );
    }

    public function testQueueMapContainsClaimRetryFields()
    {
        $queueMap = file_get_contents(
            $this->root . '/core/components/sendex/model/sendex/mysql/sxqueue.map.inc.php'
        );

        $this->assertStringContainsString("'claimed_at'", $queueMap);
        $this->assertStringContainsString("'attempts'", $queueMap);
        $this->assertStringContainsString("'expires_at'", $queueMap);
        $this->assertStringContainsString("'subscriber_id'   =>", $queueMap);
        $this->assertStringContainsString("'default'    => 0", $queueMap);
    }

    public function testSubscriberMapKeepsNullableEmail()
    {
        $subscriberMap = file_get_contents(
            $this->root . '/core/components/sendex/model/sendex/mysql/sxsubscriber.map.inc.php'
        );

        $this->assertStringContainsString("'email'         =>", $subscriberMap);
        $this->assertStringContainsString("'null'      => true", $subscriberMap);
    }

    public function testQueueClaimUsesAtomicUpdate()
    {
        $source = file_get_contents(
            $this->root . '/core/components/sendex/model/sendex/sxqueueclaim.class.php'
        );

        $this->assertStringContainsString('SET claimed_at = CURRENT_TIMESTAMP,', $source);
        $this->assertStringContainsString('attempts = attempts + 1,', $source);
        $this->assertStringContainsString('expires_at = DATE_ADD', $source);
        $this->assertStringContainsString('claimed_at IS NULL OR (expires_at IS NOT NULL AND expires_at < CURRENT_TIMESTAMP)', $source);
    }

    public function testMigrationsDeclareExpectedSchemaOperations()
    {
        $queueMigration = file_get_contents(
            $this->root . '/core/components/sendex/migrations/20260725190000_add_queue_claim_fields.php'
        );
        $this->assertStringContainsString("addColumn('claimed_at'", $queueMigration);
        $this->assertStringContainsString("addColumn('attempts'", $queueMigration);
        $this->assertStringContainsString("addColumn('expires_at'", $queueMigration);
        $this->assertStringContainsString("changeColumn('subscriber_id'", $queueMigration);

        $utfMigration = file_get_contents(
            $this->root . '/core/components/sendex/migrations/20260725191000_convert_sendex_tables_to_utf8mb4.php'
        );
        $this->assertStringContainsString('CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $utfMigration);

        $emailMigration = file_get_contents(
            $this->root . '/core/components/sendex/migrations/20260725192000_normalize_subscriber_email_collation.php'
        );
        $this->assertStringContainsString('SET email = LOWER(email) WHERE email IS NOT NULL', $emailMigration);
        $this->assertStringContainsString('COLLATE utf8mb4_unicode_ci', $emailMigration);
        $this->assertStringContainsString('utf8_general_ci NULL DEFAULT', $emailMigration);
    }
}
