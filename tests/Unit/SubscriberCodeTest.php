<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxsubscribercode.class.php';

class SubscriberCodeTest extends TestCase
{
    public function testNeedsNewCodeForEmptyValues(): void
    {
        $this->assertTrue(sxSubscriberCode::needsNewCode(null));
        $this->assertTrue(sxSubscriberCode::needsNewCode(''));
        $this->assertFalse(sxSubscriberCode::needsNewCode('abc123'));
        $this->assertFalse(sxSubscriberCode::needsNewCode('0'));
    }

    public function testGenerateReturnsSha1Hex(): void
    {
        $code = sxSubscriberCode::generate(1, 2, 'a@example.com');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $code);
    }

    public function testSaveKeepsExistingCode(): void
    {
        $modx = new FakeModX();
        $subscriber = new sxSubscriber($modx);
        $subscriber->fromArray(array(
            'user_id' => 1,
            'newsletter_id' => 2,
            'email' => 'a@example.com',
            'code' => 'stable-code-from-email',
        ));

        $this->assertTrue($subscriber->save());
        $this->assertSame('stable-code-from-email', $subscriber->get('code'));
    }

    public function testSaveGeneratesCodeWhenMissing(): void
    {
        $modx = new FakeModX();
        $subscriber = new sxSubscriber($modx);
        $subscriber->fromArray(array(
            'user_id' => 1,
            'newsletter_id' => 2,
            'email' => 'a@example.com',
        ));

        $this->assertTrue($subscriber->save());
        $code = $subscriber->get('code');
        $this->assertNotSame('', $code);
        $this->assertNotNull($code);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $code);
    }
}
