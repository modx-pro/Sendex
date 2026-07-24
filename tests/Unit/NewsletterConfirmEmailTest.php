<?php

use PHPUnit\Framework\TestCase;

class NewsletterConfirmEmailTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    /** @var TestableNewsletter */
    private $newsletter;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        $this->newsletter = new TestableNewsletter($this->modx);
        $this->newsletter->set('id', 10);
        $this->newsletter->set('active', 1);
        $this->modx->newsletters[10] = $this->newsletter;
    }

    public function testEmptyHashReturnsFalse()
    {
        $this->assertFalse($this->newsletter->confirmEmail(''));
    }

    public function testMissingRegistryEntryReturnsFalse()
    {
        $this->assertFalse($this->newsletter->confirmEmail('unknown'));
    }

    public function testConfirmsForSameNewsletter()
    {
        $this->modx->registryEntries['hash1'] = array(
            'user_id'       => 4,
            'newsletter_id' => 10,
            'email'         => 'ok@example.com',
        );

        $this->assertTrue($this->newsletter->confirmEmail('hash1'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame('sxOnSubscribe', $this->modx->invoked[1][0]);
    }

    public function testConfirmsViaOtherActiveNewsletter()
    {
        $other = new TestableNewsletter($this->modx);
        $other->set('id', 20);
        $other->set('active', 1);
        $this->modx->newsletters[20] = $other;

        $this->modx->registryEntries['hash2'] = array(
            'user_id'       => 8,
            'newsletter_id' => 20,
            'email'         => 'other@example.com',
        );

        $this->assertTrue($this->newsletter->confirmEmail('hash2'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame(20, (int) $this->modx->subscribers[0]->get('newsletter_id'));
    }

    public function testReturnsFalseWhenOtherNewsletterInactive()
    {
        $other = new TestableNewsletter($this->modx);
        $other->set('id', 20);
        $other->set('active', 0);
        $this->modx->newsletters[20] = $other;

        $this->modx->registryEntries['hash3'] = array(
            'user_id'       => 8,
            'newsletter_id' => 20,
            'email'         => 'other@example.com',
        );

        $this->assertFalse($this->newsletter->confirmEmail('hash3'));
        $this->assertCount(0, $this->modx->subscribers);
    }
}
