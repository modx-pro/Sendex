<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxsubscriberegistry.class.php';

class SubscribeRegistryTest extends TestCase
{
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
}
