<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxsubscriberegistry.class.php';

class SubscribeRegistryTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testRemainingTtlUsesExpiresMeta(): void
    {
        $ttl = sxSubscribeRegistry::remainingTtl(array(
            '_expires' => time() + 90,
        ));
        $this->assertLessThanOrEqual(90, $ttl);
        $this->assertGreaterThanOrEqual(88, $ttl);
    }

    public function testRemainingTtlFallsBackToDefault(): void
    {
        $this->assertSame(
            sxSubscribeRegistry::DEFAULT_TTL,
            sxSubscribeRegistry::remainingTtl(array('email' => 'a@example.com'))
        );
    }

    public function testRemainingTtlMinimumIsOne(): void
    {
        $this->assertSame(
            1,
            sxSubscribeRegistry::remainingTtl(array('_expires' => time() - 10))
        );
    }

    public function testStoreAndConsumeRoundtrip(): void
    {
        $stored = sxSubscribeRegistry::store(
            $this->modx,
            'hash1',
            array('email' => 'a@example.com'),
            120
        );
        $entry = sxSubscribeRegistry::consume($this->modx, 'hash1');

        $this->assertTrue($stored);
        $this->assertIsArray($entry);
        $this->assertSame('a@example.com', $entry['email']);
        $this->assertArrayHasKey('_expires', $entry);
    }

    public function testConfirmRateLimitTouchAndCheck(): void
    {
        $this->assertFalse(sxSubscribeRegistry::isConfirmRateLimited($this->modx, 'a@example.com', 60));
        sxSubscribeRegistry::touchConfirmRateLimit($this->modx, 'a@example.com', 60);
        $this->assertTrue(sxSubscribeRegistry::isConfirmRateLimited($this->modx, 'a@example.com', 60));
    }

    public function testConfirmRateLimitClaimAndRelease(): void
    {
        $this->assertTrue(sxSubscribeRegistry::claimConfirmRateLimit($this->modx, 'a@example.com', 60));
        $this->assertFalse(sxSubscribeRegistry::claimConfirmRateLimit($this->modx, 'a@example.com', 60));

        sxSubscribeRegistry::releaseConfirmRateLimit($this->modx, 'a@example.com');

        $this->assertTrue(sxSubscribeRegistry::claimConfirmRateLimit($this->modx, 'a@example.com', 60));
    }
}
