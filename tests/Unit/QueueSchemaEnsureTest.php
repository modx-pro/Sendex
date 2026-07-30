<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxqueueschema.class.php';

class QueueSchemaEnsureTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        sxQueueSchema::resetEnsured();
    }

    public function testEnsureAddsMissingClaimColumns()
    {
        $connection = $this->modx->getConnection();
        $this->assertNotContains('claimed_at', $connection->queueSchemaColumns);

        $this->assertTrue(sxQueueSchema::ensureClaimFields($this->modx));
        $this->assertContains('claimed_at', $connection->queueSchemaColumns);
        $this->assertContains('attempts', $connection->queueSchemaColumns);
        $this->assertContains('expires_at', $connection->queueSchemaColumns);

        $alters = array_filter($connection->executions, static function ($item) {
            return stripos($item['sql'], 'ALTER TABLE ') === 0;
        });
        $this->assertCount(1, $alters);
    }

    public function testEnsureIsIdempotentWhenColumnsExist()
    {
        $connection = $this->modx->getConnection();
        $connection->queueSchemaColumns = array_merge($connection->queueSchemaColumns, array(
            'claimed_at',
            'attempts',
            'expires_at',
        ));

        $this->assertTrue(sxQueueSchema::ensureClaimFields($this->modx));
        $this->assertSame(array(), array_filter($connection->executions, static function ($item) {
            return stripos($item['sql'], 'ALTER TABLE ') === 0;
        }));

        $before = count($connection->executions);
        $this->assertTrue(sxQueueSchema::ensureClaimFields($this->modx));
        $this->assertSame($before, count($connection->executions));
    }

    public function testSendexConstructorWiresSchemaEnsure()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sendex.class.php'
        );

        $this->assertStringContainsString('sxQueueSchema::ensureClaimFields', $source);
        $this->assertStringContainsString('sxqueueschema.class.php', $source);
    }
}
